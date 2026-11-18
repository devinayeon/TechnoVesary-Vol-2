<?php

namespace App\Services\Map;

use Exception;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeocodingService
{
    protected string $baseUrl;

    protected string $userAgent;

    protected int $timeout;

    protected bool $cacheEnabled;

    protected int $cacheTtl;

    public function __construct()
    {
        $this->baseUrl = rtrim((string) config('osm.nominatim.base_url', 'https://nominatim.openstreetmap.org'), '/');
        $this->userAgent = (string) config('osm.nominatim.user_agent', 'TechnoVesary-BankSampah/1.0 (contact@banksampah.test)');
        $this->timeout = (int) config('osm.nominatim.timeout', 10);
        $this->cacheEnabled = (bool) config('osm.cache.enabled', true);
        $this->cacheTtl = (int) config('osm.cache.ttl', 86400);
    }

    /**
     * Convert geographic coordinates (latitude, longitude) into readable address (Reverse Geocoding).
     *
     * @return array<string, mixed>|null
     */
    public function reverseGeocode(float $lat, float $lng): ?array
    {
        $cacheKey = sprintf('osm_geo_rev_%.4f_%.4f', $lat, $lng);

        if ($this->cacheEnabled && Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        try {
            $response = Http::withHeaders([
                'User-Agent' => $this->userAgent,
                'Accept-Language' => 'id,en;q=0.9',
            ])->timeout($this->timeout)->get("{$this->baseUrl}/reverse", [
                'format' => 'jsonv2',
                'lat' => $lat,
                'lon' => $lng,
                'addressdetails' => 1,
            ]);

            if ($response->successful() && ! empty($response->json('display_name'))) {
                $data = $response->json();
                $addr = $data['address'] ?? [];

                $result = [
                    'success' => true,
                    'display_name' => $data['display_name'],
                    'latitude' => (float) $data['lat'],
                    'longitude' => (float) $data['lon'],
                    'road' => $addr['road'] ?? $addr['pedestrian'] ?? $addr['suburb'] ?? '',
                    'neighbourhood' => $addr['neighbourhood'] ?? $addr['hamlet'] ?? '',
                    'subdistrict' => $addr['subdistrict'] ?? $addr['village'] ?? $addr['suburb'] ?? '',
                    'city' => $addr['city'] ?? $addr['regency'] ?? $addr['county'] ?? $addr['municipality'] ?? '',
                    'province' => $addr['state'] ?? '',
                    'postcode' => $addr['postcode'] ?? '',
                    'country' => $addr['country'] ?? 'Indonesia',
                ];

                if ($this->cacheEnabled) {
                    Cache::put($cacheKey, $result, $this->cacheTtl);
                }

                return $result;
            }
        } catch (Exception $e) {
            Log::warning('Nominatim reverse geocode error: '.$e->getMessage(), [
                'lat' => $lat,
                'lng' => $lng,
            ]);
        }

        return [
            'success' => false,
            'display_name' => sprintf('Titik Lokasi (%.5f, %.5f)', $lat, $lng),
            'latitude' => $lat,
            'longitude' => $lng,
        ];
    }

    /**
     * Search addresses by query string (Forward Geocoding).
     *
     * @return array<int, array<string, mixed>>
     */
    public function searchAddress(string $query, int $limit = 5): array
    {
        $cleanQuery = trim($query);
        if (mb_strlen($cleanQuery) < 3) {
            return [];
        }

        $cacheKey = 'osm_geo_search_'.md5(mb_strtolower($cleanQuery)."_{$limit}");

        if ($this->cacheEnabled && Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        try {
            $response = Http::withHeaders([
                'User-Agent' => $this->userAgent,
                'Accept-Language' => 'id,en;q=0.9',
            ])->timeout($this->timeout)->get("{$this->baseUrl}/search", [
                'format' => 'jsonv2',
                'q' => $cleanQuery,
                'countrycodes' => 'id',
                'limit' => $limit,
                'addressdetails' => 1,
            ]);

            if ($response->successful()) {
                $items = $response->json();
                $results = [];

                foreach ($items as $item) {
                    $results[] = [
                        'display_name' => $item['display_name'],
                        'latitude' => (float) $item['lat'],
                        'longitude' => (float) $item['lon'],
                        'type' => $item['type'] ?? '',
                        'category' => $item['category'] ?? '',
                    ];
                }

                if ($this->cacheEnabled) {
                    Cache::put($cacheKey, $results, $this->cacheTtl);
                }

                return $results;
            }
        } catch (Exception $e) {
            Log::warning('Nominatim search address error: '.$e->getMessage(), [
                'query' => $query,
            ]);
        }

        return [];
    }
}
