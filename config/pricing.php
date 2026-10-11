<?php

return [
    'currency' => env('PRICING_CURRENCY', 'COP'),
    'version' => env('PRICING_VERSION', '2026-10-v1'),
    'routing_url' => env('ROUTING_URL', 'https://router.project-osrm.org'),
    'road_factor' => (float) env('PRICING_ROAD_FACTOR', 1.25),
    'vehicles' => [
        'carro' => [
            'base_fare' => (int) env('CAR_BASE_FARE', 4000),
            'per_km' => (int) env('CAR_FARE_PER_KM', 1200),
            'per_minute' => (int) env('CAR_FARE_PER_MINUTE', 150),
            'booking_fee' => (int) env('CAR_BOOKING_FEE', 800),
            'minimum_fare' => (int) env('CAR_MINIMUM_FARE', 7000),
            'fallback_speed_kmh' => 24,
        ],
        'moto' => [
            'base_fare' => (int) env('MOTO_BASE_FARE', 2500),
            'per_km' => (int) env('MOTO_FARE_PER_KM', 850),
            'per_minute' => (int) env('MOTO_FARE_PER_MINUTE', 100),
            'booking_fee' => (int) env('MOTO_BOOKING_FEE', 500),
            'minimum_fare' => (int) env('MOTO_MINIMUM_FARE', 4500),
            'fallback_speed_kmh' => 28,
        ],
    ],
];