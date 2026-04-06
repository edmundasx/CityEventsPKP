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
      <form id="filterForm" action="<?= rtrim($base ?? "", "/") ?>/events/filter" method="GET" class="bg-white p-6 rounded-lg shadow-md sticky top-4">
        <h3 class="text-lg font-semibold mb-4">Filtrai</h3>

        <!-- Tipas -->
        <div class="mb-6">
          <h4 class="font-medium mb-2">Tipas</h4>
          <input type="hidden" name="category" id="categoryInput" value="<?= htmlspecialchars($_GET['category'] ?? '') ?>">
          
          <div class="max-h-40 overflow-y-auto">
            <?php 
              $currentCategory = $_GET['category'] ?? '';
              foreach ($categories as $category): 
                $isActive = $currentCategory === $category;
                $count = $categoryCounts[$category] ?? 0;
                if ($count < 1) continue;
            ?>
              <button type="button" class="category-btn w-full text-left px-3 py-2 <?= $isActive ? 'bg-blue-100 font-medium' : 'hover:bg-gray-100 active:bg-gray-200' ?> rounded-md transition-colors flex justify-between items-center" data-value="<?= $e($category) ?>">
                <span><?= $e($category) ?></span>
                <span class="ml-2 text-xs text-gray-500 bg-gray-200 rounded-full px-2 py-0.5"><?= $count ?></span>
              </button>
            <?php endforeach; ?>
          </div>
        </div>

        <!-- Renginio data -->
        <div class="mb-6">
          <h4 class="font-medium mb-2">Renginio data</h4>
          <div class="space-y-2">
            <input type="date" name="date_from" value="<?= htmlspecialchars($_GET['date_from'] ?? '') ?>" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
            <input type="date" name="date_to" value="<?= htmlspecialchars($_GET['date_to'] ?? '') ?>" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
          </div>
        </div>

        <!-- Kaina -->
        <div class="mb-6">
          <?php $currentPriceMax = $_GET['price_max'] ?? $priceRange['max']; ?>
          <h4 class="font-medium mb-2">Max Kaina: <span id="currentPriceMax">€<?= htmlspecialchars($currentPriceMax) ?></span></h4>
          <input type="range" name="price_max" min="<?= $priceRange['min'] ?>" max="<?= $priceRange['max'] ?>" value="<?= htmlspecialchars($currentPriceMax) ?>" step="1" class="w-full">
          <div class="text-sm text-gray-600 mt-1 flex justify-between">
            <span>€<?= $priceRange['min'] ?></span>
            <span>€<?= $priceRange['max'] ?></span>
          </div>
        </div>
      </form>
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

<script>
document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('filterForm');
    
    // Category buttons logic
    const categoryInput = document.getElementById('categoryInput');
    document.querySelectorAll('.category-btn').forEach(btn => {
        btn.addEventListener('click', (e) => {
            if (categoryInput.value === btn.dataset.value) {
                categoryInput.value = ''; // Deselect
                btn.classList.remove('bg-blue-100', 'font-medium');
            } else {
                categoryInput.value = btn.dataset.value;
                document.querySelectorAll('.category-btn').forEach(b => b.classList.remove('bg-blue-100', 'font-medium'));
                btn.classList.add('bg-blue-100', 'font-medium');
            }
            triggerFilter();
        });
    });

    // Inputs change logic
    form.querySelectorAll('input[type="date"], input[type="range"]').forEach(input => {
        input.addEventListener('change', triggerFilter);
    });

    const priceMaxInput = document.querySelector('input[name="price_max"]');
    const priceDisplay = document.getElementById('currentPriceMax');
    if(priceMaxInput && priceDisplay) {
        priceMaxInput.addEventListener('input', () => {
            priceDisplay.textContent = '€' + priceMaxInput.value;
        });
    }

    function triggerFilter() {
        const url = new URL(form.action, window.location.origin);
        const params = new URLSearchParams(new FormData(form));
        url.search = params.toString();

        fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(res => res.text())
            .then(html => {
                const parser = new DOMParser();
                const doc = parser.parseFromString(html, 'text/html');
                const newGrid = doc.querySelector('.events-grid');
                if (newGrid) {
                    document.querySelector('.events-grid').replaceWith(newGrid);
                }
                
                // Keep URL updated for copy-pasting
                window.history.pushState({}, '', url);
            })
            .catch(err => console.error("Filter fetch error:", err));
    }
});
</script>
