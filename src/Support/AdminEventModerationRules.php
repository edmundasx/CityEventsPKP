<?php
declare(strict_types=1);

namespace App\Support;

final class AdminEventModerationRules
{
    public static function requiresReason(string $action): bool
    {
        return strtolower($action) === "reject";
    }

    public static function canApplyAction(string $currentStatus, string $action): bool
    {
        $currentStatus = strtolower($currentStatus);
        $action = strtolower($action);

        return match ($action) {
            "approve" => $currentStatus === "pending",
            "reject" => in_array($currentStatus, ["pending", "approved"], true),
            "restore" => in_array($currentStatus, ["rejected", "declined", "rejected_by_admin"], true),
            default => false,
        };
    }
}
