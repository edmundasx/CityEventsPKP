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
$priceRaw = isset($event["price_raw"]) ? (float) $event["price_raw"] : null;
$description = $event["description"] ?? "";
$image = $event["image"] ?? "";
$category = $event["category"] ?? "";
$district = $event["district"] ?? "";
$organizerName = trim((string) ($event["organizer_name"] ?? "CityEvents organizatorius"));
$organizerInitial = strtoupper(substr($organizerName !== "" ? $organizerName : "C", 0, 1));

$priceBadge = "Nemokama";
if ($priceRaw !== null && $priceRaw > 0) {
    $priceBadge = $price !== "" ? $price : "€" . number_format($priceRaw, 2, ".", "");
}
$aboutText = $description !== "" ? $description : "Šiam renginiui dar nėra aprašymo.";
?>

<style>
  .event-show-page { padding: 1.5rem 0 3rem; }
  .event-show-grid { display: grid; gap: 1.5rem; grid-template-columns: minmax(0, 1fr); }
  .event-show-main { display: grid; gap: 1.25rem; min-width: 0; }
  .event-show-tag { display: inline-flex; width: fit-content; border-radius: 9999px; background: #fff1eb; color: #ff6b35; font-size: 0.75rem; font-weight: 700; letter-spacing: 0.02em; padding: 0.35rem 0.65rem; text-transform: lowercase; }
  .event-show-title { margin-top: 0.75rem; font-size: clamp(1.9rem, 3.4vw, 2.6rem); font-weight: 800; line-height: 1.12; color: #232b38; }
  .event-show-card { border: 1px solid #e2e8f0; border-radius: 0.75rem; background: #fff; padding: 1.2rem; }
  .event-show-card-title { color: #232b38; font-size: 1.9rem; margin-bottom: 0.7rem; font-weight: 700; }
  .event-show-text { color: #334155; line-height: 1.7; }
  .event-show-text + .event-show-text { margin-top: 0.85rem; }
  .event-show-info-list { list-style: none; margin: 0; padding: 0; display: grid; gap: 1rem; }
  .event-show-info-item { display: flex; align-items: center; gap: 0.6rem; color: #0f172a; }
  .event-show-info-icon { color: #ff6b35; font-size: 1.05rem; line-height: 1; }
  .event-show-info-label { font-weight: 700; color: #1e293b; margin-right: 0.2rem; }
  .event-show-bullets { margin: 0; padding-left: 1.1rem; color: #475569; display: grid; gap: 0.5rem; }
  .event-show-org-row { display: flex; align-items: center; gap: 0.8rem; background: #f1f5f9; border-radius: 0.6rem; padding: 0.75rem; }
  .event-show-org-avatar { width: 2.4rem; height: 2.4rem; border-radius: 9999px; background: #ff6b35; color: #fff; font-weight: 700; display: inline-flex; align-items: center; justify-content: center; text-transform: uppercase; }
  .event-show-org-name { font-weight: 700; color: #1e293b; line-height: 1.2; }
  .event-show-org-status { color: #64748b; font-size: 0.875rem; }
  .event-show-aside-card { border: 1px solid #e2e8f0; border-radius: 0.75rem; background: #fff; padding: 1.15rem; position: sticky; top: 5.25rem; }
  .event-show-price { font-size: 2.15rem; line-height: 1.1; font-weight: 800; color: #ff6b35; margin: 0 0 1rem; }
  .event-show-actions { display: grid; gap: 0.55rem; }
  .event-show-btn { display: inline-flex; align-items: center; justify-content: center; width: 100%; border-radius: 0.5rem; padding: 0.63rem 0.8rem; font-weight: 700; border: 1px solid #ff6b35; transition: all 0.2s ease; text-decoration: none; }
  .event-show-btn--primary { background: #ff6b35; color: #fff; }
  .event-show-btn--primary:hover { background: #ee5a26; border-color: #ee5a26; }
  .event-show-btn--outline { background: #fff; color: #1e293b; border-color: #d1d5db; }
  .event-show-btn--outline:hover { border-color: #ff6b35; color: #ff6b35; }
  .event-show-note { margin-top: 0.55rem; color: #64748b; font-size: 0.82rem; text-align: center; }
  .event-show-image { width: 100%; border-radius: 0.75rem; max-height: 330px; object-fit: cover; border: 1px solid #e2e8f0; }
  @media (min-width: 1024px) {
    .event-show-grid { grid-template-columns: minmax(0, 1fr) 16.5rem; align-items: start; }
  }
</style>

<article class="container-ce section event-show-page">
  <div class="event-show-grid">
    <div class="event-show-main">
      <?php if ($category !== ""): ?>
        <span class="event-show-tag"><?= $e(strtolower($category)) ?></span>
      <?php endif; ?>

      <h1 class="event-show-title"><?= $e($titleText) ?></h1>

      <section class="event-show-card">
        <h2 class="event-show-card-title">Apie renginį</h2>
        <p class="event-show-text"><?= nl2br($e($aboutText)) ?></p>
        <?php if ($image !== ""): ?>
          <img src="<?= $e($image) ?>" alt="<?= $e($titleText) ?>" class="event-show-image">
        <?php endif; ?>
      </section>

      <section class="event-show-card">
        <h2 class="event-show-card-title">Renginio informacija</h2>
        <ul class="event-show-info-list">
          <li class="event-show-info-item">
            <span class="event-show-info-icon" aria-hidden="true">📅</span>
            <span><span class="event-show-info-label">Data:</span><?= $e(trim($date . ($time !== "" ? " " . $time : ""))) ?></span>
          </li>
          <li class="event-show-info-item">
            <span class="event-show-info-icon" aria-hidden="true">🕒</span>
            <span><span class="event-show-info-label">Laikas:</span><?= $e($time !== "" ? $time : "Nenurodyta") ?></span>
          </li>
          <li class="event-show-info-item">
            <span class="event-show-info-icon" aria-hidden="true">📍</span>
            <span><span class="event-show-info-label">Vieta:</span><?= $e($location !== "" ? $location : "Nenurodyta") ?></span>
          </li>
          <?php if ($district !== ""): ?>
            <li class="event-show-info-item">
              <span class="event-show-info-icon" aria-hidden="true">🏙️</span>
              <span><span class="event-show-info-label">Rajonas:</span><?= $e($district) ?></span>
            </li>
          <?php endif; ?>
          <li class="event-show-info-item">
            <span class="event-show-info-icon" aria-hidden="true">💶</span>
            <span><span class="event-show-info-label">Kaina:</span><?= $e($price !== "" ? $price : "Nemokama") ?></span>
          </li>
        </ul>
      </section>

      <section class="event-show-card">
        <h2 class="event-show-card-title">Kas jūsų laukia</h2>
        <ul class="event-show-bullets">
          <li>Nauja patirtis</li>
          <li>Idomus renginio turinys</li>
          <li>Daugiau veiklu mieste</li>
        </ul>
      </section>

      <section class="event-show-card">
        <h2 class="event-show-card-title">Organizatorius</h2>
        <div class="event-show-org-row">
          <span class="event-show-org-avatar"><?= $e($organizerInitial) ?></span>
          <div>
            <p class="event-show-org-name"><?= $e($organizerName) ?></p>
            <p class="event-show-org-status">Patvirtinta</p>
          </div>
        </div>
      </section>
    </div>

    <aside class="event-show-aside-card">
      <p class="event-show-price"><?= $e($priceBadge) ?></p>
      <div class="event-show-actions">
        <a href="#" class="event-show-btn event-show-btn--primary">Gauti bilietus</a>
        <button type="button" class="event-show-btn event-show-btn--outline">Prideti i megstamus</button>
      </div>
      <p class="event-show-note">Galimi bilietai</p>
    </aside>
  </div>
</article>

