document.addEventListener("DOMContentLoaded", () => {
  const navbar = document.querySelector(".global-navbar");
  if (!navbar) return;

  const timezone = document.documentElement.dataset.timezone || "Asia/Jakarta";
  const dateFormat = document.querySelector("[data-navbar-date]")?.dataset.dateFormat || "long";
  const dateOptions = dateFormat === "short"
    ? { day: "2-digit", month: "short", year: "numeric", timeZone: timezone }
    : { weekday: "long", day: "numeric", month: "long", year: "numeric", timeZone: timezone };

  const updateDate = () => {
    const date = new Intl.DateTimeFormat(document.documentElement.lang || "id-ID", dateOptions).format(new Date());
    document.querySelectorAll("[data-navbar-date], [data-navbar-full-date]").forEach((element) => {
      element.textContent = date;
    });
  };

  const workTime = document.querySelector("[data-worktime]");
  const workTimeValue = workTime?.querySelector("[data-worktime-value]");
  const startedAt = workTime?.dataset.startedAt;
  if (workTimeValue && startedAt) {
    const startMilliseconds = new Date(startedAt).getTime();
    const updateWorkTime = () => {
      const elapsedSeconds = Math.max(0, Math.floor((Date.now() - startMilliseconds) / 1000));
      const hours = Math.floor(elapsedSeconds / 3600);
      const minutes = Math.floor((elapsedSeconds % 3600) / 60);
      const seconds = elapsedSeconds % 60;
      workTimeValue.textContent = [hours, minutes, seconds].map((value) => String(value).padStart(2, "0")).join(":");
    };

    updateWorkTime();
    window.setInterval(updateWorkTime, 1000);
  }

  const unreadBadge = document.querySelector("[data-unread-badge]");
  const updateUnreadCount = async () => {
    if (!unreadBadge || !navbar.dataset.unreadEndpoint) return;

    try {
      const response = await fetch(navbar.dataset.unreadEndpoint, {
        headers: { Accept: "application/json", "X-Requested-With": "XMLHttpRequest" },
        credentials: "same-origin",
      });
      if (!response.ok) return;

      const { count } = await response.json();
      const unreadCount = Number(count) || 0;
      unreadBadge.dataset.count = String(unreadCount);
      unreadBadge.textContent = unreadCount > 99 ? "99+" : String(unreadCount);
      unreadBadge.classList.toggle("hidden", unreadCount < 1);
    } catch (error) {
      // The initial server-rendered count remains visible if polling is unavailable.
    }
  };

  updateDate();
  updateUnreadCount();
  window.setInterval(updateDate, 60_000);
  window.setInterval(updateUnreadCount, 60_000);
  window.addEventListener("focus", updateUnreadCount);

  const profileMenu = document.querySelector(".global-profile-menu");
  document.addEventListener("pointerdown", (event) => {
    if (profileMenu?.open && !profileMenu.contains(event.target)) profileMenu.open = false;
  });
});
