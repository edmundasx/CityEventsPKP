<?php
$base = $base ?? "";
$container = "container-ce";
$homeMapEvents = $homeMapEvents ?? [];
$homeMapJson = json_encode(
    $homeMapEvents,
    JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES,
);
if ($homeMapJson === false) {
    $homeMapJson = "[]";
}

$searchIndexJson = $searchIndexJson ?? "[]";
$ltPlacesJson = $ltPlacesJson ?? "[]";
$ltMapTargetsJson = $ltMapTargetsJson ?? "[]";

$e = static fn($value) => htmlspecialchars(
    (string) $value,
    ENT_QUOTES,
    "UTF-8",
);
?>

<section
  class="hero"
  data-app-base="<?= $e($base ?? '') ?>"
  data-search-index="<?= $e($searchIndexJson) ?>"
  data-lt-places="<?= $e($ltPlacesJson) ?>"
  data-lt-map-targets="<?= $e($ltMapTargetsJson) ?>"
>
  <div
    id="homeHeroMap"
    class="home-hero-map-bg"
    data-events="<?= $e($homeMapJson) ?>"
  ></div>
  <?php
  $homeMapJsonInline = json_encode(
      $homeMapEvents,
      JSON_UNESCAPED_UNICODE |
          JSON_UNESCAPED_SLASHES |
          JSON_HEX_TAG |
          JSON_HEX_APOS |
          JSON_HEX_QUOT |
          JSON_HEX_AMP,
  );
  if ($homeMapJsonInline === false) {
      $homeMapJsonInline = "[]";
  }
  ?>
  <script type="application/json" id="homeMapEventsData"><?= $homeMapJsonInline ?></script>
  <div class="hero-glow"></div>

  <div class="hero-inner">
    <div class="<?= $container ?> w-full">
      <div class="hero-content">
        <div class="hero-discover">
          <h1 class="hero-title">Discover events for everything you love</h1>

          <p class="hero-lead">
            Find and join events, connect with organizers, or create your own event
          </p>
        </div>

        <div class="search-wrap">
          <div class="search-bar">
            <div class="search-field-wrap">
              <input
                id="searchInput"
                type="search"
                autocomplete="off"
                placeholder="Ieškoti renginių"
                class="search-input"
                aria-autocomplete="list"
                aria-controls="searchSuggestions"
                aria-expanded="false"
              >
              <ul
                id="searchSuggestions"
                class="search-dropdown"
                role="listbox"
                hidden
              ></ul>
            </div>
            <div class="search-field-wrap">
              <input
                id="locationInput"
                type="search"
                autocomplete="off"
                placeholder="Miestas"
                class="search-input"
                aria-autocomplete="list"
                aria-controls="locationSuggestions"
                aria-expanded="false"
              >
              <ul
                id="locationSuggestions"
                class="search-dropdown"
                role="listbox"
                hidden
              ></ul>
            </div>

            <button type="button" onclick="searchEvents()" class="search-btn">
              Ieškoti
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<section class="<?= $container ?> section-pad categories">
  <div
    id="homeCategoryBar"
    class="categories-content"
    data-expand-limit="20"
  >
    <?php foreach (($categoryPopularity ?? []) as $cat): ?>
      <button
        type="button"
        class="category"
        data-category="<?= $e((string) ($cat["key"] ?? "")) ?>"
        data-category-rank="<?= $e((string) ($cat["count"] ?? 0)) ?>"
      >
        <span class="category-label"><?= $e((string) ($cat["label"] ?? "")) ?></span>
      </button>
    <?php endforeach; ?>
    <button
      id="homeCategoryToggle"
      type="button"
      class="category shrink-0"
      hidden
      aria-expanded="false"
    >
      Daugiau
    </button>
  </div>
</section>

<section id="events" class="<?= $container ?> section">
  <div class="section-head">
    <div>
      <h2 class="section-title">Events tavo mieste</h2>
      <p class="section-subtitle">Discover the most interesting happenings near you</p>
    </div>
    <button
      id="homeEventsToggle"
      type="button"
      class="section-action"
      aria-expanded="false"
    >
      View all
    </button>
  </div>

  <?php
  $gridId = "eventsGrid";
  $gridClass = "events-grid";
  $gridExtraClass = "mt-6";
  $gridInitialVisible = 3;
  $gridStartExpanded = true;
  $emptyText = "Pagal pasirinktus kriterijus renginių nėra.";
  $basePath = ($base ?? "") . "/events";
  $events = $events ?? [];

  $partial = __DIR__ . "/../partials/events-grid.php";

  if (!is_file($partial)) {
      throw new RuntimeException("Missing partial: " . $partial);
  }

  require $partial;
  ?>
</section>

