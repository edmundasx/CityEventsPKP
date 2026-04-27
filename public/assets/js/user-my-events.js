(() => {
  function bindTabs(root) {
    const tabs = Array.from(root.querySelectorAll(".tab[data-tab-target]"));
    const panels = Array.from(root.querySelectorAll(".tab-content"));
    tabs.forEach((tab) => {
      tab.addEventListener("click", () => {
        const target = tab.dataset.tabTarget || "";
        tabs.forEach((item) => item.classList.toggle("active", item === tab));
        panels.forEach((panel) => {
          panel.classList.toggle("active", panel.id === target);
        });
      });
    });
  }

  function init() {
    const root = document.getElementById("userMyEventsRoot");
    if (!root) return;
    bindTabs(root);
  }

  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", init);
  } else {
    init();
  }
})();
