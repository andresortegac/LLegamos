<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class RoutePricingService
{
    public function quote(string $vehicleType, float $originLat, float $originLng, float $destinationLat, float $destinationLng): array
    {
        $route = $this->route($originLat, $originLng, $destinationLat, $destinationLng, $vehicleType);
        $rates = config("pricing.vehicles.{$vehicleType}");

        if (!$rates) {
            throw new \InvalidArgumentException('Tipo de vehiculo sin configuracion tarifaria.');
        }

        $baseFare = (float) $rates['base_fare'];
        $distanceFare = round($route['distance_km'] * (float) $rates['per_km'], 2);
        $timeFare = round($route['duration_minutes'] * (float) $rates['per_minute'], 2);
        $bookingFee = (float) $rates['booking_fee'];
        $total = max((float) $rates['minimum_fare'], $baseFare + $distanceFare + $timeFare + $bookingFee);

        return [
            'distance_km' => round($route['distance_km'], 2),
            'estimated_duration_minutes' => max(1, (int) ceil($route['duration_minutes'])),
            'base_fare' => $baseFare,
            'distance_fare' => $distanceFare,
            'time_fare' => $timeFare,
            'booking_fee' => $bookingFee,
            'estimated_cost' => round($total, 2),
            'currency' => config('pricing.currency', 'COP'),
            'pricing_version' => config('pricing.version'),
            'route_source' => $route['source'],
        ];
    }

    private function route(float $originLat, float $originLng, float $destinationLat, float $destinationLng, string $vehicleType): array
    {
        try {
            $baseUrl = rtrim((string) config('pricing.routing_url'), '/');
            $response = Http::timeout(5)->retry(1, 100)->get(
                "{$baseUrl}/route/v1/driving/{$originLng},{$originLat};{$destinationLng},{$destinationLat}",
                ['overview' => 'false', 'steps' => 'false']
            );

            $route = $response->successful() ? data_get($response->json(), 'routes.0') : null;
            if ($route && ($route['distance'] ?? 0) > 0 && ($route['duration'] ?? 0) > 0) {
                return [
                    'distance_km' => $route['distance'] / 1000,
                    'duration_minutes' => $route['duration'] / 60,
                    'source' => 'osrm',
                ];
            }
        } catch (\Throwable) {
            // A deterministic fallback keeps quoting available during routing outages.
        }

        $straightDistance = $this->haversine($originLat, $originLng, $destinationLat, $destinationLng);
        $distance = $straightDistance * (float) config('pricing.road_factor', 1.25);
        $speed = (float) config("pricing.vehicles.{$vehicleType}.fallback_speed_kmh", 24);

        return [
            'distance_km' => $distance,
            'duration_minutes' => ($distance / max($speed, 1)) * 60,
            'source' => 'fallback',
        ];
    }

    private function haversine(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $earthRadius = 6371;
        $latDelta = deg2rad($lat2 - $lat1);
        $lngDelta = deg2rad($lng2 - $lng1);
        $a = sin($latDelta / 2) ** 2
            + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($lngDelta / 2) ** 2;

        return $earthRadius * 2 * atan2(sqrt($a), sqrt(1 - $a));
    }
}