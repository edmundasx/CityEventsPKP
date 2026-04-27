<?php
$base = $base ?? "";
$event = $event ?? null;

if (!is_array($event)) {
    $event = [];
}

$e = static fn($value) => htmlspecialchars(
    (string) $value,
    ENT_QUOTES,
    "UTF-8",
);

$titleText = $event["title"] ?? "Event details";
$date = $event["date"] ?? "";
$time = $event["time"] ?? "";
$location = $event["location"] ?? "";
$price = $event["price"] ?? "";
$description = $event["description"] ?? "";
$image = $event["image"] ?? "";
$category = $event["category"] ?? "";
$district = $event["district"] ?? "";
$isLoggedIn = (bool) ($isLoggedIn ?? false);
$isPastEvent = (bool) ($isPastEvent ?? false);
$hasReminder = (bool) ($hasReminder ?? false);
$currentReminderMinutes = isset($currentReminderMinutes) ? (int) $currentReminderMinutes : null;
$reminderOptions = is_array($reminderOptions ?? null) ? $reminderOptions : [
  30 => "30 min.",
  60 => "1 val.",
  1440 => "1 diena",
];
$eventId = (int) ($event["id"] ?? 0);
$returnTo = $base . "/events/" . $eventId;

$currentReminderLabel = "";
if (
  $currentReminderMinutes !== null &&
  isset($reminderOptions[$currentReminderMinutes])
) {
  $currentReminderLabel = (string) $reminderOptions[$currentReminderMinutes];
}
?>

<main class="container-ce section">
  <div class="grid grid-cols-1 lg:grid-cols-[1fr_380px] gap-8">
    <!-- Left Column: Content -->
    <div class="space-y-8">
      <div class="bg-white rounded-2xl overflow-hidden shadow-sm border border-slate-100">
        <?php if ($image !== ""): ?>
          <div class="aspect-video w-full overflow-hidden">
            <img src="<?= $e($image) ?>" alt="<?= $e($titleText) ?>" class="w-full h-full object-cover">
          </div>
        <?php endif; ?>
        
        <div class="p-8">
          <div class="flex flex-wrap gap-2 mb-4">
            <?php if ($category !== ""): ?>
              <span class="bg-brand/10 text-brand px-3 py-1 rounded-md text-sm font-semibold uppercase tracking-wider"><?= $e($category) ?></span>
            <?php endif; ?>
            <?php if ($district !== ""): ?>
              <span class="bg-slate-100 text-slate-600 px-3 py-1 rounded-md text-sm font-semibold uppercase tracking-wider"><?= $e($district) ?></span>
            <?php endif; ?>
          </div>
          
          <h1 class="text-3xl md:text-4xl font-extrabold text-slate-900 mb-6 leading-tight"><?= $e($titleText) ?></h1>
          
          <div class="prose prose-slate max-w-none">
            <h2 class="text-xl font-bold text-slate-900 mb-4 flex items-center gap-2">
              <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-brand"><path d="M14.5 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7.5L14.5 2z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><line x1="10" y1="9" x2="8" y2="9"/></svg>
              Renginio aprašymas
            </h2>
            <div class="text-slate-600 text-lg leading-relaxed whitespace-pre-wrap">
              <?= nl2br($e($description !== "" ? $description : "Šiam renginiui dar nėra aprašymo.")) ?>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Right Column: Sidebar Info Card -->
    <aside class="space-y-6">
      <div class="bg-white rounded-2xl p-6 shadow-md border border-slate-100 sticky top-4">
        <h3 class="text-lg font-bold text-slate-900 mb-6 pb-4 border-b border-slate-100">Renginio informacija</h3>
        
        <div class="space-y-6">
          <div class="flex gap-4">
            <div class="w-12 h-12 bg-slate-50 rounded-xl flex items-center justify-center text-brand flex-shrink-0">
              <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
            </div>
            <div>
              <div class="text-xs text-slate-500 font-bold uppercase tracking-wider mb-1">Data</div>
              <div class="text-slate-900 font-semibold"><?= $e($date) ?></div>
            </div>
          </div>

          <div class="flex gap-4">
            <div class="w-12 h-12 bg-slate-50 rounded-xl flex items-center justify-center text-brand flex-shrink-0">
              <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
            </div>
            <div>
              <div class="text-xs text-slate-500 font-bold uppercase tracking-wider mb-1">Laikas</div>
              <div class="text-slate-900 font-semibold"><?= $e($time) ?></div>
            </div>
          </div>

          <div class="flex gap-4">
            <div class="w-12 h-12 bg-slate-50 rounded-xl flex items-center justify-center text-brand flex-shrink-0">
              <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
            </div>
            <div>
              <div class="text-xs text-slate-500 font-bold uppercase tracking-wider mb-1">Vieta</div>
              <div class="text-slate-900 font-semibold"><?= $e($location) ?></div>
            </div>
          </div>

          <div class="flex gap-4">
            <div class="w-12 h-12 bg-slate-50 rounded-xl flex items-center justify-center text-brand flex-shrink-0">
              <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
            </div>
            <div>
              <div class="text-xs text-slate-500 font-bold uppercase tracking-wider mb-1">Kaina</div>
              <div class="text-slate-900 font-bold text-xl"><?= $e($price) ?></div>
            </div>
          </div>

          <button class="w-full bg-brand hover:bg-brand-dark text-white font-bold py-4 rounded-xl transition-all shadow-lg shadow-brand/20 active:scale-95 mt-4">
            Dalyvauti renginyje
          </button>

          <div class="rounded-xl border border-slate-200 p-4 bg-slate-50/70">
            <details class="group" <?= ($isLoggedIn && !$isPastEvent) ? "" : "open" ?> >
              <summary class="list-none <?= ($isLoggedIn && !$isPastEvent) ? "cursor-pointer" : "cursor-not-allowed pointer-events-none" ?>">
                <div class="flex items-center justify-between gap-3">
                  <div class="flex items-center gap-3">
                    <div class="w-11 h-11 rounded-xl flex items-center justify-center <?= !$isLoggedIn || $isPastEvent
                        ? "bg-slate-200 text-slate-400"
                        : ($hasReminder ? "bg-amber-100 text-amber-500" : "bg-slate-100 text-slate-600") ?>">
                      <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10.268 21a2 2 0 0 0 3.464 0"/><path d="M3.262 15.326A1 1 0 0 0 4 17h16a1 1 0 0 0 .74-1.674C19.41 13.956 18 12.499 18 8a6 6 0 0 0-12 0c0 4.499-1.411 5.956-2.738 7.326"/></svg>
                    </div>
                    <div>
                      <p class="text-xs text-slate-500 font-bold uppercase tracking-wider">Priminimas</p>
                      <?php if (!$isLoggedIn): ?>
                        <p class="text-sm text-slate-500 font-semibold">Prisijunkite, kad nustatytumete priminima</p>
                      <?php elseif ($isPastEvent): ?>
                        <p class="text-sm text-slate-500 font-semibold">Renginys jau pasibaiges</p>
                      <?php elseif ($hasReminder): ?>
                        <p class="text-sm text-amber-700 font-semibold">Nustatytas: <?= $e($currentReminderLabel) ?> pries rengini</p>
                      <?php else: ?>
                        <p class="text-sm text-slate-700 font-semibold">Pasirinkite, kada priminti apie rengini</p>
                      <?php endif; ?>
                    </div>
                  </div>

                  <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-slate-500 transition-transform group-open:rotate-180 <?= (!$isLoggedIn || $isPastEvent) ? "opacity-40" : "" ?>"><polyline points="6 9 12 15 18 9"/></svg>
                </div>
              </summary>

              <?php if ($isLoggedIn && !$isPastEvent): ?>
                <div class="pt-4 mt-4 border-t border-slate-200 space-y-3">
                  <form method="post" action="<?= $base ?>/events/<?= $eventId ?>/reminder" class="space-y-3">
                    <input type="hidden" name="return_to" value="<?= $e($returnTo) ?>">

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                      <?php foreach ($reminderOptions as $minutes => $label): ?>
                        <?php $minutesInt = (int) $minutes; ?>
                        <label class="flex items-center gap-2 rounded-lg border px-3 py-2 text-sm cursor-pointer transition <?= $currentReminderMinutes === $minutesInt ? "border-amber-400 bg-amber-50 text-amber-800" : "border-slate-200 bg-white text-slate-700 hover:border-brand/40" ?>">
                          <input type="radio" name="minutes_before" value="<?= $minutesInt ?>" class="accent-brand" required <?= $currentReminderMinutes === $minutesInt ? "checked" : "" ?>>
                          <span class="font-semibold"><?= $e($label) ?></span>
                        </label>
                      <?php endforeach; ?>
                    </div>

                    <button type="submit" class="w-full bg-brand hover:bg-brand-dark text-white font-bold py-3 rounded-xl transition-all">
                      <?= $hasReminder ? "Atnaujinti priminima" : "Issaugoti priminima" ?>
                    </button>
                  </form>

                  <?php if ($hasReminder): ?>
                    <form method="post" action="<?= $base ?>/events/<?= $eventId ?>/reminder/delete">
                      <input type="hidden" name="return_to" value="<?= $e($returnTo) ?>">
                      <button type="submit" class="w-full bg-white hover:bg-slate-100 border border-slate-300 text-slate-700 font-semibold py-3 rounded-xl transition-all">
                        Istrinti priminima
                      </button>
                    </form>
                  <?php endif; ?>
                </div>
              <?php endif; ?>
            </details>
          </div>
        </div>
      </div>
    </aside>
  </div>
</main>

