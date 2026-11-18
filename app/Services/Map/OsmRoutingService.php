<?php

namespace App\Services\Map;

use Exception;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OsmRoutingService
{
    protected string $baseUrl;

    protected string $profile;

    protected int $timeout;

    protected bool $cacheEnabled;

    protected int $cacheTtl;

    public function __construct()
    {
        $this->baseUrl = rtrim((string) config('osm.osrm.base_url', 'https://router.project-osrm.org'), '/');
        $this->profile = (string) config('osm.osrm.profile', 'driving');
        $this->timeout = (int) config('osm.osrm.timeout', 10);
        $this->cacheEnabled = (bool) config('osm.cache.enabled', true);
        $this->cacheTtl = (int) config('osm.cache.ttl', 86400);
    }

    /**
     * Get driving route between two coordinate points.
     *
     * @param  array<string, mixed>  $options
     * @return array<string, mixed>
     */
    public function getRoute(float $startLat, float $startLng, float $endLat, float $endLng, array $options = []): array
    {
        $profile = $options['profile'] ?? $this->profile;

        // Cache key with 4 decimal places (~11 meters precision)
        $cacheKey = sprintf(
            'osrm_route_%s_%.4f_%.4f_%.4f_%.4f',
            $profile,
            $startLat,
            $startLng,
            $endLat,
            $endLng
        );

        if ($this->cacheEnabled && Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        try {
            // OSRM expects coordinates in {longitude},{latitude} order
            $coordinates = sprintf('%.6f,%.6f;%.6f,%.6f', $startLng, $startLat, $endLng, $endLat);
            $url = "{$this->baseUrl}/route/v1/{$profile}/{$coordinates}";

            $response = Http::timeout($this->timeout)->get($url, [
                'overview' => 'full',
                'geometries' => 'geojson',
                'steps' => 'true',
            ]);

            if ($response->successful() && ($response->json('code') === 'Ok')) {
                $routeData = $response->json('routes.0');
                $distanceMeters = (float) ($routeData['distance'] ?? 0);
                $durationSeconds = (float) ($routeData['duration'] ?? 0);
                $rawCoordinates = $routeData['geometry']['coordinates'] ?? []; // GeoJSON [lng, lat]

                // Transform to Leaflet standard [[lat, lng], ...]
                $leafletLatLngs = array_map(function ($coord) {
                    return [(float) $coord[1], (float) $coord[0]];
                }, $rawCoordinates);

                $steps = [];
                if (! empty($routeData['legs'][0]['steps'])) {
                    foreach ($routeData['legs'][0]['steps'] as $step) {
                        $steps[] = [
                            'name' => $step['name'] ?? '',
                            'instruction' => $step['maneuver']['type'] ?? '',
                            'modifier' => $step['maneuver']['modifier'] ?? null,
                            'distance' => (float) ($step['distance'] ?? 0),
                            'duration' => (float) ($step['duration'] ?? 0),
                        ];
                    }
                }

                $result = [
                    'success' => true,
                    'distance_meters' => $distanceMeters,
                    'distance_km' => round($distanceMeters / 1000, 2),
                    'duration_seconds' => $durationSeconds,
                    'duration_minutes' => (int) ceil($durationSeconds / 60),
                    'summary' => $routeData['legs'][0]['summary'] ?? '',
                    'coordinates' => $leafletLatLngs,
                    'geojson' => $routeData['geometry'] ?? null,
                    'steps' => $steps,
                ];

                if ($this->cacheEnabled) {
                    Cache::put($cacheKey, $result, $this->cacheTtl);
                }

                return $result;
            }

            Log::warning('OSRM route calculation failed or returned non-OK', [
                'status' => $response->status(),
                'body' => $response->json(),
            ]);
        } catch (Exception $e) {
            Log::error('OSRM service connection error: '.$e->getMessage(), [
                'start' => [$startLat, $startLng],
                'end' => [$endLat, $endLng],
            ]);
        }

        // Graceful fallback (straight-line calculation / Haversine)
        return $this->fallbackStraightLineRoute($startLat, $startLng, $endLat, $endLng);
    }

    /**
     * Calculate distance and duration matrix between origins and destinations using OSRM Table API.
     *
     * @param  array<int, array{lat: float, lng: float}>  $origins
     * @param  array<int, array{lat: float, lng: float}>  $destinations
     * @return array<string, mixed>|null
     */
    public function getDistanceMatrix(array $origins, array $destinations): ?array
    {
        try {
            $allPoints = array_merge($origins, $destinations);
            $coordStrings = array_map(function ($p) {
                return sprintf('%.6f,%.6f', $p['lng'], $p['lat']);
            }, $allPoints);

            $coordinates = implode(';', $coordStrings);

            // Sources indexes: 0 to count($origins) - 1
            $sourceIndexes = implode(';', range(0, count($origins) - 1));
            // Destinations indexes: count($origins) to total - 1
            $destIndexes = implode(';', range(count($origins), count($allPoints) - 1));

            $url = "{$this->baseUrl}/table/v1/{$this->profile}/{$coordinates}";

            $response = Http::timeout($this->timeout)->get($url, [
                'sources' => $sourceIndexes,
                'destinations' => $destIndexes,
                'annotations' => 'duration,distance',
            ]);

            if ($response->successful() && $response->json('code') === 'Ok') {
                return [
                    'durations' => $response->json('durations'),
                    'distances' => $response->json('distances'),
                ];
            }
        } catch (Exception $e) {
            Log::error('OSRM Distance Matrix failed: '.$e->getMessage());
        }

        return null;
    }

    /**
     * Find the nearest available driver based on shortest road driving time/distance.
     *
     * @return array<string, mixed>|null
     */
    public function findNearestDriver(float $targetLat, float $targetLng, iterable $drivers): ?array
    {
        $eligibleDrivers = [];
        $origins = [];

        foreach ($drivers as $driver) {
            $user = $driver->user ?? $driver;
            if ($user && isset($user->latitude) && isset($user->longitude) && $user->latitude !== null && $user->longitude !== null) {
                $eligibleDrivers[] = $driver;
                $origins[] = [
                    'lat' => (float) $user->latitude,
                    'lng' => (float) $user->longitude,
                ];
            }
        }

        if (empty($eligibleDrivers)) {
            return null;
        }

        $destinations = [['lat' => $targetLat, 'lng' => $targetLng]];
        $matrix = $this->getDistanceMatrix($origins, $destinations);

        if ($matrix && ! empty($matrix['durations'])) {
            $bestIndex = null;
            $bestDuration = INF;

            foreach ($matrix['durations'] as $idx => $durations) {
                $duration = $durations[0] ?? null;
                if ($duration !== null && $duration < $bestDuration) {
                    $bestDuration = $duration;
                    $bestIndex = $idx;
                }
            }

            if ($bestIndex !== null) {
                $driver = $eligibleDrivers[$bestIndex];
                $distMeters = $matrix['distances'][$bestIndex][0] ?? 0;

                return [
                    'driver' => $driver,
                    'distance_km' => round($distMeters / 1000, 2),
                    'duration_minutes' => (int) ceil($bestDuration / 60),
                ];
            }
        }

        // Fallback: Naive Haversine Distance if matrix unavailable
        $nearest = null;
        $minDist = INF;

        foreach ($eligibleDrivers as $driver) {
            $user = $driver->user ?? $driver;
            $dist = $this->calculateHaversineDistance((float) $user->latitude, (float) $user->longitude, $targetLat, $targetLng);
            if ($dist < $minDist) {
                $minDist = $dist;
                $nearest = $driver;
            }
        }

        if ($nearest) {
            return [
                'driver' => $nearest,
                'distance_km' => round($minDist, 2),
                'duration_minutes' => (int) ceil(($minDist / 30) * 60), // assume 30 km/h average
            ];
        }

        return null;
    }

    /**
     * Fallback straight line route if OSRM is unreachable.
     *
     * @return array<string, mixed>
     */
    protected function fallbackStraightLineRoute(float $startLat, float $startLng, float $endLat, float $endLng): array
    {
        $distKm = $this->calculateHaversineDistance($startLat, $startLng, $endLat, $endLng);
        $durationMinutes = (int) ceil(($distKm / 30) * 60);

        return [
            'success' => false,
            'fallback' => true,
            'distance_meters' => $distKm * 1000,
            'distance_km' => round($distKm, 2),
            'duration_seconds' => $durationMinutes * 60,
            'duration_minutes' => $durationMinutes,
            'summary' => 'Jalur Langsung (Estimasi Garis Lurus)',
            'coordinates' => [
                [$startLat, $startLng],
                [$endLat, $endLng],
            ],
            'geojson' => [
                'type' => 'LineString',
                'coordinates' => [
                    [$startLng, $startLat],
                    [$endLng, $endLat],
                ],
            ],
            'steps' => [],
        ];
    }

    /**
     * Calculate Haversine distance in kilometers.
     */
    protected function calculateHaversineDistance(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $earthRadius = 6371; // km
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat / 2) * sin($dLat / 2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($dLon / 2) * sin($dLon / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }
}
