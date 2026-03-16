<?php
$stats = $stats ?? [];
$events = $events ?? [];
$users = $users ?? [];
$recentActivity = $recentActivity ?? [];
$calendar = $calendar ?? [];
$monthLabel = $monthLabel ?? "";
$tab = (string) ($tab ?? "pending");
$adminFlash = $adminFlash ?? null;
$authUser = $authUser ?? [];
$currentAdminId = (int) ($authUser["id"] ?? 0);
$pendingCount = (int) ($stats["pending_events"] ?? 0);
$e = static fn($v) => htmlspecialchars((string) $v, ENT_QUOTES, "UTF-8");
?>

<main
  class="main-container container-ce px-4 md:px-6"
  id="adminPanelRoot"
  data-base="<?= $e($base ?? "") ?>"
  data-tab="<?= $e($tab) ?>"
  data-current-admin-id="<?= $e($currentAdminId) ?>"
>
  <h1 class="page-title">Administratoriaus skydelis</h1>
  <p class="page-subtitle">Manage events, users, and monitor platform statistics</p>

  <?php if ($adminFlash): ?>
    <div class="admin-flash"><?= $e($adminFlash) ?></div>
  <?php endif; ?>

  <?php // Administratoriaus puslapio struktura surenkama is atskiru partial failu pagal MVC architektura. ?>
  <?php require __DIR__ . "/../../../partials/admin/stats-grid.php"; ?>

  <div class="dashboard-grid">
    <?php require __DIR__ . "/../../../partials/admin/events-section.php"; ?>

    <div class="sidebar-sections">
      <?php require __DIR__ . "/../../../partials/admin/calendar-section.php"; ?>
      <?php require __DIR__ . "/../../../partials/admin/activity-section.php"; ?>
    </div>
  </div>

  <?php require __DIR__ . "/../../../partials/admin/users-section.php"; ?>

  <div class="toast" id="toast">
    <span id="toastIcon"></span>
    <span id="toastMessage"></span>
  </div>
</main>
