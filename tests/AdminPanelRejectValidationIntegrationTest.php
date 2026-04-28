<?php
declare(strict_types=1);

require_once __DIR__ . '/AdminPanelTestBootstrap.php';

// Integracinis testas: tikrina validacijos scenarijų,
// kai admin bando atmesti renginį be priežasties.
$pdo = adminTestBootstrapDatabase();

$response = dispatchAdminRoute('POST', '/admin/panel/event-status', [
    'event_id' => '101',
    'action' => 'reject',
    'rejection_reason' => '   ',
    'tab' => 'pending',
]);

// Tikriname, kad validacija sustabdytų veiksmą controllerio lygyje,
// bet tuo pačiu grąžintų korektišką JSON atsaką admin paneliui.
assertSame(200, $response['status'], 'Validation failure should still respond with HTTP 200.');
assertTrue($response['json']['ok'] === false, 'Reject without reason should fail.');
assertTrue(
    is_string($response['json']['message'] ?? null) &&
    trim((string) $response['json']['message']) !== '',
    'Validation failure should include a non-empty message.',
);
assertSame(
    1,
    (int) ($response['json']['data']['pendingCount'] ?? -1),
    'Pending count should remain unchanged when validation fails.',
);
assertSame(
    1,
    count($response['json']['data']['events'] ?? []),
    'Pending tab should still include the event after validation fails.',
);
assertSame(
    101,
    (int) (($response['json']['data']['events'][0]['id'] ?? 0)),
    'The original pending event should remain visible.',
);

// Kad testas būtų integracinis, dar patvirtiname,
// jog po nesėkmingos validacijos DB įrašas nebuvo pakeistas.
$event = fetchEventRecord($pdo, 101);
assertSame('pending', $event['status'], 'Event status should stay pending.');
assertSame(null, $event['rejection_reason'], 'Reject reason should remain empty.');

