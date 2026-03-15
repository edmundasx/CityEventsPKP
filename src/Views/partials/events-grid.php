<?php
// events-grid.php (partial)

// Inputs (optional, can be overridden by the parent view)
$events = $events ?? [];
$gridId = $gridId ?? "eventsGrid";
$gridClass = $gridClass ?? "events-grid";
$emptyText = $emptyText ?? "Events nerasti";
$basePath = $basePath ?? "/events";

// Escape helper (prevents XSS)
$e = static fn($v) => htmlspecialchars((string) $v, ENT_QUOTES, "UTF-8");
?>

<div id="<?= $e($gridId) ?>" class="events-grid <?= $e($gridExtraClass) ?>">
  <?php if (empty($events)): ?>
    <div class="events-empty"><?= $e($emptyText) ?></div>
  <?php
      // Build event URL

      // Date with safe fallbacks
      // Build event URL
      // Date with safe fallbacks
      // Build event URL

      // Date with safe fallbacks
      // Build event URL
      // Date with safe fallbacks
      // Build event URL

      // Date with safe fallbacks
      // Build event URL
      // Date with safe fallbacks
      // Build event URL

      // Date with safe fallbacks
      // Build event URL
      // Date with safe fallbacks
      // Build event URL

      // Date with safe fallbacks
      // Build event URL
      // Date with safe fallbacks
      // Build event URL

      // Date with safe fallbacks
      // Build event URL
      // Date with safe fallbacks
      // Build event URL

      // Date with safe fallbacks
      // Build event URL
      // Date with safe fallbacks
      // Build event URL

      // Date with safe fallbacks
      // Build event URL
      // Date with safe fallbacks
      else: ?>
    <?php foreach ($events as $event): ?>
      <?php
      $id = $event["id"] ?? "";
      $href = rtrim($basePath, "/") . "/" . rawurlencode((string) $id);

      $title = $event["title"] ?? "";
      $date = $event["date"] ?? "";
      $time = $event["time"] ?? "";
      $loc = $event["location"] ?? "";
      $price = $event["price"] ?? "";
      $img = $event["image"] ?? "";
      ?>

      <a class="event-card" href="<?= $e($href) ?>">
        <div class="event-media">
          <?php if ($img !== ""): ?>
            <img class="event-image" src="<?= $e($img) ?>" alt="">
          <?php else: ?>
            <div class="event-image event-image--placeholder" aria-hidden="true"></div>
          <?php endif; ?>

          <?php if ($price !== ""): ?>
            <div class="event-price"><?= $e($price) ?></div>
          <?php endif; ?>
        </div>

        <div class="event-content">
          <div class="event-title"><?= $e($title) ?></div>

          <div class="event-meta">
            <div class="event-datetime"><?= $e($date) ?> <?= $e($time) ?></div>
            <div class="event-location"><?= $e($loc) ?></div>
          </div>
        </div>
      </a>
    <?php endforeach; ?>
  <?php endif; ?>
</div>
