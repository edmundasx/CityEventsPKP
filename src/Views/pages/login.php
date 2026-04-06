<?php
$base = $base ?? "";
$loginError = $loginError ?? null;
?>

<section class="auth-page">
  <div class="container-ce auth-wrap">
    <h1 class="auth-title">Login</h1>
    <p class="auth-lead">Sign in to your CityEvents account.</p>

    <?php if ($loginError): ?>
      <div class="auth-error"><?= htmlspecialchars($loginError, ENT_QUOTES, "UTF-8") ?></div>
    <?php endif; ?>

    <form class="auth-form" method="post" action="<?= $base ?>/login">
      <label class="auth-label" for="email">Email</label>
      <input class="auth-input" id="email" name="email" type="email" required>

      <label class="auth-label" for="password">Password</label>
      <input class="auth-input" id="password" name="password" type="password" required>

      <button class="btn btn-primary auth-submit" type="submit">Log in</button>
    </form>
  </div>
</section>
