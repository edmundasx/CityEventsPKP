(() => {
  const state = {
    query: "",
    location: "",
    category: "",
    showAll: false,
  };

  const MAX_EVENT_SUGGESTIONS = 8;
  const MAX_PLACE_SUGGESTIONS = 12;

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
    const initialVisible = Number(gridEl.dataset.initialVisible || 0);
    const toggleBtn = document.getElementById("homeEventsToggle");
    state.showAll = gridEl.dataset.startExpanded === "1";

    const events = parseEvents(mapEl.dataset.events || "[]");
    const cards = getCards(gridEl);
    const eventsById = new Map(events.map((event) => [String(event.id), event]));

    const heroEl = mapEl.closest(".hero");
    const searchIndex = parseSearchIndex(
      heroEl?.dataset?.searchIndex || "[]",
    );
    const ltPlaces = parseLtPlaces(heroEl?.dataset?.ltPlaces || "[]");

    decorateCards(cards, eventsById);
    initLeafletMap(mapEl, events);
    bindInputs(
      cards,
      eventsById,
      initialVisible,
      toggleBtn,
      searchIndex,
      ltPlaces,
    );
    applyFilters(cards, eventsById, initialVisible, toggleBtn);
  }

  function parseEvents(raw) {
    try {
      const parsed = JSON.parse(raw);
      return Array.isArray(parsed) ? parsed : [];
    } catch (_error) {
      return [];
    }
  }

  function parseSearchIndex(raw) {
    try {
      const parsed = JSON.parse(raw);
      return Array.isArray(parsed) ? parsed : [];
    } catch (_error) {
      return [];
    }
  }

  function parseLtPlaces(raw) {
    try {
      const parsed = JSON.parse(raw);
      return Array.isArray(parsed) ? parsed.map(String) : [];
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
      card.dataset.organizer = normalize(event.organizer_name || "");
      card.dataset.tags = String(event.tags || "");
    });
  }

  function bindInputs(
    cards,
    eventsById,
    initialVisible,
    toggleBtn,
    searchIndex,
    ltPlaces,
  ) {
    const searchInput = document.getElementById("searchInput");
    const locationInput = document.getElementById("locationInput");
    const searchList = document.getElementById("searchSuggestions");
    const locationList = document.getElementById("locationSuggestions");
    const categoryButtons = Array.from(
      document.querySelectorAll(".cat-btn[data-category], .category[data-category]"),
    );

    function commitFromInputs() {
      state.query = searchInput?.value.trim() || "";
      state.location = locationInput?.value.trim() || "";
      hideSuggestionLists();
      applyFilters(cards, eventsById, initialVisible, toggleBtn);
    }

    function hideSuggestionLists() {
      if (searchList) {
        searchList.hidden = true;
        searchList.innerHTML = "";
      }
      if (locationList) {
        locationList.hidden = true;
        locationList.innerHTML = "";
      }
      if (searchInput) {
        searchInput.setAttribute("aria-expanded", "false");
      }
      if (locationInput) {
        locationInput.setAttribute("aria-expanded", "false");
      }
    }

    function searchSuggestAllowed() {
      return (
        searchInput &&
        document.activeElement === searchInput &&
        searchInput.value.trim().length > 0
      );
    }

    function locationSuggestAllowed() {
      return (
        locationInput &&
        document.activeElement === locationInput &&
        locationInput.value.trim().length > 0
      );
    }

    function eventRowMatchesQuery(row, q) {
      if (!q) return false;
      const parts = [
        row.title,
        row.organizer_name,
        row.location,
        row.category,
        row.district,
        row.tags,
      ];
      for (const part of parts) {
        if (normalize(part || "").includes(q)) {
          return true;
        }
      }
      const rawTags = String(row.tags || "").trim();
      if (rawTags) {
        const tagParts = rawTags.split(/[,;]+/);
        for (const t of tagParts) {
          if (normalize(t.trim()).includes(q)) {
            return true;
          }
        }
      }
      return false;
    }

    function renderSearchSuggestions() {
      if (!searchInput || !searchList) return;
      searchList.innerHTML = "";
      if (!searchSuggestAllowed()) {
        searchList.hidden = true;
        searchInput.setAttribute("aria-expanded", "false");
        return;
      }
      const q = normalize(searchInput.value.trim());
      if (!q) {
        searchList.hidden = true;
        searchInput.setAttribute("aria-expanded", "false");
        return;
      }
      const rows = searchIndex.filter((row) => eventRowMatchesQuery(row, q));
      const uniqueTitles = [];
      const seen = new Set();
      for (const row of rows) {
        const title = String(row?.title || "").trim();
        const key = normalize(title);
        if (!title || seen.has(key)) continue;
        seen.add(key);
        uniqueTitles.push(title);
        if (uniqueTitles.length >= MAX_EVENT_SUGGESTIONS) break;
      }
      const limited = uniqueTitles;
      if (!limited.length) {
        searchList.hidden = true;
        searchInput.setAttribute("aria-expanded", "false");
        return;
      }
      limited.forEach((title) => {
        const li = document.createElement("li");
        li.setAttribute("role", "option");
        li.innerHTML = `<span class="search-suggest-title">${escapeHtml(title)}</span>`;
        li.addEventListener("mousedown", (e) => {
          e.preventDefault();
          searchInput.value = title;
          commitFromInputs();
        });
        searchList.appendChild(li);
      });
      searchList.hidden = false;
      searchInput.setAttribute("aria-expanded", "true");
    }

    function renderLocationSuggestions() {
      if (!locationInput || !locationList) return;
      locationList.innerHTML = "";
      if (!locationSuggestAllowed()) {
        locationList.hidden = true;
        locationInput.setAttribute("aria-expanded", "false");
        return;
      }
      const q = normalize(locationInput.value.trim());
      if (!q) {
        locationList.hidden = true;
        locationInput.setAttribute("aria-expanded", "false");
        return;
      }
      const matches = ltPlaces
        .filter((place) => normalize(place).includes(q))
        .slice(0, MAX_PLACE_SUGGESTIONS);
      if (!matches.length) {
        locationList.hidden = true;
        locationInput.setAttribute("aria-expanded", "false");
        return;
      }
      matches.forEach((place) => {
        const li = document.createElement("li");
        li.setAttribute("role", "option");
        li.textContent = place;
        li.addEventListener("mousedown", (e) => {
          e.preventDefault();
          locationInput.value = place;
          commitFromInputs();
        });
        locationList.appendChild(li);
      });
      locationList.hidden = false;
      locationInput.setAttribute("aria-expanded", "true");
    }

    if (searchInput) {
      searchInput.addEventListener("focus", () => {
        renderSearchSuggestions();
      });

      searchInput.addEventListener("input", () => {
        renderSearchSuggestions();
      });

      searchInput.addEventListener("keydown", (event) => {
        if (event.key === "Escape") {
          hideSuggestionLists();
          return;
        }
        if (event.key === "Enter") {
          event.preventDefault();
          commitFromInputs();
        }
      });

      searchInput.addEventListener("blur", () => {
        setTimeout(() => {
          if (searchList && !searchList.matches(":hover")) {
            searchList.hidden = true;
            searchList.innerHTML = "";
            searchInput.setAttribute("aria-expanded", "false");
          }
        }, 150);
      });
    }

    if (locationInput) {
      locationInput.addEventListener("focus", () => {
        renderLocationSuggestions();
      });

      locationInput.addEventListener("input", () => {
        renderLocationSuggestions();
      });

      locationInput.addEventListener("keydown", (event) => {
        if (event.key === "Escape") {
          hideSuggestionLists();
          return;
        }
        if (event.key === "Enter") {
          event.preventDefault();
          commitFromInputs();
        }
      });

      locationInput.addEventListener("blur", () => {
        setTimeout(() => {
          if (locationList && !locationList.matches(":hover")) {
            locationList.hidden = true;
            locationList.innerHTML = "";
            locationInput.setAttribute("aria-expanded", "false");
          }
        }, 150);
      });
    }

    document.addEventListener("click", (e) => {
      const t = e.target;
      if (!(t instanceof Node)) return;
      if (searchInput?.contains(t) || searchList?.contains(t)) return;
      if (locationInput?.contains(t) || locationList?.contains(t)) return;
      hideSuggestionLists();
    });

    categoryButtons.forEach((button) => {
      button.addEventListener("click", () => {
        const category = button.dataset.category || "";
        if (state.category === category) {
          state.category = "";
        } else {
          state.category = category;
        }

        syncCategoryButtonState(categoryButtons);
        applyFilters(cards, eventsById, initialVisible, toggleBtn);
      });

      button.addEventListener("keydown", (event) => {
        if (event.key !== "Enter" && event.key !== " ") return;
        event.preventDefault();
        button.click();
      });
    });

    syncCategoryButtonState(categoryButtons);

    window.searchEvents = () => {
      commitFromInputs();
    };

    window.filterByCategory = (category) => {
      const normalized = normalize(category || "");
      state.category = state.category === normalized ? "" : normalized;
      syncCategoryButtonState(categoryButtons);
      applyFilters(cards, eventsById, initialVisible, toggleBtn);
    };

    if (toggleBtn) {
      toggleBtn.addEventListener("click", () => {
        state.showAll = !state.showAll;
        applyFilters(cards, eventsById, initialVisible, toggleBtn);
      });
    }
  }

  function syncCategoryButtonState(buttons) {
    buttons.forEach((button) => {
      const category = button.dataset.category || "";
      button.classList.toggle("is-active", state.category === category);
    });
  }

  function applyFilters(cards, eventsById, initialVisible, toggleBtn) {
    const query = normalize(state.query);
    const location = normalize(state.location);
    const category = normalize(state.category);

    const matchedIds = new Set();
    let matchedCount = 0;
    const emptyState = document.querySelector("#eventsGrid .js-events-empty");

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
      const haystackOrganizer = normalize(
        event?.organizer_name || card.dataset.organizer || "",
      );
      const haystackDistrict = normalize(event?.district || "");
      const tagsRaw = String(event?.tags ?? card.dataset.tags ?? "");

      const matchesQuery =
        !query ||
        haystackTitle.includes(query) ||
        haystackLocation.includes(query) ||
        haystackOrganizer.includes(query) ||
        itemCategory.includes(query) ||
        haystackDistrict.includes(query) ||
        tagsMatch(query, tagsRaw);
      const matchesLocation = !location || haystackLocation.includes(location);
      const matchesCategory = !category || categoryMatches(itemCategory, category);
      const matchesFilters = matchesQuery && matchesLocation && matchesCategory;

      let isVisible = false;
      if (matchesFilters) {
        matchedIds.add(String(id));
        const withinInitialLimit = initialVisible <= 0 || matchedCount < initialVisible;
        isVisible = state.showAll || withinInitialLimit;
        matchedCount += 1;
      }
      card.hidden = !isVisible;

    });

    updateToggleButton(toggleBtn, initialVisible, matchedCount);
    if (emptyState) {
      emptyState.hidden = matchedCount !== 0;
    }
    // Markers follow search/category filters, not view-all/show-less UI state.
    updateMapMarkers(matchedIds);
  }

  function updateToggleButton(toggleBtn, initialVisible, matchedCount) {
    if (!toggleBtn) return;
    toggleBtn.hidden = false;
    if (initialVisible <= 0 || matchedCount <= initialVisible) {
      toggleBtn.disabled = true;
      toggleBtn.setAttribute("aria-expanded", "false");
      toggleBtn.textContent = `View all (${matchedCount})`;
      return;
    }

    toggleBtn.disabled = false;
    const expanded = state.showAll;
    toggleBtn.setAttribute("aria-expanded", expanded ? "true" : "false");
    toggleBtn.textContent = expanded
      ? `Show less (${initialVisible})`
      : `View all (${matchedCount})`;
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

    const map = L.map(mapEl, {
      scrollWheelZoom: false,
    }).setView([54.6872, 25.2797], 11);

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
      marker.on("mouseover", function () {
        this.openPopup();
      });
      marker.on("mouseout", function () {
        this.closePopup();
      });
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

    return [
      '<div class="home-map-popup">',
      `<div class="home-map-popup-title">${title}</div>`,
      `<div class="home-map-popup-meta">${location}</div>`,
      `<div class="home-map-popup-meta">${date}</div>`,
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

  function tagsMatch(query, tagsRaw) {
    if (!query) return true;
    const raw = String(tagsRaw || "").trim();
    if (!raw) return false;
    if (normalize(raw).includes(query)) return true;
    return raw.split(/[,;]+/).some((part) => normalize(part.trim()).includes(query));
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
