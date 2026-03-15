<?php
$base = $base ?? "";
$flashSuccess = $flashSuccess ?? null;
$flashError = $flashError ?? null;
$old = is_array($old ?? null) ? $old : [];
$e = static fn($v) => htmlspecialchars((string) $v, ENT_QUOTES, "UTF-8");
?>

<section class="auth-page">
  <div class="container-ce auth-wrap auth-wrap-wide">
    <h1 class="auth-title">Create a new event</h1>
    <p class="auth-lead">Fill out the form and submit your event for approval.</p>

    <?php if ($flashSuccess): ?>
      <div class="auth-success"><?= $e($flashSuccess) ?></div>
    <?php endif; ?>
    <?php if ($flashError): ?>
      <div class="auth-error"><?= $e($flashError) ?></div>
    <?php endif; ?>

    <form class="auth-form" method="post" action="<?= $base ?>/organizer/events/create">
      <label class="auth-label" for="title">Title *</label>
      <input class="auth-input" id="title" name="title" required value="<?= $e($old["title"] ?? "") ?>">

      <label class="auth-label" for="description">Description *</label>
      <textarea class="auth-input auth-textarea" id="description" name="description" required><?= $e($old["description"] ?? "") ?></textarea>

      <label class="auth-label" for="category">Category *</label>
      <input class="auth-input" id="category" name="category" required value="<?= $e($old["category"] ?? "") ?>">

      <label class="auth-label" for="location">Location *</label>
      <input class="auth-input" id="location" name="location" required value="<?= $e($old["location"] ?? "") ?>">

      <div class="auth-two-col">
        <div>
          <label class="auth-label" for="event_date">Date ir laikas *</label>
          <input class="auth-input" id="event_date" name="event_date" type="datetime-local" required value="<?= $e($old["event_date"] ?? "") ?>">
        </div>
        <div>
          <label class="auth-label" for="price">Price (EUR)</label>
          <input class="auth-input" id="price" name="price" type="number" step="0.01" min="0" value="<?= $e($old["price"] ?? "0") ?>">
        </div>
      </div>

      <div class="auth-two-col">
        <div>
          <label class="auth-label" for="lat">Latitude</label>
          <input class="auth-input" id="lat" name="lat" value="<?= $e($old["lat"] ?? "") ?>">
        </div>
        <div>
          <label class="auth-label" for="lng">Longitude</label>
          <input class="auth-input" id="lng" name="lng" value="<?= $e($old["lng"] ?? "") ?>">
        </div>
      </div>

      <label class="auth-label" for="cover_image">Nuotraukos URL</label>
      <input class="auth-input" id="cover_image" name="cover_image" value="<?= $e($old["cover_image"] ?? "") ?>">

      <button class="btn btn-primary auth-submit" type="submit">Submit event</button>
    </form>
  </div>
</section>
