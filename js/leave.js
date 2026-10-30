// ===============================
// Sidebar Functionality
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
// Formatters & Limits
// ===============================
const formatter = new Intl.DateTimeFormat("en-GB", {
  day: "2-digit",
  month: "short",
  year: "numeric",
});

const LEAVE_LIMITS = {
  "Annual Leave": 14,
  "Sick Leave": 12,
  "Personal Leave": 5,
  "Unpaid Leave": Infinity,
};

// ===============================
// Master Data Source (Single Source of Truth)
// ===============================
let masterLeaves = [
  {
    id: "LR-2026-001",
    title: "Annual Leave",
    startDate: "2026-08-12",
    endDate: "2026-08-16",
    duration: 5,
    reason: "Family Vacation",
    status: "Approved",
    submitted: "2026-07-10",
  },
  {
    id: "LR-2026-002",
    title: "Family Leave", // Personal Leave
    startDate: "2026-08-25",
    endDate: "2026-08-27",
    duration: 3,
    reason: "Family Gathering",
    status: "Approved",
    submitted: "2026-08-01",
  },
  {
    id: "LR-2026-003",
    title: "Medical Leave", // Sick Leave
    startDate: "2026-09-08",
    endDate: "2026-09-09",
    duration: 2,
    reason: "Medical Checkup",
    status: "Approved",
    submitted: "2026-08-15",
  },
  {
    id: "LR-2026-004",
    title: "Sick Leave",
    startDate: "2026-07-08",
    endDate: "2026-07-08",
    duration: 1,
    reason: "Fever and Rest",
    status: "Pending",
    submitted: "2026-07-07",
  },
  {
    id: "LR-2026-005",
    title: "Personal Leave",
    startDate: "2026-06-20",
    endDate: "2026-06-20",
    duration: 1,
    reason: "Personal Matters",
    status: "Rejected",
    submitted: "2026-06-18",
  },
  {
    id: "LR-2026-006",
    title: "Unpaid Leave",
    startDate: "2026-05-25",
    endDate: "2026-05-29",
    duration: 5,
    reason: "Family Emergency",
    status: "Approved",
    submitted: "2026-05-18",
  },
  {
    id: "LR-2026-007",
    title: "Annual Leave",
    startDate: "2026-07-30",
    endDate: "2026-08-01",
    duration: 3,
    reason: "Summer Break",
    status: "Pending",
    submitted: "2026-07-14",
  },
];

// ===============================
// Team Leave (Static Data)
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

function renderTeamLeaves() {
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
}

// ===============================
// Core Update Functions (Keterhubungan Data)
// ===============================

// Update 8 Stat Cards di Bagian Atas
function updateCardsAndStats() {
  // Hitung penggunaan berdasarkan Approved
  let usedAnnual = 0;
  let usedSick = 0;
  let usedPersonal = 0;
  let usedUnpaid = 0;

  let pendingCount = 0;
  let approvedCount = 0;
  let rejectedCount = 0;
  let totalDaysTaken = 0;

  masterLeaves.forEach((leave) => {
    if (leave.status === "Pending") {
      pendingCount++;
    } else if (leave.status === "Approved") {
      approvedCount++;
      totalDaysTaken += leave.duration;

      // Hitung per jenis cuti
      if (leave.title.includes("Annual")) {
        usedAnnual += leave.duration;
      } else if (
        leave.title.includes("Sick") ||
        leave.title.includes("Medical")
      ) {
        usedSick += leave.duration;
      } else if (
        leave.title.includes("Personal") ||
        leave.title.includes("Family")
      ) {
        usedPersonal += leave.duration;
      } else if (leave.title.includes("Unpaid")) {
        usedUnpaid += leave.duration;
      }
    } else if (leave.status === "Rejected") {
      rejectedCount++;
    }
  });

  // 1. Annual Leave Card
  const remAnnual = LEAVE_LIMITS["Annual Leave"] - usedAnnual;
  const pctAnnual = Math.min(
    100,
    Math.round((usedAnnual / LEAVE_LIMITS["Annual Leave"]) * 100),
  );
  document.getElementById("cardAnnual").innerHTML = `
    <p class="text-sm text-slate-500">Annual Leave</p>
    <h2 class="mt-2 text-3xl font-bold text-slate-800">
      ${remAnnual}<span class="text-lg font-medium text-slate-400">/${LEAVE_LIMITS["Annual Leave"]} Days</span>
    </h2>
    <div class="mt-5">
      <div class="mb-2 flex items-center justify-between text-sm">
        <span class="text-slate-500">Used</span>
        <span class="font-semibold text-slate-700">${usedAnnual} Days</span>
      </div>
      <div class="h-2 overflow-hidden rounded-full bg-slate-200">
        <div class="h-full rounded-full bg-indigo-500" style="width: ${pctAnnual}%"></div>
      </div>
    </div>
    <p class="mt-4 text-sm text-slate-500">
      <span class="font-medium text-indigo-600">${remAnnual} days remaining</span> this year
    </p>
  `;

  // 2. Sick Leave Card
  const remSick = LEAVE_LIMITS["Sick Leave"] - usedSick;
  const pctSick = Math.min(
    100,
    Math.round((usedSick / LEAVE_LIMITS["Sick Leave"]) * 100),
  );
  document.getElementById("cardSick").innerHTML = `
    <p class="text-sm text-slate-500">Sick Leave</p>
    <h2 class="mt-2 text-3xl font-bold text-slate-800">
      ${remSick}<span class="text-lg font-medium text-slate-400">/${LEAVE_LIMITS["Sick Leave"]} Days</span>
    </h2>
    <div class="mt-5">
      <div class="mb-2 flex items-center justify-between text-sm">
        <span class="text-slate-500">Used</span>
        <span class="font-semibold text-slate-700">${usedSick} Days</span>
      </div>
      <div class="h-2 overflow-hidden rounded-full bg-slate-200">
        <div class="h-full rounded-full bg-yellow-500" style="width: ${pctSick}%"></div>
      </div>
    </div>
    <p class="mt-4 text-sm text-slate-500">
      <span class="font-medium text-yellow-600">${remSick} days remaining</span> this year
    </p>
  `;

  // 3. Personal Leave Card
  const remPersonal = LEAVE_LIMITS["Personal Leave"] - usedPersonal;
  const pctPersonal = Math.min(
    100,
    Math.round((usedPersonal / LEAVE_LIMITS["Personal Leave"]) * 100),
  );
  document.getElementById("cardPersonal").innerHTML = `
    <p class="text-sm text-slate-500">Personal Leave</p>
    <h2 class="mt-2 text-3xl font-bold text-slate-800">
      ${remPersonal}<span class="text-lg font-medium text-slate-400">/${LEAVE_LIMITS["Personal Leave"]} Days</span>
    </h2>
    <div class="mt-5">
      <div class="mb-2 flex items-center justify-between text-sm">
        <span class="text-slate-500">Used</span>
        <span class="font-semibold text-slate-700">${usedPersonal} Days</span>
      </div>
      <div class="h-2 overflow-hidden rounded-full bg-slate-200">
        <div class="h-full rounded-full bg-blue-500" style="width: ${pctPersonal}%"></div>
      </div>
    </div>
    <p class="mt-4 text-sm text-slate-500">
      <span class="font-medium text-blue-600">${remPersonal} days remaining</span> this year
    </p>
  `;

  // 4. Unpaid Leave Card
  document.getElementById("cardUnpaid").innerHTML = `
    <p class="text-sm text-slate-500">Unpaid Leave</p>
    <h2 class="mt-2 text-3xl font-bold text-slate-800">Unlimited</h2>
    <div class="mt-5">
      <div class="mb-2 flex items-center justify-between text-sm">
        <span class="text-slate-500">Used</span>
        <span class="font-semibold text-slate-700">${usedUnpaid} Days</span>
      </div>
      <div class="h-2 overflow-hidden rounded-full bg-slate-200">
        <div class="h-full w-full rounded-full bg-slate-400"></div>
      </div>
    </div>
    <p class="mt-4 text-sm text-slate-500">No limit - Approval required per submission.</p>
  `;

  // 4 Stat Cards Status
  document.getElementById("statPending").textContent = pendingCount;
  document.getElementById("statApproved").textContent = approvedCount;
  document.getElementById("statRejected").textContent = rejectedCount;
  document.getElementById("statDaysTaken").textContent = totalDaysTaken;
}

// Render Upcoming Leave (Hanya Cuti Approved)
function renderUpcomingLeaves() {
  const list = document.getElementById("upcomingLeaveList");
  const count = document.getElementById("upcomingLeaveCount");

  const upcomingData = masterLeaves.filter((l) => l.status === "Approved");

  if (count) {
    count.textContent = `${upcomingData.length} Leave${
      upcomingData.length > 1 ? "s" : ""
    }`;
  }

  if (list) {
    if (upcomingData.length === 0) {
      list.innerHTML = `<p class="text-center text-sm text-slate-400 py-4">No approved upcoming leaves.</p>`;
      return;
    }

    list.innerHTML = upcomingData
      .map(
        (leave) => `
        <div 
          onclick="openDetailModal('${leave.id}')"
          class="cursor-pointer rounded-2xl border border-slate-200 p-4 transition hover:border-emerald-300 hover:bg-emerald-50"
        >
          <div class="flex items-start justify-between gap-4">
            <div>
              <h4 class="font-medium text-slate-800">${leave.title}</h4>
              <p class="mt-1 text-sm text-slate-500">
                ${formatter.format(new Date(leave.startDate))}
                –
                ${formatter.format(new Date(leave.endDate))}
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
}

// ===============================
// Modal Detail Functionality
// ===============================
const detailModal = document.getElementById("detailModal");
const closeModal = document.getElementById("closeModal");
const closeModalBtn = document.getElementById("closeModalBtn");
const modalBody = document.getElementById("modalBody");

function openDetailModal(id) {
  const item = masterLeaves.find((l) => l.id === id);
  if (!item) return;

  let statusBadgeClass = "bg-slate-100 text-slate-700";
  if (item.status === "Approved")
    statusBadgeClass = "bg-green-100 text-green-700";
  if (item.status === "Pending")
    statusBadgeClass = "bg-yellow-100 text-yellow-700";
  if (item.status === "Rejected") statusBadgeClass = "bg-red-100 text-red-700";
  if (item.status === "Canceled")
    statusBadgeClass = "bg-gray-200 text-gray-700";

  modalBody.innerHTML = `
    <div class="grid grid-cols-2 gap-4 text-sm">
      <div class="rounded-2xl bg-slate-50 p-3">
        <p class="text-xs text-slate-400">Request ID</p>
        <p class="font-semibold text-slate-700 mt-0.5">${item.id}</p>
      </div>
      <div class="rounded-2xl bg-slate-50 p-3">
        <p class="text-xs text-slate-400">Status</p>
        <span class="inline-block mt-0.5 rounded-full px-2.5 py-0.5 text-xs font-semibold ${statusBadgeClass}">
          ${item.status}
        </span>
      </div>
      <div class="rounded-2xl bg-slate-50 p-3">
        <p class="text-xs text-slate-400">Leave Type</p>
        <p class="font-medium text-slate-700 mt-0.5">${item.title}</p>
      </div>
      <div class="rounded-2xl bg-slate-50 p-3">
        <p class="text-xs text-slate-400">Duration</p>
        <p class="font-medium text-slate-700 mt-0.5">${item.duration} Day(s)</p>
      </div>
      <div class="col-span-2 rounded-2xl bg-slate-50 p-3">
        <p class="text-xs text-slate-400">Period</p>
        <p class="font-medium text-slate-700 mt-0.5">
          ${formatter.format(new Date(item.startDate))} – ${formatter.format(new Date(item.endDate))}
        </p>
      </div>
      <div class="col-span-2 rounded-2xl bg-slate-50 p-3">
        <p class="text-xs text-slate-400">Reason</p>
        <p class="font-medium text-slate-700 mt-0.5">${item.reason}</p>
      </div>
      <div class="col-span-2 rounded-2xl bg-slate-50 p-3">
        <p class="text-xs text-slate-400">Submitted Date</p>
        <p class="font-medium text-slate-700 mt-0.5">${formatter.format(new Date(item.submitted))}</p>
      </div>
    </div>
  `;

  detailModal?.classList.remove("hidden");
}

function hideDetailModal() {
  detailModal?.classList.add("hidden");
}

closeModal?.addEventListener("click", hideDetailModal);
closeModalBtn?.addEventListener("click", hideDetailModal);
detailModal?.addEventListener("click", (e) => {
  if (e.target === detailModal) hideDetailModal();
});

// Cancel Request Handler
function cancelLeaveRequest(id) {
  const item = masterLeaves.find((l) => l.id === id);
  if (item) {
    item.status = "Canceled";
    refreshAll();
  }
}

// ===============================
// Table Filter & Pagination
// ===============================
const searchLeave = document.getElementById("searchLeave");
const filterType = document.getElementById("filterType");
const filterStatus = document.getElementById("filterStatus");
const filterYear = document.getElementById("filterYear");
const resetFilter = document.getElementById("resetFilter");

const tbody = document.getElementById("historyTableBody");
const prevBtn = document.getElementById("prevPage");
const nextBtn = document.getElementById("nextPage");
const pageIndicator = document.getElementById("pageIndicator");

const startItem = document.getElementById("startItem");
const endItem = document.getElementById("endItem");
const totalItems = document.getElementById("totalItems");
const historyTotalBadge = document.getElementById("historyTotalBadge");

const rowsPerPage = 5;
let currentPage = 1;
let filteredData = [...masterLeaves];

function filterLeaves() {
  const query = searchLeave?.value.toLowerCase().trim() || "";
  const selectedType = filterType?.value.toLowerCase() || "";
  const selectedStatus = filterStatus?.value.toLowerCase() || "";
  const selectedYear = filterYear?.value.toLowerCase() || "";

  filteredData = masterLeaves.filter((item) => {
    const matchesSearch =
      !query ||
      item.id.toLowerCase().includes(query) ||
      item.title.toLowerCase().includes(query) ||
      item.reason.toLowerCase().includes(query);

    const matchesType =
      !selectedType || item.title.toLowerCase().includes(selectedType);

    const matchesStatus =
      !selectedStatus || item.status.toLowerCase() === selectedStatus;

    const matchesYear =
      !selectedYear ||
      item.startDate.includes(selectedYear) ||
      item.submitted.includes(selectedYear);

    return matchesSearch && matchesType && matchesStatus && matchesYear;
  });

  currentPage = 1;
  renderTable();
}

function renderTable() {
  const total = filteredData.length;
  const totalPages = Math.ceil(total / rowsPerPage) || 1;

  if (historyTotalBadge) {
    historyTotalBadge.textContent = `${masterLeaves.length} Requests`;
  }

  const startIdx = (currentPage - 1) * rowsPerPage;
  const endIdx = startIdx + rowsPerPage;
  const pageItems = filteredData.slice(startIdx, endIdx);

  if (tbody) {
    if (pageItems.length === 0) {
      tbody.innerHTML = `<tr><td colspan="8" class="text-center py-6 text-slate-400">No requests found</td></tr>`;
    } else {
      tbody.innerHTML = pageItems
        .map((item) => {
          let badgeClass = "bg-slate-100 text-slate-700";
          if (item.status === "Approved")
            badgeClass = "bg-green-100 text-green-700";
          if (item.status === "Pending")
            badgeClass = "bg-yellow-100 text-yellow-700";
          if (item.status === "Rejected")
            badgeClass = "bg-red-100 text-red-700";
          if (item.status === "Canceled")
            badgeClass = "bg-gray-200 text-gray-700";

          const isSingleDate = item.startDate === item.endDate;
          const periodDisplay = isSingleDate
            ? formatter.format(new Date(item.startDate))
            : `${formatter.format(new Date(item.startDate))} - ${formatter.format(new Date(item.endDate))}`;

          return `
            <tr class="hover:bg-slate-50 transition">
              <td class="px-4 py-4 font-medium text-slate-800">${item.id}</td>
              <td class="px-4 py-4">${item.title}</td>
              <td class="px-4 py-4">${periodDisplay}</td>
              <td class="px-4 py-4">${item.duration} Day${item.duration > 1 ? "s" : ""}</td>
              <td class="px-4 py-4">${item.reason}</td>
              <td class="px-4 py-4">
                <span class="rounded-full px-3 py-1 text-xs font-semibold ${badgeClass}">
                  ${item.status}
                </span>
              </td>
              <td class="px-4 py-4">${formatter.format(new Date(item.submitted))}</td>
              <td class="px-4 py-4">
                <div class="flex justify-center gap-2">
                  <button
                    onclick="openDetailModal('${item.id}')"
                    class="rounded-lg bg-indigo-600 px-3 py-1.5 text-xs font-medium text-white transition hover:bg-indigo-700"
                  >
                    View
                  </button>
                  ${
                    item.status === "Pending"
                      ? `<button
                          onclick="cancelLeaveRequest('${item.id}')"
                          class="rounded-lg bg-red-600 px-3 py-1.5 text-xs font-medium text-white transition hover:bg-red-700"
                        >
                          Cancel
                        </button>`
                      : ""
                  }
                </div>
              </td>
            </tr>
          `;
        })
        .join("");
    }
  }

  // Update Indicator
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

// Global Refresh Function
function refreshAll() {
  updateCardsAndStats();
  renderUpcomingLeaves();
  filterLeaves();
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

// Pagination Listeners
prevBtn?.addEventListener("click", () => {
  if (currentPage > 1) {
    currentPage--;
    renderTable();
  }
});

nextBtn?.addEventListener("click", () => {
  const totalPages = Math.ceil(filteredData.length / rowsPerPage);
  if (currentPage < totalPages) {
    currentPage++;
    renderTable();
  }
});

// Init Dynamic Execution
renderTeamLeaves();
refreshAll();
