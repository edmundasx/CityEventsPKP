<?php
$events = $events ?? [];
$tab = (string) ($tab ?? "pending");
$base = $base ?? "";
$pendingCount = (int) ($pendingCount ?? 0);
$e = $e ?? static fn($v) => htmlspecialchars((string) $v, ENT_QUOTES, "UTF-8");
?>

<div class="section-card">
  <?php // Laukianciu patvirtinimo renginiu saraso sekcija su skirtukais pagal statusa. ?>
  <div class="section-header">
    <h2><?= $tab === "pending" ? "Laukiantys patvirtinimo renginiai" : "Renginiu sarasas pagal busena" ?></h2>
  </div>
  <div class="tabs">
    <a class="tab <?= $tab === "pending" ? "active" : "" ?>" data-tab="pending" href="<?= $base ?>/admin/panel?tab=pending">
      Laukia patvirtinimo
      <span class="tab-badge" id="pendingCount"><?= $e($pendingCount) ?></span>
    </a>
    <a class="tab <?= $tab === "approved" ? "active" : "" ?>" data-tab="approved" href="<?= $base ?>/admin/panel?tab=approved">Patvirtinti</a>
    <a class="tab <?= $tab === "rejected" ? "active" : "" ?>" data-tab="rejected" href="<?= $base ?>/admin/panel?tab=rejected">Atmesti</a>
  </div>
  <div class="section-body" id="eventsContainer">
    <div class="table-responsive">
      <table class="users-table" id="adminEventsTable">
        <thead>
          <tr>
            <th>Renginys</th>
            <th>Organizatorius</th>
            <th>Data</th>
            <th>Vieta</th>
            <th>Busena</th>
            <th>Veiksmai</th>
          </tr>
        </thead>
        <tbody id="adminEventsBody">
          <?php if (empty($events)): ?>
            <tr>
              <td colspan="6" class="empty-state">
                <?= $tab === "pending" ? "Laukianciu patvirtinimo renginiu nera" : "Sioje kategorijoje renginiu nera" ?>
              </td>
            </tr>
          <?php else: ?>
            <?php foreach ($events as $event): ?>
              <tr>
                <td><?= $e($event["title"] ?? "") ?></td>
                <td><?= $e($event["organizer_name"] ?? "-") ?></td>
                <td><?= $e((string) ($event["event_date"] ?? "")) ?></td>
                <td><?= $e($event["location"] ?? "") ?></td>
                <td><span class="admin-badge"><?= $e($event["status"] ?? "") ?></span></td>
                <td>
                  <div class="admin-action-row">
                    <?php if ($tab === "pending"): ?>
                      <?php // Patvirtinimo mygtukas aktyvuoja renginio patvirtinimo funkcija administratoriaus skydelyje. ?>
                      <form method="post" action="<?= $base ?>/admin/panel/event-status" class="admin-inline-form js-admin-event-form">
                        <input type="hidden" name="event_id" value="<?= $e($event["id"] ?? "") ?>">
                        <input type="hidden" name="action" value="approve">
                        <input type="hidden" name="tab" value="pending">
                        <button type="submit" class="admin-action-btn admin-action-approve">Patvirtinti</button>
                      </form>
                      <form method="post" action="<?= $base ?>/admin/panel/event-status" class="admin-inline-form js-admin-event-form">
                        <input type="hidden" name="event_id" value="<?= $e($event["id"] ?? "") ?>">
                        <input type="hidden" name="action" value="reject">
                        <input type="hidden" name="tab" value="pending">
                        <input type="hidden" name="rejection_reason" value="">
                        <button type="submit" class="admin-action-btn admin-action-reject">Atmesti</button>
                      </form>
                    <?php elseif ($tab === "approved"): ?>
                      <form method="post" action="<?= $base ?>/admin/panel/event-status" class="admin-inline-form js-admin-event-form">
                        <input type="hidden" name="event_id" value="<?= $e($event["id"] ?? "") ?>">
                        <input type="hidden" name="action" value="reject">
                        <input type="hidden" name="tab" value="approved">
                        <input type="hidden" name="rejection_reason" value="">
                        <button type="submit" class="admin-action-btn admin-action-reject">Atmesti</button>
                      </form>
                    <?php else: ?>
                      <form method="post" action="<?= $base ?>/admin/panel/event-status" class="admin-inline-form js-admin-event-form">
                        <input type="hidden" name="event_id" value="<?= $e($event["id"] ?? "") ?>">
                        <input type="hidden" name="action" value="restore">
                        <input type="hidden" name="tab" value="rejected">
                        <button type="submit" class="admin-action-btn admin-action-restore">Grazinti i laukima</button>
                      </form>
                    <?php endif; ?>
                  </div>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
