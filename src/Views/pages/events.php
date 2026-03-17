<?php
$base = $base ?? "";
$events = $events ?? [];
$categories = $categories ?? [];
$priceRange = $priceRange ?? ['min' => 0, 'max' => 100];

$e = static fn($value) => htmlspecialchars(
    (string) $value,
    ENT_QUOTES,
    "UTF-8",
);
?>

<main class="container-ce section">
  <header class="section-head">
    <div>
      <h1 class="section-title">Visi renginiai</h1>
      <p class="section-subtitle">Naršyk artėjančius renginius savo mieste</p>
    </div>
  </header>

  <div class="grid grid-cols-1 lg:grid-cols-[320px_1fr] gap-8">
    <aside>
      <div class="bg-white p-6 rounded-lg shadow-md sticky top-4">
        <h3 class="text-lg font-semibold mb-4">Filtrai</h3>

        <!-- Tipas -->
        <div class="mb-6">
          <h4 class="font-medium mb-2">Tipas</h4>
          <input type="text" placeholder="Ieškoti tipų..." class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 mb-2">
          <div class="max-h-40 overflow-y-auto">
            <?php foreach ($categories as $category): ?>
              <button class="w-full text-left px-3 py-2 hover:bg-gray-100 active:bg-gray-200 rounded-md transition-colors">
                <?= $e($category) ?>
              </button>
            <?php endforeach; ?>
          </div>
        </div>

        <!-- Atstumas nuo vartotojo -->
        <div class="mb-6">
          <h4 class="font-medium mb-2">Atstumas nuo vartotojo</h4>
          <input type="range" min="0" max="50" step="1" class="w-full">
          <div class="text-sm text-gray-600 mt-1 flex justify-between">
            <span>0 km</span>
            <span>50 km</span>
          </div>
        </div>

        <!-- Renginio data -->
        <div class="mb-6">
          <h4 class="font-medium mb-2">Renginio data</h4>
          <div class="space-y-2">
            <input type="date" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
            <input type="date" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
          </div>
        </div>

        <!-- Kaina -->
        <div class="mb-6">
          <h4 class="font-medium mb-2">Kaina</h4>
          <input type="range" min="<?= $priceRange['min'] ?>" max="<?= $priceRange['max'] ?>" step="1" class="w-full">
          <div class="text-sm text-gray-600 mt-1 flex justify-between">
            <span>€<?= $priceRange['min'] ?></span>
            <span>€<?= $priceRange['max'] ?></span>
          </div>
        </div>

        <!-- Kalba -->
        <div class="mb-6">
          <h4 class="font-medium mb-2">Kalba</h4>
          <div class="flex gap-2">
            <button class="px-4 py-2 bg-gray-200 hover:bg-gray-300 active:bg-gray-400 rounded-md transition-colors">Lietuvių</button>
            <button class="px-4 py-2 bg-gray-200 hover:bg-gray-300 active:bg-gray-400 rounded-md transition-colors">Anglų</button>
          </div>
        </div>
      </div>
    </aside>

    <div class="flex-1">
      <?php
      $gridId = "eventsGridAll";
      $gridClass = "events-grid";
      $emptyText = "Renginiai nerasti";
      $basePath = ($base ?? "") . "/events";

      $partial = __DIR__ . "/../partials/events-grid.php";

      if (!is_file($partial)) {
          throw new RuntimeException("Missing partial: " . $partial);
      }

      require $partial;
      ?>
    </div>
  </div>
</main>

