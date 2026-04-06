<?php
declare(strict_types=1);

namespace App\Controllers;

/**
 * Proxies Nominatim reverse geocoding so the browser can stay same-origin and
 * requests include an identifiable User-Agent (Nominatim usage policy).
 */
final class GeocodeController
{
    public function reverse(): void
    {
        header("Content-Type: application/json; charset=utf-8");

        $lat = filter_input(INPUT_GET, "lat", FILTER_VALIDATE_FLOAT);
        $lon = filter_input(INPUT_GET, "lon", FILTER_VALIDATE_FLOAT);

        if (
            $lat === false ||
            $lat === null ||
            $lon === false ||
            $lon === null ||
            $lat < -90.0 ||
            $lat > 90.0 ||
            $lon < -180.0 ||
            $lon > 180.0
        ) {
            http_response_code(400);
            echo json_encode(["city" => null], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

            return;
        }

        $url = sprintf(
            "https://nominatim.openstreetmap.org/reverse?lat=%s&lon=%s&format=json&addressdetails=1",
            rawurlencode((string) $lat),
            rawurlencode((string) $lon),
        );

        $ctx = stream_context_create([
            "http" => [
                "timeout" => 6,
                "header" =>
                    "User-Agent: CityEventsPKP/1.0 (event directory)\r\n" .
                    "Accept: application/json\r\n" .
                    "Accept-Language: lt,en\r\n",
            ],
            "https" => [
                "timeout" => 6,
                "header" =>
                    "User-Agent: CityEventsPKP/1.0 (event directory)\r\n" .
                    "Accept: application/json\r\n" .
                    "Accept-Language: lt,en\r\n",
            ],
        ]);

        $body = @file_get_contents($url, false, $ctx);
        if ($body === false) {
            http_response_code(502);
            echo json_encode(["city" => null], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

            return;
        }

        /** @var mixed $data */
        $data = json_decode($body, true);
        if (!is_array($data)) {
            http_response_code(502);
            echo json_encode(["city" => null], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

            return;
        }

        $addr = $data["address"] ?? [];
        if (!is_array($addr)) {
            $addr = [];
        }

        $city =
            $addr["city"] ??
            $addr["town"] ??
            $addr["village"] ??
            $addr["city_district"] ??
            $addr["municipality"] ??
            $addr["county"] ??
            null;

        $city = $city !== null ? trim((string) $city) : null;
        if ($city === "") {
            $city = null;
        }

        echo json_encode(["city" => $city], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }
}
