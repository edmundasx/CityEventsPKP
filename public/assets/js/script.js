(() => {
  if (window.__authModalInit) {
    return;
  }
  window.__authModalInit = true;

  function initAuthModals() {
    const loginModal = document.getElementById("loginModal");
    const registerModal = document.getElementById("registerModal");
    if (!loginModal && !registerModal) {
      return;
    }

    const modalConfigs = [
      {
        modal: loginModal,
        openButtons: Array.from(document.querySelectorAll(".js-open-login-modal")),
        form: document.getElementById("loginModalForm"),
        errorBox: document.getElementById("loginModalError"),
        focusInput: document.getElementById("loginModalEmail"),
        fallbackError: "Login failed.",
      },
      {
        modal: registerModal,
        openButtons: Array.from(document.querySelectorAll(".js-open-register-modal")),
        form: document.getElementById("registerModalForm"),
        errorBox: document.getElementById("registerModalError"),
        focusInput: document.getElementById("registerModalName"),
        fallbackError: "Registration failed.",
      },
    ].filter((config) => config.modal);

    function hideAllModals() {
      modalConfigs.forEach(({ modal }) => {
        modal.classList.add("hidden");
        modal.setAttribute("aria-hidden", "true");
      });
      document.body.classList.remove("auth-modal-open");
    }

    function openModal(config) {
      hideAllModals();
      config.modal.classList.remove("hidden");
      config.modal.setAttribute("aria-hidden", "false");
      document.body.classList.add("auth-modal-open");

      if (config.errorBox) {
        config.errorBox.textContent = "";
        config.errorBox.hidden = true;
      }
      if (config.focusInput) {
        config.focusInput.focus();
      }
    }

    modalConfigs.forEach((config) => {
      const closeButtons = Array.from(config.modal.querySelectorAll("[data-auth-close]"));

      config.openButtons.forEach((button) => {
        button.addEventListener("click", (event) => {
          event.preventDefault();
          openModal(config);
        });
      });

      closeButtons.forEach((button) => {
        button.addEventListener("click", hideAllModals);
      });

      if (!config.form) {
        return;
      }

      config.form.addEventListener("submit", async (event) => {
        event.preventDefault();
        const formData = new FormData(config.form);
        const body = new URLSearchParams(formData);

        if (config.errorBox) {
          config.errorBox.textContent = "";
          config.errorBox.hidden = true;
        }

        try {
          const response = await fetch(config.form.action, {
            method: "POST",
            headers: {
              "Content-Type": "application/x-www-form-urlencoded;charset=UTF-8",
              "X-Requested-With": "XMLHttpRequest",
              Accept: "application/json",
            },
            body: body.toString(),
          });
          // Some environments may return HTML redirect/page instead of JSON.
          // Handle both response types so auth never appears "stuck".
          const contentType = (response.headers.get("content-type") || "").toLowerCase();
          const isJson = contentType.includes("application/json");
          const data = isJson ? await response.json() : null;

          if (response.redirected && response.url) {
            window.location.assign(response.url);
            return;
          }

          if (isJson && (!response.ok || !data || !data.ok)) {
            const message = (data && data.message) || config.fallbackError;
            if (config.errorBox) {
              config.errorBox.textContent = message;
              config.errorBox.hidden = false;
            }
            return;
          }

          if (isJson) {
            const redirectUrl =
              (data && data.redirect) || response.url || window.location.href;
            window.location.assign(redirectUrl);
            return;
          }

          if (response.ok && response.url) {
            window.location.assign(response.url);
            return;
          }

          if (config.errorBox) {
            config.errorBox.textContent = config.fallbackError;
            config.errorBox.classList.remove("hidden");
          }
        } catch (_error) {
          if (config.errorBox) {
            config.errorBox.textContent = "Server error. Please try again.";
            config.errorBox.hidden = false;
          }
        }
      });
    });

    const switchToRegister = document.querySelector(".js-switch-to-register");
    if (switchToRegister && registerModal) {
      switchToRegister.addEventListener("click", () => {
        const registerConfig = modalConfigs.find((config) => config.modal === registerModal);
        if (registerConfig) {
          openModal(registerConfig);
        }
      });
    }

    const switchToLogin = document.querySelector(".js-switch-to-login");
    if (switchToLogin && loginModal) {
      switchToLogin.addEventListener("click", () => {
        const loginConfig = modalConfigs.find((config) => config.modal === loginModal);
        if (loginConfig) {
          openModal(loginConfig);
        }
      });
    }

    document.addEventListener("keydown", (event) => {
      if (event.key !== "Escape") {
        return;
      }
      const hasOpenModal = modalConfigs.some(
        ({ modal }) => modal.getAttribute("aria-hidden") === "false",
      );
      if (hasOpenModal) {
        hideAllModals();
      }
    });
  }

  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", initAuthModals);
  } else {
    initAuthModals();
  }
})();
