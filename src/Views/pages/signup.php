<?php
$base = $base ?? "";
$registerError = $registerError ?? null;
?>

<section class="auth-page">
  <div class="container-ce auth-wrap">
    <h1 class="auth-title">Registration</h1>
    <p class="auth-lead">Create your CityEvents account.</p>

    <?php if ($registerError): ?>
      <div class="auth-error"><?= htmlspecialchars($registerError, ENT_QUOTES, "UTF-8") ?></div>
    <?php endif; ?>

    <form class="auth-form" method="post" action="<?= $base ?>/register">
      <label class="auth-label" for="name">Name</label>
      <input class="auth-input" id="name" name="name" type="text" required>

      <label class="auth-label" for="email">Email</label>
      <input class="auth-input" id="email" name="email" type="email" required>

      <label class="auth-label" for="password">Password</label>
      <input class="auth-input" id="password" name="password" type="password" minlength="6" required>

      <label class="auth-label" for="confirm_password">Confirm password</label>
      <input class="auth-input" id="confirm_password" name="confirm_password" type="password" minlength="6" required>

      <label class="auth-label" for="role">Account type</label>
      <select class="auth-input" id="role" name="role" required>
        <option value="user">User</option>
        <option value="organizer">Organizer</option>
      </select>

      <button class="btn btn-primary auth-submit" type="submit">Sign up</button>
    </form>
  </div>
</section>
