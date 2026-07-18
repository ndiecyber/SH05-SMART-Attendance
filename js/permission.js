// Sidebar mobile
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

// Attendance UI elements
const headerTime = document.getElementById("headerTime");
const headerDate = document.getElementById("headerDate");

function formatTime(date) {
  return date.toLocaleTimeString("en-US", {
    hour: "2-digit",
    minute: "2-digit",
    hour12: false,
  });
}

function updateClock() {
  const now = new Date();
  const timeText = now.toLocaleTimeString("en-US", {
    hour: "2-digit",
    minute: "2-digit",
    second: "2-digit",
    hour12: false,
  });
  const dayText = now.toLocaleDateString("en-US", { weekday: "long" });
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

// ===============================
// Team Permission
// ===============================

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

document.getElementById("teamPermissionCount").textContent =
  `${teamPermissions.length} Members`;

document.getElementById("teamPermissionList").innerHTML = teamPermissions
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
              permissionBadge[member.type]
            }"
          >
            ${member.type}
          </span>

          <p class="mt-2 text-sm text-slate-500">
            ${member.date}
          </p>
        </div>
      </div>
    `,
  )
  .join("");

const searchPermission = document.getElementById("searchPermission");
const filterType = document.getElementById("filterType");
const filterStatus = document.getElementById("filterStatus");
const filterMonth = document.getElementById("filterMonth");
const resetFilter = document.getElementById("resetFilter");

resetFilter.addEventListener("click", () => {
  searchPermission.value = "";
  filterType.selectedIndex = 0;
  filterStatus.selectedIndex = 0;
  filterMonth.selectedIndex = 0;

  // Jalankan ulang filter jika ada fungsi filtering
  filterLeaves();
});

const rowsPerPage = 8;
const tbody = document.querySelector("tbody");
const rows = [...tbody.querySelectorAll("tr")];

const prevBtn = document.getElementById("prevPage");
const nextBtn = document.getElementById("nextPage");
const pageIndicator = document.getElementById("pageIndicator");

const startItem = document.getElementById("startItem");
const endItem = document.getElementById("endItem");
const totalItems = document.getElementById("totalItems");

let currentPage = 1;

function renderTable() {
  const totalPages = Math.ceil(rows.length / rowsPerPage);

  rows.forEach((row, index) => {
    const start = (currentPage - 1) * rowsPerPage;
    const end = start + rowsPerPage;

    row.classList.toggle("hidden", !(index >= start && index < end));
  });

  const start = (currentPage - 1) * rowsPerPage + 1;
  const end = Math.min(currentPage * rowsPerPage, rows.length);

  startItem.textContent = rows.length ? start : 0;
  endItem.textContent = end;
  totalItems.textContent = rows.length;

  pageIndicator.textContent = `Page ${currentPage} of ${totalPages}`;

  prevBtn.disabled = currentPage === 1;
  nextBtn.disabled = currentPage === totalPages;
}

prevBtn.addEventListener("click", () => {
  if (currentPage > 1) {
    currentPage--;
    renderTable();
  }
});

nextBtn.addEventListener("click", () => {
  const totalPages = Math.ceil(rows.length / rowsPerPage);

  if (currentPage < totalPages) {
    currentPage++;
    renderTable();
  }
});

renderTable();
