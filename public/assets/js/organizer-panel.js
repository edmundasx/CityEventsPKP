(() => {
  function parseEvents(root) {
    try {
      return JSON.parse(root.dataset.calendarEvents || "[]");
    } catch (_error) {
      return [];
    }
  }

  function eventDateParts(eventDate) {
    if (!eventDate) return null;
    const date = new Date(eventDate);
    if (Number.isNaN(date.getTime())) return null;
    return {
      year: date.getFullYear(),
      month: date.getMonth(),
      day: date.getDate(),
      label: date.toLocaleString("en-US"),
    };
  }

  function setupFilter(root) {
    const cards = Array.from(root.querySelectorAll(".stats-grid .stat-card"));
    const eventCards = Array.from(root.querySelectorAll("#organizerEventsList .my-event-card"));
    if (!cards.length || !eventCards.length) return;

    cards.forEach((card) => {
      card.addEventListener("click", () => {
        const status = card.dataset.status || "all";
        cards.forEach((item) => item.classList.toggle("active-filter", item === card));
        eventCards.forEach((eventCard) => {
          const eventStatus = eventCard.dataset.status || "";
          const show = status === "all"
            || eventStatus === status
            || (status === "rejected" && ["rejected", "update_pending"].includes(eventStatus))
            || (status === "pending" && ["pending", "update_pending"].includes(eventStatus));
          eventCard.style.display = show ? "" : "none";
        });
      });
    });
  }

  function setupProfileModal() {
    const modal = document.getElementById("profileModal");
    const openBtn = document.getElementById("openOrganizerProfileModal");
    const closeBtn = document.getElementById("closeOrganizerProfileModal");
    const cancelBtn = document.getElementById("cancelOrganizerProfileModal");
    if (!modal || !openBtn || !closeBtn || !cancelBtn) return;

    const open = () => modal.classList.add("active");
    const close = () => modal.classList.remove("active");

    openBtn.addEventListener("click", open);
    closeBtn.addEventListener("click", close);
    cancelBtn.addEventListener("click", close);
    modal.addEventListener("click", (event) => {
      if (event.target === modal) close();
    });
  }

  function setupCalendar(root, events) {
    const grid = document.getElementById("calendarDays");
    const monthEl = document.getElementById("calendarMonth");
    const prevBtn = document.getElementById("organizerPrevMonth");
    const nextBtn = document.getElementById("organizerNextMonth");
    const tooltip = document.getElementById("organizerCalendarTooltip");
    if (!grid || !monthEl || !prevBtn || !nextBtn || !tooltip) return;

    const mapped = events
      .map((event) => ({ ...event, parts: eventDateParts(event.event_date) }))
      .filter((event) => event.parts);

    const now = new Date();
    const state = { year: now.getFullYear(), month: now.getMonth() };

    function eventsForDay(year, month, day) {
      return mapped.filter((event) => event.parts.year === year && event.parts.month === month && event.parts.day === day);
    }

    function render() {
      const firstDay = new Date(state.year, state.month, 1);
      const daysInMonth = new Date(state.year, state.month + 1, 0).getDate();
      const offset = (firstDay.getDay() + 6) % 7;
      monthEl.textContent = firstDay.toLocaleDateString("en-US", { month: "long", year: "numeric" });
      grid.innerHTML = "";

      for (let i = 0; i < offset; i += 1) {
        const empty = document.createElement("div");
        empty.className = "calendar-day other-month";
        grid.appendChild(empty);
      }

      for (let day = 1; day <= daysInMonth; day += 1) {
        const cell = document.createElement("div");
        const dayEvents = eventsForDay(state.year, state.month, day);
        cell.className = "calendar-day";
        if (dayEvents.length) cell.classList.add("has-event");
        if (
          state.year === now.getFullYear() &&
          state.month === now.getMonth() &&
          day === now.getDate()
        ) {
          cell.classList.add("today");
        }
        cell.textContent = String(day);
        if (dayEvents.length) {
          cell.addEventListener("mousemove", (event) => {
            tooltip.innerHTML = dayEvents.map((item) => `
              <div class="calendar-tooltip-item">
                ${item.cover_image ? `<img src="${item.cover_image}" alt="">` : `<div class="calendar-tooltip-thumb" style="background:#e5e7eb;"></div>`}
                <span class="calendar-tooltip-title">${item.title || ""}</span>
              </div>
            `).join("");
            tooltip.classList.add("visible");
            tooltip.style.left = `${event.pageX + 12}px`;
            tooltip.style.top = `${event.pageY + 12}px`;
          });
          cell.addEventListener("mouseleave", () => {
            tooltip.classList.remove("visible");
          });
        }
        grid.appendChild(cell);
      }
    }

    prevBtn.addEventListener("click", () => {
      state.month -= 1;
      if (state.month < 0) {
        state.month = 11;
        state.year -= 1;
      }
      render();
    });

    nextBtn.addEventListener("click", () => {
      state.month += 1;
      if (state.month > 11) {
        state.month = 0;
        state.year += 1;
      }
      render();
    });

    render();
  }

  function init() {
    const root = document.getElementById("organizerPanelRoot");
    if (!root) return;

    const events = parseEvents(root);
    setupFilter(root);
    setupProfileModal();
    setupCalendar(root, events);
  }

  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", init);
  } else {
    init();
  }
})();
