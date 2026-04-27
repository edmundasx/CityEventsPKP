<?php
$users = $users ?? [];
$authUser = $authUser ?? [];
$base = $base ?? "";
$currentAdminId = (int) (($authUser["id"] ?? 0));
$e = $e ?? static fn($v) => htmlspecialchars((string) $v, ENT_QUOTES, "UTF-8");
?>

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
              <?php $uid = (int) ($user["id"] ?? 0); ?>
              <tr>
                <td><?= $e($user["name"] ?? "") ?></td>
                <td><?= $e($user["email"] ?? "") ?></td>
                <td><span class="admin-badge"><?= $e($user["role"] ?? "") ?></span></td>
                <td>
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
