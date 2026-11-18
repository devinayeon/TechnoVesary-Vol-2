<?php

return [

    /*
    |--------------------------------------------------------------------------
    | OSRM (Open Source Routing Machine) Configuration
    |--------------------------------------------------------------------------
    |
    | Base URL endpoint for OSRM routing service. Defaults to the public demo
    | server, but can be switched to a self-hosted Docker instance for high
    | throughput in production.
    |
    */
    'osrm' => [
        'base_url' => env('OSRM_BASE_URL', 'https://router.project-osrm.org'),
        'profile' => env('OSRM_PROFILE', 'driving'), // driving, bike, foot
        'timeout' => (int) env('OSRM_TIMEOUT', 10),
    ],

    /*
    |--------------------------------------------------------------------------
    | Nominatim OpenStreetMap Geocoding Configuration
    |--------------------------------------------------------------------------
    |
    | Nominatim requires a valid custom User-Agent identifying the application
    | to comply with its Acceptable Use Policy.
    |
    */
    'nominatim' => [
        'base_url' => env('NOMINATIM_BASE_URL', 'https://nominatim.openstreetmap.org'),
        'user_agent' => env('NOMINATIM_USER_AGENT', env('APP_NAME', 'TechnoVesary').' BankSampah/1.0 (contact@banksampah.test)'),
        'timeout' => (int) env('NOMINATIM_TIMEOUT', 10),
    ],

    /*
    |--------------------------------------------------------------------------
    | Cache Settings
    |--------------------------------------------------------------------------
    |
    | Cache duration in seconds for routes and geocoding to prevent excessive
    | calls to external APIs.
    |
    */
    'cache' => [
        'enabled' => (bool) env('OSM_CACHE_ENABLED', true),
        'ttl' => (int) env('OSM_CACHE_TTL', 86400), // 24 hours
    ],

    /*
    |--------------------------------------------------------------------------
    | Map Center Default Coordinates (Indonesia / Jakarta Default)
    |--------------------------------------------------------------------------
    */
    'defaults' => [
        'latitude' => (float) env('MAP_DEFAULT_LATITUDE', -6.2088),
        'longitude' => (float) env('MAP_DEFAULT_LONGITUDE', 106.8456),
        'zoom' => (int) env('MAP_DEFAULT_ZOOM', 13),
    ],

    'basemap_api_key' => env('BASEMAP_API_KEY'),

];
