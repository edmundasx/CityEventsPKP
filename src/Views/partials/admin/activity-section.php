<?php
$recentActivity = $recentActivity ?? [];
$e = $e ?? static fn($v) => htmlspecialchars((string) $v, ENT_QUOTES, "UTF-8");
?>

<div class="section-card">
  <div class="section-header">
    <h2>Naujausia veikla</h2>
  </div>
  <div class="section-body" id="activityContainer">
    <?php if (empty($recentActivity)): ?>
      <div class="empty-state">
        <div class="empty-state-icon">&#8505;</div>
        <p>Aktyvumo duomenys bus rodomi netrukus</p>
      </div>
    <?php else: ?>
      <ul class="admin-activity-list">
        <?php foreach ($recentActivity as $row): ?>
          <li class="admin-activity-item">
            <p class="admin-activity-title"><?= $e($row["title"] ?? "") ?></p>
            <p class="admin-activity-meta"><?= $e($row["status"] ?? "") ?> · <?= $e((string) ($row["updated_at"] ?? "")) ?></p>
          </li>
        <?php endforeach; ?>
      </ul>
    <?php endif; ?>
  </div>
</div>
