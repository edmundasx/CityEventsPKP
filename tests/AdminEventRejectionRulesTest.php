<?php
declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use App\Support\AdminEventModerationRules;

function assertTrue(bool $condition, string $message): void
{
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

function assertFalse(bool $condition, string $message): void
{
    assertTrue(!$condition, $message);
}

// CPG-71: atmetimas leidziamas tik laukianciam arba jau patvirtintam renginiui.
assertTrue(
    AdminEventModerationRules::canApplyAction('pending', 'reject'),
    'Pending event should be rejectable.',
);
assertTrue(
    AdminEventModerationRules::canApplyAction('approved', 'reject'),
    'Approved event should be rejectable.',
);
assertFalse(
    AdminEventModerationRules::canApplyAction('rejected', 'reject'),
    'Rejected event should not be rejectable again.',
);

// CPG-71: atmetimo veiksmui priezastis yra privaloma.
assertTrue(
    AdminEventModerationRules::requiresReason('reject'),
    'Reject action must require a reason.',
);
assertFalse(
    AdminEventModerationRules::requiresReason('approve'),
    'Approve action should not require a reason.',
);

echo "AdminEventRejectionRulesTest passed.\n";
