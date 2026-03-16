<?php
use App\Auth\Auth;
$base = $base ?? rtrim(str_replace("\\", "/", dirname($_SERVER["SCRIPT_NAME"] ?? "")), "/");
$enableLoginModal = (bool) ($enableLoginModal ?? true);
$enableLoginModal = $enableLoginModal && !Auth::check();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= $title ??
      "City Events - Discover events you love" ?></title>
  <meta name="description" content="Find and join events, browse organizers, or create your own event.">
  <link rel="stylesheet" href="<?= $base ?>/assets/css/tailwind.css">
  <?php if (!empty($pageStyles) && is_array($pageStyles)): ?>
    <?php foreach ($pageStyles as $style): ?>
      <link rel="stylesheet" href="<?= htmlspecialchars($style) ?>">
    <?php endforeach; ?>
  <?php endif; ?>
</head>
<body>

<?php require __DIR__ . "/../partials/header.php"; ?>

<?php require __DIR__ . "/../partials/footer.php"; ?>
<?php if ($enableLoginModal): ?>
  <div class="auth-modal hidden" id="loginModal" aria-hidden="true">
    <div class="auth-modal-backdrop" data-auth-close></div>
    <div class="auth-modal-dialog" role="dialog" aria-modal="true" aria-labelledby="loginModalTitle">
      <button type="button" class="auth-modal-close" data-auth-close aria-label="Close">x</button>
      <h2 id="loginModalTitle" class="auth-modal-title">Log in</h2>
      <p class="auth-modal-lead">Sign in to your CityEvents account.</p>

      <div class="auth-modal-error hidden" id="loginModalError"></div>

      <form id="loginModalForm" class="auth-modal-form" method="post" action="<?= $base ?>/login">
        <label class="auth-modal-label" for="loginModalEmail">Email</label>
        <input id="loginModalEmail" name="email" type="email" class="auth-modal-input" required>

        <label class="auth-modal-label" for="loginModalPassword">Password</label>
        <input id="loginModalPassword" name="password" type="password" class="auth-modal-input" required>

        <button type="submit" class="btn-primary auth-modal-submit">Log in</button>
      </form>

      <p class="auth-modal-lead">
        Don't have an account?
        <button type="button" class="btn-outline js-switch-to-register">Sign up</button>
      </p>
    </div>
  </div>

  <div class="auth-modal hidden" id="registerModal" aria-hidden="true">
    <div class="auth-modal-backdrop" data-auth-close></div>
    <div class="auth-modal-dialog" role="dialog" aria-modal="true" aria-labelledby="registerModalTitle">
      <button type="button" class="auth-modal-close" data-auth-close aria-label="Close">x</button>
      <h2 id="registerModalTitle" class="auth-modal-title">Sign up</h2>
      <p class="auth-modal-lead">Create your CityEvents account.</p>

      <div class="auth-modal-error hidden" id="registerModalError"></div>

      <form id="registerModalForm" class="auth-modal-form" method="post" action="<?= $base ?>/register">
        <label class="auth-modal-label" for="registerModalName">Name</label>
        <input id="registerModalName" name="name" type="text" class="auth-modal-input" required>

        <label class="auth-modal-label" for="registerModalEmail">Email</label>
        <input id="registerModalEmail" name="email" type="email" class="auth-modal-input" required>

        <label class="auth-modal-label" for="registerModalPassword">Password</label>
        <input id="registerModalPassword" name="password" type="password" minlength="6" class="auth-modal-input" required>

        <label class="auth-modal-label" for="registerModalConfirmPassword">Confirm password</label>
        <input id="registerModalConfirmPassword" name="confirm_password" type="password" minlength="6" class="auth-modal-input" required>

        <label class="auth-modal-label" for="registerModalRole">Account type</label>
        <select id="registerModalRole" name="role" class="auth-modal-input" required>
          <option value="user">User</option>
          <option value="organizer">Organizer</option>
        </select>

        <button type="submit" class="btn-primary auth-modal-submit">Sign up</button>
      </form>

      <p class="auth-modal-lead">
        Already have an account?
        <button type="button" class="btn-outline js-switch-to-login">Log in</button>
      </p>
    </div>
  </div>
<?php endif; ?>

<?php if (!empty($pageScripts) && is_array($pageScripts)): ?>
  <?php foreach ($pageScripts as $script): ?>
    <script src="<?= htmlspecialchars($script) ?>" defer></script>
  <?php endforeach; ?>
<?php endif; ?>
<script src="<?= $base ?>/assets/js/script.js"></script>
</body>
</html>
