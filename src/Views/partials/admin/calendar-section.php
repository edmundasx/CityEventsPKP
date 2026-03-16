<?php
$calendar = $calendar ?? [];
$monthLabel = $monthLabel ?? "";
$e = $e ?? static fn($v) => htmlspecialchars((string) $v, ENT_QUOTES, "UTF-8");
?>

<section class="card calendar-card section-card">
  <?php // Kalendoriaus sritis administratoriaus puslapiui. ?>
  <div class="section-header">
    <h2>Event calendar</h2>
  </div>
  <div class="section-body">
    <div class="calendar-header-nav">
      <button class="calendar-nav-btn" type="button" id="adminPrevMonth" aria-label="Previous month">&#9664;</button>
      <span class="calendar-month" id="adminCalendarMonth"><?= $e($monthLabel) ?></span>
      <button class="calendar-nav-btn" type="button" id="adminNextMonth" aria-label="Next month">&#9654;</button>
    </div>
    <div class="calendar-grid" id="adminCalendarDayHeaders">
      <div class="calendar-day-header">Pr</div>
      <div class="calendar-day-header">An</div>
      <div class="calendar-day-header">Tr</div>
      <div class="calendar-day-header">Kt</div>
      <div class="calendar-day-header">Pn</div>
      <div class="calendar-day-header">St</div>
      <div class="calendar-day-header">Sk</div>
    </div>
    <div
      class="calendar-grid"
      id="adminCalendar"
      data-current-year="<?= $e(date("Y")) ?>"
      data-current-month="<?= $e(date("n")) ?>"
      data-current-day="<?= $e(date("j")) ?>"
    >
      <?php foreach ($calendar as $week): ?>
        <?php foreach ($week as $day): ?>
          <div class="calendar-day-cell <?= $day === (int) date("j") ? "calendar-day-today" : "" ?>">
            <?= $day !== null ? $e($day) : "" ?>
          </div>
        <?php endforeach; ?>
      <?php endforeach; ?>
    </div>
    <div class="calendar-tooltip" id="adminCalendarTooltip" aria-live="polite"></div>
  </div>
</section>
