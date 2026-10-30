const sidebar = document.getElementById("sidebar");
const sidebarOverlay = document.getElementById("sidebarOverlay");
const openSidebar = document.getElementById("openSidebar");
const closeSidebar = document.getElementById("closeSidebar");
const attendanceCalendar = document.getElementById("attendanceCalendar");
const calendarTitle = document.getElementById("calendarTitle");
const prevMonthBtn = document.getElementById("prevMonth");
const nextMonthBtn = document.getElementById("nextMonth");
const filterPeriodSelect = document.getElementById("filterPeriod");
const monthlyTrendCanvas = document.getElementById("monthlyTrendChart");
const monthlyTrendEmpty = document.getElementById("monthlyTrendEmpty");
const importModal = document.getElementById("importCsvModal");
const csvInput = document.getElementById("attendanceCsvFile");
const csvDropzone = document.getElementById("csvDropzone");
const csvFileName = document.getElementById("csvFileName");
const csvFileSize = document.getElementById("csvFileSize");
const csvImportError = document.getElementById("csvImportError");
const csvSubmit = document.getElementById("submitCsvImport");
const historySlider = document.getElementById("historySlider");
const historyDots = document.querySelectorAll("#historyDots span");
const historyPrev = document.getElementById("historyPrev");
const historyNext = document.getElementById("historyNext");
const historyPageCount = historySlider?.children.length || 0;

let currentDate = parseLocalDate(window.attendanceToday);
let historyPage = 0;
let chartInstance = null;
let requestSequence = 0;
let dashboardData = window.smartAttendanceData || {};
let importScrollState = null;

if (importModal && importModal.parentElement !== document.body) {
  document.body.append(importModal);
}

function parseLocalDate(dateString) {
  const [year, month, day] = String(dateString || "").split("-").map(Number);
  return year && month && day ? new Date(year, month - 1, day) : new Date();
}

function formatDateKey(date) {
  return `${date.getFullYear()}-${String(date.getMonth() + 1).padStart(2, "0")}-${String(date.getDate()).padStart(2, "0")}`;
}

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

function updateClock() {
  const now = new Date();
  const headerTime = document.getElementById("headerTime");
  const headerDate = document.getElementById("headerDate");
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

function attendanceCell(status, dateKey, day, isToday) {
  const styles = {
    present: "bg-emerald-50 border-emerald-300 text-emerald-800",
    late: "bg-amber-50 border-amber-300 text-amber-800",
    absent: "bg-rose-50 border-rose-300 text-rose-700",
    none: "bg-white border-slate-200 text-slate-700",
  };
  const record = dashboardData.calendar?.[dateKey];
  const timeSummary = record?.check_in_at
    ? `${record.check_in_at}${record.check_out_at ? ` · ${record.check_out_at}` : ""}`
    : "";
  const statusLabel = status === "none" ? "No attendance" : status[0].toUpperCase() + status.slice(1);
  const title = [dateKey, statusLabel, timeSummary].filter(Boolean).join(" · ");
  const todayClass = isToday ? "ring-2 ring-indigo-500 ring-offset-1" : "";

  const cell = document.createElement("div");
  cell.className = `flex min-h-14 flex-col items-center justify-center rounded-xl border p-1 text-center transition hover:shadow-sm sm:min-h-16 sm:p-2 ${styles[status] || styles.none} ${todayClass}`;
  cell.title = title;
  cell.setAttribute("aria-label", title);

  const dayElement = document.createElement("span");
  dayElement.className = "font-semibold";
  dayElement.textContent = String(day);
  cell.append(dayElement);

  if (timeSummary) {
    const timeElement = document.createElement("span");
    timeElement.className = "mt-0.5 max-w-full truncate text-[9px] sm:text-[10px]";
    timeElement.textContent = timeSummary;
    cell.append(timeElement);
  }

  return cell;
}

function renderCalendar() {
  if (!attendanceCalendar || !calendarTitle) return;
  attendanceCalendar.replaceChildren();
  const year = currentDate.getFullYear();
  const month = currentDate.getMonth();
  const firstDay = new Date(year, month, 1).getDay();
  const daysInMonth = new Date(year, month + 1, 0).getDate();
  const todayKey = formatDateKey(parseLocalDate(window.attendanceToday));

  calendarTitle.textContent = currentDate.toLocaleString("en-US", {
    month: "long",
    year: "numeric",
  });

  for (let index = 0; index < firstDay; index++) {
    const spacer = document.createElement("div");
    spacer.className = "min-h-14 rounded-xl bg-slate-50 sm:min-h-16";
    spacer.setAttribute("aria-hidden", "true");
    attendanceCalendar.append(spacer);
  }

  for (let day = 1; day <= daysInMonth; day++) {
    const dateKey = formatDateKey(new Date(year, month, day));
    const status = dashboardData.calendar?.[dateKey]?.status || "none";
    attendanceCalendar.append(attendanceCell(status, dateKey, day, dateKey === todayKey));
  }

  const remainingCells = (7 - (attendanceCalendar.children.length % 7)) % 7;
  for (let index = 0; index < remainingCells; index++) {
    const spacer = document.createElement("div");
    spacer.className = "min-h-14 rounded-xl bg-slate-50 sm:min-h-16";
    spacer.setAttribute("aria-hidden", "true");
    attendanceCalendar.append(spacer);
  }
}

function updateSummary(summary) {
  if (!summary) return;
  const values = {
    totalPresent: summary.present,
    totalLate: summary.late,
    totalAbsent: summary.absent,
    attendanceRate: summary.rate,
  };
  Object.entries(values).forEach(([id, value]) => {
    const element = document.getElementById(id);
    if (element) element.textContent = String(value);
  });
  document.querySelectorAll("[data-period-label]").forEach((element) => {
    element.textContent = summary.period;
  });
}

function renderTrend(trend) {
  if (!monthlyTrendCanvas || !trend) return;
  const hasData = Boolean(trend.has_attendance);
  monthlyTrendCanvas.classList.toggle("hidden", !hasData);
  if (monthlyTrendEmpty) {
    monthlyTrendEmpty.classList.toggle("hidden", hasData);
    monthlyTrendEmpty.classList.toggle("flex", !hasData);
  }
  if (!hasData || typeof window.Chart === "undefined") {
    chartInstance?.destroy();
    chartInstance = null;
    return;
  }

  const datasets = [
    { label: "Present", data: trend.present, backgroundColor: "#10b981", borderRadius: 6, barThickness: 18 },
    { label: "Late", data: trend.late, backgroundColor: "#f59e0b", borderRadius: 6, barThickness: 18 },
    { label: "Absent", data: trend.absent, backgroundColor: "#ef4444", borderRadius: 6, barThickness: 18 },
  ];

  if (chartInstance) {
    chartInstance.data.labels = trend.labels;
    chartInstance.data.datasets.forEach((dataset, index) => {
      dataset.data = datasets[index].data;
    });
    chartInstance.update();
    return;
  }

  chartInstance = new window.Chart(monthlyTrendCanvas.getContext("2d"), {
    type: "bar",
    data: { labels: trend.labels, datasets },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: { legend: { display: false } },
      scales: {
        x: { grid: { display: false } },
        y: { beginAtZero: true, ticks: { stepSize: 1 } },
      },
    },
  });
}

function renderDashboard(data) {
  dashboardData = data;
  updateSummary(data.summary);
  renderCalendar();
  renderTrend(data.trend);
}

async function refreshDashboard() {
  if (!window.attendanceDataUrl) return;
  const sequence = ++requestSequence;
  const query = new URLSearchParams({
    month: formatDateKey(new Date(currentDate.getFullYear(), currentDate.getMonth(), 1)).slice(0, 7),
    anchor_date: formatDateKey(currentDate),
    period: filterPeriodSelect?.value || "This Month",
  });

  try {
    const response = await fetch(`${window.attendanceDataUrl}?${query.toString()}`, {
      headers: { Accept: "application/json" },
      credentials: "same-origin",
    });
    if (!response.ok) throw new Error("Attendance data could not be refreshed.");
    const data = await response.json();
    if (sequence === requestSequence) renderDashboard(data);
  } catch (error) {
    console.error(error);
  }
}

function changeMonth(amount) {
  const originalDay = currentDate.getDate();
  const target = new Date(currentDate.getFullYear(), currentDate.getMonth() + amount, 1);
  const lastDay = new Date(target.getFullYear(), target.getMonth() + 1, 0).getDate();
  target.setDate(Math.min(originalDay, lastDay));
  currentDate = target;
  refreshDashboard();
}

prevMonthBtn?.addEventListener("click", () => changeMonth(-1));
nextMonthBtn?.addEventListener("click", () => changeMonth(1));
filterPeriodSelect?.addEventListener("change", refreshDashboard);

function updateHistorySlider() {
  if (!historySlider || historyPageCount <= 1) return;
  historySlider.style.transform = `translateX(-${historyPage * 100}%)`;
  historyDots.forEach((dot, index) => {
    dot.classList.toggle("bg-indigo-600", index === historyPage);
    dot.classList.toggle("bg-slate-300", index !== historyPage);
  });
}

historyPrev?.addEventListener("click", () => {
  historyPage = (historyPage + historyPageCount - 1) % historyPageCount;
  updateHistorySlider();
});
historyNext?.addEventListener("click", () => {
  historyPage = (historyPage + 1) % historyPageCount;
  updateHistorySlider();
});

function openImportModal() {
  if (!importModal || !importModal.classList.contains("hidden")) return;

  const root = document.documentElement;
  const body = document.body;
  const scrollY = window.scrollY;

  importScrollState = {
    scrollY,
    rootOverflowY: root.style.overflowY,
    bodyPosition: body.style.position,
    bodyTop: body.style.top,
    bodyLeft: body.style.left,
    bodyRight: body.style.right,
    bodyWidth: body.style.width,
  };

  root.style.overflowY = "scroll";
  body.style.position = "fixed";
  body.style.top = `-${scrollY}px`;
  body.style.left = "0";
  body.style.right = "0";
  body.style.width = "auto";
  importModal?.classList.remove("hidden");
  importModal?.classList.add("flex");
}

function closeImportModal() {
  if (!importModal || importModal.classList.contains("hidden")) return;

  importModal?.classList.add("hidden");
  importModal?.classList.remove("flex");

  if (importScrollState) {
    const root = document.documentElement;
    const body = document.body;
    const { scrollY, rootOverflowY, bodyPosition, bodyTop, bodyLeft, bodyRight, bodyWidth } = importScrollState;

    body.style.position = bodyPosition;
    body.style.top = bodyTop;
    body.style.left = bodyLeft;
    body.style.right = bodyRight;
    body.style.width = bodyWidth;
    root.style.overflowY = rootOverflowY;
    importScrollState = null;
    window.scrollTo(0, scrollY);
  }
}

document.getElementById("openImportModal")?.addEventListener("click", openImportModal);
document.getElementById("closeImportModal")?.addEventListener("click", closeImportModal);
document.getElementById("cancelImportModal")?.addEventListener("click", closeImportModal);
importModal?.addEventListener("click", (event) => {
  if (event.target === importModal) closeImportModal();
});
document.addEventListener("keydown", (event) => {
  if (event.key === "Escape" && importModal && !importModal.classList.contains("hidden")) {
    closeImportModal();
  }
});

function displayCsvFile(file) {
  const isCsv = file && file.name.toLowerCase().endsWith(".csv");
  csvImportError?.classList.toggle("hidden", isCsv);
  if (!isCsv) {
    if (csvImportError) csvImportError.textContent = "Pilih file dengan ekstensi .csv.";
    if (csvImportError) csvImportError.classList.remove("hidden");
    if (csvInput) csvInput.value = "";
    csvSubmit?.setAttribute("disabled", "disabled");
    return;
  }

  if (csvFileName) {
    csvFileName.textContent = file.name;
    csvFileName.classList.remove("hidden");
  }
  if (csvFileSize) {
    csvFileSize.textContent = `${(file.size / 1024).toFixed(1)} KB`;
    csvFileSize.classList.remove("hidden");
  }
  csvSubmit?.removeAttribute("disabled");
}

csvInput?.addEventListener("change", () => displayCsvFile(csvInput.files?.[0]));
csvDropzone?.addEventListener("dragover", (event) => {
  event.preventDefault();
  csvDropzone.classList.add("border-indigo-500", "bg-indigo-50");
});
csvDropzone?.addEventListener("dragleave", () => {
  csvDropzone.classList.remove("border-indigo-500", "bg-indigo-50");
});
csvDropzone?.addEventListener("drop", (event) => {
  event.preventDefault();
  csvDropzone.classList.remove("border-indigo-500", "bg-indigo-50");
  const file = event.dataTransfer?.files?.[0];
  if (!file || !csvInput) return;
  const transfer = new DataTransfer();
  transfer.items.add(file);
  csvInput.files = transfer.files;
  displayCsvFile(file);
});

renderDashboard(dashboardData);
updateHistorySlider();
updateClock();
window.setInterval(updateClock, 1000);
