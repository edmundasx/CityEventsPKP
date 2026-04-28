<?php
declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use App\Support\AdminEventModerationRules;

$assertTrue = static function (bool $condition, string $message): void {
    if (!$condition) {
        throw new RuntimeException($message);
    }
};

$assertFalse = static function (bool $condition, string $message) use ($assertTrue): void {
    $assertTrue(!$condition, $message);
};

// CPG-70: tik laukiantis renginys gali buti patvirtintas.
$assertTrue(
    AdminEventModerationRules::canApplyAction('pending', 'approve'),
    'Pending event should be approvable.',
);
$assertFalse(
    AdminEventModerationRules::canApplyAction('approved', 'approve'),
    'Approved event should not be approvable again.',
);
$assertFalse(
    AdminEventModerationRules::canApplyAction('rejected', 'approve'),
    'Rejected event should not be approvable directly.',
);

