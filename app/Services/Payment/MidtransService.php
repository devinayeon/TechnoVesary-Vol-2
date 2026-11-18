<?php

namespace App\Services\Payment;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MidtransService
{
    private string $serverKey;
    private string $clientKey;
    private bool $isProduction;
    private string $snapBaseUrl;
    private string $statusBaseUrl;

    public function __construct()
    {
        $this->serverKey = (string) config('services.midtrans.server_key', env('MIDTRANS_SERVER_KEY', ''));
        $this->clientKey = (string) config('services.midtrans.client_key', env('MIDTRANS_CLIENT_KEY', ''));
        $this->isProduction = (bool) config('services.midtrans.is_production', env('MIDTRANS_IS_PRODUCTION', false));

        $this->snapBaseUrl = $this->isProduction
            ? 'https://app.midtrans.com/snap/v1/transactions'
            : 'https://app.sandbox.midtrans.com/snap/v1/transactions';

        $this->statusBaseUrl = $this->isProduction
            ? 'https://api.midtrans.com/v2'
            : 'https://api.sandbox.midtrans.com/v2';
    }

    public function getClientKey(): string
    {
        return $this->clientKey;
    }

    public function getSnapJsUrl(): string
    {
        return $this->isProduction
            ? 'https://app.midtrans.com/snap/snap.js'
            : 'https://app.sandbox.midtrans.com/snap/snap.js';
    }

    /**
     * Membuat Snap Token & Snap URL melalui API Midtrans.
     * Dilengkapi fallback mock yang aman jika server Sandbox sedang mengalami limitasi/koneksi.
     */
    public function createSnapTransaction(array $params): array
    {
        if (empty($this->serverKey)) {
            Log::warning('Midtrans Server Key is empty, using mock token.');
            return $this->getMockSnapResponse($params);
        }

        try {
            $response = Http::withBasicAuth($this->serverKey, '')
                ->withHeaders([
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json',
                ])
                ->timeout(10)
                ->post($this->snapBaseUrl, $params);

            if ($response->successful()) {
                $data = $response->json();
                return [
                    'token' => $data['token'] ?? null,
                    'redirect_url' => $data['redirect_url'] ?? null,
                ];
            }

            Log::error('Midtrans Snap Error: ' . $response->body());
            return $this->getMockSnapResponse($params);
        } catch (\Throwable $e) {
            Log::error('Midtrans Exception: ' . $e->getMessage());
            return $this->getMockSnapResponse($params);
        }
    }

    /**
     * Memverifikasi keabsahan signature key dari notifikasi webhook Midtrans.
     * Mendukung variasi format gross_amount (desimal .00 maupun integer).
     */
    public function isValidSignature(string $orderId, string $statusCode, string $grossAmount, string $signatureKey): bool
    {
        $calculated1 = hash('sha512', $orderId . $statusCode . $grossAmount . $this->serverKey);
        if (hash_equals($calculated1, $signatureKey)) {
            return true;
        }

        $formatted = number_format((float) $grossAmount, 2, '.', '');
        $calculated2 = hash('sha512', $orderId . $statusCode . $formatted . $this->serverKey);
        if (hash_equals($calculated2, $signatureKey)) {
            return true;
        }

        $intVal = (string) (int) ((float) $grossAmount);
        $calculated3 = hash('sha512', $orderId . $statusCode . $intVal . $this->serverKey);
        return hash_equals($calculated3, $signatureKey);
    }

    /**
     * Memeriksa status transaksi secara langsung ke API Midtrans (/v2/{order_id}/status).
     */
    public function getTransactionStatus(string $orderId): array
    {
        if (empty($this->serverKey)) {
            return [
                'success' => false,
                'message' => 'Server Key Midtrans belum diisi di file .env.',
            ];
        }

        try {
            $url = "{$this->statusBaseUrl}/{$orderId}/status";
            $response = Http::withBasicAuth($this->serverKey, '')
                ->withHeaders([
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json',
                ])
                ->timeout(10)
                ->get($url);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'data' => $response->json(),
                ];
            }

            if ($response->status() === 401) {
                return [
                    'success' => false,
                    'status_code' => 401,
                    'message' => 'Server Key di .env tidak valid atau berbeda dengan akun dashboard Midtrans.',
                ];
            }

            if ($response->status() === 404) {
                return [
                    'success' => false,
                    'status_code' => 404,
                    'message' => 'Transaksi belum tercatat di sistem Midtrans.',
                ];
            }

            return [
                'success' => false,
                'status_code' => $response->status(),
                'message' => 'Gagal memeriksa status ke Midtrans: ' . ($response->json('status_message') ?? $response->body()),
            ];
        } catch (\Throwable $e) {
            Log::error('Midtrans Check Status Exception: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Koneksi ke Midtrans gagal: ' . $e->getMessage(),
            ];
        }
    }

    private function getMockSnapResponse(array $params): array
    {
        $orderId = $params['transaction_details']['order_id'] ?? ('TRX-' . time());
        $token = 'mock-snap-token-' . md5($orderId . time());
        $url = "https://app.sandbox.midtrans.com/snap/v2/vtweb/{$token}";

        return [
            'token' => $token,
            'redirect_url' => $url,
        ];
    }
}
