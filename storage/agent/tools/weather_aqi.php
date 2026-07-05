<?php
$toolDefinition_weather_aqi = array ( 'type' => 'function', 'function' => array ( 'name' => 'weather_aqi', 'description' => 'Fetch real-time air quality index (AQI) and pollutant data from the free Open-Meteo Air Quality API (no API key required). Accepts a city name (uses geocoding) or explicit lat/lon. Returns European AQI, US AQI, PM2.5, PM10, NO2, O3, SO2, CO levels with human-readable quality descriptions. First environmental/air quality data capability.', 'parameters' => array ( 'type' => 'object', 'properties' => array ( 'city' => array ( 'type' => 'string', 'description' => 'City name (e.g. London, Tokyo).' ), 'latitude' => array ( 'type' => 'number', 'description' => 'Explicit latitude.' ), 'longitude' => array ( 'type' => 'number', 'description' => 'Explicit longitude.' ), 'timeout' => array ( 'type' => 'integer', 'description' => 'Timeout in seconds (5-30). Default: 15.' ) ), 'required' => array ( ) ) ) );


if (! function_exists('weather_aqi')) {
    function weather_aqi($city = null, $latitude = null, $longitude = null, $timeout = null) {
        $city = $city ?? '';
        $latitude = $latitude ?? null;
        $longitude = $longitude ?? null;
        $timeout = $timeout ?? 15;
        $timeout = max(5, min(30, (int)$timeout));

        $fetch_json = function($url, $t) {
            $ctx = stream_context_create([
                'http' => ['method' => 'GET', 'timeout' => $t, 'header' => "User-Agent: weather_aqi/1.0\r\n", 'ignore_errors' => true],
                'ssl' => ['verify_peer' => false, 'verify_peer_name' => false],
            ]);
            $body = @file_get_contents($url, false, $ctx);
            if ($body === false) return ['error' => error_get_last()['message'] ?? 'fetch failed', 'success' => false];
            $status = 200;
            if (isset($http_response_header)) {
                foreach ($http_response_header as $h) {
                    if (preg_match('#^HTTP/\d+\.\d+ (\d+)#', $h, $m)) { $status = (int)$m[1]; break; }
                }
            }
            if ($status >= 400) return ['error' => "HTTP $status", 'success' => false];
            $data = @json_decode($body, true);
            if ($data === null) return ['error' => 'Invalid JSON', 'success' => false];
            return ['data' => $data, 'status' => $status, 'success' => true];
        };

        $lat = null; $lon = null; $location_name = '';

        if (!empty($city)) {
            $geo = $fetch_json('https://geocoding-api.open-meteo.com/v1/search?name=' . urlencode($city) . '&count=5&language=en&format=json', $timeout);
            if (!$geo['success']) return ['success' => false, 'error' => 'Geocoding failed: ' . $geo['error']];
            if (!isset($geo['data']['results']) || empty($geo['data']['results'])) {
                return ['success' => false, 'error' => "City not found: '$city'. Try a different spelling or use lat/lon."];
            }
            $loc = $geo['data']['results'][0];
            $lat = $loc['latitude']; $lon = $loc['longitude'];
            $location_name = $loc['name'] . ', ' . ($loc['country'] ?? '');
        } elseif ($latitude !== null && $longitude !== null) {
            $lat = (float)$latitude; $lon = (float)$longitude;
            $location_name = "Lat $lat, Lon $lon";
        } else {
            return ['success' => false, 'error' => 'Provide a city name or lat/lon.'];
        }

        if ($lat < -90 || $lat > 90 || $lon < -180 || $lon > 180) {
            return ['success' => false, 'error' => "Invalid coordinates: lat=$lat, lon=$lon"];
        }

        $aqi_url = sprintf(
            'https://air-quality-api.open-meteo.com/v1/air-quality?latitude=%s&longitude=%s&current=european_aqi,us_aqi,pm2_5,pm10,nitrogen_dioxide,ozone,sulphur_dioxide,carbon_monoxide',
            $lat, $lon
        );

        $aqi = $fetch_json($aqi_url, $timeout);
        if (!$aqi['success']) {
            return ['success' => false, 'error' => 'Air Quality API failed: ' . ($aqi['error'] ?? 'unknown')];
        }

        $data = $aqi['data'];
        $current = $data['current'] ?? [];
        $units = $data['current_units'] ?? [];

        if (empty($current)) {
            return ['success' => false, 'error' => 'No air quality data returned.'];
        }

        // European AQI level descriptions
        $euaqi_levels = [
            [0, 20, 'Very Good', "\xf0\x9f\x9f\xa2"],
            [20, 40, 'Good', "\xf0\x9f\x9f\xa2"],
            [40, 60, 'Fair', "\xf0\x9f\x9f\xa1"],
            [60, 80, 'Moderate', "\xf0\x9f\x9f\xa0"],
            [80, 100, 'Poor', "\xf0\x9f\x94\xb4"],
            [100, 200, 'Very Poor', "\xf0\x9f\x9f\xa3"],
            [200, 1000, 'Extremely Poor', "\xf0\x9f\x9f\xa4"],
        ];

        $euaqi = $current['european_aqi'] ?? null;
        $euaqi_desc = 'Unknown'; $euaqi_icon = '';
        if ($euaqi !== null) {
            foreach ($euaqi_levels as $level) {
                if ($euaqi >= $level[0] && $euaqi < $level[1]) {
                    $euaqi_desc = $level[2]; $euaqi_icon = $level[3]; break;
                }
            }
        }

        $pollutants = [];
        $names = [
            'pm2_5' => 'PM2.5 (Fine Particulates)',
            'pm10' => 'PM10 (Coarse Particulates)',
            'nitrogen_dioxide' => "NO\xe2\x82\x82 (Nitrogen Dioxide)",
            'ozone' => "O\xe2\x82\x83 (Ozone)",
            'sulphur_dioxide' => "SO\xe2\x82\x82 (Sulphur Dioxide)",
            'carbon_monoxide' => 'CO (Carbon Monoxide)',
        ];

        foreach ($names as $key => $name) {
            if (isset($current[$key])) {
                $pollutants[] = [
                    'id' => $key, 'name' => $name,
                    'value' => $current[$key],
                    'unit' => $units[$key] ?? '',
                    'formatted' => $current[$key] . ' ' . ($units[$key] ?? ''),
                ];
            }
        }

        return [
            'success' => true,
            'location' => $location_name,
            'coordinates' => ['lat' => $lat, 'lon' => $lon],
            'source' => 'Open-Meteo Air Quality API',
            'european_aqi' => ['value' => $euaqi, 'description' => $euaqi_desc, 'icon' => $euaqi_icon],
            'us_aqi' => ['value' => $current['us_aqi'] ?? null],
            'pollutants' => $pollutants,
        ];
    }
}