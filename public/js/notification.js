document.addEventListener("DOMContentLoaded", () => {
  const searchForm = document.getElementById("notificationSearchForm");
  const searchInput = document.getElementById("notificationSearch");
  const loadingIndicator = document.getElementById("notificationsLoadingIndicator");
  document.querySelectorAll("form").forEach((form) => {
    form.addEventListener("submit", (event) => {
      if (!event.defaultPrevented) loadingIndicator?.classList.remove("hidden");
    });
  });

  document.querySelectorAll("[data-submit-on-change]").forEach((select) => {
    select.addEventListener("change", () => select.form?.requestSubmit());
  });

  searchInput?.addEventListener("input", () => {
    window.clearTimeout(searchTimeout);
    searchTimeout = window.setTimeout(() => searchForm?.requestSubmit(), 450);
  });

  const timeZone = document.documentElement.dataset.timezone || "Asia/Jakarta";
  const dateOptions = { timeZone, day: "2-digit", month: "short", year: "numeric", weekday: "short" };
  const dateElement = document.getElementById("currentDate");
  const timeElement = document.getElementById("currentTime");
  const updateClock = () => {
    const now = new Date();
    if (dateElement) dateElement.textContent = new Intl.DateTimeFormat("id-ID", dateOptions).format(now);
    if (timeElement) timeElement.textContent = `${new Intl.DateTimeFormat("id-ID", { timeZone, hour: "2-digit", minute: "2-digit", hour12: false }).format(now)} WIB`;
  };
  updateClock();
  window.setInterval(updateClock, 30000);

  const browserPreference = document.querySelector("[data-browser-permission]");
  const browserMessage = document.getElementById("browserPermissionMessage");
  if (browserPreference) {
    browserPreference.addEventListener("change", async () => {
      if (!browserPreference.checked) return;

      if (!("Notification" in window)) {
        browserPreference.checked = false;
        if (browserMessage) {
          browserMessage.textContent = "Browser ini tidak mendukung Notification API.";
          browserMessage.classList.remove("hidden");
        }
        return;
      }

      let permission = Notification.permission;
      if (permission === "default") {
        try {
          permission = await Notification.requestPermission();
        } catch {
          permission = "denied";
        }
      }

      if (permission !== "granted") {
        browserPreference.checked = false;
        if (browserMessage) {
          browserMessage.textContent = "Izin browser tidak diberikan. Preferensi tidak akan diaktifkan.";
          browserMessage.classList.remove("hidden");
        }
      } else if (browserMessage) {
        browserMessage.textContent = "Izin browser aktif. Pengiriman push belum dikonfigurasi.";
        browserMessage.classList.remove("hidden");
        browserMessage.classList.remove("text-rose-600");
        browserMessage.classList.add("text-emerald-600");
      }
    });
  }
});
