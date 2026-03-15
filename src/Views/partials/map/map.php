<?php
declare(strict_types=1);

$events = $events ?? [];
$base = $base ?? "";

// map payload: keep it small and safe
$mapEvents = array_map(static function ($e) {
    return [
        "id" => (int) ($e["id"] ?? 0),
        "title" => (string) ($e["title"] ?? ""),
        "location" => (string) ($e["location"] ?? ""),
        "lat" => isset($e["lat"]) ? (float) $e["lat"] : null,
        "lng" => isset($e["lng"]) ? (float) $e["lng"] : null,
        "date" => (string) ($e["event_date"] ?? ""),
        "category" => (string) ($e["category"] ?? ""),
        "district" => (string) ($e["district"] ?? ""),
        "price_eur" => isset($e["price_eur"]) ? (float) $e["price_eur"] : null,
        "is_free" => (bool) ($e["is_free"] ?? false),
        "cover" => (string) ($e["cover_image"] ?? ""),
    ];
}, $events);
?>


<section class="map-card">
    <div class="map-card-shell">
        <div class="map-card-frame">
            <!-- Leaflet will render into this -->
            <div
                id="ce-map"
                class="map-canvas"
                data-events='<?= json_encode(
                    $mapEvents,
                    JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP,
                ) ?>'
            ></div>

            <!-- Custom zoom controls -->
            <div class="map-zoom">
                <button id="ce-zoom-in" type="button" class="map-zoom-button" aria-label="Zoom in">+</button>
                <div class="map-zoom-divider"></div>
                <button id="ce-zoom-out" type="button" class="map-zoom-button" aria-label="Zoom out">−</button>
            </div>

            <div class="map-attribution">
                © OpenStreetMap contributors | Leaflet
            </div>
        </div>
    </div>
</section>
