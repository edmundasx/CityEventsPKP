(() => {
  function toFormBody(form) {
    return new URLSearchParams(new FormDate(form)).toString();
  }

  async function postForm(form) {
    const response = await fetch(form.action, {
      method: "POST",
      headers: {
        "Content-Type": "application/x-www-form-urlencoded;charset=UTF-8",
        "X-Requested-With": "XMLHttpRequest",
        Accept: "application/json",
      },
      body: toFormBody(form),
    });

    let data = null;
    try {
      data = await response.json();
    } catch (_error) {
      data = { ok: false };
    }

    return { response, data };
  }

  function updateFavoriteUI(eventId, favorited) {
    const forms = document.querySelectorAll(`.js-favorite-form[data-event-id="${eventId}"]`);
    forms.forEach((form) => {
      const heart = form.querySelector(".js-favorite-heart");
      if (heart) {
        heart.innerHTML = favorited ? "&#10084;" : "&#9825;";
      }

      const miniButton = form.querySelector(".user-mini-btn");
      if (miniButton) {
        miniButton.textContent = favorited ? "Remove from favorites" : "Prideti i megstamus";
      }
    });
  }

  function bindFavoriteForms(root) {
    root.addEventListener("submit", async (event) => {
      const form = event.target;
      if (!(form instanceof HTMLFormElement) || !form.classList.contains("js-favorite-form")) {
        return;
      }

      event.preventDefault();
      const eventId = form.dataset.eventId || "";
      if (!eventId) {
        form.submit();
        return;
      }

      try {
        const { response, data } = await postForm(form);
        if (!response.ok || !data.ok) {
          return;
        }

        updateFavoriteUI(String(data.event_id || eventId), Boolean(data.favorited));
      } catch (_error) {
        // Keep silent and rely on no-JS fallback next submit.
      }
    });
  }

  function bindNotificationForms(root) {
    root.addEventListener("submit", async (event) => {
      const form = event.target;
      if (!(form instanceof HTMLFormElement) || !form.classList.contains("js-notification-read-form")) {
        return;
      }

      event.preventDefault();
      const notificationId = form.dataset.notificationId || "";
      if (!notificationId) {
        form.submit();
        return;
      }

      try {
        const { response, data } = await postForm(form);
        if (!response.ok || !data.ok) {
          return;
        }

        const item = root.querySelector(`.user-notif-item[data-notification-id="${notificationId}"]`);
        if (item) {
          item.classList.add("user-notif-read");
        }
        form.remove();
      } catch (_error) {
        // Keep silent and rely on no-JS fallback next submit.
      }
    });
  }

  async function fetchPanelHtml(url) {
    const response = await fetch(url, {
      headers: {
        "X-Requested-With": "XMLHttpRequest",
      },
    });
    return response.text();
  }

  function bindLoadMore(root) {
    root.addEventListener("click", async (event) => {
      const link = event.target instanceof Element
        ? event.target.closest(".js-user-load-more")
        : null;

      if (!link || !(link instanceof HTMLAnchorElement)) {
        return;
      }

      event.preventDefault();

      try {
        const html = await fetchPanelHtml(link.href);
        const parser = new DOMParser();
        const doc = parser.parseFromString(html, "text/html");

        const nextGrid = doc.querySelector(".user-reco-grid");
        const currentGrid = root.querySelector(".user-reco-grid");
        if (nextGrid && currentGrid) {
          currentGrid.innerHTML = nextGrid.innerHTML;
        }

        const nextLoadMore = doc.querySelector(".js-user-load-more");
        const currentWrap = root.querySelector(".user-load-more-wrap");

        if (currentWrap) {
          if (nextLoadMore instanceof HTMLAnchorElement) {
            currentWrap.innerHTML = "";
            currentWrap.appendChild(nextLoadMore);
          } else {
            currentWrap.remove();
          }
        }
      } catch (_error) {
        window.location.assign(link.href);
      }
    });
  }

  function initUserPanel() {
    const root = document.getElementById("userPanelRoot");
    if (!root) {
      return;
    }

    bindFavoriteForms(root);
    bindNotificationForms(root);
    bindLoadMore(root);
  }

  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", initUserPanel);
  } else {
    initUserPanel();
  }
})();
