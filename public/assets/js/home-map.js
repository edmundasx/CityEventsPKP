(() => {
  const state = {
    query: "",
    location: "",
    /** Normalized category keys; empty set = no category filter. Multiple = OR. */
    categories: new Set(),
    showAll: false,
  };
  const categoryViewState = {
    expanded: false,
    /** Chips visible when collapsed (used for Mažiau (m)). */
    collapsedVisible: 0,
  };

  const MAX_EVENT_SUGGESTIONS = 8;
  const MAX_PLACE_SUGGESTIONS = 12;

  const mapState = {
    map: null,
    markersLayer: null,
    markersById: new Map(),
    lithuaniaBounds: null,
    ltMapTargets: [],
    appBase: "",
  };
  const userGeoState = {
    attempted: false,
    lat: null,
    lng: null,
  };

  function initHomeMap() {
    const mapEl = document.getElementById("homeHeroMap");
    const gridEl = document.getElementById("eventsGrid");
    if (!mapEl || !gridEl) return;
    const initialVisible = Number(gridEl.dataset.initialVisible || 0);
    const toggleBtn = document.getElementById("homeEventsToggle");
    state.showAll = gridEl.dataset.startExpanded === "1";

    const events = parseEventsFromPage(mapEl);
    const cards = getCards(gridEl);
    const eventsById = new Map(events.map((event) => [String(event.id), event]));

    const heroEl = mapEl.closest(".hero");
    const searchIndex = parseSearchIndex(
      heroEl?.dataset?.searchIndex || "[]",
    );
    const ltPlaces = parseLtPlaces(heroEl?.dataset?.ltPlaces || "[]");
    const ltMapTargets = parseLtMapTargets(
      heroEl?.dataset?.ltMapTargets || "[]",
    );

    decorateCards(cards, eventsById);
    const appBase = (heroEl?.dataset?.appBase ?? "").replace(/\/$/, "");
    mapState.appBase = appBase;
    initLeafletMap(mapEl, events, ltMapTargets);

    bindInputs(
      cards,
      eventsById,
      initialVisible,
      toggleBtn,
      searchIndex,
      ltPlaces,
      appBase,
      mapEl,
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

  function parseEventsFromPage(mapEl) {
    const embedded = document.getElementById("homeMapEventsData");
    if (embedded && embedded.textContent.trim()) {
      try {
        const parsed = JSON.parse(embedded.textContent);
        if (Array.isArray(parsed)) {
          return parsed;
        }
      } catch (_error) {
        /* fall back */
      }
    }
    return parseEvents(mapEl.dataset.events || "[]");
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

  function parseLtMapTargets(raw) {
    try {
      const parsed = JSON.parse(raw);
      if (!Array.isArray(parsed)) {
        return [];
      }
      return parsed
        .map((row) => ({
          name: String(row?.name ?? ""),
          lat: Number(row?.lat),
          lng: Number(row?.lng),
          zoom: Number(row?.zoom),
        }))
        .filter(
          (row) =>
            row.name &&
            Number.isFinite(row.lat) &&
            Number.isFinite(row.lng) &&
            Number.isFinite(row.zoom),
        );
    } catch (_error) {
      return [];
    }
  }

  function matchCityToKnownPlace(rawCity, places) {
    const trimmed = String(rawCity || "").trim();
    if (!trimmed) {
      return "";
    }
    const rawN = normalize(trimmed);
    for (const p of places) {
      if (normalize(p) === rawN) {
        return p;
      }
    }
    let best = "";
    let bestLen = 0;
    for (const p of places) {
      const n = normalize(p);
      if (!n) {
        continue;
      }
      if (rawN.includes(n) || n.includes(rawN)) {
        if (n.length > bestLen) {
          best = p;
          bestLen = n.length;
        }
      }
    }
    return best || trimmed;
  }

  async function fetchReverseCity(lat, lon, appBase) {
    const root = (appBase || "").replace(/\/$/, "");
    const params = new URLSearchParams({
      lat: String(lat),
      lon: String(lon),
    });
    const url = `${root}/api/reverse-geocode?${params.toString()}`;
    const res = await fetch(url, {
      headers: { Accept: "application/json" },
    });
    if (!res.ok) {
      return null;
    }
    const data = await res.json();
    const city = data?.city;
    return typeof city === "string" && city.trim() !== "" ? city.trim() : null;
  }

  function requestUserCityAutofill(locationInput, ltPlaces, commitFromInputs, appBase) {
    if (!locationInput || !navigator.geolocation) {
      return;
    }
    if (locationInput.value.trim() !== "") {
      return;
    }

    navigator.geolocation.getCurrentPosition(
      (pos) => {
        void (async () => {
          if (locationInput.value.trim() !== "") {
            return;
          }
          try {
            const rawCity = await fetchReverseCity(
              pos.coords.latitude,
              pos.coords.longitude,
              appBase,
            );
            if (!rawCity || locationInput.value.trim() !== "") {
              return;
            }
            locationInput.value = matchCityToKnownPlace(rawCity, ltPlaces);
            commitFromInputs();
          } catch (_error) {
            /* ignore */
          }
        })();
      },
      () => {},
      {
        enableHighAccuracy: false,
        timeout: 12000,
        maximumAge: 600000,
      },
    );
  }

  function findPlaceView(location, targets) {
    const q = normalize(location);
    if (!q || !targets.length) {
      return null;
    }
    let best = null;
    let bestLen = 0;
    for (const p of targets) {
      const n = normalize(p.name);
      if (!n) {
        continue;
      }
      if (q === n) {
        return p;
      }
      if (q.includes(n) || n.includes(q)) {
        if (n.length > bestLen) {
          best = p;
          bestLen = n.length;
        }
      }
    }
    return best;
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
    appBase,
    mapEl,
  ) {
    const searchInput = document.getElementById("searchInput");
    const locationInput = document.getElementById("locationInput");
    const searchList = document.getElementById("searchSuggestions");
    const locationList = document.getElementById("locationSuggestions");
    const categoryButtons = Array.from(
      document.querySelectorAll(".cat-btn[data-category], .category[data-category]"),
    );
    initDynamicCategoryBar(mapEl, categoryButtons);

    function commitFromInputs(skipHeroActivation = false) {
      state.query = searchInput?.value.trim() || "";
      state.location = locationInput?.value.trim() || "";
      hideSuggestionLists();
      if (
        !skipHeroActivation &&
        (state.query || state.location)
      ) {
        activateHeroSearchFocus();
      }
      applyFilters(cards, eventsById, initialVisible, toggleBtn);
    }

    function activateHeroSearchFocus() {
      document.querySelector(".hero")?.classList.add("hero--search-focused");
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
      searchInput.addEventListener("input", () => {
        renderSearchSuggestions();
        if (searchInput.value.trim() === "") {
          commitFromInputs();
        }
      });

      searchInput.addEventListener("focus", () => {
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
        const cat = normalize(button.dataset.category || "");
        if (!cat) return;
        if (state.categories.has(cat)) {
          state.categories.delete(cat);
        } else {
          state.categories.add(cat);
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
      if (!normalized) return;
      if (state.categories.has(normalized)) {
        state.categories.delete(normalized);
      } else {
        state.categories.add(normalized);
      }
      syncCategoryButtonState(categoryButtons);
      applyFilters(cards, eventsById, initialVisible, toggleBtn);
    };

    if (toggleBtn) {
      toggleBtn.addEventListener("click", () => {
        state.showAll = !state.showAll;
        applyFilters(cards, eventsById, initialVisible, toggleBtn);
      });
    }

    setTimeout(() => {
      requestUserCityAutofill(
        locationInput,
        ltPlaces,
        () => commitFromInputs(true),
        appBase,
      );
    }, 400);
  }

  function syncCategoryButtonState(buttons) {
    buttons.forEach((button) => {
      const cat = normalize(button.dataset.category || "");
      button.classList.toggle("is-active", cat !== "" && state.categories.has(cat));
    });
  }

  function applyFilters(cards, eventsById, initialVisible, toggleBtn) {
    const query = normalize(state.query);
    const location = normalize(state.location);

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
      const matchesCategory =
        state.categories.size === 0 ||
        [...state.categories].some((sel) =>
          categoryMatches(itemCategory, sel),
        );
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
    if (!itemCategory) return false;
    if (itemCategory === selectedCategory) return true;
    const categories = splitCategories(itemCategory);
    return categories.includes(selectedCategory);
  }

  function getCardEventId(card) {
    const href = card.getAttribute("href") || "";
    const segments = href.split("/").filter(Boolean);
    const maybeId = segments[segments.length - 1] || "";
    return /^\d+$/.test(maybeId) ? maybeId : "";
  }

  function initLeafletMap(mapEl, events, ltMapTargets) {
    if (typeof L === "undefined") return;

    const lithuaniaBounds = L.latLngBounds(
      [53.85, 20.65],
      [56.55, 26.95],
    );

    const map = L.map(mapEl, {
      scrollWheelZoom: false,
    });

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
      marker.bindPopup(buildPopup(event), {
        maxWidth: 320,
        className: "home-map-leaflet-popup",
      });
      marker.on("popupopen", function (e) {
        const el = e.popup?.getElement?.();
        const link = el?.querySelector?.(".home-map-popup-directions");
        if (link) {
          link.setAttribute(
            "href",
            buildRouteLink(Number(event.lat), Number(event.lng)),
          );
        }
      });
      mapState.markersById.set(String(event.id), marker);
    });

    mapState.map = map;
    mapState.markersLayer = markersLayer;
    mapState.lithuaniaBounds = lithuaniaBounds;
    mapState.ltMapTargets = ltMapTargets;

    map.fitBounds(lithuaniaBounds, { padding: [16, 16], maxZoom: 8 });

    setTimeout(() => {
      map.invalidateSize();
      updateMapMarkers(new Set(events.map((event) => String(event.id))));
    }, 0);
  }

  function buildPopup(event) {
    const title = escapeHtml(event.title || "");
    const location = escapeHtml(event.location || "");
    const date = escapeHtml([event.date || "", event.time || ""].join(" ").trim());
    const price = escapeHtml(event.price || "");
    const image = escapeHtml(event.image || "");
    const eventLat = Number(event.lat);
    const eventLng = Number(event.lng);
    const routeLink = buildRouteLink(eventLat, eventLng);
    const detailsHref = `${mapState.appBase}/events/${encodeURIComponent(String(event.id || ""))}`;

    const hasMedia = Boolean((event.image || "").trim() || (event.price || "").trim());
    const mediaHtml = hasMedia
      ? [
          '<div class="home-map-popup-media">',
          (event.image || "").trim()
            ? `<img class="home-map-popup-image" src="${image}" alt="${title}">`
            : '<div class="home-map-popup-image-placeholder" aria-hidden="true"></div>',
          price
            ? `<div class="event-price">${price}</div>`
            : "",
          "</div>",
        ].join("")
      : "";

    return [
      '<div class="home-map-popup">',
      mediaHtml,
      '<div class="home-map-popup-body">',
      '<div class="home-map-popup-text-stack">',
      `<div class="home-map-popup-title">${title}</div>`,
      `<div class="home-map-popup-meta">${location}</div>`,
      `<div class="home-map-popup-meta">${date}</div>`,
      `<a class="home-map-popup-link" href="${detailsHref}">Peržiūrėti renginį</a>`,
      `<a class="home-map-popup-link home-map-popup-directions" target="_blank" rel="noopener noreferrer" href="${routeLink}">Gauti maršrutą per Google Maps</a>`,
      "</div>",
      "</div>",
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

    const loc = (state.location || "").trim();
    const bounds = mapState.lithuaniaBounds;
    const query = normalize(state.query);

    if (query && visibleMarkers.length > 0) {
      const map = mapState.map;
      if (visibleMarkers.length === 1) {
        const marker = visibleMarkers[0];
        map.flyTo(marker.getLatLng(), 15, {
          animate: true,
          duration: 0.45,
        });
        map.once("moveend", () => {
          const dy = Math.round(map.getSize().y * 0.22);
          map.panBy(L.point(0, -dy), { animate: true, duration: 0.35 });
          map.once("moveend", () => {
            marker.openPopup();
          });
        });
      } else {
        const resultBounds = L.featureGroup(visibleMarkers).getBounds();
        if (resultBounds.isValid()) {
          const h = map.getSize().y;
          const extraTop = Math.round(h * 0.18);
          map.fitBounds(resultBounds, {
            paddingTopLeft: L.point(40, 40 + extraTop),
            paddingBottomRight: L.point(40, 40),
            maxZoom: 15,
          });
          map.once("moveend", () => {
            const dy = Math.round(map.getSize().y * 0.14);
            map.panBy(L.point(0, -dy), { animate: true, duration: 0.3 });
          });
        }
      }
      return;
    }

    if (!normalize(loc)) {
      if (bounds && bounds.isValid()) {
        mapState.map.fitBounds(bounds, { padding: [16, 16], maxZoom: 8 });
      }
      return;
    }

    const place = findPlaceView(loc, mapState.ltMapTargets);
    if (place) {
      mapState.map.flyTo([place.lat, place.lng], place.zoom, {
        duration: 0.45,
        animate: true,
      });
      return;
    }

    if (bounds && bounds.isValid()) {
      mapState.map.fitBounds(bounds, { padding: [16, 16], maxZoom: 8 });
    }
  }

  function normalize(value) {
    return String(value || "").trim().toLowerCase();
  }

  function splitCategories(value) {
    return String(value || "")
      .split(",")
      .map((part) => normalize(part))
      .filter(Boolean);
  }

  function initDynamicCategoryBar(_mapEl, categoryButtons) {
    const categoryBar = document.getElementById("homeCategoryBar");
    const toggleButton = document.getElementById("homeCategoryToggle");
    if (
      !categoryBar ||
      !toggleButton ||
      !categoryBar.contains(toggleButton) ||
      !categoryButtons.length
    ) {
      return;
    }

    const expandLimit = Number(categoryBar.dataset.expandLimit || 20);
    const sortedButtons = [...categoryButtons].sort((a, b) => {
      const ar = Number(a.dataset.categoryRank || 0);
      const br = Number(b.dataset.categoryRank || 0);
      return br - ar;
    });

    function categoryBarOverflows() {
      return categoryBar.scrollWidth > categoryBar.clientWidth + 1;
    }

    function maxCollapsedVisible(total) {
      toggleButton.hidden = true;
      toggleButton.style.visibility = "";
      sortedButtons.forEach((btn, i) => {
        btn.hidden = i >= total;
      });
      void categoryBar.offsetHeight;

      if (!categoryBarOverflows()) {
        return total;
      }

      toggleButton.hidden = false;
      toggleButton.style.visibility = "hidden";
      toggleButton.textContent = `Daugiau (${Math.max(0, total - 1)})`;

      let lo = 1;
      let hi = total;
      let best = 1;
      while (lo <= hi) {
        const mid = (lo + hi) >> 1;
        sortedButtons.forEach((btn, i) => {
          btn.hidden = i >= mid;
        });
        void categoryBar.offsetHeight;
        if (!categoryBarOverflows()) {
          best = mid;
          lo = mid + 1;
        } else {
          hi = mid - 1;
        }
      }

      toggleButton.style.visibility = "";
      sortedButtons.forEach((btn, i) => {
        btn.hidden = i >= best;
      });
      return best;
    }

    let resizeQueued = false;
    function queueResizeApply() {
      if (resizeQueued) {
        return;
      }
      resizeQueued = true;
      requestAnimationFrame(() => {
        resizeQueued = false;
        if (!categoryViewState.expanded) {
          applyCategoryVisibility();
        }
      });
    }

    function applyCategoryVisibility() {
      const total = Math.min(expandLimit, sortedButtons.length);

      if (categoryViewState.expanded) {
        categoryBar.classList.add("categories-content--expanded");
        sortedButtons.forEach((btn, i) => {
          btn.hidden = i >= total;
        });
        toggleButton.hidden = false;
        toggleButton.style.visibility = "";
        const m = Math.max(0, categoryViewState.collapsedVisible);
        toggleButton.textContent = `Mažiau (${m})`;
        toggleButton.setAttribute("aria-expanded", "true");
        return;
      }

      categoryBar.classList.remove("categories-content--expanded");

      const best = maxCollapsedVisible(total);
      categoryViewState.collapsedVisible = best;

      sortedButtons.forEach((btn, i) => {
        btn.hidden = i >= best;
      });

      if (best >= total) {
        toggleButton.hidden = true;
        toggleButton.removeAttribute("aria-expanded");
      } else {
        toggleButton.hidden = false;
        const n = Math.max(0, total - best);
        toggleButton.textContent = `Daugiau (${n})`;
        toggleButton.setAttribute("aria-expanded", "false");
      }
    }

    toggleButton.addEventListener("click", () => {
      categoryViewState.expanded = !categoryViewState.expanded;
      applyCategoryVisibility();
    });

    window.addEventListener("resize", queueResizeApply);
    applyCategoryVisibility();
  }

  function requestUserGeoForDirections() {
    if (userGeoState.attempted || !navigator.geolocation) {
      return;
    }
    userGeoState.attempted = true;
    navigator.geolocation.getCurrentPosition(
      (position) => {
        userGeoState.lat = position.coords.latitude;
        userGeoState.lng = position.coords.longitude;
      },
      () => {},
      {
        enableHighAccuracy: false,
        timeout: 12000,
        maximumAge: 600000,
      },
    );
  }

  function buildRouteLink(eventLat, eventLng) {
    const destination = `${eventLat},${eventLng}`;
    const hasUserLocation =
      Number.isFinite(userGeoState.lat) && Number.isFinite(userGeoState.lng);
    const params = new URLSearchParams();
    params.set("api", "1");
    params.set("destination", destination);
    params.set("travelmode", "driving");
    if (hasUserLocation) {
      params.set("origin", `${userGeoState.lat},${userGeoState.lng}`);
    }
    return `https://www.google.com/maps/dir/?${params.toString()}`;
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
  requestUserGeoForDirections();
})();
