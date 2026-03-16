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

// CPG-70: tik laukiantis renginys gali buti patvirtintas.
assertTrue(
    AdminEventModerationRules::canApplyAction('pending', 'approve'),
    'Pending event should be approvable.',
);
assertFalse(
    AdminEventModerationRules::canApplyAction('approved', 'approve'),
    'Approved event should not be approvable again.',
);
assertFalse(
    AdminEventModerationRules::canApplyAction('rejected', 'approve'),
    'Rejected event should not be approvable directly.',
);

// CPG-70: atmetimas leidziamas tik laukianciam arba jau patvirtintam renginiui.
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

// CPG-70/CPG-71: atmetimo veiksmui reikia priezasties.
assertTrue(
    AdminEventModerationRules::requiresReason('reject'),
    'Reject action must require a reason.',
);
assertFalse(
    AdminEventModerationRules::requiresReason('approve'),
    'Approve action should not require a reason.',
);

echo "AdminEventModerationRulesTest passed.\n";
