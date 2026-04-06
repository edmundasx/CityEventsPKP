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

$e = static fn($value) => htmlspecialchars(
    (string) $value,
    ENT_QUOTES,
    "UTF-8",
);
?>

<section class="hero">
  <div
    id="homeHeroMap"
    class="home-hero-map-bg"
    data-events="<?= $e($homeMapJson) ?>"
  ></div>
  <div class="hero-glow"></div>

  <div class="<?= $container ?> hero-inner">
    <div class="hero-content">
      <h1 class="hero-title">Discover events for everything you love</h1>

      <p class="hero-lead">
        Find and join events, connect with organizers, or create your own event
      </p>

      <div class="search-wrap">
        <div class="search-bar">
          <input id="searchInput" type="text" placeholder="Search events" class="search-input">
          <input id="locationInput" type="text" placeholder="Location" class="search-input">

          <button type="button" onclick="searchEvents()" class="search-btn">
            Search
          </button>
        </div>
      </div>
    </div>
  </div>
</section>

<section class="<?= $container ?> section-pad categories">
  <div class="categories-content">
    <?php
    $cats = [
        ["music", "&#127925;", "Music"],
        ["arts", "&#127912;", "Art"],
        ["charity", "&#10084;&#65039;", "Charity"],
        ["business", "&#128188;", "Business"],
        ["education", "&#128218;", "Education"],
        ["food", "&#127869;&#65039;", "Food & Drinks"],
    ];
    foreach ($cats as [$key, $icon, $label]): ?>
      <div
        class="category"
        role="button"
        tabindex="0"
        data-category="<?= $e($key) ?>"
      >
        <span class="category-icon"><?= $icon ?></span>
        <span class="category-label"><?= $label ?></span>
      </div>
    <?php endforeach;
    ?>
  </div>
</section>

<section id="events" class="<?= $container ?> section">
  <div class="section-head">
    <div>
      <h2 class="section-title">Events tavo mieste</h2>
      <p class="section-subtitle">Discover the most interesting happenings near you</p>
    </div>
  </div>

  <div class="mt-4 flex justify-end">
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
  $emptyText = "Events nerasti";
  $basePath = ($base ?? "") . "/events";
  $events = $events ?? [];

  $partial = __DIR__ . "/../partials/events-grid.php";

  if (!is_file($partial)) {
      throw new RuntimeException("Missing partial: " . $partial);
  }

  require $partial;
  ?>
</section>

