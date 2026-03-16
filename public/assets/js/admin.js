(() => {
  function escapeHtml(value) {
    return String(value ?? "")
      .replace(/&/g, "&amp;")
      .replace(/</g, "&lt;")
      .replace(/>/g, "&gt;")
      .replace(/\"/g, "&quot;")
      .replace(/'/g, "&#39;");
  }

  function initAdminCalendar() {
    const calendarEl = document.getElementById("adminCalendar");
    const monthEl = document.getElementById("adminCalendarMonth");
    const prevBtn = document.getElementById("adminPrevMonth");
    const nextBtn = document.getElementById("adminNextMonth");

    if (!calendarEl || !monthEl || !prevBtn || !nextBtn) {
      return;
    }

    const nowYear = Number(calendarEl.dataset.currentYear || new Date().getFullYear());
    const nowMonth = Number(calendarEl.dataset.currentMonth || new Date().getMonth() + 1);
    const nowDay = Number(calendarEl.dataset.currentDay || new Date().getDate());

    const state = {
      year: nowYear,
      month: nowMonth,
    };

    const ltMonths = [
      "sausis",
      "vasaris",
      "kovas",
      "balandis",
      "geguze",
      "birzelis",
      "liepa",
      "rugpjutis",
      "rugsejis",
      "spalis",
      "lapkritis",
      "gruodis",
    ];

    function renderCalendar() {
      monthEl.textContent = `${state.year} m. ${ltMonths[state.month - 1] || ""}`;
      calendarEl.innerHTML = "";

      const firstDate = new Date(state.year, state.month - 1, 1);
      const jsWeekday = firstDate.getDay();
      const firstWeekday = jsWeekday === 0 ? 7 : jsWeekday;
      const daysInMonth = new Date(state.year, state.month, 0).getDate();

      const cells = [];
      for (let i = 1; i < firstWeekday; i += 1) {
        cells.push(null);
      }
      for (let d = 1; d <= daysInMonth; d += 1) {
        cells.push(d);
      }
      while (cells.length % 7 !== 0) {
        cells.push(null);
      }

      cells.forEach((day) => {
        const cell = document.createElement("div");
        cell.className = "calendar-day-cell";

        if (day !== null) {
          cell.textContent = String(day);
          if (state.year === nowYear && state.month === nowMonth && day === nowDay) {
            cell.classList.add("calendar-day-today");
          }
        } else {
          cell.innerHTML = "&nbsp;";
        }

        calendarEl.appendChild(cell);
      });
    }

    prevBtn.addEventListener("click", () => {
      state.month -= 1;
      if (state.month < 1) {
        state.month = 12;
        state.year -= 1;
      }
      renderCalendar();
    });

    nextBtn.addEventListener("click", () => {
      state.month += 1;
      if (state.month > 12) {
        state.month = 1;
        state.year += 1;
      }
      renderCalendar();
    });

    renderCalendar();
  }

  function initAdminPanelAjax() {
    const root = document.getElementById("adminPanelRoot");
    if (!root) {
      return;
    }

    const base = root.dataset.base || "";
    const currentAdminId = Number(root.dataset.currentAdminId || 0);

    const tabs = Array.from(root.querySelectorAll(".tab[data-tab]"));
    const statButtons = Array.from(root.querySelectorAll("[data-tab-target]"));
    const eventsBody = document.getElementById("adminEventsBody");
    const usersBody = document.getElementById("usersTableBody");

    let currentTab = root.dataset.tab || "pending";

    function showToast(message) {
      if (!message) return;
      const toast = document.getElementById("toast");
      const msg = document.getElementById("toastMessage");
      if (!toast || !msg) return;

      msg.textContent = message;
      toast.style.display = "flex";
      window.setTimeout(() => {
        toast.style.display = "none";
      }, 1800);
    }

    function updateStats(stats) {
      const total = document.getElementById("totalEvents");
      const pending = document.getElementById("pendingEvents");
      const approved = document.getElementById("approvedEvents");
      const rejected = document.getElementById("rejectedEvents");
      const pendingCount = document.getElementById("pendingCount");

      if (total) total.textContent = String(stats.total_events ?? 0);
      if (pending) pending.textContent = String(stats.pending_events ?? 0);
      if (approved) approved.textContent = String(stats.approved_events ?? 0);
      if (rejected) rejected.textContent = String(stats.rejected_events ?? 0);
      if (pendingCount) pendingCount.textContent = String(stats.pending_events ?? 0);
    }

    function setActiveTab(tab) {
      currentTab = tab;
      root.dataset.tab = tab;
      tabs.forEach((link) => {
        link.classList.toggle("active", link.dataset.tab === tab);
      });
    }

    function eventActionButtons(eventId, tab) {
      const hidden = (action, tabName) => [
        `<input type="hidden" name="event_id" value="${eventId}">`,
        `<input type="hidden" name="action" value="${action}">`,
        `<input type="hidden" name="tab" value="${tabName}">`,
      ].join("");

      if (tab === "pending") {
        return [
          `<form method="post" action="${base}/admin/panel/event-status" class="admin-inline-form js-admin-event-form">${hidden("approve", "pending")}<button type="submit" class="admin-action-btn admin-action-approve">Approve</button></form>`,
          `<form method="post" action="${base}/admin/panel/event-status" class="admin-inline-form js-admin-event-form">${hidden("reject", "pending")}<button type="submit" class="admin-action-btn admin-action-reject">Reject</button></form>`,
        ].join("");
      }

      if (tab === "approved") {
        return `<form method="post" action="${base}/admin/panel/event-status" class="admin-inline-form js-admin-event-form">${hidden("reject", "approved")}<button type="submit" class="admin-action-btn admin-action-reject">Reject</button></form>`;
      }

      return `<form method="post" action="${base}/admin/panel/event-status" class="admin-inline-form js-admin-event-form">${hidden("restore", "rejected")}<button type="submit" class="admin-action-btn admin-action-restore">Return to pending</button></form>`;
    }

    function renderEvents(events, tab) {
      if (!eventsBody) return;

      if (!Array.isArray(events) || events.length === 0) {
        eventsBody.innerHTML = '<tr><td colspan="6" class="empty-state">No events in this category</td></tr>';
        return;
      }

      eventsBody.innerHTML = events.map((event) => {
        const id = escapeHtml(event.id ?? "");
        return [
          "<tr>",
          `<td>${escapeHtml(event.title ?? "")}</td>`,
          `<td>${escapeHtml(event.organizer_name ?? "-")}</td>`,
          `<td>${escapeHtml(event.event_date ?? "")}</td>`,
          `<td>${escapeHtml(event.location ?? "")}</td>`,
          `<td><span class="admin-badge">${escapeHtml(event.status ?? "")}</span></td>`,
          `<td><div class="admin-action-row">${eventActionButtons(id, tab)}</div></td>`,
          "</tr>",
        ].join("");
      }).join("");
    }

    function renderUsers(users) {
      if (!usersBody || !Array.isArray(users)) return;

      if (!users.length) {
        usersBody.innerHTML = '<tr><td colspan="4" class="empty-state">No users found</td></tr>';
        return;
      }

      usersBody.innerHTML = users.map((user) => {
        const uid = Number(user.id || 0);
        const role = String(user.role || "user");

        const actions = uid === currentAdminId
          ? '<span class="admin-action-muted">Logged-in admin</span>'
          : [
              `<form method="post" action="${base}/admin/panel/user-role" class="admin-role-form js-admin-role-form">`,
              `<input type="hidden" name="user_id" value="${uid}">`,
              '<select name="role" class="admin-role-select">',
              `<option value="user" ${role === "user" ? "selected" : ""}>user</option>`,
              `<option value="organizer" ${role === "organizer" ? "selected" : ""}>organizer</option>`,
              `<option value="admin" ${role === "admin" ? "selected" : ""}>admin</option>`,
              "</select>",
              '<button type="submit" class="admin-action-btn admin-action-approve">Save</button>',
              "</form>",
            ].join("");

        return [
          "<tr>",
          `<td>${escapeHtml(user.name ?? "")}</td>`,
          `<td>${escapeHtml(user.email ?? "")}</td>`,
          `<td><span class="admin-badge">${escapeHtml(role)}</span></td>`,
          `<td>${actions}</td>`,
          "</tr>",
        ].join("");
      }).join("");
    }

    async function fetchTabData(tab) {
      const response = await fetch(`${base}/admin/panel/data?tab=${encodeURIComponent(tab)}`, {
        headers: {
          "X-Requested-With": "XMLHttpRequest",
          Accept: "application/json",
        },
      });

      const payload = await response.json();
      if (!response.ok || !payload.ok) {
        return;
      }

      const data = payload.data || {};
      setActiveTab(tab);
      updateStats(data.stats || {});
      renderEvents(data.events || [], tab);
      renderUsers(data.users || []);
    }

    tabs.forEach((tabLink) => {
      tabLink.addEventListener("click", (event) => {
        event.preventDefault();
        const tab = tabLink.dataset.tab || "pending";
        fetchTabData(tab);
      });
    });

    statButtons.forEach((button) => {
      button.addEventListener("click", () => {
        const targetTab = button.dataset.tabTarget || "pending";
        fetchTabData(targetTab);
      });
    });

    root.addEventListener("submit", async (event) => {
      const form = event.target;
      if (!(form instanceof HTMLFormElement)) {
        return;
      }

      if (!form.classList.contains("js-admin-event-form") && !form.classList.contains("js-admin-role-form")) {
        return;
      }

      event.preventDefault();

      const response = await fetch(form.action, {
        method: "POST",
        headers: {
          "Content-Type": "application/x-www-form-urlencoded;charset=UTF-8",
          "X-Requested-With": "XMLHttpRequest",
          Accept: "application/json",
        },
        body: new URLSearchParams(new FormData(form)).toString(),
      });

      const payload = await response.json();
      if (!response.ok) {
        showToast("Action failed.");
        return;
      }

      if (payload.message) {
        showToast(payload.message);
      }

      if (payload.data && payload.data.stats) {
        updateStats(payload.data.stats);
      }
      if (payload.data && payload.data.events) {
        renderEvents(payload.data.events, currentTab);
      } else if (form.classList.contains("js-admin-event-form")) {
        await fetchTabData(currentTab);
      }
      if (payload.data && payload.data.users) {
        renderUsers(payload.data.users);
      }
    });
  }

  function init() {
    initAdminCalendar();
    initAdminPanelAjax();
  }

  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", init);
  } else {
    init();
  }
})();
