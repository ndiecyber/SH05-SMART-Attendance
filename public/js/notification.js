// =========================================================
// SMART ATTENDANCE - NOTIFICATION ENGINE (INTERCONNECTED)
// =========================================================

// 1. Central Data State (Single Source of Truth)
let notificationsData = [
  {
    id: "notif-1",
    title: "Late arrival detected",
    description: "Check-in 08:16 WIB — 16 minutes past entry time.",
    category: "Attendance",
    priority: "High",
    requiresAction: true,
    actionText: "Submit Permission Form",
    actionLink: "permission.html",
    timeGroup: "Today",
    timeText: "10:15 WIB - 15 minutes ago",
    source: "Attendance Engine",
    read: false,
    archived: false,
  },
  {
    id: "notif-2",
    title: "Permission awaiting details",
    description: "WFH request needs a more detailed reason attached.",
    category: "Approval",
    priority: "High",
    requiresAction: true,
    actionText: "Complete Permission",
    actionLink: "permission.html",
    timeGroup: "Today",
    timeText: "08:15 WIB - 2 hours ago",
    source: "Approval Center",
    read: false,
    archived: false,
  },
  {
    id: "notif-3",
    title: "Evaluation Schedule Reminder",
    description: "Weekly evaluation session will start at 14:00 WIB in Room A.",
    category: "Schedule",
    priority: "Normal",
    requiresAction: false,
    actionText: "",
    actionLink: "schedule.html",
    timeGroup: "Today",
    timeText: "07:30 WIB - 3 hours ago",
    source: "Scheduling Bot",
    read: false,
    archived: false,
  },
  {
    id: "notif-4",
    title: "Check-in successfully verified",
    description: "FaceID match 98%, location inside office area.",
    category: "Attendance",
    priority: "Normal",
    requiresAction: false,
    actionText: "",
    actionLink: "attendance.html",
    timeGroup: "Yesterday",
    timeText: "17:00 WIB - 1 day ago",
    source: "Attendance Engine",
    read: true,
    archived: false,
  },
  {
    id: "notif-5",
    title: "Leave Request approved",
    description: "2 days annual leave approved by Andi Wijaya.",
    category: "Approval",
    priority: "High",
    requiresAction: false,
    actionText: "",
    actionLink: "leave.html",
    timeGroup: "Yesterday",
    timeText: "10:45 WIB - 1 day ago",
    source: "Andi Wijaya - Mentor",
    read: true,
    archived: false,
  },
  {
    id: "notif-6",
    title: "Attendance System Version Update",
    description: "System version v2.4 successfully updated without issues.",
    category: "System",
    priority: "Normal",
    requiresAction: false,
    actionText: "",
    actionLink: "#",
    timeGroup: "Yesterday",
    timeText: "09:00 WIB - 1 day ago",
    source: "System Core",
    read: true,
    archived: false,
  },
];

// App State
let currentFilterCategory = "All"; // "All", "Unread", "Action Required", "Attendance", "Schedule", "System", "Announcement"
let currentSearchQuery = "";
let activeCategoryPreferences = {
  Attendance: true,
  Approval: true,
  Schedule: true,
  System: true,
  Announcement: true,
};
let myActivityChart = null;

// =========================================================
// INITIALIZATION
// =========================================================
document.addEventListener("DOMContentLoaded", () => {
  initHeaderClock();
  initSidebarMobile();
  initEventListeners();
  renderApp();
});

// =========================================================
// HEADER & SIDEBAR LOGIC
// =========================================================
function initHeaderClock() {
  const currentDateEl = document.getElementById("currentDate");
  const currentTimeEl = document.getElementById("currentTime");

  function updateClock() {
    const now = new Date();
    if (currentTimeEl) {
      currentTimeEl.textContent = now.toLocaleTimeString("en-US", {
        hour: "2-digit",
        minute: "2-digit",
        hour12: false,
      });
    }
    if (currentDateEl) {
      currentDateEl.textContent = now.toLocaleDateString("en-US", {
        weekday: "short",
        day: "numeric",
        month: "short",
        year: "numeric",
      });
    }
  }
  updateClock();
  setInterval(updateClock, 1000);
}

function initSidebarMobile() {
  const sidebar = document.getElementById("sidebar");
  const sidebarOverlay = document.getElementById("sidebarOverlay");
  const openSidebar = document.getElementById("openSidebar");
  const closeSidebar = document.getElementById("closeSidebar");

  function showSidebar() {
    sidebar?.classList.remove("-translate-x-full");
    sidebarOverlay?.classList.remove("hidden");
    document.body.classList.add("overflow-hidden");
  }

  function hideSidebar() {
    sidebar?.classList.add("-translate-x-full");
    sidebarOverlay?.classList.add("hidden");
    document.body.classList.remove("overflow-hidden");
  }

  openSidebar?.addEventListener("click", showSidebar);
  closeSidebar?.addEventListener("click", hideSidebar);
  sidebarOverlay?.addEventListener("click", hideSidebar);

  window.addEventListener("resize", () => {
    if (window.innerWidth >= 768) {
      sidebarOverlay?.classList.add("hidden");
      document.body.classList.remove("overflow-hidden");
      sidebar?.classList.remove("-translate-x-full");
    } else {
      sidebar?.classList.add("-translate-x-full");
    }
  });
}

// =========================================================
// EVENT LISTENERS & BINDINGS
// =========================================================
function initEventListeners() {
  // Search input real-time
  const searchInput = document.getElementById("searchInput");
  const clearSearchBtn = document.getElementById("clearSearchBtn");

  searchInput?.addEventListener("input", (e) => {
    currentSearchQuery = e.target.value.toLowerCase().trim();
    if (clearSearchBtn) {
      clearSearchBtn.classList.toggle("hidden", currentSearchQuery === "");
    }
    renderApp();
  });

  clearSearchBtn?.addEventListener("click", () => {
    if (searchInput) searchInput.value = "";
    currentSearchQuery = "";
    clearSearchBtn.classList.add("hidden");
    renderApp();
  });

  // Mark all read button
  const markAllReadBtn = document.getElementById("markAllReadBtn");
  markAllReadBtn?.addEventListener("click", () => {
    notificationsData.forEach((item) => (item.read = true));
    renderApp();
  });

  // Scroll to settings
  const scrollToSettingsBtn = document.getElementById("scrollToSettingsBtn");
  scrollToSettingsBtn?.addEventListener("click", () => {
    const settingsWidget = document.getElementById("settingsWidget");
    settingsWidget?.scrollIntoView({ behavior: "smooth" });
  });

  // Preferences category toggles
  const categoryToggles = document.querySelectorAll(".category-toggle");
  categoryToggles.forEach((toggle) => {
    toggle.addEventListener("change", (e) => {
      const cat = e.target.dataset.category;
      activeCategoryPreferences[cat] = e.target.checked;
      renderApp();
    });
  });
}

// =========================================================
// CORE RENDER CONTROLLER
// =========================================================
function renderApp() {
  const activeItems = notificationsData.filter((item) => !item.archived);

  // 1. Calculate Summary Metrics
  const totalCount = activeItems.length;
  const unreadCount = activeItems.filter((i) => !i.read).length;
  const actionNeededCount = activeItems.filter(
    (i) => i.requiresAction && !i.read,
  ).length;
  const highPriorityCount = activeItems.filter(
    (i) => i.priority === "High",
  ).length;

  // Update UI Stats
  document.getElementById("statTotalNotif").textContent = totalCount;
  document.getElementById("statUnreadSub").textContent =
    `${unreadCount} unread`;
  document.getElementById("statActionNeeded").textContent = actionNeededCount;
  document.getElementById("statHighPriority").textContent = highPriorityCount;

  const headerBadge = document.getElementById("headerBadge");
  if (headerBadge) {
    headerBadge.classList.toggle("hidden", unreadCount === 0);
  }

  // 2. Render Filter Pills
  renderFilterPills(activeItems);

  // 3. Filter Items for Main Feed
  const filteredFeedItems = activeItems.filter((item) => {
    // Check user settings toggle
    if (!activeCategoryPreferences[item.category]) return false;

    // Check Search Query
    if (currentSearchQuery !== "") {
      const matchTitle = item.title.toLowerCase().includes(currentSearchQuery);
      const matchDesc = item.description
        .toLowerCase()
        .includes(currentSearchQuery);
      const matchCategory = item.category
        .toLowerCase()
        .includes(currentSearchQuery);
      if (!matchTitle && !matchDesc && !matchCategory) return false;
    }

    // Check Selected Filter Pill
    if (currentFilterCategory === "Unread") return !item.read;
    if (currentFilterCategory === "Action Required") return item.requiresAction;
    if (
      currentFilterCategory !== "All" &&
      currentFilterCategory !== item.category
    ) {
      return false;
    }

    return true;
  });

  // 4. Render Feed & Action Section
  renderNotificationFeed(filteredFeedItems);
  renderActionRequiredSection(activeItems);

  // 5. Render Right Column Widgets
  renderCategoryProgressBars(activeItems);
  renderArchivedList();
  renderChart(activeItems);
}

// =========================================================
// RENDER FILTER PILLS
// =========================================================
function renderFilterPills(activeItems) {
  const container = document.getElementById("filterPillsContainer");
  if (!container) return;

  const categories = [
    { label: "All", count: activeItems.length },
    { label: "Unread", count: activeItems.filter((i) => !i.read).length },
    {
      label: "Action Required",
      count: activeItems.filter((i) => i.requiresAction).length,
    },
    {
      label: "Attendance",
      count: activeItems.filter((i) => i.category === "Attendance").length,
    },
    {
      label: "Schedule",
      count: activeItems.filter((i) => i.category === "Schedule").length,
    },
    {
      label: "System",
      count: activeItems.filter((i) => i.category === "System").length,
    },
    {
      label: "Announcement",
      count: activeItems.filter((i) => i.category === "Announcement").length,
    },
  ];

  container.innerHTML = categories
    .map((cat) => {
      const isActive = currentFilterCategory === cat.label;
      const activeClasses = "bg-blue-600 text-white shadow-sm font-semibold";
      const inactiveClasses =
        "bg-slate-100 text-slate-600 hover:bg-slate-200/80 font-medium";

      return `
      <button
        onclick="setFilterCategory('${cat.label}')"
        class="whitespace-nowrap rounded-lg px-3 py-1.5 text-xs transition ${
          isActive ? activeClasses : inactiveClasses
        }"
      >
        ${cat.label} (${cat.count})
      </button>
    `;
    })
    .join("");
}

function setFilterCategory(category) {
  currentFilterCategory = category;
  renderApp();
}

// =========================================================
// RENDER NOTIFICATION FEED
// =========================================================
function renderNotificationFeed(items) {
  const feedContainer = document.getElementById("notificationFeed");
  if (!feedContainer) return;

  if (items.length === 0) {
    feedContainer.innerHTML = `
      <div class="rounded-2xl border border-dashed border-slate-200 bg-white p-8 text-center">
        <svg class="mx-auto h-10 w-10 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0a3 3 0 11-6 0"/></svg>
        <p class="mt-2 text-sm font-medium text-slate-600">No notifications found</p>
        <p class="text-xs text-slate-400 mt-0.5">Try changing search keywords or selected filter.</p>
      </div>
    `;
    return;
  }

  // Group by timeGroup
  const groups = {};
  items.forEach((item) => {
    if (!groups[item.timeGroup]) groups[item.timeGroup] = [];
    groups[item.timeGroup].push(item);
  });

  let html = "";
  for (const groupName in groups) {
    html += `
      <div class="space-y-3">
        <div class="flex items-center justify-between border-b border-slate-200 pb-2">
          <h3 class="text-xs font-bold uppercase tracking-wider text-slate-400">${groupName}</h3>
        </div>
        ${groups[groupName].map((item) => renderFeedCardHTML(item)).join("")}
      </div>
    `;
  }

  feedContainer.innerHTML = html;
}

function renderFeedCardHTML(item) {
  const isUnread = !item.read;
  const bgStyle = isUnread
    ? "bg-white border-blue-200 shadow-sm"
    : "bg-white/70 border-slate-200/80";

  // Dynamic icon per category
  let iconSvg = `<svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0a3 3 0 11-6 0"/></svg>`;
  let iconBg = "bg-blue-50 text-blue-600";

  if (item.category === "Approval") {
    iconSvg = `<svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>`;
    iconBg = "bg-emerald-50 text-emerald-600";
  } else if (item.category === "Schedule") {
    iconSvg = `<svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>`;
    iconBg = "bg-purple-50 text-purple-600";
  } else if (item.category === "System") {
    iconSvg = `<svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>`;
    iconBg = "bg-slate-100 text-slate-700";
  }

  return `
    <div class="relative flex gap-4 rounded-2xl border ${bgStyle} p-4 transition hover:border-slate-300">
      <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl ${iconBg}">
        ${iconSvg}
      </div>
      <div class="flex-1 space-y-1">
        <div class="flex flex-wrap items-center justify-between gap-2">
          <div class="flex flex-wrap items-center gap-2">
            <span class="font-semibold text-slate-800 text-sm">${item.title}</span>
            ${isUnread ? `<span class="h-2 w-2 rounded-full bg-blue-600" title="Unread"></span>` : ""}
            ${
              item.priority === "High"
                ? `<span class="rounded-md bg-rose-100 px-2 py-0.5 text-[10px] font-semibold text-rose-700">High</span>`
                : ""
            }
            ${
              item.requiresAction
                ? `<span class="rounded-md bg-amber-100 px-2 py-0.5 text-[10px] font-semibold text-amber-700">Action Required</span>`
                : ""
            }
          </div>

          <!-- Actions -->
          <div class="flex items-center gap-2">
            ${
              isUnread
                ? `<button onclick="toggleReadStatus('${item.id}')" class="text-[11px] font-medium text-blue-600 hover:text-blue-800">Mark as Read</button>`
                : `<span class="text-[11px] text-slate-400">Read</span>`
            }
            <button onclick="archiveNotification('${item.id}')" class="text-slate-400 hover:text-slate-600 text-xs" title="Archive">
              <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 01-2-2V5a2 2 0 012-2h14a2 2 0 012 2v1a2 2 0 01-2 2M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/></svg>
            </button>
          </div>
        </div>

        <p class="text-xs text-slate-600 leading-relaxed">${item.description}</p>
        
        <div class="flex flex-wrap items-center gap-3 pt-2 text-[11px] text-slate-400">
          <span class="rounded-md bg-slate-100 px-2 py-0.5 font-medium text-slate-600">${item.category}</span>
          <span>${item.source}</span>
          <span>• ${item.timeText}</span>
        </div>
      </div>
    </div>
  `;
}

// =========================================================
// RENDER ACTION REQUIRED SECTION
// =========================================================
function renderActionRequiredSection(activeItems) {
  const container = document.getElementById("actionCardsContainer");
  const countText = document.getElementById("actionSectionCountText");

  const actionItems = activeItems.filter((i) => i.requiresAction);

  if (countText) {
    countText.textContent = `${actionItems.length} notifications awaiting response`;
  }

  if (!container) return;

  if (actionItems.length === 0) {
    container.innerHTML = `
      <div class="rounded-xl border border-dashed border-slate-200 bg-white p-4 text-center text-xs text-slate-400">
        No action required at this time.
      </div>
    `;
    return;
  }

  container.innerHTML = actionItems
    .map(
      (item) => `
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 rounded-xl border border-slate-200 bg-white p-3.5 shadow-sm transition hover:border-slate-300">
      <div class="space-y-1">
        <div class="flex items-center gap-2">
          <svg class="h-4 w-4 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
          <span class="text-xs font-bold text-slate-800">${item.title}</span>
        </div>
        <p class="text-[11px] text-slate-500 pl-6">${item.description}</p>
        <p class="text-[10px] text-slate-400 pl-6">${item.timeText}</p>
      </div>
      <a
        href="${item.actionLink}"
        class="inline-flex items-center gap-1 text-xs font-semibold text-blue-600 hover:text-blue-700 self-end sm:self-center"
      >
        <span>${item.actionText}</span>
        <span>&rarr;</span>
      </a>
    </div>
  `,
    )
    .join("");
}

// =========================================================
// RENDER CATEGORY PROGRESS BARS (CONNECTED)
// =========================================================
function renderCategoryProgressBars(activeItems) {
  const container = document.getElementById("categoryBarsContainer");
  if (!container) return;

  const total = activeItems.length || 1;
  const categories = [
    "Approval",
    "Attendance",
    "System",
    "Schedule",
    "Announcement",
  ];
  const colorMap = {
    Approval: "bg-emerald-500",
    Attendance: "bg-blue-500",
    System: "bg-slate-700",
    Schedule: "bg-purple-500",
    Announcement: "bg-amber-500",
  };

  container.innerHTML = categories
    .map((cat) => {
      const count = activeItems.filter((i) => i.category === cat).length;
      const percentage = Math.round((count / total) * 100);
      const color = colorMap[cat] || "bg-blue-500";

      return `
      <div class="space-y-1 text-xs">
        <div class="flex items-center justify-between">
          <span class="text-slate-600 font-medium">${cat}</span>
          <span class="font-semibold text-slate-700">${count}</span>
        </div>
        <div class="h-1.5 w-full rounded-full bg-slate-100 overflow-hidden">
          <div class="h-full ${color} rounded-full transition-all duration-300" style="width: ${percentage}%"></div>
        </div>
      </div>
    `;
    })
    .join("");
}

// =========================================================
// RENDER ARCHIVED LIST
// =========================================================
function renderArchivedList() {
  const container = document.getElementById("archivedListContainer");
  const countText = document.getElementById("archivedCountText");

  const archivedItems = notificationsData.filter((i) => i.archived);

  if (countText) {
    countText.textContent = `${archivedItems.length} notifications archived`;
  }

  if (!container) return;

  if (archivedItems.length === 0) {
    container.innerHTML = `<p class="text-xs text-slate-400">No notifications archived yet.</p>`;
    return;
  }

  container.innerHTML = archivedItems
    .map(
      (item) => `
    <div class="flex items-center justify-between gap-2 border-b border-slate-100 py-2 text-left text-xs last:border-0">
      <div class="truncate">
        <p class="font-semibold text-slate-700 truncate">${item.title}</p>
        <p class="text-[10px] text-slate-400">${item.category}</p>
      </div>
      <button onclick="restoreNotification('${item.id}')" class="text-[10px] text-blue-600 font-medium hover:underline shrink-0">
        Restore
      </button>
    </div>
  `,
    )
    .join("");
}

// =========================================================
// ITEM ACTIONS (TOGGLE READ, ARCHIVE, RESTORE)
// =========================================================
function toggleReadStatus(id) {
  const item = notificationsData.find((i) => i.id === id);
  if (item) {
    item.read = !item.read;
    renderApp();
  }
}

function archiveNotification(id) {
  const item = notificationsData.find((i) => i.id === id);
  if (item) {
    item.archived = true;
    renderApp();
  }
}

function restoreNotification(id) {
  const item = notificationsData.find((i) => i.id === id);
  if (item) {
    item.archived = false;
    renderApp();
  }
}

// =========================================================
// CHART.JS RENDERER (CONNECTED TO ACTIVE DATA)
// =========================================================
function renderChart(activeItems) {
  const canvas = document.getElementById("notifActivityChart");
  if (!canvas || typeof Chart === "undefined") return;

  // Chart Labels & Simulated Daily Counts
  const labels = ["Thu", "Fri", "Sat", "Sun", "Mon", "Tue", "Wed"];

  // Calculate activity dataset dynamically
  const totalActive = activeItems.length;
  const baseData = [3, 2, 1, 4, 3, 4, totalActive];

  if (myActivityChart) {
    myActivityChart.destroy();
  }

  const ctx = canvas.getContext("2d");
  myActivityChart = new Chart(ctx, {
    type: "bar",
    data: {
      labels: labels,
      datasets: [
        {
          label: "Notifications",
          data: baseData,
          backgroundColor: "#3b82f6",
          hoverBackgroundColor: "#2563eb",
          borderSkipped: false,
          borderRadius: 6,
          barThickness: 16,
        },
      ],
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: { display: false },
        tooltip: {
          enabled: true,
          callbacks: {
            label: (ctx) => ` ${ctx.raw} notifications`,
          },
        },
      },
      scales: {
        x: {
          grid: { display: false, drawBorder: false },
          ticks: {
            color: "#94a3b8",
            font: { size: 10, family: "Inter" },
          },
        },
        y: {
          display: false,
          beginAtZero: true,
        },
      },
    },
  });
}

// ==========================================
// TOAST NOTIFICATION (EXPORT)
// ==========================================
const toast = document.getElementById("toast");
const triggerBtn = document.getElementById("triggerBtn");
const csvBtn = document.getElementById("csvBtn");
const closeToastBtn = document.getElementById("closeToastBtn");
let toastTimer;

function ubahTekspdf() {
  const popupTeks = document.getElementById("teksPopup");
  if (popupTeks) popupTeks.textContent = "PDF File";
}

function ubahTekscsv() {
  const popupTeks = document.getElementById("teksPopup");
  if (popupTeks) popupTeks.textContent = "CSV File";
}

function showToast() {
  clearTimeout(toastTimer);
  toast?.classList.remove("hidden");
  setTimeout(() => {
    toast?.classList.remove("-translate-y-10", "opacity-0");
    toast?.classList.add("translate-y-0", "opacity-100");
  }, 10);
  toastTimer = setTimeout(hideToast, 3000);
}

function hideToast() {
  toast?.classList.remove("translate-y-0", "opacity-100");
  toast?.classList.add("-translate-y-10", "opacity-0");
  setTimeout(() => {
    toast?.classList.add("hidden");
  }, 300);
}

triggerBtn?.addEventListener("click", showToast);
csvBtn?.addEventListener("click", showToast);
closeToastBtn?.addEventListener("click", hideToast);
