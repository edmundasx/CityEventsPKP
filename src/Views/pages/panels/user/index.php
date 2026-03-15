<?php
use App\Auth\Auth;

$authUser = Auth::user() ?? [];
$name = (string) ($authUser["name"] ?? "User");
$favoriteEvents = is_array($favoriteEvents ?? null) ? $favoriteEvents : [];
$recommendedEvents = is_array($recommendedEvents ?? null) ? $recommendedEvents : [];
$recommendedHasMore = (bool) ($recommendedHasMore ?? false);
$recPage = (int) ($recPage ?? 1);
$notifications = is_array($notifications ?? null) ? $notifications : [];
$calendar = $calendar ?? [];
$monthLabel = $monthLabel ?? "";
$e = static fn($v) => htmlspecialchars((string) $v, ENT_QUOTES, "UTF-8");
$returnTo = $base . "/user/panel?rec_page=" . $recPage;
?>

<section class="user-panel-page" id="userPanelRoot" data-base="<?= $e($base ?? "") ?>">
  <div class="container-ce user-panel-wrap">
    <header class="user-panel-head">
      <h1 class="user-panel-title"><?= $e($name) ?>, your events and favorites</h1>
      <p class="user-panel-lead">Find and manage events you like, including personalized recommendations</p>
    </header>

    <section class="user-layout">
      <div class="user-main-col">
        <article class="user-card">
          <div class="user-card-head">
            <h3>&#10084; Liked events</h3>
          </div>

          <?php if (empty($favoriteEvents)): ?>
            <div class="user-empty">You do not have any liked events yet.</div>
          <?php else: ?>
            <?php $fav = $favoriteEvents[0]; ?>
            <div class="user-fav-feature">
              <a class="user-fav-image-wrap" href="<?= $base ?>/events/<?= $e($fav["id"] ?? "") ?>">
                <?php if (!empty($fav["cover_image"])): ?>
                  <img src="<?= $e($fav["cover_image"]) ?>" alt="" class="user-fav-image">
                <?php else: ?>
                  <div class="user-fav-image-placeholder"></div>
                <?php endif; ?>
              </a>
              <div class="user-fav-content">
                <a class="user-fav-title" href="<?= $base ?>/events/<?= $e($fav["id"] ?? "") ?>"><?= $e($fav["title"] ?? "") ?></a>
                <p class="user-fav-meta"><?= $e($fav["category"] ?? "") ?> · <?= $e($fav["location"] ?? "") ?></p>
                <p class="user-fav-meta"><?= $e((string) ($fav["event_date"] ?? "")) ?> · <?= $e($fav["organizer_name"] ?? "") ?></p>
                <div class="user-fav-tags">
                  <span class="user-tag">&#10084; Favorite</span>
                  <?php if (!empty($fav["favorite_tag"])): ?>
                    <span class="user-tag user-tag-soft"><?= $e($fav["favorite_tag"]) ?></span>
                  <?php endif; ?>
                </div>
                <form method="post" action="<?= $base ?>/user/panel/favorite-toggle" class="user-inline-form js-favorite-form" data-event-id="<?= $e($fav["id"] ?? "") ?>">
                  <input type="hidden" name="event_id" value="<?= $e($fav["id"] ?? "") ?>">
                  <input type="hidden" name="return_to" value="<?= $e($returnTo) ?>">
                  <button type="submit" class="user-mini-btn">Remove from favorites</button>
                </form>
              </div>
            </div>
          <?php endif; ?>
        </article>

        <article class="user-card">
          <div class="user-card-head">
            <h3>&#11088; Recommended events</h3>
            <a href="<?= $base ?>/events" class="user-link-muted">See all</a>
          </div>

          <?php if (empty($recommendedEvents)): ?>
            <div class="user-empty">There are no recommendations right now.</div>
          <?php else: ?>
            <div class="user-reco-grid">
              <?php foreach ($recommendedEvents as $event): ?>
                <article class="user-reco-card">
                  <a class="user-reco-media" href="<?= $base ?>/events/<?= $e($event["id"] ?? "") ?>">
                    <?php if (!empty($event["cover_image"])): ?>
                      <img src="<?= $e($event["cover_image"]) ?>" alt="" class="user-reco-image">
                    <?php else: ?>
                      <div class="user-reco-image-placeholder"></div>
                    <?php endif; ?>
                    <form method="post" action="<?= $base ?>/user/panel/favorite-toggle" class="user-heart-form js-favorite-form" data-event-id="<?= $e($event["id"] ?? "") ?>">
                      <input type="hidden" name="event_id" value="<?= $e($event["id"] ?? "") ?>">
                      <input type="hidden" name="return_to" value="<?= $e($returnTo) ?>">
                      <button type="submit" class="user-reco-heart js-favorite-heart" aria-label="Favorite"><?= (int) ($event["is_favorite"] ?? 0) === 1 ? "&#10084;" : "&#9825;" ?></button>
                    </form>
                  </a>
                  <div class="user-reco-body">
                    <a class="user-reco-title" href="<?= $base ?>/events/<?= $e($event["id"] ?? "") ?>"><?= $e($event["title"] ?? "") ?></a>
                    <p class="user-reco-meta"><?= $e((string) ($event["event_date"] ?? "")) ?></p>
                    <p class="user-reco-meta"><?= $e($event["location"] ?? "") ?> · <?= $e($event["organizer_name"] ?? "") ?></p>
                  </div>
                </article>
              <?php endforeach; ?>
            </div>

            <?php if ($recommendedHasMore): ?>
              <div class="user-load-more-wrap">
                <a class="btn btn-outline js-user-load-more" href="<?= $base ?>/user/panel?rec_page=<?= $recPage + 1 ?>">Load more</a>
              </div>
            <?php endif; ?>
          <?php endif; ?>
        </article>
      </div>

      <aside class="user-side-col">
        <article class="user-card">
          <div class="user-card-head">
            <h3>&#128197; Event calendar</h3>
          </div>
          <div class="user-cal-head"><?= $e($monthLabel) ?></div>
          <div class="user-cal-days">
            <span>Mon</span><span>Tue</span><span>Wed</span><span>Thu</span><span>Fri</span><span>Sat</span><span>Sun</span>
          </div>
          <div class="user-cal-grid">
            <?php foreach ($calendar as $week): ?>
              <?php foreach ($week as $day): ?>
                <span class="user-cal-cell <?= $day === (int) date("j") ? "user-cal-today" : "" ?>">
                  <?= $day !== null ? $e($day) : "" ?>
                </span>
              <?php endforeach; ?>
            <?php endforeach; ?>
          </div>
        </article>

        <article class="user-card">
          <div class="user-card-head">
            <h3>&#9888; Notifications</h3>
          </div>
          <?php if (empty($notifications)): ?>
            <div class="user-empty user-empty-small">No notifications.</div>
          <?php else: ?>
            <ul class="user-notif-list">
              <?php foreach ($notifications as $n): ?>
                <li class="user-notif-item <?= (int) ($n["is_read"] ?? 0) === 1 ? "user-notif-read" : "" ?>" data-notification-id="<?= $e($n["id"] ?? "") ?>">
                  <p class="user-notif-msg"><?= $e($n["message"] ?? "") ?></p>
                  <div class="user-notif-row">
                    <p class="user-notif-time"><?= $e((string) ($n["created_at"] ?? "")) ?></p>
                    <?php if ((int) ($n["is_read"] ?? 0) === 0): ?>
                      <form method="post" action="<?= $base ?>/user/panel/notification-read" class="user-inline-form js-notification-read-form" data-notification-id="<?= $e($n["id"] ?? "") ?>">
                        <input type="hidden" name="notification_id" value="<?= $e($n["id"] ?? "") ?>">
                        <input type="hidden" name="return_to" value="<?= $e($returnTo) ?>">
                        <button type="submit" class="user-mini-btn">Mark as read</button>
                      </form>
                    <?php endif; ?>
                  </div>
                </li>
              <?php endforeach; ?>
            </ul>
          <?php endif; ?>
        </article>
      </aside>
    </section>
  </div>
</section>
