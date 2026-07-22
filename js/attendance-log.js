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

// Activity chart (canvas, no external library required)
const activityChart = document.getElementById("activityTrendChart");
const activityLabels = [
  "1 Jul",
  "2 Jul",
  "3 Jul",
  "4 Jul",
  "5 Jul",
  "6 Jul",
  "7 Jul",
  "8 Jul",
  "9 Jul",
  "10 Jul",
  "11 Jul",
  "12 Jul",
  "13 Jul",
  "14 Jul",
];
const activitySeries = [
  {
    label: "Check In",
    values: [18, 20, 21, 19, 20, 22, 18, 21, 20, 19, 21, 18, 20, 22],
    color: "#22c55e",
  },
  {
    label: "Check Out",
    values: [18, 19, 20, 18, 20, 21, 18, 20, 19, 18, 20, 17, 19, 21],
    color: "#6366f1",
  },
  {
    label: "Break",
    values: [8, 7, 9, 8, 7, 8, 7, 8, 9, 8, 8, 7, 8, 8],
    color: "#f59e0b",
  },
  {
    label: "Other",
    values: [2, 3, 2, 2, 4, 3, 2, 3, 2, 2, 3, 2, 3, 2],
    color: "#a855f7",
  },
];

function renderActivityChart() {
  if (!activityChart) return;

  activityChart.style.width = "100%";
  activityChart.style.height = "100%";
  activityChart.style.display = "block";

  const ctx = activityChart.getContext("2d");
  if (!ctx) return;

  const rect = activityChart.getBoundingClientRect();
  const width = rect.width || activityChart.clientWidth || 600;
  const height = rect.height || activityChart.clientHeight || 320;
  const dpr = window.devicePixelRatio || 1;

  activityChart.width = width * dpr;
  activityChart.height = height * dpr;
  ctx.setTransform(dpr, 0, 0, dpr, 0, 0);

  ctx.clearRect(0, 0, width, height);

  const padding = { top: 20, right: 20, bottom: 40, left: 30 };
  const chartWidth = width - padding.left - padding.right;
  const chartHeight = height - padding.top - padding.bottom;
  const maxValue =
    Math.max(...activitySeries.flatMap((serie) => serie.values)) + 4;
  const stepY = chartHeight / 5;

  ctx.strokeStyle = "#e2e8f0";
  ctx.lineWidth = 1;
  ctx.font = "12px Inter, sans-serif";
  ctx.fillStyle = "#64748b";

  for (let i = 0; i <= 5; i += 1) {
    const y = padding.top + stepY * i;
    ctx.beginPath();
    ctx.moveTo(padding.left, y);
    ctx.lineTo(width - padding.right, y);
    ctx.stroke();

    const value = Math.round(maxValue - (maxValue / 5) * i);
    ctx.fillText(value, 6, y + 4);
  }

  const groupCount = activityLabels.length;
  const groupWidth = chartWidth / groupCount;
  const barWidth = Math.max(6, groupWidth / (activitySeries.length + 2));
  const gap = Math.max(3, barWidth / 2);

  activitySeries.forEach((serie, seriesIndex) => {
    const seriesHeight = chartHeight / maxValue;

    serie.values.forEach((value, index) => {
      const groupX = padding.left + index * groupWidth;
      const x = groupX + gap + seriesIndex * (barWidth + gap / 2);
      const barHeight = Math.max(8, value * seriesHeight);
      const y = padding.top + chartHeight - barHeight;

      ctx.fillStyle = serie.color;
      ctx.fillRect(x, y, barWidth, barHeight);
    });
  });

  ctx.beginPath();
  ctx.moveTo(padding.left, padding.top + chartHeight);
  ctx.lineTo(width - padding.right, padding.top + chartHeight);
  ctx.stroke();

  ctx.beginPath();
  ctx.moveTo(padding.left, padding.top);
  ctx.lineTo(padding.left, padding.top + chartHeight);
  ctx.stroke();

  ctx.fillStyle = "#64748b";
  activityLabels.forEach((label, index) => {
    const x = padding.left + index * groupWidth + groupWidth / 2;
    ctx.fillText(label, x - 14, height - 10);
  });
}

window.addEventListener("resize", renderActivityChart);
renderActivityChart();

// Table filters and pagination
const searchPermission = document.getElementById("searchPermission");
const filterEvent = document.getElementById("filterEvent");
const filterType = document.getElementById("filterType");
const filterStatus = document.getElementById("filterStatus");
const filterLocation = document.getElementById("filterLocation");
const startDate = document.getElementById("startDate");
const endDate = document.getElementById("endDate");
const resetFilter = document.getElementById("resetFilter");

function normalizeEvent(value) {
  const text = value.toLowerCase();
  if (text.includes("check in")) return "checkIn";
  if (text.includes("check out")) return "checkOut";
  if (text.includes("break")) return "break";
  return "other";
}

function normalizeVerify(value) {
  const text = value.toLowerCase();
  if (text.includes("face")) return "faceId";
  if (text.includes("fingerprint")) return "fingerprint";
  if (text.includes("qr")) return "qrCode";
  if (text.includes("pin")) return "pin";
  return "";
}

function normalizeStatus(value) {
  const text = value.toLowerCase();
  if (text.includes("pending")) return "pending";
  if (text.includes("success")) return "verified";
  if (text.includes("failed")) return "failed";
  return "";
}

function normalizeLocation(value) {
  const text = value.toLowerCase();
  if (text.includes("remote")) return "remote";
  return "office";
}

function parseDate(value) {
  if (!value) return "";
  const parsed = new Date(value);
  return Number.isNaN(parsed.getTime())
    ? ""
    : parsed.toISOString().slice(0, 10);
}

const rowsPerPage = 8;
const tbody = document.querySelector("tbody");
const allRows = tbody ? [...tbody.querySelectorAll("tr")] : [];

allRows.forEach((row) => {
  const timeCell = row.querySelectorAll("td")[0];
  const eventCell = row.querySelectorAll("td")[1];
  const locationCell = row.querySelectorAll("td")[2];
  const ipCell = row.querySelectorAll("td")[3];
  const verifyCell = row.querySelectorAll("td")[4];
  const statusCell = row.querySelectorAll("td")[5];

  const timeText =
    timeCell?.querySelector("p")?.textContent?.trim() ||
    timeCell?.textContent?.trim() ||
    "";
  const dateText =
    timeCell?.querySelectorAll("p")[1]?.textContent?.trim() || "";
  const eventText = eventCell?.textContent?.trim() || "";
  const locationText = locationCell?.textContent?.trim() || "";
  const ipText = ipCell?.textContent?.trim() || "";
  const verifyText = verifyCell?.textContent?.trim() || "";
  const statusText = statusCell?.textContent?.trim() || "";

  row.dataset.search = [
    timeText,
    dateText,
    eventText,
    locationText,
    ipText,
    verifyText,
    statusText,
  ]
    .join(" ")
    .toLowerCase();
  row.dataset.event = normalizeEvent(eventText);
  row.dataset.verifyType = normalizeVerify(verifyText);
  row.dataset.status = normalizeStatus(statusText);
  row.dataset.location = normalizeLocation(locationText);
  row.dataset.date = parseDate(dateText);
});

const prevBtn = document.getElementById("prevPage");
const nextBtn = document.getElementById("nextPage");
const pageIndicator = document.getElementById("pageIndicator");
const startItem = document.getElementById("startItem");
const endItem = document.getElementById("endItem");
const totalItems = document.getElementById("totalItems");

let currentPage = 1;
let filteredRows = [...allRows];

function applyFilters() {
  const query = searchPermission?.value?.toLowerCase().trim() || "";
  const selectedEvent = filterEvent?.value || "";
  const selectedType = filterType?.value || "";
  const selectedStatus = filterStatus?.value || "";
  const selectedLocation = filterLocation?.value || "";
  const selectedFrom = startDate?.value || "";
  const selectedTo = endDate?.value || "";

  filteredRows = allRows.filter((row) => {
    const matchesSearch = !query || row.dataset.search?.includes(query);
    const matchesEvent = !selectedEvent || row.dataset.event === selectedEvent;
    const matchesType =
      !selectedType || row.dataset.verifyType === selectedType;
    const matchesStatus =
      !selectedStatus || row.dataset.status === selectedStatus;
    const matchesLocation =
      !selectedLocation ||
      row.dataset.location === selectedLocation.toLowerCase();
    const matchesFrom = !selectedFrom || row.dataset.date >= selectedFrom;
    const matchesTo = !selectedTo || row.dataset.date <= selectedTo;

    return (
      matchesSearch &&
      matchesEvent &&
      matchesType &&
      matchesStatus &&
      matchesLocation &&
      matchesFrom &&
      matchesTo
    );
  });

  currentPage = 1;
  renderTable();
}

function renderTable() {
  const totalPages = Math.max(1, Math.ceil(filteredRows.length / rowsPerPage));

  allRows.forEach((row) => row.classList.add("hidden"));

  filteredRows
    .slice((currentPage - 1) * rowsPerPage, currentPage * rowsPerPage)
    .forEach((row) => {
      row.classList.remove("hidden");
    });

  const start = filteredRows.length ? (currentPage - 1) * rowsPerPage + 1 : 0;
  const end = Math.min(currentPage * rowsPerPage, filteredRows.length);

  if (startItem) startItem.textContent = start;
  if (endItem) endItem.textContent = end;
  if (totalItems) totalItems.textContent = filteredRows.length;
  if (pageIndicator)
    pageIndicator.textContent = `Page ${currentPage} of ${totalPages}`;

  if (prevBtn) prevBtn.disabled = currentPage === 1;
  if (nextBtn) nextBtn.disabled = currentPage === totalPages;
}

prevBtn?.addEventListener("click", () => {
  if (currentPage > 1) {
    currentPage -= 1;
    renderTable();
  }
});

nextBtn?.addEventListener("click", () => {
  const totalPages = Math.max(1, Math.ceil(filteredRows.length / rowsPerPage));

  if (currentPage < totalPages) {
    currentPage += 1;
    renderTable();
  }
});

[
  searchPermission,
  filterEvent,
  filterType,
  filterStatus,
  filterLocation,
  startDate,
  endDate,
].forEach((element) => {
  element?.addEventListener("input", applyFilters);
  element?.addEventListener("change", applyFilters);
});

resetFilter?.addEventListener("click", () => {
  if (searchPermission) searchPermission.value = "";
  if (filterEvent) filterEvent.selectedIndex = 0;
  if (filterType) filterType.selectedIndex = 0;
  if (filterStatus) filterStatus.selectedIndex = 0;
  if (filterLocation) filterLocation.selectedIndex = 0;
  if (startDate) startDate.value = "";
  if (endDate) endDate.value = "";
  applyFilters();
});

renderTable();
