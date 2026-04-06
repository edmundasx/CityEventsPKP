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

  <div class="stats-grid" id="statsGrid">
    <button class="stat-card" type="button" data-status="all" data-tab-target="pending">
      <div class="stat-icon total">&#128202;</div>
      <div class="stat-content">
        <h3 id="totalEvents"><?= $e($stats["total_events"] ?? 0) ?></h3>
        <p>Total events</p>
      </div>
    </button>
    <button class="stat-card" type="button" data-status="pending" data-tab-target="pending">
      <div class="stat-icon pending">&#9203;</div>
      <div class="stat-content">
        <h3 id="pendingEvents"><?= $e($stats["pending_events"] ?? 0) ?></h3>
        <p>Awaiting approval</p>
      </div>
    </button>
    <button class="stat-card" type="button" data-status="approved" data-tab-target="approved">
      <div class="stat-icon approved">&#9989;</div>
      <div class="stat-content">
        <h3 id="approvedEvents"><?= $e($stats["approved_events"] ?? 0) ?></h3>
        <p>Approve</p>
      </div>
    </button>
    <button class="stat-card" type="button" data-status="rejected" data-tab-target="rejected">
      <div class="stat-icon rejected">&#10060;</div>
      <div class="stat-content">
        <h3 id="rejectedEvents"><?= $e($stats["rejected_events"] ?? 0) ?></h3>
        <p>Reject</p>
      </div>
    </button>
  </div>

  <div class="dashboard-grid">
    <div class="section-card">
      <div class="tabs">
        <a class="tab <?= $tab === "pending" ? "active" : "" ?>" data-tab="pending" href="<?= $base ?>/admin/panel?tab=pending">
          Pending approval
          <span class="tab-badge" id="pendingCount"><?= $e($pendingCount) ?></span>
        </a>
        <a class="tab <?= $tab === "approved" ? "active" : "" ?>" data-tab="approved" href="<?= $base ?>/admin/panel?tab=approved">Approve</a>
        <a class="tab <?= $tab === "rejected" ? "active" : "" ?>" data-tab="rejected" href="<?= $base ?>/admin/panel?tab=rejected">Reject</a>
      </div>
      <div class="section-body" id="eventsContainer">
        <div class="table-responsive">
          <table class="users-table" id="adminEventsTable">
            <thead>
              <tr>
                <th>Event</th>
                <th>Organizer</th>
                <th>Date</th>
                <th>Location</th>
                <th>Status</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody id="adminEventsBody">
              <?php if (empty($events)): ?>
                <tr>
                  <td colspan="6" class="empty-state">No events in this category</td>
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
                          <form method="post" action="<?= $base ?>/admin/panel/event-status" class="admin-inline-form js-admin-event-form">
                            <input type="hidden" name="event_id" value="<?= $e($event["id"] ?? "") ?>">
                            <input type="hidden" name="action" value="approve">
                            <input type="hidden" name="tab" value="pending">
                            <button type="submit" class="admin-action-btn admin-action-approve">Approve</button>
                          </form>
                          <form method="post" action="<?= $base ?>/admin/panel/event-status" class="admin-inline-form js-admin-event-form">
                            <input type="hidden" name="event_id" value="<?= $e($event["id"] ?? "") ?>">
                            <input type="hidden" name="action" value="reject">
                            <input type="hidden" name="tab" value="pending">
                            <button type="submit" class="admin-action-btn admin-action-reject">Reject</button>
                          </form>
                        <?php elseif ($tab === "approved"): ?>
                          <form method="post" action="<?= $base ?>/admin/panel/event-status" class="admin-inline-form js-admin-event-form">
                            <input type="hidden" name="event_id" value="<?= $e($event["id"] ?? "") ?>">
                            <input type="hidden" name="action" value="reject">
                            <input type="hidden" name="tab" value="approved">
                            <button type="submit" class="admin-action-btn admin-action-reject">Reject</button>
                          </form>
                        <?php else: ?>
                          <form method="post" action="<?= $base ?>/admin/panel/event-status" class="admin-inline-form js-admin-event-form">
                            <input type="hidden" name="event_id" value="<?= $e($event["id"] ?? "") ?>">
                            <input type="hidden" name="action" value="restore">
                            <input type="hidden" name="tab" value="rejected">
                            <button type="submit" class="admin-action-btn admin-action-restore">Return to pending</button>
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

    <div class="sidebar-sections">
      <section class="card calendar-card section-card">
        <div class="section-header">
          <h2>Event calendar</h2>
        </div>
        <div class="section-body">
          <div class="calendar-header-nav">
            <button class="calendar-nav-btn" type="button" id="adminPrevMonth" aria-label="Ankstesnis menuo">&#9664;</button>
            <span class="calendar-month" id="adminCalendarMonth"><?= $e($monthLabel) ?></span>
            <button class="calendar-nav-btn" type="button" id="adminNextMonth" aria-label="Kitas menuo">&#9654;</button>
          </div>
          <div class="calendar-grid" id="adminCalendarDayHeaders">
            <div class="calendar-day-header">Pr</div>
            <div class="calendar-day-header">An</div>
            <div class="calendar-day-header">Tr</div>
            <div class="calendar-day-header">Kt</div>
            <div class="calendar-day-header">Pn</div>
            <div class="calendar-day-header">St</div>
            <div class="calendar-day-header">Sk</div>
          </div>
          <div
            class="calendar-grid"
            id="adminCalendar"
            data-current-year="<?= $e(date("Y")) ?>"
            data-current-month="<?= $e(date("n")) ?>"
            data-current-day="<?= $e(date("j")) ?>"
          >
            <?php foreach ($calendar as $week): ?>
              <?php foreach ($week as $day): ?>
                <div class="calendar-day-cell <?= $day === (int) date("j") ? "calendar-day-today" : "" ?>">
                  <?= $day !== null ? $e($day) : "" ?>
                </div>
              <?php endforeach; ?>
            <?php endforeach; ?>
          </div>
          <div class="calendar-tooltip" id="adminCalendarTooltip" aria-live="polite"></div>
        </div>
      </section>

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
    </div>
  </div>

  <div class="section-card mt-8">
    <div class="section-header">
      <h2>User management</h2>
    </div>
    <div class="section-body">
      <div class="table-responsive">
        <table class="users-table" id="adminUsersTable">
          <thead>
            <tr>
              <th>Vartotojas</th>
              <th>Email</th>
              <th>Role</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody id="usersTableBody">
            <?php if (empty($users)): ?>
              <tr>
                <td colspan="4" class="empty-state">Loading users...</td>
              </tr>
            <?php else: ?>
              <?php foreach ($users as $user): ?>
                <tr>
                  <td><?= $e($user["name"] ?? "") ?></td>
                  <td><?= $e($user["email"] ?? "") ?></td>
                  <td><span class="admin-badge"><?= $e($user["role"] ?? "") ?></span></td>
                  <td>
                    <?php $uid = (int) ($user["id"] ?? 0); ?>
                    <?php if ($uid === $currentAdminId): ?>
                      <span class="admin-action-muted">Logged-in admin</span>
                    <?php else: ?>
                      <form method="post" action="<?= $base ?>/admin/panel/user-role" class="admin-role-form js-admin-role-form">
                        <input type="hidden" name="user_id" value="<?= $e($uid) ?>">
                        <select name="role" class="admin-role-select">
                          <option value="user" <?= ($user["role"] ?? "") === "user" ? "selected" : "" ?>>user</option>
                          <option value="organizer" <?= ($user["role"] ?? "") === "organizer" ? "selected" : "" ?>>organizer</option>
                          <option value="admin" <?= ($user["role"] ?? "") === "admin" ? "selected" : "" ?>>admin</option>
                        </select>
                        <button type="submit" class="admin-action-btn admin-action-approve">Save</button>
                      </form>
                    <?php endif; ?>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <div class="toast" id="toast">
    <span id="toastIcon"></span>
    <span id="toastMessage"></span>
  </div>
</main>
