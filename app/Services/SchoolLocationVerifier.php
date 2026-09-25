<?php

namespace App\Services;

use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;

class SchoolLocationVerifier
{
    /**
     * @return array{latitude: float, longitude: float, accuracy: float, distance: float, verified_at: Carbon}
     */
    public function verify(mixed $latitude, mixed $longitude, mixed $accuracy): array
    {
        $latitude = $this->coordinate($latitude, -90, 90, 'latitude');
        $longitude = $this->coordinate($longitude, -180, 180, 'longitude');
        $accuracy = is_numeric($accuracy) ? (float) $accuracy : NAN;
        $schoolLatitude = config('school.latitude');
        $schoolLongitude = config('school.longitude');
        $radius = config('school.radius_meters');
        $maximumAccuracy = config('school.max_gps_accuracy');

        if (! is_finite($accuracy) || $accuracy < 0 || ! is_numeric($maximumAccuracy) || $accuracy > (float) $maximumAccuracy) {
            throw ValidationException::withMessages([
                'location_accuracy' => 'Akurasi GPS buruk. Coba periksa lokasi kembali di area terbuka.',
            ]);
        }

        if (! is_numeric($schoolLatitude) || ! is_numeric($schoolLongitude) || ! is_numeric($radius)
            || ! is_numeric($maximumAccuracy) || (float) $schoolLatitude < -90 || (float) $schoolLatitude > 90
            || (float) $schoolLongitude < -180 || (float) $schoolLongitude > 180 || (float) $radius <= 0
            || (float) $maximumAccuracy < 0) {
            throw ValidationException::withMessages([
                'location' => 'Koordinat sekolah belum dikonfigurasi dengan benar.',
            ]);
        }

        $distance = $this->haversineDistance($latitude, $longitude, (float) $schoolLatitude, (float) $schoolLongitude);
        if ($distance > (float) $radius) {
            throw ValidationException::withMessages([
                'location' => sprintf('Di luar area sekolah. Jarak %.0f meter, batas %.0f meter.', $distance, (float) $radius),
            ]);
        }

        return [
            'latitude' => $latitude,
            'longitude' => $longitude,
            'accuracy' => $accuracy,
            'distance' => $distance,
            'verified_at' => now(),
        ];
    }

    private function coordinate(mixed $value, float $minimum, float $maximum, string $field): float
    {
        if (! is_numeric($value) || ! is_finite((float) $value) || (float) $value < $minimum || (float) $value > $maximum) {
            throw ValidationException::withMessages([
                'location_'.$field => 'Koordinat GPS tidak valid.',
            ]);
        }

        return (float) $value;
    }

    private function haversineDistance(float $latitude, float $longitude, float $schoolLatitude, float $schoolLongitude): float
    {
        $earthRadius = 6371000;
        $latitudeDelta = deg2rad($schoolLatitude - $latitude);
        $longitudeDelta = deg2rad($schoolLongitude - $longitude);
        $a = sin($latitudeDelta / 2) ** 2
            + cos(deg2rad($latitude)) * cos(deg2rad($schoolLatitude)) * sin($longitudeDelta / 2) ** 2;

        return $earthRadius * 2 * atan2(sqrt($a), sqrt(1 - $a));
    }
}
