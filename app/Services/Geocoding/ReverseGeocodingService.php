<?php

namespace App\Services\Geocoding;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ReverseGeocodingService
{
    /**
     * Translate coordinates (lat, lng) to a detailed Arabic address string.
     * Uses Photon OpenStreetMap as primary engine and BigDataCloud as secondary fallback.
     * Caches results for 30 days to maximize performance.
     */
    public function reverseGeocode(float $lat, float $lng): array
    {
        $cacheKey = sprintf('reverse_geo_%.4f_%.4f', $lat, $lng);

        return Cache::remember($cacheKey, now()->addDays(30), function () use ($lat, $lng) {
            // Tier 1: Photon (OpenStreetMap)
            try {
                $response = Http::timeout(3)->get('https://photon.komoot.io/reverse', [
                    'lat' => $lat,
                    'lon' => $lng,
                ]);

                if ($response->successful()) {
                    $props = $response->json('features.0.properties');
                    if (! empty($props)) {
                        return $this->formatPhotonResult($props, $lat, $lng);
                    }
                }
            } catch (\Throwable $e) {
                Log::debug('Photon reverse geocoding fallback triggered: ' . $e->getMessage());
            }

            // Tier 2: BigDataCloud Reverse Geocode
            try {
                $response = Http::timeout(3)->get('https://api.bigdatacloud.net/data/reverse-geocode-client', [
                    'latitude' => $lat,
                    'longitude' => $lng,
                    'localityLanguage' => 'ar',
                ]);

                if ($response->successful()) {
                    $data = $response->json();
                    if (! empty($data)) {
                        return $this->formatBigDataCloudResult($data, $lat, $lng);
                    }
                }
            } catch (\Throwable $e) {
                Log::debug('BigDataCloud reverse geocoding failed: ' . $e->getMessage());
            }

            // Tier 3: Generic fallback
            return [
                'formatted_address' => sprintf('اليمن - إحداثيات (%.4f, %.4f)', $lat, $lng),
                'short_address' => sprintf('موقع محدد (%.4f, %.4f)', $lat, $lng),
                'country' => 'اليمن',
                'governorate' => null,
                'district' => null,
                'street' => null,
                'lat' => $lat,
                'lng' => $lng,
                'source' => 'fallback',
            ];
        });
    }

    private function formatPhotonResult(array $p, float $lat, float $lng): array
    {
        $country = $p['country'] ?? 'اليمن';
        $governorate = $this->cleanGovernorate($p['state'] ?? $p['county'] ?? null);
        $district = $p['county'] ?? $p['district'] ?? $p['locality'] ?? null;
        $neighborhood = $p['locality'] ?? $p['district'] ?? null;
        $street = $p['street'] ?? null;
        $landmark = isset($p['name']) && $p['name'] !== $street && $p['name'] !== ($p['city'] ?? '') ? $p['name'] : null;

        $segments = array_filter([
            $country,
            $governorate,
            $district,
            $neighborhood !== $district ? $neighborhood : null,
            $street,
            $landmark,
        ]);

        $formatted = implode(' - ', array_unique($segments));

        return [
            'formatted_address' => $formatted ?: sprintf('إحداثيات (%.4f, %.4f)', $lat, $lng),
            'short_address' => implode(' - ', array_filter(array_unique([$governorate, $street ?? $district]))),
            'country' => $country,
            'governorate' => $governorate,
            'district' => $district,
            'street' => $street,
            'landmark' => $landmark,
            'lat' => $lat,
            'lng' => $lng,
            'source' => 'photon',
        ];
    }

    private function formatBigDataCloudResult(array $data, float $lat, float $lng): array
    {
        $country = $data['countryName'] ?? 'اليمن';
        $governorate = $this->cleanGovernorate($data['principalSubdivision'] ?? $data['city'] ?? null);
        $district = $data['locality'] ?? null;
        $city = $data['city'] ?? null;

        $segments = array_filter([
            $country,
            $governorate,
            $district,
            $city !== $governorate ? $city : null,
        ]);

        return [
            'formatted_address' => implode(' - ', array_unique($segments)),
            'short_address' => implode(' - ', array_filter(array_unique([$governorate, $district]))),
            'country' => $country,
            'governorate' => $governorate,
            'district' => $district,
            'street' => null,
            'landmark' => null,
            'lat' => $lat,
            'lng' => $lng,
            'source' => 'bigdatacloud',
        ];
    }

    private function cleanGovernorate(?string $name): ?string
    {
        if (! $name) return null;
        $cleaned = trim(preg_replace('/^(محافظة|منطقة)\s+/u', '', $name));
        if (str_contains($cleaned, 'أمانة العاصمة') || str_contains($cleaned, 'امانة العاصمة')) {
            return 'أمانة العاصمة';
        }
        return $cleaned;
    }
}
