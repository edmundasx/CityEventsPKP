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
        config.errorBox.classList.add("hidden");
      }
      if (config.focusInput) {
        config.focusInput.focus();
      }
    }

    modalConfigs.forEach((config) => {
      const closeButtons = Array.from(config.modal.querySelectorAll("[data-auth-close]"));

      config.openButtons.forEach((button) => {
        button.addEventListener("click", () => openModal(config));
      });

      closeButtons.forEach((button) => {
        button.addEventListener("click", hideAllModals);
      });

      if (!config.form) {
        return;
      }

      config.form.addEventListener("submit", async (event) => {
        event.preventDefault();
        const formDate = new FormDate(config.form);
        const body = new URLSearchParams(formDate);

        if (config.errorBox) {
          config.errorBox.textContent = "";
          config.errorBox.classList.add("hidden");
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

          const data = await response.json();
          if (!response.ok || !data.ok) {
            const message = (data && data.message) || config.fallbackError;
            if (config.errorBox) {
              config.errorBox.textContent = message;
              config.errorBox.classList.remove("hidden");
            }
            return;
          }

          const redirectUrl = data.redirect || window.location.href;
          window.location.assign(redirectUrl);
        } catch (_error) {
          if (config.errorBox) {
            config.errorBox.textContent = "Server error. Please try again.";
            config.errorBox.classList.remove("hidden");
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
