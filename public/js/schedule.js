// ================= 1. SIDEBAR & MOBILE NAVIGATION ================= //
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

// Realtime Header Clock
const headerTime = document.getElementById("headerTime");
const headerDate = document.getElementById("headerDate");

function updateClock() {
  const now = new Date();
  if (headerTime) {
    headerTime.textContent = now.toLocaleTimeString("en-US", {
      hour: "2-digit",
      minute: "2-digit",
      second: "2-digit",
      hour12: false,
    });
  }
  if (headerDate) {
    headerDate.textContent = now.toLocaleDateString("en-US", {
      day: "numeric",
      month: "long",
      year: "numeric",
    });
  }
}
updateClock();
setInterval(updateClock, 1000);

// ================= 2. MASTER DATA SCHEDULE (MASTER TERHUBUNG) ================= //
// Semua komponen (Upcoming, Today, Weekly, & All Schedule) mengambil data dari sini!
const masterSchedules = [
  {
    id: 1,
    title: "Morning Shift",
    subtitle: "Standard working hours",
    type: "Work Shift",
    dateStr: "2026-07-20", // YYYY-MM-DD
    dayName: "Monday",
    displayDate: "20 Jul 2026",
    startTime: "08:00",
    endTime: "17:00",
    duration: "9h 00m",
    location: "Head Office - Lt. 2",
    status: "Carried Out",
    description:
      "Shift kerja pagi reguler. Menangani tiket masuk dan pemantauan harian sistem.",
    bgBadge: "bg-emerald-100",
    textBadge: "text-emerald-700",
    colorClass: "bg-emerald-500",
  },
  {
    id: 2,
    title: "Weekly Team Meeting",
    subtitle: "Weekly progress discussion",
    type: "Meeting",
    dateStr: "2026-07-20",
    dayName: "Monday",
    displayDate: "20 Jul 2026",
    startTime: "09:00",
    endTime: "10:00",
    duration: "1h 00m",
    location: "Meeting Room A",
    status: "Carried Out",
    description:
      "Diskusi mingguan perkembangan proyek frontend dan pembagian sprint mingguan.",
    bgBadge: "bg-indigo-100",
    textBadge: "text-indigo-700",
    colorClass: "bg-indigo-500",
  },
  {
    id: 3,
    title: "Lunch Break",
    subtitle: "Regular lunch break",
    type: "Break",
    dateStr: "2026-07-20",
    dayName: "Monday",
    displayDate: "20 Jul 2026",
    startTime: "12:00",
    endTime: "13:00",
    duration: "1h 00m",
    location: "Break Area",
    status: "Carried Out",
    description: "Waktu istirahat makan siang harian karyawan.",
    bgBadge: "bg-amber-100",
    textBadge: "text-amber-700",
    colorClass: "bg-amber-500",
  },
  {
    id: 4,
    title: "Safety & Security Training",
    subtitle: "Safety and emergency procedures",
    type: "Training",
    dateStr: "2026-07-21",
    dayName: "Tuesday",
    displayDate: "21 Jul 2026",
    startTime: "10:00",
    endTime: "12:00",
    duration: "2h 00m",
    location: "Training Room",
    status: "Carried Out",
    description: "Pelatihan K3 dan keselamatan kerja dalam lingkungan kantor.",
    bgBadge: "bg-violet-100",
    textBadge: "text-violet-700",
    colorClass: "bg-violet-500",
  },
  {
    id: 5,
    title: "Project Review Meeting",
    subtitle: "Project progress and evaluation",
    type: "Meeting",
    dateStr: "2026-07-22",
    dayName: "Wednesday",
    displayDate: "22 Jul 2026",
    startTime: "13:30",
    endTime: "14:30",
    duration: "1h 00m",
    location: "Meeting Room B",
    status: "Scheduled",
    description:
      "Peninjauan milestone dan evaluasi performa fitur Smart Attendance.",
    bgBadge: "bg-indigo-100",
    textBadge: "text-indigo-700",
    colorClass: "bg-indigo-500",
  },
  {
    id: 6,
    title: "Regular Work Shift",
    subtitle: "Standard working hours",
    type: "Work Shift",
    dateStr: "2026-07-23",
    dayName: "Thursday",
    displayDate: "23 Jul 2026",
    startTime: "08:00",
    endTime: "17:00",
    duration: "9h 00m",
    location: "Head Office",
    status: "Scheduled",
    description:
      "Jam kerja rutin operasional dan pengerjaan tugas rutin harian.",
    bgBadge: "bg-emerald-100",
    textBadge: "text-emerald-700",
    colorClass: "bg-emerald-500",
  },
  {
    id: 7,
    title: "Communication Training",
    subtitle: "Effective workplace communication",
    type: "Training",
    dateStr: "2026-07-24",
    dayName: "Friday",
    displayDate: "24 Jul 2026",
    startTime: "09:00",
    endTime: "11:00",
    duration: "2h 00m",
    location: "Training Room",
    status: "Scheduled",
    description:
      "Sesi interaktif pengembangan kemampuan komunikasi profesional antar anggota tim.",
    bgBadge: "bg-violet-100",
    textBadge: "text-violet-700",
    colorClass: "bg-violet-500",
  },
];

// ================= 3. MODAL POPUP TERPUSAT (GLOBAL) ================= //
function openGlobalEventModal(item) {
  const modal = document.getElementById("eventDetailModal");
  const modalContent = document.getElementById("eventModalContent");

  if (!modal) return;

  document.getElementById("modalTitle").textContent = item.title;
  document.getElementById("modalCategory").textContent = item.type;
  document.getElementById("modalCategory").className =
    `inline-block rounded-full ${item.bgBadge} ${item.textBadge} px-2.5 py-0.5 text-[11px] font-semibold`;
  document.getElementById("modalDateTime").textContent =
    `${item.dayName}, ${item.displayDate} (${item.startTime} - ${item.endTime})`;
  document.getElementById("modalLocation").textContent = item.location;
  document.getElementById("modalDescription").textContent =
    item.description || "Tidak ada deskripsi tambahan.";

  const modalBadgeIcon = document.getElementById("modalBadgeIcon");
  if (modalBadgeIcon) {
    modalBadgeIcon.className = `flex h-10 w-10 items-center justify-center rounded-2xl ${item.bgBadge} ${item.textBadge}`;
    modalBadgeIcon.innerHTML = `<span class="h-3 w-3 rounded-full ${item.colorClass}"></span>`;
  }

  modal.classList.remove("opacity-0", "pointer-events-none");
  modalContent?.classList.remove("scale-95");
  modalContent?.classList.add("scale-100");
  document.body.classList.add("overflow-hidden");
}

function closeGlobalEventModal() {
  const modal = document.getElementById("eventDetailModal");
  const modalContent = document.getElementById("eventModalContent");

  if (!modal) return;

  modal.classList.add("opacity-0", "pointer-events-none");
  modalContent?.classList.remove("scale-100");
  modalContent?.classList.add("scale-95");
  document.body.classList.remove("overflow-hidden");
}

document
  .getElementById("closeModalBtn")
  ?.addEventListener("click", closeGlobalEventModal);
document
  .getElementById("confirmModalBtn")
  ?.addEventListener("click", closeGlobalEventModal);
document.getElementById("eventDetailModal")?.addEventListener("click", (e) => {
  if (e.target.id === "eventDetailModal") closeGlobalEventModal();
});

// ================= 4. UPCOMING EVENTS SECTION ================= //
function renderUpcomingSection() {
  const container = document.getElementById("upcomingEventsContainer");
  if (!container) return;

  // Ambil 5 acara terdekat dari master
  const upcomingList = masterSchedules.slice(0, 5);

  container.innerHTML = upcomingList
    .map(
      (item) => `
    <div 
      onclick="openGlobalEventModal(masterSchedules.find(e => e.id === ${item.id}))"
      class="group flex cursor-pointer items-center justify-between rounded-2xl border border-slate-100 bg-slate-50/50 p-3.5 transition-all duration-200 hover:border-indigo-200 hover:bg-white hover:shadow-md"
    >
      <div class="flex items-center gap-3.5">
        <div class="h-10 w-1.5 rounded-full ${item.colorClass}"></div>
        <div>
          <h4 class="font-semibold text-slate-800 transition group-hover:text-indigo-600">
            ${item.title}
          </h4>
          <p class="text-xs text-slate-500 mt-0.5 flex items-center gap-1">
            <svg class="h-3.5 w-3.5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m7-3A10 10 0 1112 2a10 10 0 0110 10z"/>
            </svg>
            ${item.dayName}, ${item.displayDate} • ${item.startTime} - ${item.endTime}
          </p>
        </div>
      </div>
      <div class="flex items-center gap-2">
        <span class="rounded-full ${item.bgBadge} ${item.textBadge} px-2.5 py-1 text-[11px] font-semibold hidden sm:inline-block">
          ${item.type}
        </span>
        <svg class="h-5 w-5 text-slate-300 transition group-hover:translate-x-0.5 group-hover:text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
        </svg>
      </div>
    </div>
  `,
    )
    .join("");
}

// ================= 5. TODAY'S SCHEDULE (OVERLAPPING) ================= //
function renderTodaySection() {
  const container = document.getElementById("todayOverlapCol");
  if (!container) return;

  const BASE_START = 7;
  const HOUR_HEIGHT = 48;

  // Filter jadwal untuk hari ini (misal ambil tanggal 2026-07-20 sebagai contoh)
  const todayEvents = masterSchedules.filter(
    (item) => item.dateStr === "2026-07-20",
  );

  const oldCards = container.querySelectorAll(".event-card-item");
  oldCards.forEach((c) => c.remove());

  todayEvents.forEach((item, index) => {
    const [startH, startM] = item.startTime.split(":").map(Number);
    const [endH, endM] = item.endTime.split(":").map(Number);

    const startTotal = (startH - BASE_START) * 60 + startM;
    const duration = (endH - startH) * 60 + (endM - startM);

    const topPx = (startTotal / 60) * HOUR_HEIGHT;
    const heightPx = (duration / 60) * HOUR_HEIGHT;

    // Atur penumpukan z-index & indentasi jika tumpang tindih
    const isOverlay = duration < 120 || index > 0;
    const leftIndent = isOverlay ? "left-3 right-3" : "left-1 right-1";
    const zIndex = isOverlay ? "z-10" : "z-0";

    const block = document.createElement("div");
    block.className = `event-card-item absolute ${leftIndent} ${zIndex} rounded-xl ${item.bgBadge} border border-slate-200/60 p-2 cursor-pointer transition-all duration-200 hover:scale-[1.01] hover:shadow-md flex flex-col justify-between overflow-hidden`;
    block.style.top = `${topPx}px`;
    block.style.height = `${heightPx - 2}px`;

    block.innerHTML = `
      <div>
        <div class="flex items-center justify-between gap-1">
          <span class="truncate text-[11px] font-bold ${item.textBadge}">${item.title}</span>
          <span class="text-[9px] px-1.5 py-0.5 rounded-full bg-white/70 font-medium shrink-0">${item.type}</span>
        </div>
        <p class="mt-0.5 text-[10px] ${item.textBadge} opacity-90 flex items-center gap-1">
          <svg class="h-3 w-3 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m7-3A10 10 0 1112 2a10 10 0 0110 10z"/>
          </svg>
          ${item.startTime} - ${item.endTime}
        </p>
      </div>
    `;

    block.addEventListener("click", () => openGlobalEventModal(item));
    container.appendChild(block);
  });
}

// ================= 6. WEEKLY SCHEDULE SECTION ================= //
let currentMonday = new Date("2026-07-20");

function renderWeeklySection() {
  const weekRangeEl = document.getElementById("weekRange");
  const dates = [];

  for (let i = 0; i < 7; i++) {
    const d = new Date(currentMonday);
    d.setDate(d.getDate() + i);
    dates.push(d);

    const dayNumEl = document.getElementById(`dayNum-${i}`);
    if (dayNumEl) dayNumEl.textContent = d.getDate();
  }

  if (weekRangeEl) {
    const startDay = dates[0].getDate();
    const endDay = dates[6].getDate();
    const month = dates[6].toLocaleDateString("en-US", { month: "long" });
    const year = dates[6].getFullYear();
    weekRangeEl.textContent = `${startDay} - ${endDay} ${month} ${year}`;
  }

  const BASE_START = 7;
  const HOUR_HEIGHT = 48;

  for (let i = 0; i < 7; i++) {
    const col = document.getElementById(`weeklyCol-${i}`);
    if (!col) continue;

    col.innerHTML = "";

    if (i === 5 || i === 6) {
      col.innerHTML = `
        <div class="flex h-[624px] items-center justify-center">
          <span class="rounded-full bg-slate-100 px-2.5 py-1 text-[10px] font-medium text-slate-400">Day Off</span>
        </div>
      `;
      continue;
    }

    const dayStr = dates[i].toISOString().split("T")[0];
    const dayEvents = masterSchedules.filter((e) => e.dateStr === dayStr);

    dayEvents.forEach((item) => {
      const [startH, startM] = item.startTime.split(":").map(Number);
      const [endH, endM] = item.endTime.split(":").map(Number);

      const startTotal = (startH - BASE_START) * 60 + startM;
      const duration = (endH - startH) * 60 + (endM - startM);

      const topPx = (startTotal / 60) * HOUR_HEIGHT;
      const heightPx = (duration / 60) * HOUR_HEIGHT;

      const block = document.createElement("div");
      block.className = `absolute left-1 right-1 rounded-lg border px-1.5 py-1 cursor-pointer transition-all hover:scale-[1.02] hover:shadow-md ${item.bgBadge} ${item.textBadge} border-slate-200`;
      block.style.top = `${topPx}px`;
      block.style.height = `${heightPx}px`;

      block.innerHTML = `
        <p class="truncate text-[10px] font-bold">${item.title}</p>
        <p class="truncate text-[9px] opacity-80">${item.startTime} - ${item.endTime}</p>
      `;

      block.addEventListener("click", () => openGlobalEventModal(item));
      col.appendChild(block);
    });
  }
}

document.getElementById("prevWeek")?.addEventListener("click", () => {
  currentMonday.setDate(currentMonday.getDate() - 7);
  renderWeeklySection();
});

document.getElementById("nextWeek")?.addEventListener("click", () => {
  currentMonday.setDate(currentMonday.getDate() + 7);
  renderWeeklySection();
});

document.getElementById("todayWeek")?.addEventListener("click", () => {
  currentMonday = new Date("2026-07-20");
  renderWeeklySection();
});

// ================= 7. ALL SCHEDULE TABLE SECTION ================= //
const ITEMS_PER_PAGE = 5;
let currentTablePage = 1;
let filteredTableData = [...masterSchedules];

const scheduleSearch = document.getElementById("scheduleSearch");
const scheduleTypeSelect = document.getElementById("scheduleType");
const scheduleStatusSelect = document.getElementById("scheduleStatus");
const scheduleTableBodyEl = document.getElementById("scheduleTableBody");
const scheduleEmptyEl = document.getElementById("scheduleEmpty");

const scheduleStartEl = document.getElementById("scheduleStart");
const scheduleEndEl = document.getElementById("scheduleEnd");
const scheduleTotalEl = document.getElementById("scheduleTotal");
const schedulePageEl = document.getElementById("schedulePage");
const schedulePrevBtn = document.getElementById("schedulePrev");
const scheduleNextBtn = document.getElementById("scheduleNext");

function filterAndRenderTable() {
  const searchTerm = scheduleSearch?.value.toLowerCase().trim() || "";
  const selectedType = scheduleTypeSelect?.value || "all";
  const selectedStatus = scheduleStatusSelect?.value || "all";

  filteredTableData = masterSchedules.filter((item) => {
    const matchSearch =
      item.title.toLowerCase().includes(searchTerm) ||
      item.location.toLowerCase().includes(searchTerm) ||
      item.description.toLowerCase().includes(searchTerm);

    const matchType = selectedType === "all" || item.type === selectedType;
    const matchStatus =
      selectedStatus === "all" || item.status === selectedStatus;

    return matchSearch && matchType && matchStatus;
  });

  currentTablePage = 1;
  renderTableRows();
}

function renderTableRows() {
  if (!scheduleTableBodyEl) return;

  const total = filteredTableData.length;
  const totalPages = Math.max(1, Math.ceil(total / ITEMS_PER_PAGE));

  if (currentTablePage > totalPages) currentTablePage = totalPages;

  const startIndex = (currentTablePage - 1) * ITEMS_PER_PAGE;
  const endIndex = Math.min(startIndex + ITEMS_PER_PAGE, total);
  const visibleItems = filteredTableData.slice(startIndex, endIndex);

  scheduleTableBodyEl.innerHTML = "";

  if (total === 0) {
    scheduleEmptyEl?.classList.remove("hidden");
    scheduleTableBodyEl.classList.add("hidden");
  } else {
    scheduleEmptyEl?.classList.add("hidden");
    scheduleTableBodyEl.classList.remove("hidden");

    visibleItems.forEach((item) => {
      const tr = document.createElement("tr");
      tr.className = "hover:bg-slate-50/80 cursor-pointer transition-colors";
      tr.innerHTML = `
        <td class="whitespace-nowrap px-6 py-4">
          <p class="font-medium text-slate-800">${item.dayName}</p>
          <p class="text-xs text-slate-500">${item.displayDate}</p>
        </td>
        <td class="px-6 py-4">
          <span class="rounded-full ${item.bgBadge} ${item.textBadge} px-3 py-1 text-xs font-medium">
            ${item.type}
          </span>
        </td>
        <td class="px-6 py-4">
          <p class="font-medium text-slate-700">${item.title}</p>
          <p class="text-xs text-slate-500">${item.subtitle}</p>
        </td>
        <td class="whitespace-nowrap px-6 py-4 text-sm text-slate-600">${item.startTime} - ${item.endTime}</td>
        <td class="whitespace-nowrap px-6 py-4 text-sm text-slate-600">${item.duration}</td>
        <td class="px-6 py-4 text-sm text-slate-600">${item.location}</td>
        <td class="px-6 py-4">
          <span class="rounded-full ${item.status === "Carried Out" ? "bg-slate-100 text-slate-600" : "bg-blue-100 text-blue-700"} px-3 py-1 text-xs font-medium">
            ${item.status}
          </span>
        </td>
      `;

      tr.addEventListener("click", () => openGlobalEventModal(item));
      scheduleTableBodyEl.appendChild(tr);
    });
  }

  if (scheduleStartEl)
    scheduleStartEl.textContent = total === 0 ? 0 : startIndex + 1;
  if (scheduleEndEl) scheduleEndEl.textContent = endIndex;
  if (scheduleTotalEl) scheduleTotalEl.textContent = total;
  if (schedulePageEl) schedulePageEl.textContent = currentTablePage;

  if (schedulePrevBtn) schedulePrevBtn.disabled = currentTablePage === 1;
  if (scheduleNextBtn)
    scheduleNextBtn.disabled = currentTablePage >= totalPages;
}

scheduleSearch?.addEventListener("input", filterAndRenderTable);
scheduleTypeSelect?.addEventListener("change", filterAndRenderTable);
scheduleStatusSelect?.addEventListener("change", filterAndRenderTable);

schedulePrevBtn?.addEventListener("click", () => {
  if (currentTablePage > 1) {
    currentTablePage--;
    renderTableRows();
  }
});

scheduleNextBtn?.addEventListener("click", () => {
  const totalPages = Math.ceil(filteredTableData.length / ITEMS_PER_PAGE);
  if (currentTablePage < totalPages) {
    currentTablePage++;
    renderTableRows();
  }
});

// ================= 8. INITIALIZE ALL COMPONENTS ================= //
document.addEventListener("DOMContentLoaded", () => {
  renderUpcomingSection();
  renderTodaySection();
  renderWeeklySection();
  filterAndRenderTable();
});

// Fallback init
renderUpcomingSection();
renderTodaySection();
renderWeeklySection();
filterAndRenderTable();

// ================= 9. STAT CARDS AUTO-CALCULATION ================= //
function updateStatCards() {
  const todayStr = "2026-07-20"; // Tanggal acuan hari ini (bisa disesuaikan)

  // 1. Today's Shift (Cari shift kerja hari ini)
  const todayWorkShift = masterSchedules.find(
    (item) => item.dateStr === todayStr && item.type === "Work Shift",
  );
  const todayShiftEl = document.getElementById("todayShift");
  if (todayShiftEl) {
    todayShiftEl.textContent = todayWorkShift
      ? `${todayWorkShift.startTime} - ${todayWorkShift.endTime}`
      : "Off Day";
  }

  // 2. Shifts This Week (Hitung total Work Shift minggu ini)
  const weeklyShiftsCount = masterSchedules.filter(
    (item) => item.type === "Work Shift",
  ).length;
  const weeklyShiftsEl = document.getElementById("weeklyShifts");
  if (weeklyShiftsEl) {
    weeklyShiftsEl.textContent = weeklyShiftsCount;
  }

  // 3. Upcoming (Hitung jadwal yang statusnya 'Scheduled')
  const upcomingCount = masterSchedules.filter(
    (item) => item.status === "Scheduled",
  ).length;
  const upcomingShiftsEl = document.getElementById("upcomingShifts");
  if (upcomingShiftsEl) {
    upcomingShiftsEl.textContent = upcomingCount;
  }

  // 4. Hours / Week (Kalkulasi total jam kerja dari durasi Work Shift minggu ini)
  let totalMinutes = 0;
  masterSchedules.forEach((item) => {
    if (item.type === "Work Shift") {
      const [startH, startM] = item.startTime.split(":").map(Number);
      const [endH, endM] = item.endTime.split(":").map(Number);
      totalMinutes += endH * 60 + endM - (startH * 60 + startM);
    }
  });

  const totalHours = Math.round(totalMinutes / 60);
  const weeklyHoursEl = document.getElementById("weeklyHours");
  if (weeklyHoursEl) {
    weeklyHoursEl.textContent = `${totalHours}h`;
  }
}

document.addEventListener("DOMContentLoaded", () => {
  updateStatCards(); // <--- Tambahkan baris ini
  renderUpcomingSection();
  renderTodaySection();
  renderWeeklySection();
  filterAndRenderTable();
});
