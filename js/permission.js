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
// 4. TABLE FILTERING & PAGINATION SYSTEM
// ==========================================
const searchPermission = document.getElementById("searchPermission");
const filterType = document.getElementById("filterType");
const filterStatus = document.getElementById("filterStatus");
const filterMonth = document.getElementById("filterMonth");
const resetFilter = document.getElementById("resetFilter");

const tbody = document.querySelector("tbody");
const allRows = Array.from(tbody?.querySelectorAll("tr") || []);

const prevBtn = document.getElementById("prevPage");
const nextBtn = document.getElementById("nextPage");
const pageIndicator = document.getElementById("pageIndicator");

const startItem = document.getElementById("startItem");
const endItem = document.getElementById("endItem");
const totalItems = document.getElementById("totalItems");

const rowsPerPage = 8;
let currentPage = 1;
let filteredRows = [...allRows];

// Normalisasi pencocokan Tipe Izin antara <select> dan teks Tabel
function matchType(selectValue, textContent) {
  if (!selectValue) return true;
  const target = selectValue.toLowerCase().replace(/\s+/g, "");
  const source = textContent.toLowerCase().replace(/\s+/g, "");
  return source.includes(target);
}

// Fungsi Penyaringan Utama
function filterLeaves() {
  const query = searchPermission?.value.toLowerCase().trim() || "";
  const selectedType = filterType?.value || "";
  const selectedStatus = filterStatus?.value.toLowerCase() || "";
  const selectedMonth = filterMonth?.value.toLowerCase() || "";

  filteredRows = allRows.filter((row) => {
    const text = row.textContent.toLowerCase();
    const typeText = row.children[1]?.textContent.trim() || "";
    const dateText = row.children[2]?.textContent.toLowerCase() || "";
    const statusText = row.children[6]?.textContent.trim().toLowerCase() || "";

    // Validation Filters
    const matchesSearch = !query || text.includes(query);
    const matchesType = matchType(selectedType, typeText);
    const matchesStatus =
      !selectedStatus || statusText.includes(selectedStatus);
    const matchesMonth = !selectedMonth || dateText.includes(selectedMonth);

    return matchesSearch && matchesType && matchesStatus && matchesMonth;
  });

  currentPage = 1; // Reset ke halaman pertama setiap kali filter berubah
  renderTable();
}

// Fungsi Render Paginasi
function renderTable() {
  const total = filteredRows.length;
  const totalPages = Math.ceil(total / rowsPerPage) || 1;

  // Sembunyikan semua elemen row terlebih dahulu
  allRows.forEach((row) => row.classList.add("hidden"));

  // Tampilkan hanya baris yang lolos filter pada halaman aktif
  const startIdx = (currentPage - 1) * rowsPerPage;
  const endIdx = startIdx + rowsPerPage;
  const pageItems = filteredRows.slice(startIdx, endIdx);

  pageItems.forEach((row) => row.classList.remove("hidden"));

  // Update statistik paginasi UI
  const startDisplay = total > 0 ? startIdx + 1 : 0;
  const endDisplay = Math.min(endIdx, total);

  if (startItem) startItem.textContent = startDisplay;
  if (endItem) endItem.textContent = endDisplay;
  if (totalItems) totalItems.textContent = total;
  if (pageIndicator)
    pageIndicator.textContent = `Page ${currentPage} of ${totalPages}`;

  if (prevBtn) prevBtn.disabled = currentPage === 1;
  if (nextBtn) nextBtn.disabled = currentPage === totalPages || total === 0;
}

// Event Listeners untuk Filter
searchPermission?.addEventListener("input", filterLeaves);
filterType?.addEventListener("change", filterLeaves);
filterStatus?.addEventListener("change", filterLeaves);
filterMonth?.addEventListener("change", filterLeaves);

resetFilter?.addEventListener("click", () => {
  if (searchPermission) searchPermission.value = "";
  if (filterType) filterType.selectedIndex = 0;
  if (filterStatus) filterStatus.selectedIndex = 0;
  if (filterMonth) filterMonth.selectedIndex = 0;

  filterLeaves();
});

// Event Listeners untuk Navigasi Paginasi
prevBtn?.addEventListener("click", () => {
  if (currentPage > 1) {
    currentPage--;
    renderTable();
  }
});

nextBtn?.addEventListener("click", () => {
  const totalPages = Math.ceil(filteredRows.length / rowsPerPage);
  if (currentPage < totalPages) {
    currentPage++;
    renderTable();
  }
});

// Inisialisasi awal
filterLeaves();

// ==========================================
// DATA SEMENTARA: PERMISSION TODAY'S
// ==========================================
const todayPermissions = [
  {
    id: "PM-2026-002",
    name: "Farrel Jhonathan",
    avatar: "https://i.pravatar.cc/100?img=3",
    type: "Arrive Late",
    time: "09:30 - 10:30",
    duration: "1 Hour",
    reason: "Hospital appointment",
    status: "Pending",
  },
  {
    id: "PM-2026-001",
    name: "Khansa Putri",
    avatar: "https://i.pravatar.cc/100?img=12",
    type: "WFH",
    time: "08:00 - 17:00",
    duration: "9 Hours",
    reason: "Internet maintenance at home",
    status: "Approved",
  },
  {
    id: "PM-2026-005",
    name: "Tuminah Putri",
    avatar: "https://i.pravatar.cc/100?img=20",
    type: "Pop Out",
    time: "13:00 - 14:00",
    duration: "1 Hour",
    reason: "Bank administration",
    status: "Approved",
  },
];

const permissionBadgeStyles = {
  WFH: "bg-sky-100 text-sky-700",
  "Arrive Late": "bg-amber-100 text-amber-700",
  "Business Trip": "bg-indigo-100 text-indigo-700",
  "Leave Early": "bg-orange-100 text-orange-700",
  "Pop Out": "bg-violet-100 text-violet-700",
};

const statusBadgeStyles = {
  Approved: "bg-emerald-100 text-emerald-700",
  Pending: "bg-yellow-100 text-yellow-700",
  Rejected: "bg-rose-100 text-rose-700",
};

// Render Ke Dalam Container #upcomingPermissionList
const upcomingPermissionListEl = document.getElementById(
  "upcomingPermissionList",
);

if (upcomingPermissionListEl) {
  if (todayPermissions.length === 0) {
    upcomingPermissionListEl.innerHTML = `
      <div class="py-8 text-center text-slate-400 text-sm">
        No permissions recorded for today.
      </div>
    `;
  } else {
    upcomingPermissionListEl.innerHTML = todayPermissions
      .map(
        (item) => `
        <div class="flex items-start gap-4 rounded-2xl border border-slate-200 p-4 transition hover:border-slate-300 hover:bg-slate-50">
          <img
            src="${item.avatar}"
            alt="${item.name}"
            class="h-10 w-10 rounded-full object-cover shrink-0"
          />

          <div class="flex-1 min-w-0">
            <div class="flex items-center justify-between gap-2">
              <h4 class="font-medium text-slate-800 truncate">${item.name}</h4>
              <span class="rounded-full px-2.5 py-0.5 text-xs font-semibold shrink-0 ${statusBadgeStyles[item.status] || "bg-slate-100 text-slate-600"}">
                ${item.status}
              </span>
            </div>

            <div class="mt-1 flex flex-wrap items-center gap-2 text-xs text-slate-500">
              <span class="rounded-full px-2 py-0.5 font-medium ${permissionBadgeStyles[item.type] || "bg-slate-100 text-slate-600"}">
                ${item.type}
              </span>
              <span>•</span>
              <span class="font-medium text-slate-700">${item.time} (${item.duration})</span>
            </div>

            <p class="mt-2 text-xs text-slate-500 line-clamp-1">
              <span class="font-medium text-slate-600">Reason:</span> ${item.reason}
            </p>
          </div>
        </div>
      `,
      )
      .join("");
  }
}
