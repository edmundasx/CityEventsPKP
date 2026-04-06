<?php
$events = is_array($events ?? null) ? $events : [];
$flashSuccess = $flashSuccess ?? null;
$e = static fn($v) => htmlspecialchars((string) $v, ENT_QUOTES, "UTF-8");
?>

<section class="panel-page">
  <div class="container-ce panel-wrap panel-wrap-wide panel-dark">
    <div class="panel-head-row">
      <h1 class="panel-title">My Events</h1>
      <a class="btn btn-primary" href="<?= $base ?>/organizer/events/create">Create new</a>
    </div>

    <?php if ($flashSuccess): ?>
      <div class="auth-success"><?= $e($flashSuccess) ?></div>
    <?php endif; ?>

    <?php if (empty($events)): ?>
      <p class="panel-lead">You do not have any events yet.</p>
    <?php else: ?>
      <div class="org-table-wrap">
        <table class="org-table">
          <thead>
            <tr>
              <th>Title</th>
              <th>Category</th>
              <th>Location</th>
              <th>Date</th>
              <th>Price</th>
              <th>Status</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($events as $event): ?>
              <tr>
                <td><?= $e($event["title"] ?? "") ?></td>
                <td><?= $e($event["category"] ?? "") ?></td>
                <td><?= $e($event["location"] ?? "") ?></td>
                <td><?= $e((string) ($event["event_date"] ?? "")) ?></td>
                <td><?= $e((string) ($event["price"] ?? "")) ?></td>
                <td><span class="org-badge"><?= $e($event["status"] ?? "") ?></span></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </div>
</section>
