<?php

namespace App\Http\Controllers;

use App\Services\Map\GeocodingService;
use App\Services\Map\OsmRoutingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MapController extends Controller
{
    public function __construct(
        protected OsmRoutingService $routingService,
        protected GeocodingService  $geocodingService,
    ) {}

    /**
     * Hitung rute jalan raya antara dua koordinat via OSRM.
     *
     * GET /map/route
     * Params: start_lat, start_lng, end_lat, end_lng
     */
    public function route(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'start_lat' => ['required', 'numeric', 'between:-90,90'],
            'start_lng' => ['required', 'numeric', 'between:-180,180'],
            'end_lat'   => ['required', 'numeric', 'between:-90,90'],
            'end_lng'   => ['required', 'numeric', 'between:-180,180'],
        ]);

        $result = $this->routingService->getRoute(
            (float) $validated['start_lat'],
            (float) $validated['start_lng'],
            (float) $validated['end_lat'],
            (float) $validated['end_lng'],
        );

        return response()->json($result);
    }

    /**
     * Konversi koordinat (lat, lng) menjadi teks alamat — Reverse Geocoding via Nominatim.
     *
     * GET /map/reverse-geocode
     * Params: lat, lng
     */
    public function reverseGeocode(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'lat' => ['required', 'numeric', 'between:-90,90'],
            'lng' => ['required', 'numeric', 'between:-180,180'],
        ]);

        $result = $this->geocodingService->reverseGeocode(
            (float) $validated['lat'],
            (float) $validated['lng'],
        );

        return response()->json($result);
    }

    /**
     * Cari lokasi/alamat berdasarkan kata kunci — Forward Geocoding via Nominatim.
     *
     * GET /map/search
     * Params: q (query), limit (opsional, default 5)
     */
    public function search(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'q'     => ['required', 'string', 'min:3', 'max:200'],
            'limit' => ['sometimes', 'integer', 'min:1', 'max:10'],
        ]);

        $results = $this->geocodingService->searchAddress(
            $validated['q'],
            (int) ($validated['limit'] ?? 5),
        );

        return response()->json($results);
    }

    /**
     * Kembalikan konfigurasi default peta ke frontend (koordinat tengah, zoom).
     *
     * GET /map/config
     */
    public function config(): JsonResponse
    {
        return response()->json([
            'defaults' => [
                'latitude'  => config('osm.defaults.latitude', -6.2088),
                'longitude' => config('osm.defaults.longitude', 106.8456),
                'zoom'      => config('osm.defaults.zoom', 13),
            ],
            'tile_url' => 'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',
            'attribution' => '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
        ]);
    }
}
