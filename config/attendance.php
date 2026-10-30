<?php

$latitude = env('OFFICE_LATITUDE');
$longitude = env('OFFICE_LONGITUDE');

return [
    'office' => [
        'latitude' => $latitude === null || trim((string) $latitude) === '' ? null : (float) $latitude,
        'longitude' => $longitude === null || trim((string) $longitude) === '' ? null : (float) $longitude,
    ],
    'radius_meters' => (float) env('ATTENDANCE_RADIUS', 50),
    'gps_warning_accuracy_meters' => (float) env('GPS_MAX_ACCURACY', 200),
    'office_allowed_ip' => env('OFFICE_ALLOWED_IP'),
];
