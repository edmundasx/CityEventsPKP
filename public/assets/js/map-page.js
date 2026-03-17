﻿(() => {
  function initMapPage() {
    const el = document.getElementById("ce-map");
    if (!el) return;

    if (window.__CE_MAP_INIT__) return;
    window.__CE_MAP_INIT__ = true;

    if (typeof L === "undefined") {
      console.error(
        "Leaflet is not loaded. Load leaflet.js before map-page.js",
      );
      return;
    }

    let events = [];
    try {
      events = JSON.parse(el.dataset.events || "[]");
    } catch (e) {
      console.error("Bad map events JSON", e);
    }

    const map = L.map(el, { zoomControl: false }).setView(
      [54.6872, 25.2797],
      12,
    );

    L.tileLayer("https://tile.openstreetmap.org/{z}/{x}/{y}.png", {
      maxZoom: 19,
      attribution: "&copy; OpenStreetMap contributors",
    }).addTo(map);

    const markers = [];
    const markerById = new Map();
    for (const ev of events) {
      if (typeof ev.lat !== "number" || typeof ev.lng !== "number") continue;

      const m = L.marker([ev.lat, ev.lng]).addTo(map);

      // Keep popup simple (avoid injecting raw HTML from DB)
      const title = (ev.title || "").toString();
      const loc = (ev.location || "").toString();
      const date = (ev.date || "").toString();

      m.bindPopup(
        `<div style="min-width:200px">
          <div style="font-weight:600">${escapeHtml(title)}</div>
          <div style="font-size:12px;opacity:.8">${escapeHtml(loc)}</div>
          <div style="font-size:12px;opacity:.8">${escapeHtml(date)}</div>
        </div>`,
      );

      m.on("mouseover", function () {
        this.openPopup();
      });
      m.on("mouseout", function () {
        this.closePopup();
      });

      markers.push(m);
      markerById.set(String(ev.id), m);
    }

    if (markers.length) {
      const group = L.featureGroup(markers);
      map.fitBounds(group.getBounds().pad(0.2));
    }

    document
      .getElementById("ce-zoom-in")
      ?.addEventListener("click", () => map.zoomIn());
    document
      .getElementById("ce-zoom-out")
      ?.addEventListener("click", () => map.zoomOut());

    initFilters({ events, map, markerById });
  }

  function initFilters({ events, map, markerById }) {
    const filterInputs = Array.from(
      document.querySelectorAll(
        ".map-filters input[type='checkbox'], .map-filters input[type='radio']",
      ),
    );
    if (!filterInputs.length) return;

    const clearButton = document.getElementById("clearAllBtn");
    const meta = document.getElementById("activeFiltersMeta");
    const chips = document.getElementById("activeChips");
    const resultCount = document.getElementById("resultCount");
    const cards = Array.from(
      document.querySelectorAll("[data-event-card='1']"),
    );
    const emptyState = document.getElementById("resultsEmpty");

    const labelsByValue = new Map();
    filterInputs.forEach((input) => {
      const label = input.closest("label");
      const text = label ? label.textContent || "" : "";
      labelsByValue.set(`${input.name}:${input.value}`, text.trim());
    });

    function applyFilters() {
      const active = getActiveFilters(filterInputs);
      const activeCount = countActiveFilters(active);

      if (meta) {
        meta.textContent = `${activeCount} filters applied`;
      }

      if (chips) {
        chips.innerHTML = "";
        const chipItems = buildChips(active, labelsByValue);
        for (const chip of chipItems) {
          chips.appendChild(chip);
        }
      }

      const matchingIds = new Set();
      for (const ev of events) {
        if (matchesFilters(ev, active)) {
          matchingIds.add(String(ev.id));
        }
      }

      let visibleCount = 0;
      for (const card of cards) {
        const id = card.dataset.eventId || "";
        const isMatch = matchingIds.has(id);
        card.hidden = !isMatch;
        if (isMatch) visibleCount += 1;
      }

      if (resultCount) {
        resultCount.textContent = String(visibleCount);
      }
      if (emptyState) {
        emptyState.hidden = visibleCount !== 0;
      }

      updateMarkers({ matchingIds, map, markerById });
    }

    filterInputs.forEach((input) => {
      input.addEventListener("change", applyFilters);
    });

    chips?.addEventListener("click", (event) => {
      const target = event.target;
      if (!(target instanceof HTMLButtonElement)) return;
      const name = target.dataset.filterName;
      const value = target.dataset.filterValue;
      if (!name || !value) return;
      const input = filterInputs.find(
        (item) => item.name === name && item.value === value,
      );
      if (input) {
        input.checked = false;
        applyFilters();
      }
    });

    clearButton?.addEventListener("click", () => {
      filterInputs.forEach((input) => {
        input.checked = false;
      });
      applyFilters();
    });

    applyFilters();
  }

  function getActiveFilters(inputs) {
    const categories = [];
    const districts = [];
    const prices = [];
    let dateRange = "";

    for (const input of inputs) {
      if (!input.checked) continue;
      if (input.name === "category") {
        categories.push(input.value);
      } else if (input.name === "district") {
        districts.push(input.value);
      } else if (input.name === "price") {
        prices.push(input.value);
      } else if (input.name === "dateRange") {
        dateRange = input.value;
      }
    }

    return { categories, districts, prices, dateRange };
  }

  function countActiveFilters(active) {
    let count =
      active.categories.length + active.districts.length + active.prices.length;
    if (active.dateRange) count += 1;
    return count;
  }

  function buildChips(active, labelsByValue) {
    const chips = [];

    for (const value of active.categories) {
      chips.push(createChip("category", value, labelsByValue));
    }
    for (const value of active.districts) {
      chips.push(createChip("district", value, labelsByValue));
    }
    for (const value of active.prices) {
      chips.push(createChip("price", value, labelsByValue));
    }
    if (active.dateRange) {
      chips.push(createChip("dateRange", active.dateRange, labelsByValue));
    }

    return chips.filter(Boolean);
  }

  function createChip(name, value, labelsByValue) {
    const label = labelsByValue.get(`${name}:${value}`) || value;
    const button = document.createElement("button");
    button.type = "button";
    button.className = "map-filters-chip";
    button.dataset.filterName = name;
    button.dataset.filterValue = value;
    button.textContent = label;
    return button;
  }

  function matchesFilters(event, active) {
    if (active.categories.length) {
      const category = normalize(event.category);
      const wanted = active.categories.map((value) => normalize(value));
      if (!wanted.includes(category)) return false;
    }

    if (active.districts.length) {
      const district = normalize(event.district);
      const wanted = active.districts.map((value) => normalize(value));
      if (!wanted.includes(district)) return false;
    }

    if (active.prices.length) {
      const isFree = Boolean(event.is_free) || Number(event.price_eur) <= 0;
      const priceType = isFree ? "free" : "paid";
      if (!active.prices.includes(priceType)) return false;
    }

    if (active.dateRange) {
      if (!matchesDateRange(event.date, active.dateRange)) return false;
    }

    return true;
  }

  function matchesDateRange(dateValue, range) {
    if (!dateValue) return false;
    if (range === "custom") return true;

    const today = startOfDay(new Date());
    const eventDate = startOfDay(new Date(`${dateValue}T00:00:00`));

    if (Number.isNaN(eventDate.getTime())) return false;

    if (range === "today") {
      return sameDay(eventDate, today);
    }

    if (range === "tomorrow") {
      const tomorrow = addDays(today, 1);
      return sameDay(eventDate, tomorrow);
    }

    if (range === "weekend") {
      const { start, end } = weekendRange(today);
      return eventDate >= start && eventDate <= end;
    }

    return true;
  }

  function updateMarkers({ matchingIds, map, markerById }) {
    markerById.forEach((marker, id) => {
      if (matchingIds.has(id)) {
        if (!map.hasLayer(marker)) marker.addTo(map);
      } else if (map.hasLayer(marker)) {
        map.removeLayer(marker);
      }
    });
  }

  function normalize(value) {
    return String(value || "")
      .trim()
      .toLowerCase();
  }

  function startOfDay(date) {
    const copy = new Date(date);
    copy.setHours(0, 0, 0, 0);
    return copy;
  }

  function addDays(date, amount) {
    const copy = new Date(date);
    copy.setDate(copy.getDate() + amount);
    return copy;
  }

  function weekendRange(today) {
    const day = today.getDay();
    if (day === 6) {
      return { start: today, end: addDays(today, 1) };
    }
    if (day === 0) {
      return { start: addDays(today, -1), end: today };
    }
    const start = addDays(today, 6 - day);
    return { start, end: addDays(start, 1) };
  }

  function sameDay(a, b) {
    return (
      a.getFullYear() === b.getFullYear() &&
      a.getMonth() === b.getMonth() &&
      a.getDate() === b.getDate()
    );
  }

  function escapeHtml(s) {
    return String(s)
      .replaceAll("&", "&amp;")
      .replaceAll("<", "&lt;")
      .replaceAll(">", "&gt;")
      .replaceAll('"', "&quot;")
      .replaceAll("'", "&#039;");
  }

  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", initMapPage);
  } else {
    initMapPage();
  }
})();
