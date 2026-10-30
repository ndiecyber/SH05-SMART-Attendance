const reportData = window.smartReportData || { summary: {}, days: [] };

const sidebar = document.getElementById("sidebar");
const sidebarOverlay = document.getElementById("sidebarOverlay");

function hideSidebar() {
  sidebar?.classList.add("-translate-x-full");
  sidebarOverlay?.classList.add("hidden");
  document.body.classList.remove("overflow-hidden");
}

document.getElementById("openSidebar")?.addEventListener("click", () => {
  sidebar?.classList.remove("-translate-x-full");
  sidebarOverlay?.classList.remove("hidden");
  document.body.classList.add("overflow-hidden");
});
document.getElementById("closeSidebar")?.addEventListener("click", hideSidebar);
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

function updateClock() {
  const now = new Date();
  const time = document.getElementById("headerTime");
  const date = document.getElementById("headerDate");
  if (time) time.textContent = now.toLocaleTimeString("en-GB", { hour: "2-digit", minute: "2-digit", second: "2-digit" });
  if (date) date.textContent = now.toLocaleDateString("en-GB", { day: "numeric", month: "long", year: "numeric" });
}
updateClock();
window.setInterval(updateClock, 1000);

function setText(id, value) {
  const element = document.getElementById(id);
  if (element) element.textContent = value;
}

function updateSummary() {
  const summary = reportData.summary;
  setText("attendanceRate", summary.attendance_rate || "0.0%");
  setText("punctualityRate", summary.punctuality_rate || "0.0%");
  setText("totalWorkHours", summary.work_hours || "0h 0m");
  setText("overtimeHours", summary.overtime_hours || "0h 0m");
  setText("compositionAttendanceRate", summary.attendance_rate || "0.0%");
  setText("presentCompositionCount", `${summary.present || 0} days`);
  setText("lateCompositionCount", `${summary.late || 0} days`);
  setText("absentCompositionCount", `${summary.absent || 0} days`);
  setText("totalWorkingDays", `${summary.expected_workdays || 0} Days`);

  const expectedDays = Number(summary.expected_workdays || 0);
  const present = expectedDays ? (Number(summary.present || 0) / expectedDays) * 100 : 0;
  const late = expectedDays ? (Number(summary.late || 0) / expectedDays) * 100 : 0;
  const absent = expectedDays ? (Number(summary.absent || 0) / expectedDays) * 100 : 0;
  const presentCircle = document.getElementById("presentComposition");
  const lateCircle = document.getElementById("lateComposition");
  const absentCircle = document.getElementById("absentComposition");

  if (presentCircle) {
    presentCircle.setAttribute("stroke-dasharray", `${present} ${100 - present}`);
    presentCircle.setAttribute("stroke-dashoffset", "0");
  }
  if (lateCircle) {
    lateCircle.setAttribute("stroke-dasharray", `${late} ${100 - late}`);
    lateCircle.setAttribute("stroke-dashoffset", `${-present}`);
  }
  if (absentCircle) {
    absentCircle.setAttribute("stroke-dasharray", `${absent} ${100 - absent}`);
    absentCircle.setAttribute("stroke-dashoffset", `${-(present + late)}`);
  }

  setText("presentCompositionPercent", `${Math.round(present)}%`);
  setText("lateCompositionPercent", `${Math.round(late)}%`);
  setText("absentCompositionPercent", `${Math.round(absent)}%`);
}

function escapeHtml(value) {
  return String(value ?? "").replace(/[&<>"']/g, (character) => ({
    "&": "&amp;",
    "<": "&lt;",
    ">": "&gt;",
    '"': "&quot;",
    "'": "&#039;",
  })[character]);
}

function renderAttendanceTable() {
  const tableBody = document.getElementById("attendanceTableBody");
  if (!tableBody) return;

  const rows = reportData.days || [];
  if (!rows.length) {
    tableBody.innerHTML = '<tr><td colspan="8" class="px-6 py-8 text-center text-sm text-slate-500">No attendance records for this period.</td></tr>';
    return;
  }

  tableBody.innerHTML = rows.map((day) => {
    const statusLabel = day.status === "none" ? "—" : day.status[0].toUpperCase() + day.status.slice(1);
    const dailyRate = day.status === "present" || day.status === "late" ? "100%" : day.status === "absent" ? "0%" : "—";
    const statusColor = day.status === "present" ? "text-emerald-700" : day.status === "late" ? "text-amber-700" : day.status === "absent" ? "text-rose-700" : "text-slate-400";

    return `<tr class="hover:bg-slate-50">
      <td class="px-6 py-4 font-medium text-slate-800">${escapeHtml(day.date)}</td>
      <td class="px-6 py-4 text-center">${day.is_workday ? "1" : "0"}</td>
      <td class="px-6 py-4 text-center">${day.status === "present" ? "1" : "0"}</td>
      <td class="px-6 py-4 text-center">${day.status === "late" ? "1" : "0"}</td>
      <td class="px-6 py-4 text-center">${day.status === "absent" ? "1" : "0"}</td>
      <td class="px-6 py-4 text-center">${escapeHtml(day.work_duration_minutes === null ? "—" : `${(day.work_duration_minutes / 60).toFixed(2)}h`)}</td>
      <td class="px-6 py-4 text-center">${escapeHtml(day.overtime_minutes === null ? "—" : `${(day.overtime_minutes / 60).toFixed(2)}h`)}</td>
      <td class="px-6 py-4 font-semibold ${statusColor}">${statusLabel} · ${dailyRate}</td>
    </tr>`;
  }).join("");
}

function drawWorkHoursChart() {
  const canvas = document.getElementById("activityTrendChart");
  const emptyState = document.getElementById("reportChartEmpty");
  if (!canvas) return;

  const days = reportData.days || [];
  const hasAttendance = days.some((day) => day.status === "present" || day.status === "late");
  canvas.classList.toggle("hidden", !hasAttendance);
  emptyState?.classList.toggle("hidden", hasAttendance);
  emptyState?.classList.toggle("flex", !hasAttendance);
  if (!hasAttendance) return;

  const context = canvas.getContext("2d");
  if (!context) return;
  const bounds = canvas.getBoundingClientRect();
  const width = bounds.width || 600;
  const height = bounds.height || 320;
  const ratio = window.devicePixelRatio || 1;
  canvas.width = width * ratio;
  canvas.height = height * ratio;
  context.setTransform(ratio, 0, 0, ratio, 0, 0);
  context.clearRect(0, 0, width, height);

  const padding = { top: 16, right: 12, bottom: 36, left: 34 };
  const plotWidth = width - padding.left - padding.right;
  const plotHeight = height - padding.top - padding.bottom;
  const maxHours = Math.max(1, Math.ceil(Math.max(...days.map((day) => day.work_hours || 0))));
  const step = plotWidth / Math.max(days.length, 1);
  const barWidth = Math.max(2, Math.min(22, step * 0.58));

  context.font = "11px Inter, sans-serif";
  context.fillStyle = "#64748b";
  context.strokeStyle = "#e2e8f0";
  for (let mark = 0; mark <= 4; mark += 1) {
    const y = padding.top + (plotHeight * mark) / 4;
    context.beginPath();
    context.moveTo(padding.left, y);
    context.lineTo(width - padding.right, y);
    context.stroke();
    context.fillText(String(Math.round(maxHours * (1 - mark / 4))), 4, y + 4);
  }

  days.forEach((day, index) => {
    const x = padding.left + index * step + (step - barWidth) / 2;
    const overtime = Math.min(day.overtime_hours || 0, day.work_hours || 0);
    const regular = Math.max(0, (day.work_hours || 0) - overtime);
    const regularHeight = (regular / maxHours) * plotHeight;
    const overtimeHeight = (overtime / maxHours) * plotHeight;
    context.fillStyle = "#6366f1";
    context.fillRect(x, padding.top + plotHeight - regularHeight, barWidth, regularHeight);
    context.fillStyle = "#f59e0b";
    context.fillRect(x, padding.top + plotHeight - regularHeight - overtimeHeight, barWidth, overtimeHeight);

    const interval = days.length > 21 ? 5 : days.length > 12 ? 3 : 1;
    if (index % interval === 0 || index === days.length - 1) {
      context.fillStyle = "#64748b";
      context.fillText(day.label, x - 4, height - 10);
    }
  });
}

function renderPeriodSummary() {
  const container = document.getElementById("reportsList");
  if (!container) return;
  const summary = reportData.summary;
  container.innerHTML = `
    <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
      <div class="rounded-2xl bg-slate-50 p-4"><p class="text-xs text-slate-500">Period</p><p class="mt-1 font-semibold text-slate-800">${escapeHtml(reportData.period)}</p><p class="mt-1 text-xs text-slate-500">${escapeHtml(reportData.start_date)} to ${escapeHtml(reportData.end_date)}</p></div>
      <div class="rounded-2xl bg-slate-50 p-4"><p class="text-xs text-slate-500">Present / Late / Absent</p><p class="mt-1 font-semibold text-slate-800">${summary.present} / ${summary.late} / ${summary.absent}</p></div>
      <div class="rounded-2xl bg-slate-50 p-4"><p class="text-xs text-slate-500">Average Check-in</p><p class="mt-1 font-semibold text-slate-800">${escapeHtml(summary.average_check_in)}</p></div>
      <div class="rounded-2xl bg-slate-50 p-4"><p class="text-xs text-slate-500">Completed Work</p><p class="mt-1 font-semibold text-slate-800">${escapeHtml(summary.work_hours)}</p></div>
    </div>`;
}

function updateCsvExportLink() {
  const exportButton = document.getElementById("exportCsvBtn");
  exportButton?.addEventListener("click", () => {
    const params = new URLSearchParams();
    params.set("period", document.getElementById("reportPeriod")?.value || "This Month");
    const start = document.getElementById("reportStartDate")?.value;
    const end = document.getElementById("reportEndDate")?.value;
    if (start) params.set("start_date", start);
    if (end) params.set("end_date", end);
    window.location.assign(`${window.reportExportUrl}?${params.toString()}`);
  });
}

function initializePeriodFilter() {
  const form = document.getElementById("reportPeriodForm");
  const period = document.getElementById("reportPeriod");
  const dateRange = document.getElementById("customReportDates");
  period?.addEventListener("change", () => {
    const custom = period.value === "Custom Date Range";
    dateRange?.classList.toggle("hidden", !custom);
    dateRange?.classList.toggle("flex", custom);
    if (!custom) form?.requestSubmit();
  });
}

function showToast(type = "pdf", fileName = "Attendance_Report.pdf", message = "Report:") {
  const toast = document.getElementById("toast");
  const messageElement = document.getElementById("toast-message");
  const fileElement = document.getElementById("file-name");
  if (!toast) return;
  if (messageElement) messageElement.textContent = message;
  if (fileElement) fileElement.textContent = fileName;
  toast.classList.remove("hidden", "-translate-y-10", "opacity-0");
  toast.classList.add("translate-y-0", "opacity-100");
  window.setTimeout(() => toast.classList.add("hidden"), 3000);
}

document.getElementById("closeToastBtn")?.addEventListener("click", () => {
  document.getElementById("toast")?.classList.add("hidden");
});

updateSummary();
renderAttendanceTable();
drawWorkHoursChart();
renderPeriodSummary();
updateCsvExportLink();
initializePeriodFilter();
window.addEventListener("resize", drawWorkHoursChart);
