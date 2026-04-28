<?php
$profile = is_array($profile ?? null) ? $profile : [];
$flashSuccess = $flashSuccess ?? null;
$flashError = $flashError ?? null;
$e = static fn($v) => htmlspecialchars((string) $v, ENT_QUOTES, "UTF-8");
?>

<section class="auth-page">
  <div class="container-ce auth-wrap">
    <h1 class="auth-title">Edit profile</h1>
    <p class="auth-lead">Update organizer information.</p>

    <?php if ($flashSuccess): ?>
      <div class="auth-success"><?= $e($flashSuccess) ?></div>
    <?php endif; ?>
    <?php if ($flashError): ?>
      <div class="auth-error"><?= $e($flashError) ?></div>
    <?php endif; ?>

    <form class="auth-form" method="post" action="<?= $base ?>/organizer/profile">
      <label class="auth-label" for="name">Name *</label>
      <input class="auth-input" id="name" name="name" required value="<?= $e($profile["name"] ?? "") ?>">

      <label class="auth-label" for="email">Email</label>
      <input class="auth-input" id="email" value="<?= $e($profile["email"] ?? "") ?>" disabled>

      <label class="auth-label" for="phone">Phone</label>
      <input class="auth-input" id="phone" name="phone" value="<?= $e($profile["phone"] ?? "") ?>">

      <button class="btn btn-primary auth-submit" type="submit">Save changes</button>
    </form>
  </div>
</section>
