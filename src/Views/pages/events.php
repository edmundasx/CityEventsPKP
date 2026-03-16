<?php
$base = $base ?? "";
$events = $events ?? [];

$e = static fn($value) => htmlspecialchars(
    (string) $value,
    ENT_QUOTES,
    "UTF-8",
);
?>

<main class="container-ce section">
  <header class="section-head">
    <div>
      <h1 class="section-title">Visi renginiai</h1>
      <p class="section-subtitle">Naršyk artėjančius renginius savo mieste</p>
    </div>
  </header>

  <?php
  $gridId = "eventsGridAll";
  $gridClass = "events-grid";
  $emptyText = "Renginiai nerasti";
  $basePath = ($base ?? "") . "/events";

  $partial = __DIR__ . "/../partials/events-grid.php";

  if (!is_file($partial)) {
      throw new RuntimeException("Missing partial: " . $partial);
  }

  require $partial;
  ?>
</main>

