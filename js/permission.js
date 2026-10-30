// ==========================================
// 1. SIDEBAR & NAVIGATION SYSTEM
// ==========================================
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

function handleResize() {
  if (window.innerWidth >= 768) {
    sidebarOverlay?.classList.add("hidden");
    document.body.classList.remove("overflow-hidden");
    sidebar?.classList.remove("-translate-x-full");
  } else {
    sidebar?.classList.add("-translate-x-full");
  }
}

openSidebar?.addEventListener("click", showSidebar);
closeSidebar?.addEventListener("click", hideSidebar);
sidebarOverlay?.addEventListener("click", hideSidebar);
window.addEventListener("resize", handleResize);
handleResize();

// ==========================================
// 2. CLOCK SYSTEM
// ==========================================
const headerTime = document.getElementById("headerTime");
const headerDate = document.getElementById("headerDate");

function updateClock() {
  const now = new Date();
  const timeText = now.toLocaleTimeString("en-US", {
    hour: "2-digit",
    minute: "2-digit",
    second: "2-digit",
    hour12: false,
  });
  const dateText = now.toLocaleDateString("en-US", {
    day: "numeric",
    month: "long",
    year: "numeric",
  });

  if (headerTime) headerTime.textContent = timeText;
  if (headerDate) headerDate.textContent = dateText;
}

updateClock();
setInterval(updateClock, 1000);

// ==========================================
// 3. TEAM PERMISSION RENDER
// ==========================================
const teamPermissions = [
  {
    name: "Khansa Putri",
    position: "UI/UX Designer",
    avatar: "https://i.pravatar.cc/100?img=12",
    type: "WFH",
    date: "full day",
  },
  {
    name: "Ajib Pratama",
    position: "Frontend Developer",
    avatar: "https://i.pravatar.cc/100?img=33",
    type: "Arrive Late",
    date: "07:30",
  },
  {
    name: "Zaki Ramadhan",
    position: "Backend Developer",
    avatar: "https://i.pravatar.cc/100?img=66",
    type: "Business Trip",
    date: "full day",
  },
  {
    name: "Farrel Jhonathan",
    position: "QA Engineer",
    avatar: "https://i.pravatar.cc/100?img=3",
    type: "Leave Early",
    date: "15:00",
  },
  {
    name: "Tuminah Putri",
    position: "HR Officer",
    avatar: "https://i.pravatar.cc/100?img=20",
    type: "Pop Out",
    date: "09:35 - 10:15",
  },
];

const permissionBadge = {
  WFH: "bg-sky-100 text-sky-700",
  "Arrive Late": "bg-amber-100 text-amber-700",
  "Business Trip": "bg-indigo-100 text-indigo-700",
  "Leave Early": "bg-orange-100 text-orange-700",
  "Pop Out": "bg-violet-100 text-violet-700",
};

const teamCountEl = document.getElementById("teamPermissionCount");
const teamListEl = document.getElementById("teamPermissionList");

if (teamCountEl) teamCountEl.textContent = `${teamPermissions.length} Members`;

if (teamListEl) {
  teamListEl.innerHTML = teamPermissions
    .map(
      (member) => `
      <div class="flex items-center justify-between rounded-2xl border border-slate-200 p-4 transition hover:border-slate-300 hover:bg-slate-50">
        <div class="flex items-center gap-4">
          <img
            src="${member.avatar}"
            alt="${member.name}"
            class="h-12 w-12 rounded-full object-cover"
          />
          <div>
            <h4 class="font-medium text-slate-800">${member.name}</h4>
            <p class="text-sm text-slate-500">${member.position}</p>
          </div>
        </div>

        <div class="text-right">
          <span class="inline-flex rounded-full px-3 py-1 text-xs font-medium ${
            permissionBadge[member.type] || "bg-slate-100 text-slate-700"
          }">
            ${member.type}
          </span>
          <p class="mt-2 text-sm text-slate-500">${member.date}</p>
        </div>
      </div>
    `,
    )
    .join("");
}

// ==========================================
// CENTRALIZED DATA: MY PERMISSIONS
// ==========================================
// Simulasi database izin pribadi kita (Single Source of Truth)
let myPermissions = [
  {
    id: "PM-001",
    type: "WFH",
    date: "2026-09-25",
    time: "08:00-17:00",
    durationHours: 9,
    reason: "Koneksi internet rumah mati total",
    status: "Approved",
    submitted: "24 Sep 16:35",
  },
  {
    id: "PM-002",
    type: "Arrive Late",
    date: "2026-09-26",
    time: "09:30-10:30",
    durationHours: 1,
    reason: "Jadwal dokter di rumah sakit",
    status: "Pending",
    submitted: "24 Sep 19:10",
  },
  {
    id: "PM-003",
    type: "Business Trip",
    date: "2026-09-10",
    time: "09:00-16:00",
    durationHours: 7,
    reason: "Meeting dengan Klien A",
    status: "Approved",
    submitted: "05 Sep 14:25",
  },
  {
    id: "PM-004",
    type: "Pop Out",
    date: "2026-09-25",
    time: "13:00-14:30",
    durationHours: 1.5,
    reason: "Urus administrasi bank",
    status: "Pending",
    submitted: "25 Sep 10:00",
  },
  {
    id: "PM-005",
    type: "Leave Early",
    date: "2026-09-02",
    time: "15:00-17:00",
    durationHours: 2,
    reason: "Urusan keluarga mendesak",
    status: "Rejected",
    submitted: "01 Sep 08:30",
  },
];

const badgeStyles = {
  WFH: "bg-sky-100 text-sky-700",
  "Arrive Late": "bg-amber-100 text-amber-700",
  "Business Trip": "bg-indigo-100 text-indigo-700",
  "Leave Early": "bg-orange-100 text-orange-700",
  "Pop Out": "bg-violet-100 text-violet-700",
};

const statusStyles = {
  Approved: "bg-green-100 text-green-700",
  Pending: "bg-yellow-100 text-yellow-700",
  Rejected: "bg-red-100 text-red-700",
  Canceled: "bg-slate-100 text-slate-600",
};

// ==========================================
// RENDER 9 CARDS STATS
// ==========================================
function updateDashboardCards() {
  let pending = 0,
    approved = 0,
    rejected = 0,
    totalHours = 0;
  let stats = {
    "Arrive Late": { count: 0, hours: 0 },
    "Leave Early": { count: 0, hours: 0 },
    "Pop Out": { count: 0, hours: 0 },
    WFH: { count: 0, hours: 0 },
    "Business Trip": { count: 0, hours: 0 },
  };

  myPermissions.forEach((p) => {
    // Top 4 Cards
    if (p.status === "Pending") pending++;
    if (p.status === "Approved") approved++;
    if (p.status === "Rejected") rejected++;
    if (p.status === "Approved" || p.status === "Pending") {
      totalHours += p.durationHours;
    }

    // Bottom 5 Cards (Tipe Izin)
    if (stats[p.type] && p.status !== "Rejected" && p.status !== "Canceled") {
      stats[p.type].count++;
      stats[p.type].hours += p.durationHours;
    }
  });

  // Update Top 4
  document.getElementById("pendingCount").innerText = pending;
  document.getElementById("approvedCount").innerText = approved;
  document.getElementById("rejectedCount").innerText = rejected;
  document.getElementById("totalHoursCount").innerText =
    `${Math.floor(totalHours)}h ${(totalHours % 1) * 60 || 0}m`;

  // Update Bottom 5
  const updateStatCard = (typeId, data) => {
    document.getElementById(`${typeId}Count`).innerHTML =
      `${data.count}<span class="pl-1 text-xs font-medium text-slate-400">this month</span>`;
    let avg = data.count > 0 ? data.hours / data.count : 0;
    document.getElementById(`${typeId}Avg`).innerText =
      avg > 0 ? `${Math.floor(avg)}h ${(avg % 1) * 60 || 0}m` : "-";
  };

  updateStatCard("arriveLate", stats["Arrive Late"]);
  updateStatCard("leaveEarly", stats["Leave Early"]);
  updateStatCard("popOut", stats["Pop Out"]);
  updateStatCard("wfh", stats["WFH"]);
  updateStatCard("businessTrip", stats["Business Trip"]);
}

// ==========================================
// RENDER "PERMISSION TODAY'S"
// ==========================================
function updateTodayPermissions() {
  const container = document.getElementById("upcomingPermissionList");
  const today = "2026-09-25"; // Simulasi tanggal hari ini

  const todayData = myPermissions.filter((p) => p.date === today);

  if (todayData.length === 0) {
    container.innerHTML = `<div class="py-8 text-center text-slate-400 text-sm">No permissions requested for today.</div>`;
    return;
  }

  container.innerHTML = todayData
    .map(
      (item) => `
    <div onclick="openModal('${item.id}')" class="cursor-pointer flex items-start gap-4 rounded-2xl border border-slate-200 p-4 transition hover:border-slate-300 hover:bg-slate-50">
      <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full ${badgeStyles[item.type] || "bg-slate-100"}">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd" /></svg>
      </div>
      <div class="flex-1 min-w-0">
        <div class="flex items-center justify-between gap-2">
          <h4 class="font-medium text-slate-800 truncate">${item.type}</h4>
          <span class="rounded-full px-2.5 py-0.5 text-xs font-semibold shrink-0 ${statusStyles[item.status]}">${item.status}</span>
        </div>
        <div class="mt-1 flex items-center gap-2 text-xs text-slate-500">
          <span class="font-medium text-slate-700">${item.time} (${item.durationHours}h)</span>
        </div>
        <p class="mt-2 text-xs text-slate-500 line-clamp-1"><span class="font-medium text-slate-600">Reason:</span> ${item.reason}</p>
      </div>
    </div>
  `,
    )
    .join("");
}

// ==========================================
// RENDER HISTORY TABLE & ACTIONS
// ==========================================
function updateHistoryTable() {
  const tbody = document.getElementById("historyTableBody");

  tbody.innerHTML = myPermissions
    .map(
      (item) => `
    <tr class="hover:bg-slate-50 transition-colors">
      <td class="py-3 px-2 font-medium text-slate-800">${item.id}</td>
      <td class="py-3 px-2">
        <span class="rounded-full px-2 py-0.5 text-xs font-semibold ${badgeStyles[item.type] || "bg-slate-100 text-slate-700"}">${item.type}</span>
      </td>
      <td class="py-3 px-2 text-xs sm:text-sm">${item.date}</td>
      <td class="py-3 px-2 hidden sm:table-cell text-xs">${item.time}</td>
      <td class="py-3 px-2 hidden lg:table-cell">${item.durationHours} Hours</td>
      <td class="py-3 px-2 hidden md:table-cell truncate max-w-[150px]">${item.reason}</td>
      <td class="py-3 px-2">
        <span class="rounded-full px-2 py-0.5 text-xs font-semibold ${statusStyles[item.status]}">${item.status}</span>
      </td>
      <td class="py-3 px-2 hidden xl:table-cell text-xs">${item.submitted}</td>
      <td class="py-3 px-2 text-center">
        <div class="flex justify-center gap-2">
          <button onclick="openModal('${item.id}')" class="rounded-lg bg-indigo-50 px-3 py-1 text-xs font-medium text-indigo-600 hover:bg-indigo-100 transition">View</button>
          ${item.status === "Pending" ? `<button onclick="cancelPermission('${item.id}')" class="rounded-lg bg-red-50 px-3 py-1 text-xs font-medium text-red-600 hover:bg-red-100 transition">Cancel</button>` : ""}
        </div>
      </td>
    </tr>
  `,
    )
    .join("");
}

// Action: Cancel Permission
function cancelPermission(id) {
  const index = myPermissions.findIndex((p) => p.id === id);
  if (index !== -1) {
    myPermissions[index].status = "Canceled";
    renderAll(); // Re-render seluruh dashboard untuk update semua datanya
  }
}

// ==========================================
// MODAL SYSTEM
// ==========================================
const modal = document.getElementById("permissionModal");
const modalContent = document.getElementById("modalContent");
const modalBody = document.getElementById("modalBody");

function openModal(id) {
  const item = myPermissions.find((p) => p.id === id);
  if (!item) return;

  modalBody.innerHTML = `
    <div class="grid grid-cols-2 gap-4 text-sm">
      <div>
        <p class="text-slate-500 mb-1">Permission ID</p>
        <p class="font-medium text-slate-800">${item.id}</p>
      </div>
      <div>
        <p class="text-slate-500 mb-1">Status</p>
        <span class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-semibold ${statusStyles[item.status]}">${item.status}</span>
      </div>
      <div>
        <p class="text-slate-500 mb-1">Type</p>
        <span class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-semibold ${badgeStyles[item.type]}">${item.type}</span>
      </div>
      <div>
        <p class="text-slate-500 mb-1">Date & Time</p>
        <p class="font-medium text-slate-800">${item.date}</p>
        <p class="text-xs text-slate-500">${item.time} (${item.durationHours}h)</p>
      </div>
      <div class="col-span-2 rounded-xl bg-slate-50 p-3">
        <p class="text-slate-500 mb-1">Reason</p>
        <p class="font-medium text-slate-800">${item.reason}</p>
      </div>
      <div class="col-span-2 border-t border-slate-100 pt-3">
        <p class="text-xs text-slate-400">Submitted on: ${item.submitted}</p>
      </div>
    </div>
  `;

  modal.classList.remove("hidden");
  modal.classList.add("flex");
  setTimeout(() => {
    modal.classList.remove("opacity-0");
    modalContent.classList.remove("scale-95");
  }, 10);
}

function closeModal() {
  modal.classList.add("opacity-0");
  modalContent.classList.add("scale-95");
  setTimeout(() => {
    modal.classList.add("hidden");
    modal.classList.remove("flex");
  }, 300);
}

// Init Render
function renderAll() {
  updateDashboardCards();
  updateTodayPermissions();
  updateHistoryTable();
}

document.addEventListener("DOMContentLoaded", renderAll);
