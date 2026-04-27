<?php
declare(strict_types=1);

require __DIR__ . '/AdminPanelTestBootstrap.php';

// Integracinis testas: tikrina pilną admin atmetimo kelią
// nuo maršruto ir controllerio iki įrašo pakeitimo DB bei JSON atsako.
$pdo = adminTestBootstrapDatabase();

$response = dispatchAdminRoute('POST', '/admin/panel/event-status', [
    'event_id' => '101',
    'action' => 'reject',
    'rejection_reason' => 'Missing schedule details',
    'tab' => 'pending',
]);

// Patikriname ne tik verslo rezultatą, bet ir tai,
// ką admin panelis gautų atgal po AJAX užklausos.
assertSame(200, $response['status'], 'Reject route should respond with HTTP 200.');
assertTrue($response['json']['ok'] === true, 'Reject route should succeed.');
assertSame(
    'Renginys atmestas.',
    $response['json']['message'] ?? null,
    'Reject route should return the success message.',
);
assertSame(
    0,
    (int) ($response['json']['data']['pendingCount'] ?? -1),
    'Pending count should drop after rejecting the only pending event.',
);
assertSame(
    [],
    $response['json']['data']['events'] ?? null,
    'Pending tab should no longer contain the rejected event.',
);

// Galutinis integracijos patikrinimas: būsena ir atmetimo priežastis
// turi būti realiai išsaugotos duomenų bazėje.
$event = fetchEventRecord($pdo, 101);
assertSame('rejected', $event['status'], 'Event should be persisted as rejected.');
assertSame(
    'Missing schedule details',
    $event['rejection_reason'],
    'Reject reason should be persisted.',
);

echo "AdminPanelRejectEventIntegrationTest passed.\n";
