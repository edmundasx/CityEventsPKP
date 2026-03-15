(() => {
  const state = {
    query: "",
    location: "",
    category: "",
  };

  const mapState = {
    map: null,
    markersLayer: null,
    markersById: new Map(),
  };
  const categoryAliases = {
    music: ["music", "muzika"],
    arts: ["arts", "menas", "art"],
    charity: ["charity", "labdara"],
    business: ["business", "verslas"],
    education: ["education", "svietimas", "sveitimas", "mokslas"],
    food: ["food", "maistas", "gerimai", "maistas ir gerimai"],
  };

  function initHomeMap() {
    const mapEl = document.getElementById("homeHeroMap");
    const gridEl = document.getElementById("eventsGrid");
    if (!mapEl || !gridEl) return;

    const events = parseEvents(mapEl.dataset.events || "[]");
    const cards = getCards(gridEl);
    const eventsById = new Map(events.map((event) => [String(event.id), event]));

    decorateCards(cards, eventsById);
    initLeafletMap(mapEl, events);
    bindInputs(cards, eventsById);
    applyFilters(cards, eventsById);
  }

  function parseEvents(raw) {
    try {
      const parsed = JSON.parse(raw);
      return Array.isArray(parsed) ? parsed : [];
    } catch (_error) {
      return [];
    }
  }

  function getCards(gridEl) {
    return Array.from(gridEl.querySelectorAll(".event-card"));
  }

  function decorateCards(cards, eventsById) {
    cards.forEach((card) => {
      const id = getCardEventId(card);
      if (!id) return;

      const event = eventsById.get(id);
      if (!event) return;

      card.dataset.eventId = id;
      card.dataset.category = normalize(event.category || "");
      card.dataset.location = normalize(event.location || "");
      card.dataset.title = normalize(event.title || "");
    });
  }

  function bindInputs(cards, eventsById) {
    const searchInput = document.getElementById("searchInput");
    const locationInput = document.getElementById("locationInput");
    const categoryButtons = Array.from(
      document.querySelectorAll(".cat-btn[data-category], .category[data-category]"),
    );

    if (searchInput) {
      searchInput.addEventListener("input", () => {
        state.query = searchInput.value.trim();
        applyFilters(cards, eventsById);
      });

      searchInput.addEventListener("keydown", (event) => {
        if (event.key === "Enter") {
          state.query = searchInput.value.trim();
          applyFilters(cards, eventsById);
        }
      });
    }

    if (locationInput) {
      locationInput.addEventListener("input", () => {
        state.location = locationInput.value.trim();
        applyFilters(cards, eventsById);
      });

      locationInput.addEventListener("keydown", (event) => {
        if (event.key === "Enter") {
          state.location = locationInput.value.trim();
          applyFilters(cards, eventsById);
        }
      });
    }

    categoryButtons.forEach((button) => {
      button.addEventListener("click", () => {
        const category = button.dataset.category || "";
        if (state.category === category) {
          state.category = "";
        } else {
          state.category = category;
        }

        syncCategoryButtonState(categoryButtons);
        applyFilters(cards, eventsById);
      });

      button.addEventListener("keydown", (event) => {
        if (event.key !== "Enter" && event.key !== " ") return;
        event.preventDefault();
        button.click();
      });
    });

    syncCategoryButtonState(categoryButtons);

    window.searchEvents = () => {
      state.query = searchInput?.value.trim() || "";
      state.location = locationInput?.value.trim() || "";
      applyFilters(cards, eventsById);
    };

    window.filterByCategory = (category) => {
      const normalized = normalize(category || "");
      state.category = state.category === normalized ? "" : normalized;
      syncCategoryButtonState(categoryButtons);
      applyFilters(cards, eventsById);
    };
  }

  function syncCategoryButtonState(buttons) {
    buttons.forEach((button) => {
      const category = button.dataset.category || "";
      button.classList.toggle("is-active", state.category === category);
    });
  }

  function applyFilters(cards, eventsById) {
    const query = normalize(state.query);
    const location = normalize(state.location);
    const category = normalize(state.category);

    const visibleIds = new Set();

    cards.forEach((card) => {
      const id = card.dataset.eventId || getCardEventId(card);
      if (!id) {
        card.hidden = false;
        return;
      }

      const event = eventsById.get(id);
      const haystackTitle = normalize(event?.title || card.dataset.title || "");
      const haystackLocation = normalize(
        event?.location || card.dataset.location || "",
      );
      const itemCategory = normalize(event?.category || card.dataset.category || "");

      const matchesQuery =
        !query ||
        haystackTitle.includes(query) ||
        haystackLocation.includes(query);
      const matchesLocation = !location || haystackLocation.includes(location);
      const matchesCategory = !category || categoryMatches(itemCategory, category);

      const isVisible = matchesQuery && matchesLocation && matchesCategory;
      card.hidden = !isVisible;

      if (isVisible) {
        visibleIds.add(String(id));
      }
    });

    updateMapMarkers(visibleIds);
  }

  function categoryMatches(itemCategory, selectedCategory) {
    if (!selectedCategory) return true;
    if (itemCategory === selectedCategory) return true;

    const aliases = categoryAliases[selectedCategory] || [selectedCategory];
    return aliases.some((alias) => itemCategory.includes(normalize(alias)));
  }

  function getCardEventId(card) {
    const href = card.getAttribute("href") || "";
    const segments = href.split("/").filter(Boolean);
    const maybeId = segments[segments.length - 1] || "";
    return /^\d+$/.test(maybeId) ? maybeId : "";
  }

  function initLeafletMap(mapEl, events) {
    if (typeof L === "undefined") return;

    const map = L.map(mapEl).setView([54.6872, 25.2797], 11);

    L.tileLayer("https://tile.openstreetmap.org/{z}/{x}/{y}.png", {
      maxZoom: 19,
      attribution: "&copy; OpenStreetMap contributors",
    }).addTo(map);

    const markersLayer = L.layerGroup().addTo(map);

    events.forEach((event) => {
      if (typeof event.lat !== "number" || typeof event.lng !== "number") {
        return;
      }

      const marker = L.marker([event.lat, event.lng]);
      marker.bindPopup(buildPopup(event));
      mapState.markersById.set(String(event.id), marker);
    });

    mapState.map = map;
    mapState.markersLayer = markersLayer;

    setTimeout(() => {
      map.invalidateSize();
      updateMapMarkers(new Set(events.map((event) => String(event.id))));
    }, 0);
  }

  function buildPopup(event) {
    const title = escapeHtml(event.title || "");
    const location = escapeHtml(event.location || "");
    const date = escapeHtml([event.date || "", event.time || ""].join(" ").trim());
    const url = escapeHtml(event.url || "#");

    return [
      '<div class="home-map-popup">',
      `<div class="home-map-popup-title">${title}</div>`,
      `<div class="home-map-popup-meta">${location}</div>`,
      `<div class="home-map-popup-meta">${date}</div>`,
      `<a class="home-map-popup-link" href="${url}">View</a>`,
      "</div>",
    ].join("");
  }

  function updateMapMarkers(visibleIds) {
    if (!mapState.map || !mapState.markersLayer) return;

    mapState.markersLayer.clearLayers();

    const visibleMarkers = [];
    mapState.markersById.forEach((marker, id) => {
      if (!visibleIds.has(id)) return;
      marker.addTo(mapState.markersLayer);
      visibleMarkers.push(marker);
    });

    if (!visibleMarkers.length) return;

    const group = L.featureGroup(visibleMarkers);
    const bounds = group.getBounds();
    if (bounds.isValid()) {
      mapState.map.fitBounds(bounds.pad(0.2), { maxZoom: 13 });
    }
  }

  function normalize(value) {
    return String(value || "").trim().toLowerCase();
  }

  function escapeHtml(value) {
    return String(value)
      .replace(/&/g, "&amp;")
      .replace(/</g, "&lt;")
      .replace(/>/g, "&gt;")
      .replace(/\"/g, "&quot;")
      .replace(/'/g, "&#39;");
  }

  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", initHomeMap);
  } else {
    initHomeMap();
  }
})();
