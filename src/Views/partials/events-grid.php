<?php
$events = $events ?? [];
$gridId = $gridId ?? "eventsGrid";
$gridClass = $gridClass ?? "events-grid";
$gridExtraClass = $gridExtraClass ?? "";
$gridInitialVisible = isset($gridInitialVisible)
    ? max(0, (int) $gridInitialVisible)
    : 0;
$gridStartExpanded = !empty($gridStartExpanded);
$emptyText = $emptyText ?? "Events nerasti";
$base = $base ?? rtrim(str_replace("\\", "/", dirname($_SERVER["SCRIPT_NAME"] ?? "")), "/");
if ($base === "." || $base === "/") {
    $base = "";
}
$basePath = $basePath ?? ($base . "/events");

$e = static fn($v) => htmlspecialchars((string) $v, ENT_QUOTES, "UTF-8");
?>

<div
  id="<?= $e($gridId) ?>"
  class="<?= $e(trim($gridClass . " " . $gridExtraClass)) ?>"
  data-initial-visible="<?= $e((string) $gridInitialVisible) ?>"
  data-start-expanded="<?= $gridStartExpanded ? "1" : "0" ?>"
>
  <?php if (empty($events)): ?>
    <div class="events-empty js-events-empty"><?= $e($emptyText) ?></div>
  <?php else: ?>
    <?php foreach ($events as $index => $event): ?>
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

      <a
        class="event-card h-full"
        href="<?= $e($href) ?>"
        data-event-index="<?= $e((string) $index) ?>"
      >
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

        <div class="event-content h-full flex flex-col">
          <div class="event-title"><?= $e($title) ?></div>

          <div class="event-meta">
            <div class="event-datetime"><?= $e($date) ?> <?= $e($time) ?></div>
            <div class="event-location"><?= $e($loc) ?></div>
          </div>
        </div>
      </a>
    <?php endforeach; ?>
    <div class="events-empty js-events-empty" hidden><?= $e($emptyText) ?></div>
  <?php endif; ?>
</div>
