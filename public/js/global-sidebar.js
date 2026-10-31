document.addEventListener("DOMContentLoaded", () => {
  const sidebar = document.getElementById("sidebar");
  const overlay = document.getElementById("sidebarOverlay");
  const openButton = document.getElementById("openSidebar");
  const closeButton = document.getElementById("closeSidebar");
  const collapseButton = document.getElementById("sidebarCollapseButton");
  const storageKey = "lexa.sidebar.collapsed";
  const tooltipClass = "sidebar-tooltip";

  let collapsed = document.documentElement.dataset.sidebarCollapsed === "true";
  let tooltip;

  const applyCollapsedState = (isCollapsed, persist = false) => {
    collapsed = isCollapsed;
    document.documentElement.dataset.sidebarCollapsed = String(isCollapsed);
    document.getElementById("appShell")?.setAttribute("data-sidebar-collapsed", String(isCollapsed));
    collapseButton?.setAttribute("aria-expanded", String(!isCollapsed));
    collapseButton?.setAttribute("aria-label", isCollapsed ? "Perbesar sidebar" : "Perkecil sidebar");
    collapseButton?.setAttribute("title", isCollapsed ? "Perbesar sidebar" : "Perkecil sidebar");

    if (persist) {
      try {
        localStorage.setItem(storageKey, String(isCollapsed));
      } catch (error) {
        // The in-memory layout still works when browser storage is unavailable.
      }
    }
  };

  const hideMobileSidebar = () => {
    sidebar?.classList.add("-translate-x-full");
    overlay?.classList.add("hidden");
    document.body.classList.remove("overflow-hidden");
  };

  const showMobileSidebar = () => {
    sidebar?.classList.remove("-translate-x-full");
    overlay?.classList.remove("hidden");
    document.body.classList.add("overflow-hidden");
  };

  applyCollapsedState(collapsed);
  collapseButton?.addEventListener("click", () => {
    const previousCollapsed = collapsed;
    const nextCollapsed = !collapsed;
    applyCollapsedState(nextCollapsed, true);

    fetch(document.documentElement.dataset.sidebarPreferenceUrl || "/settings/sidebar-preference", {
      method: "PUT",
      credentials: "same-origin",
      headers: {
        Accept: "application/json",
        "Content-Type": "application/json",
        "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]')?.content || "",
      },
      body: JSON.stringify({ collapsed: nextCollapsed }),
    })
      .then((response) => {
        if (!response.ok) throw new Error("Sidebar preference could not be saved.");
      })
      .catch(() => applyCollapsedState(previousCollapsed, true));
  });
  openButton?.addEventListener("click", showMobileSidebar);
  closeButton?.addEventListener("click", hideMobileSidebar);
  overlay?.addEventListener("click", hideMobileSidebar);

  window.addEventListener("resize", () => {
    if (window.innerWidth >= 768) {
      overlay?.classList.add("hidden");
      document.body.classList.remove("overflow-hidden");
      sidebar?.classList.remove("-translate-x-full");
    } else {
      hideMobileSidebar();
    }
  });

  const removeTooltip = () => {
    tooltip?.remove();
    tooltip = null;
  };

  const showTooltip = (link) => {
    if (!collapsed || window.innerWidth < 768) return;

    removeTooltip();
    tooltip = document.createElement("div");
    tooltip.className = tooltipClass;
    tooltip.textContent = link.dataset.sidebarTooltip || "";
    document.body.append(tooltip);

    const rect = link.getBoundingClientRect();
    tooltip.style.left = `${rect.right + 10}px`;
    tooltip.style.top = `${rect.top + (rect.height - tooltip.offsetHeight) / 2}px`;
  };

  document.querySelectorAll("[data-sidebar-tooltip]").forEach((link) => {
    link.addEventListener("pointerenter", () => showTooltip(link));
    link.addEventListener("pointerleave", removeTooltip);
    link.addEventListener("focus", () => showTooltip(link));
    link.addEventListener("blur", removeTooltip);
  });

  collapseButton?.addEventListener("click", removeTooltip);
  window.addEventListener("resize", removeTooltip);
});
