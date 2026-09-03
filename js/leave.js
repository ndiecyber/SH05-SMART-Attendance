// ===============================
// Sidebar
// ===============================

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
    sidebar?.classList.remove("-translate-x-full");
    sidebarOverlay?.classList.add("hidden");
    document.body.classList.remove("overflow-hidden");
    return;
  }

  sidebar?.classList.add("-translate-x-full");
}

openSidebar?.addEventListener("click", showSidebar);
closeSidebar?.addEventListener("click", hideSidebar);
sidebarOverlay?.addEventListener("click", hideSidebar);
window.addEventListener("resize", handleResize);

handleResize();

// ===============================
// Header Clock
// ===============================

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

// ===============================
// Formatter
// ===============================

const formatter = new Intl.DateTimeFormat("en-GB", {
  day: "2-digit",
  month: "short",
  year: "numeric",
});

// ===============================
// Upcoming Leave
// ===============================

const upcomingLeaves = [
  {
    title: "Annual Leave",
    start_date: "2026-08-12",
    end_date: "2026-08-16",
    duration: 5,
  },
  {
    title: "Family Leave",
    start_date: "2026-08-25",
    end_date: "2026-08-27",
    duration: 3,
  },
  {
    title: "Medical Leave",
    start_date: "2026-09-08",
    end_date: "2026-09-09",
    duration: 2,
  },
];

const list = document.getElementById("upcomingLeaveList");
const count = document.getElementById("upcomingLeaveCount");

if (count) {
  count.textContent = `${upcomingLeaves.length} Leave${
    upcomingLeaves.length > 1 ? "s" : ""
  }`;
}

if (list) {
  list.innerHTML = upcomingLeaves
    .map(
      (leave) => `
        <div class="rounded-2xl border border-slate-200 p-4 transition hover:border-emerald-300 hover:bg-emerald-50">
          <div class="flex items-start justify-between gap-4">
            <div>
              <h4 class="font-medium text-slate-800">${leave.title}</h4>
              <p class="mt-1 text-sm text-slate-500">
                ${formatter.format(new Date(leave.start_date))}
                –
                ${formatter.format(new Date(leave.end_date))}
              </p>
            </div>

            <span class="whitespace-nowrap rounded-full bg-blue-100 px-3 py-1 text-xs font-medium text-blue-700">
              ${leave.duration} Day${leave.duration > 1 ? "s" : ""}
            </span>
          </div>
        </div>
      `,
    )
    .join("");
}

// ===============================
// Team Leave
// ===============================

const teamLeaves = [
  {
    name: "Khansa Putri",
    position: "UI/UX Designer",
    avatar: "https://i.pravatar.cc/100?img=12",
    status: "Currently on Leave",
    startDate: "10 Aug 2026",
    endDate: "15 Aug 2026",
  },
  {
    name: "Ajib Pratama",
    position: "Frontend Developer",
    avatar: "https://i.pravatar.cc/100?img=33",
    status: "Upcoming Leave",
    startDate: "18 Aug 2026",
    endDate: "20 Aug 2026",
  },
  {
    name: "Zaki Ramadhan",
    position: "Backend Developer",
    avatar: "https://i.pravatar.cc/100?img=66",
    status: "Upcoming Leave",
    startDate: "25 Aug 2026",
    endDate: "29 Aug 2026",
  },
  {
    name: "Farrel Jhonathan",
    position: "QA Engineer",
    avatar: "https://i.pravatar.cc/100?img=3",
    status: "Currently on Leave",
    startDate: "12 Aug 2026",
    endDate: "14 Aug 2026",
  },
];

const teamLeaveCountEl = document.getElementById("teamLeaveCount");
const teamLeaveListEl = document.getElementById("teamLeaveList");

if (teamLeaveCountEl) {
  teamLeaveCountEl.textContent = `${teamLeaves.length} Members`;
}

if (teamLeaveListEl) {
  teamLeaveListEl.innerHTML = teamLeaves
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
            <span
              class="inline-flex rounded-full px-3 py-1 text-xs font-medium ${
                member.status === "Currently on Leave"
                  ? "bg-red-100 text-red-700"
                  : "bg-amber-100 text-amber-700"
              }"
            >
              ${member.status}
            </span>

            <p class="mt-2 text-sm text-slate-500">
              ${member.startDate} – ${member.endDate}
            </p>
          </div>
        </div>
      `,
    )
    .join("");
}

// ===============================
// Table Filter & Pagination
// ===============================

const searchLeave = document.getElementById("searchLeave");
const filterType = document.getElementById("filterType");
const filterStatus = document.getElementById("filterStatus");
const filterYear = document.getElementById("filterYear");
const resetFilter = document.getElementById("resetFilter");

const tbody = document.querySelector("tbody");
const allRows = [...tbody.querySelectorAll("tr")];

const prevBtn = document.getElementById("prevPage");
const nextBtn = document.getElementById("nextPage");
const pageIndicator = document.getElementById("pageIndicator");

const startItem = document.getElementById("startItem");
const endItem = document.getElementById("endItem");
const totalItems = document.getElementById("totalItems");

const rowsPerPage = 8;
let currentPage = 1;
let filteredRows = [...allRows];

function filterLeaves() {
  const query = searchLeave?.value.toLowerCase().trim() || "";
  const selectedType = filterType?.value.toLowerCase() || "";
  const selectedStatus = filterStatus?.value.toLowerCase() || "";
  const selectedYear = filterYear?.value.toLowerCase() || "";

  filteredRows = allRows.filter((row) => {
    const cells = row.querySelectorAll("td");
    if (cells.length < 7) return true;

    const leaveType = cells[1].textContent.toLowerCase();
    const period = cells[2].textContent.toLowerCase();
    const reason = cells[4].textContent.toLowerCase();
    const status = cells[5].textContent.toLowerCase();
    const submitted = cells[6].textContent.toLowerCase();
    const fullText = row.textContent.toLowerCase();

    // Matching logic
    const matchesSearch =
      !query || fullText.includes(query) || reason.includes(query);
    const matchesType = !selectedType || leaveType.includes(selectedType);
    const matchesStatus = !selectedStatus || status.includes(selectedStatus);
    const matchesYear =
      !selectedYear ||
      period.includes(selectedYear) ||
      submitted.includes(selectedYear);

    return matchesSearch && matchesType && matchesStatus && matchesYear;
  });

  currentPage = 1;
  renderTable();
}

function renderTable() {
  const total = filteredRows.length;
  const totalPages = Math.ceil(total / rowsPerPage) || 1;

  // Sembunyikan semua baris
  allRows.forEach((row) => row.classList.add("hidden"));

  // Tampilkan baris yang lolos filter sesuai halaman aktif
  const startIdx = (currentPage - 1) * rowsPerPage;
  const endIdx = startIdx + rowsPerPage;
  const activeItems = filteredRows.slice(startIdx, endIdx);

  activeItems.forEach((row) => row.classList.remove("hidden"));

  // Update indikator UI
  const startDisplay = total > 0 ? startIdx + 1 : 0;
  const endDisplay = Math.min(endIdx, total);

  if (startItem) startItem.textContent = startDisplay;
  if (endItem) endItem.textContent = endDisplay;
  if (totalItems) totalItems.textContent = total;

  if (pageIndicator) {
    pageIndicator.textContent = `Page ${currentPage} of ${totalPages}`;
  }

  if (prevBtn) prevBtn.disabled = currentPage === 1;
  if (nextBtn) nextBtn.disabled = currentPage === totalPages || total === 0;
}

// Event Listeners Filter
searchLeave?.addEventListener("input", filterLeaves);
filterType?.addEventListener("change", filterLeaves);
filterStatus?.addEventListener("change", filterLeaves);
filterYear?.addEventListener("change", filterLeaves);

resetFilter?.addEventListener("click", () => {
  if (searchLeave) searchLeave.value = "";
  if (filterType) filterType.selectedIndex = 0;
  if (filterStatus) filterStatus.selectedIndex = 0;
  if (filterYear) filterYear.selectedIndex = 0;

  filterLeaves();
});

// Event Listeners Pagination
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
