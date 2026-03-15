<?php

use App\Auth\Auth;

$base = $base ?? "";
$authUser = Auth::user();
$role = $authUser["role"] ?? null;
$enableLoginModal = $enableLoginModal ?? false;
?>

<header class="site-header">
  <div class="container-ce">
    <div class="header-row">
      <a href="<?= $base ?>/home" class="logo">CityEvents</a>

      <nav class="nav">
        <a class="nav-link" href="<?= $base ?>/home#events">Events</a>
        <a class="nav-link" href="<?= $base ?>/map">Map</a>
        <a class="nav-link" href="<?= $base ?>/organizers">For Organizers</a>
        <a class="nav-link" href="<?= $base ?>/help">Help</a>
        <?php if ($role === "user"): ?>
          <a class="nav-link" href="<?= $base ?>/user/panel">User Panel</a>
        <?php elseif ($role === "organizer"): ?>
          <a class="nav-link" href="<?= $base ?>/organizer/panel">Organizer Panel</a>
        <?php elseif ($role === "admin"): ?>
          <a class="nav-link" href="<?= $base ?>/admin/panel">Admin Panel</a>
        <?php endif; ?>
      </nav>

      <div class="header-actions">
        <?php if ($authUser): ?>
          <span class="header-user-name"><?= htmlspecialchars((string) ($authUser["name"] ?? ""), ENT_QUOTES, "UTF-8") ?></span>
          <form method="post" action="<?= $base ?>/logout" class="header-logout-form">
            <button type="submit" class="btn-outline">Log out</button>
          </form>
        <?php else: ?>
          <?php if ($enableLoginModal): ?>
            <button type="button" class="btn-outline js-open-login-modal">Log in</button>
            <button type="button" class="btn-primary js-open-register-modal">Sign up</button>
            <noscript>
              <a class="btn-outline" href="<?= $base ?>/login">Log in</a>
              <a class="btn-primary" href="<?= $base ?>/signup">Sign up</a>
            </noscript>
          <?php else: ?>
            <a class="btn-outline" href="<?= $base ?>/login">Log in</a>
            <a class="btn-primary" href="<?= $base ?>/signup">Sign up</a>
          <?php endif; ?>
        <?php endif; ?>
      </div>
    </div>
  </div>
</header>
