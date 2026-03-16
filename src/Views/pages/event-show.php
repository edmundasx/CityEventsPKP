<?php
$base = $base ?? "";
$event = $event ?? null;

if (!is_array($event)) {
    $event = [];
}

$e = static fn($value) => htmlspecialchars(
    (string) $value,
    ENT_QUOTES,
    "UTF-8",
);

$titleText = $event["title"] ?? "Event details";
$date = $event["date"] ?? "";
$time = $event["time"] ?? "";
$location = $event["location"] ?? "";
$price = $event["price"] ?? "";
$description = $event["description"] ?? "";
$image = $event["image"] ?? "";
$category = $event["category"] ?? "";
$district = $event["district"] ?? "";
?>

<main class="container-ce section">
  <article class="event-detail">
    <header class="event-detail__header">
      <div class="event-detail__meta">
        <?php if ($category !== "" || $district !== ""): ?>
          <p class="event-detail__pill">
            <?= $e(trim($category . " " . ($district !== "" ? "· " . $district : ""))) ?>
          </p>
        <?php endif; ?>

        <h1 class="event-detail__title"><?= $e($titleText) ?></h1>

        <div class="event-detail__info">
          <?php if ($date !== "" || $time !== ""): ?>
            <div class="event-detail__info-item">
              <span class="event-detail__info-label">Data ir laikas</span>
              <span class="event-detail__info-value">
                <?= $e(trim($date . " " . $time)) ?>
              </span>
            </div>
          <?php endif; ?>

          <?php if ($location !== ""): ?>
            <div class="event-detail__info-item">
              <span class="event-detail__info-label">Vieta</span>
              <span class="event-detail__info-value"><?= $e($location) ?></span>
            </div>
          <?php endif; ?>

          <?php if ($price !== ""): ?>
            <div class="event-detail__info-item">
              <span class="event-detail__info-label">Kaina</span>
              <span class="event-detail__info-value"><?= $e($price) ?></span>
            </div>
          <?php endif; ?>
        </div>
      </div>

      <?php if ($image !== ""): ?>
        <div class="event-detail__media">
          <img
            src="<?= $e($image) ?>"
            alt="<?= $e($titleText) ?>"
            class="event-detail__image"
          >
        </div>
      <?php endif; ?>
    </header>

    <section class="event-detail__body">
      <h2 class="event-detail__section-title">Aprašymas</h2>
      <p class="event-detail__description">
        <?= nl2br($e($description !== "" ? $description : "Šiam renginiui dar nėra aprašymo.")) ?>
      </p>
    </section>
  </article>
</main>

