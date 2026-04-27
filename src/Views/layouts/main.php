<?php
use App\Auth\Auth;
use App\Support\AppBasePath;

$base = $base ?? AppBasePath::fromServer();
$enableLoginModal = (bool) ($enableLoginModal ?? true);
$enableLoginModal = $enableLoginModal && !Auth::check();

$projectRoot = dirname(__DIR__, 3);
$_ceTailwindFs = $projectRoot . "/public/assets/css/tailwind.css";
$_ceTailwindVer = is_file($_ceTailwindFs) ? (string) filemtime($_ceTailwindFs) : "1";
$_ceScriptFs = $projectRoot . "/public/assets/js/script.js";
$_ceScriptVer = is_file($_ceScriptFs) ? (string) filemtime($_ceScriptFs) : "1";
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= $title ??
      "City Events - Discover events you love" ?></title>
  <meta name="description" content="Find and join events, browse organizers, or create your own event.">
  <link rel="stylesheet" href="<?= htmlspecialchars($base, ENT_QUOTES, "UTF-8") ?>/assets/css/tailwind.css?v=<?= htmlspecialchars($_ceTailwindVer, ENT_QUOTES, "UTF-8") ?>">
  <?php if (!empty($pageStyles) && is_array($pageStyles)): ?>
    <?php foreach ($pageStyles as $style): ?>
      <link rel="stylesheet" href="<?= htmlspecialchars($style) ?>">
    <?php endforeach; ?>
  <?php endif; ?>
</head>
<body>

<?php require __DIR__ . "/../partials/header.php"; ?>

<?php
if (isset($view) && is_file($view)) {
    require $view;
}
?>

<?php require __DIR__ . "/../partials/footer.php"; ?>
<?php if ($enableLoginModal): ?>
  <div class="auth-modal hidden" id="loginModal" aria-hidden="true">
    <div class="auth-modal-backdrop" data-auth-close></div>
    <div class="auth-modal-dialog" role="dialog" aria-modal="true" aria-labelledby="loginModalTitle">
      <button type="button" class="auth-modal-close" data-auth-close aria-label="Close">&times;</button>
      <h2 id="loginModalTitle" class="auth-modal-title">Log in</h2>
      <p class="auth-modal-lead">Sign in to your CityEvents account.</p>

      <div class="auth-modal-error" id="loginModalError" hidden></div>

      <form id="loginModalForm" class="auth-modal-form" method="post" action="<?= $base ?>/login">
        <label class="auth-modal-label" for="loginModalEmail">Email</label>
        <input id="loginModalEmail" name="email" type="email" class="auth-modal-input" required>

        <label class="auth-modal-label" for="loginModalPassword">Password</label>
        <input id="loginModalPassword" name="password" type="password" class="auth-modal-input" required>

        <button type="submit" class="btn-primary auth-modal-submit">Log in</button>
      </form>

      <p class="auth-modal-lead">
        Don't have an account?
        <button type="button" class="auth-modal-switch js-switch-to-register">Sign up</button>
      </p>
    </div>
  </div>

  <div class="auth-modal hidden" id="registerModal" aria-hidden="true">
    <div class="auth-modal-backdrop" data-auth-close></div>
    <div class="auth-modal-dialog" role="dialog" aria-modal="true" aria-labelledby="registerModalTitle">
      <button type="button" class="auth-modal-close" data-auth-close aria-label="Close">&times;</button>
      <h2 id="registerModalTitle" class="auth-modal-title">Sign up</h2>
      <p class="auth-modal-lead">Create your CityEvents account.</p>

      <div class="auth-modal-error" id="registerModalError" hidden></div>

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
        <button type="button" class="auth-modal-switch js-switch-to-login">Log in</button>
      </p>
    </div>
  </div>
<?php endif; ?>

<?php if (!empty($pageScripts) && is_array($pageScripts)): ?>
  <?php foreach ($pageScripts as $script): ?>
    <script src="<?= htmlspecialchars($script) ?>" defer></script>
  <?php endforeach; ?>
<?php endif; ?>
<script src="<?= htmlspecialchars($base, ENT_QUOTES, "UTF-8") ?>/assets/js/script.js?v=<?= htmlspecialchars($_ceScriptVer, ENT_QUOTES, "UTF-8") ?>"></script>
</body>
</html>
