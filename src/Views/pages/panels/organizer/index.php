<?php
use App\Auth\Auth;

$authUser = Auth::user() ?? [];
$name = (string) ($authUser["name"] ?? "Organizer");
$email = (string) ($authUser["email"] ?? "");
$stats = $stats ?? ["total" => 0, "approved" => 0, "pending" => 0, "rejected" => 0];
$myEvents = $myEvents ?? [];
$calendar = $calendar ?? [];
$monthLabel = $monthLabel ?? "";

$e = static fn($v) => htmlspecialchars((string) $v, ENT_QUOTES, "UTF-8");
$avatar = strtoupper(substr($name, 0, 1));
?>

<section class="org-panel-page">
  <div class="container-ce org-panel-wrap">
    <section class="org-greeting-card">
      <h1 class="org-greeting-title">&#128075; Sveiki, <?= $e($name) ?>!</h1>
      <p class="org-greeting-lead">Here you can manage your events and track stats</p>
    </section>

    <section class="org-stats-grid">
      <article class="org-stat-card org-stat-card-accent">
        <div class="org-stat-icon org-icon-indigo">&#128202;</div>
        <div>
          <p class="org-stat-value"><?= $e($stats["total"] ?? 0) ?></p>
          <p class="org-stat-label">Total events</p>
        </div>
      </article>
      <article class="org-stat-card">
        <div class="org-stat-icon org-icon-green">&#10004;</div>
        <div>
          <p class="org-stat-value"><?= $e($stats["approved"] ?? 0) ?></p>
          <p class="org-stat-label">Approved</p>
        </div>
      </article>
      <article class="org-stat-card">
        <div class="org-stat-icon org-icon-amber">&#9203;</div>
        <div>
          <p class="org-stat-value"><?= $e($stats["pending"] ?? 0) ?></p>
          <p class="org-stat-label">Awaiting approval</p>
        </div>
      </article>
      <article class="org-stat-card">
        <div class="org-stat-icon org-icon-red">&#10005;</div>
        <div>
          <p class="org-stat-value"><?= $e($stats["rejected"] ?? 0) ?></p>
          <p class="org-stat-label">Rejected/Returned</p>
        </div>
      </article>
    </section>

    <section class="org-quick-grid">
      <a class="org-quick-card" href="<?= $base ?>/organizer/events/create">
        <div class="org-quick-icon">&#10133;</div>
        <div>
          <h3 class="org-quick-title">Create a new event</h3>
          <p class="org-quick-text">Publish a new event and reach attendees</p>
        </div>
      </a>
      <a class="org-quick-card" href="<?= $base ?>/organizer/events">
        <div class="org-quick-icon">&#128203;</div>
        <div>
          <h3 class="org-quick-title">View my events</h3>
          <p class="org-quick-text">Manage all submitted events</p>
        </div>
      </a>
      <a class="org-quick-card" href="<?= $base ?>/map">
        <div class="org-quick-icon">&#128506;</div>
        <div>
          <h3 class="org-quick-title">City map</h3>
          <p class="org-quick-text">View all events on the map</p>
        </div>
      </a>
    </section>

    <section class="org-main-grid">
      <div class="org-main-left">
        <article class="org-card">
          <div class="org-card-head">
            <h3>&#128450; My Events</h3>
            <a href="<?= $base ?>/organizer/events" class="org-link-muted">Manage all &rarr;</a>
          </div>
          <?php if (empty($myEvents)): ?>
            <div class="org-empty">You do not have any events yet.</div>
          <?php else: ?>
            <div class="org-table-wrap">
              <table class="org-table">
                <thead>
                  <tr>
                    <th>Title</th>
                    <th>Date</th>
                    <th>Status</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($myEvents as $event): ?>
                    <tr>
                      <td><?= $e($event["title"] ?? "") ?></td>
                      <td><?= $e((string) ($event["event_date"] ?? "")) ?></td>
                      <td><span class="org-badge"><?= $e($event["status"] ?? "") ?></span></td>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>
          <?php endif; ?>
        </article>

        <article class="org-card">
          <div class="org-card-head">
            <h3>&#128196; Drafts</h3>
            <a href="<?= $base ?>/organizer/events/create" class="org-link-muted">Create new &rarr;</a>
          </div>
          <div class="org-empty">You currently have no drafts.</div>
        </article>

        <article class="org-card">
          <div class="org-card-head">
            <h3>&#128276; Paskutine veikla</h3>
          </div>
          <div class="org-empty">No recent notifications.</div>
        </article>
      </div>

      <aside class="org-main-right">
        <article class="org-card org-profile-card">
          <div class="org-avatar"><?= $e($avatar) ?></div>
          <h3 class="org-profile-name"><?= $e($name) ?></h3>
          <p class="org-profile-role">Organizer</p>
          <p class="org-profile-email"><?= $e($email) ?></p>
          <a class="btn btn-primary org-profile-btn" href="<?= $base ?>/organizer/profile">&#9998; Edit profile</a>
        </article>

        <article class="org-card">
          <div class="org-card-head">
            <h3>&#128216; Event history</h3>
          </div>
          <div class="org-calendar-head"><?= $e($monthLabel) ?></div>
          <div class="org-calendar-days">
            <span>Mon</span><span>Tue</span><span>Wed</span><span>Thu</span><span>Fri</span><span>Sat</span><span>Sun</span>
          </div>
          <div class="org-calendar-grid">
            <?php foreach ($calendar as $week): ?>
              <?php foreach ($week as $day): ?>
                <span class="org-cal-cell <?= $day === (int) date("j") ? "org-cal-today" : "" ?>">
                  <?= $day !== null ? $e($day) : "" ?>
                </span>
              <?php endforeach; ?>
            <?php endforeach; ?>
          </div>
        </article>
      </aside>
    </section>
  </div>
</section>
