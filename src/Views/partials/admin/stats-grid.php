<?php
$stats = $stats ?? [];
$e = $e ?? static fn($v) => htmlspecialchars((string) $v, ENT_QUOTES, "UTF-8");
?>

<div class="stats-grid" id="statsGrid">
  <?php // Statistikos blokai administratoriaus suvestinei. ?>
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
      <p>Approved</p>
    </div>
  </button>
  <button class="stat-card" type="button" data-status="rejected" data-tab-target="rejected">
    <div class="stat-icon rejected">&#10060;</div>
    <div class="stat-content">
      <h3 id="rejectedEvents"><?= $e($stats["rejected_events"] ?? 0) ?></h3>
      <p>Rejected</p>
    </div>
  </button>
</div>
