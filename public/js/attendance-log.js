// ==========================================
// 1. SIDEBAR & MOBILE MENU
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
// 2. CLOCK & HEADER TIME
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
// 3. TOAST NOTIFICATION (EXPORT)
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
closeToastBtn?.addEventListener("click", hideToast);

// ==========================================
// 4. DATA SOURCE
const attendanceData = Array.isArray(window.smartAttendanceLogData)
  ? window.smartAttendanceLogData
  : [];

let filteredData = [...attendanceData];
let currentPage = 1;
const rowsPerPage = 5;

// ==========================================
// 5. HELPER FORMATTING
// ==========================================
function escapeHtml(value) {
  return String(value ?? "").replace(/[&<>"']/g, (character) => ({
    "&": "&amp;",
    "<": "&lt;",
    ">": "&gt;",
    '"': "&quot;",
    "'": "&#039;",
  })[character]);
}

function getStatusBadge(status) {
  const normalizedStatus = String(status ?? "").toLowerCase();
  const label = normalizedStatus === "present" ? "Present" : escapeHtml(status || "Unknown");

  if (["success", "present"].includes(normalizedStatus))
    return `<span class="rounded-full bg-green-100 px-3 py-1 text-xs font-semibold text-green-700">${label}</span>`;
  if (["failed", "absent"].includes(normalizedStatus))
    return `<span class="rounded-full bg-red-100 px-3 py-1 text-xs font-semibold text-red-700">${label}</span>`;
  if (normalizedStatus === "late")
    return `<span class="rounded-full bg-yellow-100 px-3 py-1 text-xs font-semibold text-yellow-700">${label}</span>`;
  return `<span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-700">${label}</span>`;
}

function getEventBadge(event) {
  const ev = event.toLowerCase();
  if (ev.includes("check in"))
    return `<span class="rounded-full bg-emerald-100 px-3 py-1 text-xs font-semibold text-emerald-700">${escapeHtml(event)}</span>`;
  if (ev.includes("check out"))
    return `<span class="rounded-full bg-rose-100 px-3 py-1 text-xs font-semibold text-rose-700">${escapeHtml(event)}</span>`;
  if (ev.includes("break"))
    return `<span class="rounded-full bg-amber-100 px-3 py-1 text-xs font-semibold text-amber-700">${escapeHtml(event)}</span>`;
  return `<span class="rounded-full bg-blue-100 px-3 py-1 text-xs font-semibold text-blue-700">${escapeHtml(event)}</span>`;
}

function formatDisplayDate(rawDate) {
  const [year, month, day] = rawDate.split("-").map(Number);
  const dateObj = new Date(year, month - 1, day);
  return dateObj.toLocaleDateString("en-GB", {
    day: "numeric",
    month: "short",
    year: "numeric",
  });
}

// ==========================================
// 6. RENDER 4 TOP CARDS
// ==========================================
function renderTopCards(data) {
  const totalEvents = data.length;
  const onTime = data.filter(
    (d) => d.event === "Check In" && String(d.status).toLowerCase() !== "late",
  ).length;
  const late = data.filter(
    (d) => d.event === "Check In" && String(d.status).toLowerCase() === "late",
  ).length;
  const tracksAnomalies = data.some((d) => d.anomaly !== null && d.anomaly !== undefined);
  const anomalies = data.filter((d) => d.anomaly === true).length;

  const elTotal = document.getElementById("totalEvents");
  const elOnTime = document.getElementById("ontimeCheckins");
  const elLate = document.getElementById("lateCheckins");
  const elAnomaly = document.getElementById("anomalyFlags");

  if (elTotal) elTotal.textContent = totalEvents;
  if (elOnTime) elOnTime.textContent = onTime;
  if (elLate) elLate.textContent = late;
  if (elAnomaly) elAnomaly.textContent = tracksAnomalies ? anomalies : "Not tracked";
}

// ==========================================
// 7. RENDER VERIFICATION METHOD CARD
// ==========================================
function renderVerificationMethod(data) {
  const methods = ["GPS", "Network", "Photo", "Other"];
  const counts = Object.fromEntries(methods.map((method) => [method, 0]));

  data.forEach((d) => {
    (d.verify || []).forEach((method) => {
      if (counts[method] !== undefined) counts[method]++;
    });
  });

  const updateBar = (idPrefix, count) => {
    const percent =
      data.length === 0 ? 0 : Math.round((count / data.length) * 100);
    const textEl = document.getElementById(`${idPrefix}Text`);
    const barEl = document.getElementById(`${idPrefix}Bar`);
    if (textEl) textEl.textContent = `${percent}%`;
    if (barEl) barEl.style.width = `${percent}%`;
  };

  updateBar("faceId", counts.GPS);
  updateBar("fingerprint", counts.Network);
  updateBar("qrCode", counts.Photo);
  updateBar("pin", counts.Other);

  const totalVerifEl = document.getElementById("totalVerifications");
  if (totalVerifEl) totalVerifEl.textContent = data.length.toLocaleString();
}

// ==========================================
// 8. RENDER DYNAMIC ACTIVITY CHART
// ==========================================
let chartInstance = null;
const activityChart = document.getElementById("activityTrendChart");

function renderLast14Days() {
  const list = document.getElementById("last14DaysList");
  if (!list) return;

  list.replaceChildren();
  [...(window.last14AttendanceData || [])].reverse().forEach((day) => {
    const card = document.createElement("div");
    card.className = "rounded-xl border border-slate-200 bg-slate-50 px-3 py-2";

    const heading = document.createElement("p");
    heading.className = "text-xs font-semibold text-slate-700";
    heading.textContent = `${day.relative_label} · ${day.label}`;

    const detail = document.createElement("p");
    detail.className = "mt-1 text-xs text-slate-500";
    const hasCheckIn = Boolean(day.check_in_at);
    const hasCheckOut = Boolean(day.check_out_at);
    detail.textContent = hasCheckIn || hasCheckOut
      ? `In ${day.check_in_at || "—"} · Out ${day.check_out_at || "—"}`
      : "No attendance";

    card.append(heading, detail);
    list.append(card);
  });
}

function renderActivityChart() {
  const canvas = document.getElementById("activityTrendChart");
  if (!canvas) return;

  const days = Array.isArray(window.last14AttendanceData)
    ? window.last14AttendanceData
    : [];
  const series = [
    { label: "Check In", color: "#22c55e", values: days.map((day) => Number(Boolean(day.check_in_at))) },
    { label: "Check Out", color: "#6366f1", values: days.map((day) => Number(Boolean(day.check_out_at))) },
  ];

  canvas.style.width = "100%";
  canvas.style.height = "100%";
  canvas.style.display = "block";
  const ctx = canvas.getContext("2d");
  if (!ctx) return;

  const rect = canvas.parentElement.getBoundingClientRect();
  const width = rect.width || canvas.clientWidth || 600;
  const height = rect.height || canvas.clientHeight || 320;
  const dpr = window.devicePixelRatio || 1;
  canvas.width = width * dpr;
  canvas.height = height * dpr;
  ctx.setTransform(dpr, 0, 0, dpr, 0, 0);
  ctx.clearRect(0, 0, width, height);

  const padding = { top: 20, right: 14, bottom: 40, left: 30 };
  const chartWidth = width - padding.left - padding.right;
  const chartHeight = height - padding.top - padding.bottom;
  const maxValue = 1;
  const stepY = chartHeight / 4;

  ctx.strokeStyle = "#e2e8f0";
  ctx.lineWidth = 1;
  ctx.font = "12px Inter, sans-serif";
  ctx.fillStyle = "#64748b";
  for (let index = 0; index <= 4; index++) {
    const y = padding.top + stepY * index;
    ctx.beginPath();
    ctx.moveTo(padding.left, y);
    ctx.lineTo(width - padding.right, y);
    ctx.stroke();
    ctx.fillText(String(1 - Math.round(index / 4)), 8, y + 4);
  }

  if (days.length > 0) {
    const groupWidth = chartWidth / days.length;
    const barWidth = Math.min(12, groupWidth * 0.28);
    series.forEach((item, seriesIndex) => {
      item.values.forEach((value, dayIndex) => {
        if (value === 0) return;
        const x = padding.left + dayIndex * groupWidth + groupWidth / 2
          + (seriesIndex === 0 ? -barWidth : 0);
        const barHeight = Math.max(4, (value / maxValue) * chartHeight);
        ctx.fillStyle = item.color;
        ctx.fillRect(x, padding.top + chartHeight - barHeight, barWidth - 2, barHeight);
      });
    });

    ctx.fillStyle = "#64748b";
    ctx.font = "10px Inter, sans-serif";
    ctx.textAlign = "center";
    days.forEach((day, index) => {
      const label = String(day.label || day.date).split(", ").pop();
      const x = padding.left + index * groupWidth + groupWidth / 2;
      ctx.fillText(label, x, height - 10);
    });
    ctx.textAlign = "left";
  } else {
    ctx.fillText("No attendance data", width / 2 - 48, height / 2);
  }

  ctx.beginPath();
  ctx.moveTo(padding.left, padding.top + chartHeight);
  ctx.lineTo(width - padding.right, padding.top + chartHeight);
  ctx.stroke();
  renderLast14Days();
}
// Handle resize canvas properly
window.addEventListener("resize", () => {
  renderActivityChart(filteredData);
});

// ==========================================
// 9. DETAIL SIDEBAR (MUNCUL DARI SISI KANAN)
// ==========================================
const detailSidebar = document.getElementById("detailSidebar");
const closeDetailSidebar = document.getElementById("closeDetailSidebar");
const detailSidebarContent = document.getElementById("detailSidebarContent");

function showDetail(item) {
  if (!detailSidebar) return;
  const verification = Array.isArray(item.verify) && item.verify.length
    ? item.verify.join(", ")
    : "Not recorded";
  const anomalyText = item.anomaly === null || item.anomaly === undefined
    ? "Not recorded"
    : item.anomaly ? "Detected" : "Clear";
  const anomalyClass = item.anomaly === true
    ? "text-red-500 font-bold"
    : item.anomaly === false ? "text-emerald-500" : "text-slate-500";
  detailSidebarContent.innerHTML = `
    <div class="rounded-xl bg-slate-50 p-5 border border-slate-100">
      <div class="mb-5 flex items-center justify-between">
        ${getEventBadge(item.event)}
        ${getStatusBadge(item.status)}
      </div>
      <div class="grid grid-cols-2 gap-y-5">
        <div>
          <p class="text-xs text-slate-400 uppercase tracking-wider">Time</p>
          <p class="font-medium text-slate-800 mt-1">${escapeHtml(item.time)}</p>
        </div>
        <div>
          <p class="text-xs text-slate-400 uppercase tracking-wider">Date</p>
          <p class="font-medium text-slate-800 mt-1">${formatDisplayDate(item.rawDate)}</p>
        </div>
        <div class="col-span-2">
          <p class="text-xs text-slate-400 uppercase tracking-wider">Location & Device</p>
          <p class="font-medium text-slate-800 mt-1">${escapeHtml(item.location)}</p>
          <p class="text-xs text-slate-500">${escapeHtml(item.device)}</p>
        </div>
        <div class="col-span-2">
          <p class="text-xs text-slate-400 uppercase tracking-wider">IP Address</p>
          <p class="font-medium text-slate-800 mt-1">${escapeHtml(item.ip)}</p>
        </div>
        <div>
          <p class="text-xs text-slate-400 uppercase tracking-wider">Verification</p>
          <p class="font-medium text-slate-800 mt-1">${escapeHtml(verification)}</p>
        </div>
        <div>
          <p class="text-xs text-slate-400 uppercase tracking-wider">Anomaly Warning</p>
          <p class="font-medium mt-1 ${anomalyClass}">
            ${anomalyText}
          </p>
        </div>
      </div>
    </div>
  `;

  // Hapus class translate-x-full agar sidebar meluncur masuk dari kanan
  detailSidebar.classList.remove("translate-x-full");
}

closeDetailSidebar?.addEventListener("click", () => {
  // Tambahkan kembali class translate-x-full agar sidebar tersembunyi ke kanan
  detailSidebar?.classList.add("translate-x-full");
});

// ==========================================
// 10. HISTORY TABLE & PAGINATION
// ==========================================
const tableBody = document.getElementById("tableBody");
const prevBtn = document.getElementById("prevPage");
const nextBtn = document.getElementById("nextPage");
const pageIndicator = document.getElementById("pageIndicator");
const startItem = document.getElementById("startItem");
const endItem = document.getElementById("endItem");
const totalItems = document.getElementById("totalItems");
const attendanceEventCount = document.getElementById("attendanceEventCount");

function renderTable() {
  if (!tableBody) return;
  tableBody.innerHTML = "";

  const totalPages = Math.max(1, Math.ceil(filteredData.length / rowsPerPage));
  const startIndex = (currentPage - 1) * rowsPerPage;
  const paginatedData = filteredData.slice(
    startIndex,
    startIndex + rowsPerPage,
  );

  if (paginatedData.length === 0) {
    tableBody.innerHTML = `<tr><td colspan="7" class="px-4 py-8 text-center text-slate-500">No records found matching the filter.</td></tr>`;
  } else {
    paginatedData.forEach((item) => {
      const tr = document.createElement("tr");
      tr.className = "hover:bg-slate-50 cursor-pointer transition-colors";
      tr.innerHTML = `
        <td class="px-4 py-4">
          <p class="font-medium text-slate-800">${escapeHtml(item.time)}</p>
          <p class="text-xs text-slate-500">${formatDisplayDate(item.rawDate)}</p>
        </td>
        <td class="px-4 py-4">${getEventBadge(item.event)}</td>
        <td class="px-4 py-4">
          <p class="font-medium">${escapeHtml(item.location)}</p>
          <p class="text-xs text-slate-500">${escapeHtml(item.device)}</p>
        </td>
        <td class="px-4 py-4">${escapeHtml(item.ip)}</td>
        <td class="px-4 py-4">${escapeHtml(Array.isArray(item.verify) ? item.verify.join(", ") : "Not recorded")}</td>
        <td class="px-4 py-4">${getStatusBadge(item.status)}</td>
        <td class="px-4 py-4 text-center">
          <button class="rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs font-medium text-slate-700 shadow-sm hover:bg-slate-50 hover:text-indigo-600 transition">Detail</button>
        </td>
      `;

      // Klik pada baris akan membuka detail sidebar
      tr.addEventListener("click", () => showDetail(item));
      tableBody.appendChild(tr);
    });
  }

  // Update info paginasi
  const start = filteredData.length ? startIndex + 1 : 0;
  const end = Math.min(currentPage * rowsPerPage, filteredData.length);

  if (startItem) startItem.textContent = start;
  if (endItem) endItem.textContent = end;
  if (totalItems) totalItems.textContent = filteredData.length;
  if (attendanceEventCount) attendanceEventCount.textContent = `${attendanceData.length} Events`;
  if (pageIndicator)
    pageIndicator.textContent = `Page ${currentPage} of ${totalPages}`;

  if (prevBtn) prevBtn.disabled = currentPage === 1;
  if (nextBtn) nextBtn.disabled = currentPage === totalPages;
}

prevBtn?.addEventListener("click", () => {
  if (currentPage > 1) {
    currentPage--;
    renderTable();
  }
});

nextBtn?.addEventListener("click", () => {
  const totalPages = Math.max(1, Math.ceil(filteredData.length / rowsPerPage));
  if (currentPage < totalPages) {
    currentPage++;
    renderTable();
  }
});

// ==========================================
// 11. FILTER LOGIC & TRIGGER UPDATE
// ==========================================
const searchPermission = document.getElementById("searchPermission");
const filterEvent = document.getElementById("filterEvent");
const filterType = document.getElementById("filterType");
const filterStatus = document.getElementById("filterStatus");
const filterLocation = document.getElementById("filterLocation");
const startDate = document.getElementById("startDate");
const endDate = document.getElementById("endDate");
const resetFilter = document.getElementById("resetFilter");

function syncLocationFilterOptions() {
  if (!filterLocation) return;

  const selectedLocation = filterLocation.value;
  const locations = [...new Set(attendanceData.map((item) => item.location))].sort();
  filterLocation.replaceChildren(new Option("All Locations", ""));

  locations.forEach((location) => {
    const option = new Option(location, location);
    filterLocation.append(option);
  });

  if (locations.includes(selectedLocation)) filterLocation.value = selectedLocation;
}

function applyAllFilters() {
  syncLocationFilterOptions();
  const query = searchPermission?.value.toLowerCase().trim() || "";
  const ev = filterEvent?.value.toLowerCase() || "";
  const type = filterType?.value.toLowerCase() || "";
  const status = filterStatus?.value.toLowerCase() || "";
  const loc = filterLocation?.value.toLowerCase() || "";
  const start = startDate?.value || "";
  const end = endDate?.value || "";

  filteredData = attendanceData.filter((item) => {
    // Search gabungan IP, Device, dan Lokasi
    const searchString =
      `${item.ip} ${item.device} ${item.location} ${item.event}`.toLowerCase();
    const matchSearch = !query || searchString.includes(query);

    // Normalisasi Filter Value sesuai Data
    const normalizeEv =
      ev === "checkin" ? "check in" : ev === "checkout" ? "check out" : ev;
    const matchEvent = !ev || item.event.toLowerCase().includes(normalizeEv);

    const methods = Array.isArray(item.verify) ? item.verify : [];
    const matchType = !type || (type === "other"
      ? methods.length === 0
      : methods.some((method) => method.toLowerCase() === type));

    const matchStatus = !status || item.status.toLowerCase() === status;
    const matchLoc = !loc || item.location.toLowerCase().includes(loc);

    const matchFrom = !start || item.rawDate >= start;
    const matchTo = !end || item.rawDate <= end;

    return (
      matchSearch &&
      matchEvent &&
      matchType &&
      matchStatus &&
      matchLoc &&
      matchFrom &&
      matchTo
    );
  });

  // Reset ke halaman 1 setiap kali filter diubah
  currentPage = 1;

  // Render Ulang Seluruh Komponen Terhubung
  renderTopCards(filteredData);
  renderVerificationMethod(filteredData);
  renderActivityChart(filteredData);
  renderInsights(filteredData);
  renderTable();
}

// Bind event listener ke semua input filter
[
  searchPermission,
  filterEvent,
  filterType,
  filterStatus,
  filterLocation,
  startDate,
  endDate,
].forEach((el) => {
  el?.addEventListener("input", applyAllFilters);
  el?.addEventListener("change", applyAllFilters);
});

resetFilter?.addEventListener("click", () => {
  if (searchPermission) searchPermission.value = "";
  if (filterEvent) filterEvent.value = "";
  if (filterType) filterType.value = "";
  if (filterStatus) filterStatus.value = "";
  if (filterLocation) filterLocation.value = "";
  if (startDate) startDate.value = "";
  if (endDate) endDate.value = "";
  applyAllFilters();
});

csvBtn?.addEventListener("click", () => {
  const params = new URLSearchParams();
  const filters = {
    search: searchPermission?.value,
    event: filterEvent?.value,
    type: filterType?.value,
    status: filterStatus?.value,
    location: filterLocation?.value,
    start_date: startDate?.value,
    end_date: endDate?.value,
  };

  Object.entries(filters).forEach(([key, value]) => {
    if (value) params.set(key, value);
  });

  window.location.assign(`${window.attendanceExportUrl}?${params.toString()}`);
});

// ==========================================
// 12. INITIALIZATION PADA SAAT LOAD
// ==========================================
// Panggil filter pertama kali untuk merender semua komponen dengan data default
applyAllFilters();

// ==========================================
// RENDER INSIGHTS (ATTENDANCE PATTERN SUMMARY)
// ==========================================
function renderInsights(data) {
  // 1. Calculate Average Check-In Time
  const checkIns = data.filter((d) =>
    d.event.toLowerCase().includes("check in"),
  );
  const avgTimeEl = document.getElementById("avgCheckInTime");
  const avgBadgeEl = document.getElementById("avgCheckInBadge");

  if (checkIns.length > 0 && avgTimeEl) {
    let totalSeconds = 0;
    checkIns.forEach((d) => {
      const [h, m, s] = d.time.split(":").map(Number);
      totalSeconds += h * 3600 + m * 60 + s;
    });

    const avgSeconds = Math.floor(totalSeconds / checkIns.length);
    const avgH = String(Math.floor(avgSeconds / 3600)).padStart(2, "0");
    const avgM = String(Math.floor((avgSeconds % 3600) / 60)).padStart(2, "0");
    const formattedTime = `${avgH}:${avgM}`;

    avgTimeEl.textContent = `${formattedTime} AM`;

    const lateCount = checkIns.filter(
      (checkIn) => checkIn.status?.toLowerCase() === "late",
    ).length;
    if (avgBadgeEl) {
      avgBadgeEl.textContent = `${lateCount} late / ${checkIns.length} check-ins`;
      avgBadgeEl.className = lateCount > 0
        ? "rounded-full bg-amber-100 px-3 py-1 text-xs font-semibold text-amber-700"
        : "rounded-full bg-emerald-100 px-3 py-1 text-xs font-semibold text-emerald-700";
    }
  } else if (avgTimeEl) {
    avgTimeEl.textContent = "--:--";
    if (avgBadgeEl) {
      avgBadgeEl.textContent = "No Data";
      avgBadgeEl.className =
        "rounded-full bg-slate-200 px-3 py-1 text-xs font-semibold text-slate-600";
    }
  }

  // 2. Most Frequent Late Day
  const lateCheckIns = checkIns.filter(
    (checkIn) => checkIn.status?.toLowerCase() === "late",
  );
  const lateDayEl = document.getElementById("mostLateDay");
  const lateDescEl = document.getElementById("mostLateDayDesc");
  const lateBadgeEl = document.getElementById("mostLateDayBadge");

  if (lateCheckIns.length > 0 && lateDayEl) {
    const dayCounts = {};
    const daysName = [
      "Sunday",
      "Monday",
      "Tuesday",
      "Wednesday",
      "Thursday",
      "Friday",
      "Saturday",
    ];

    lateCheckIns.forEach((d) => {
      const [year, month, day] = d.rawDate.split("-").map(Number);
      const dayIndex = new Date(year, month - 1, day).getDay();
      const dayName = daysName[dayIndex];
      dayCounts[dayName] = (dayCounts[dayName] || 0) + 1;
    });

    let maxDay = "—";
    let maxCount = 0;
    Object.entries(dayCounts).forEach(([day, count]) => {
      if (count > maxCount) {
        maxCount = count;
        maxDay = day;
      }
    });

    lateDayEl.textContent = maxDay;
    if (lateDescEl)
      lateDescEl.textContent = `${maxCount} late check-in(s) recorded on ${maxDay}s.`;
    if (lateBadgeEl) {
      lateBadgeEl.textContent = "Pattern Detected";
      lateBadgeEl.className =
        "rounded-full bg-amber-100 px-3 py-1 text-xs font-semibold text-amber-700";
    }
  } else if (lateDayEl) {
    lateDayEl.textContent = "—";
    if (lateDescEl)
      lateDescEl.textContent =
        "No late arrivals detected in current filtered data.";
    if (lateBadgeEl) {
      lateBadgeEl.textContent = "No Pattern";
      lateBadgeEl.className =
        "rounded-full bg-slate-200 px-3 py-1 text-xs font-semibold text-slate-600";
    }
  }

  // 3. Anomaly Warning
  const anomalies = data.filter((d) => d.anomaly);
  const cardContainer = document.getElementById("anomalyCardContainer");
  const anomalyHeading = document.getElementById("anomalyCardHeading");
  const anomalyDesc = document.getElementById("anomalyCardDesc");
  const anomalyBadge = document.getElementById("anomalyCardBadge");

  if (anomalies.length > 0) {
    if (cardContainer)
      cardContainer.className =
        "h-full rounded-2xl border border-amber-200 bg-amber-50 p-5";
    if (anomalyHeading)
      anomalyHeading.textContent = `${anomalies.length} suspicious verification detected`;
    if (anomalyDesc)
      anomalyDesc.textContent =
        "Multiple flags or failed attempts found in the selected filter range.";
    if (anomalyBadge) {
      anomalyBadge.textContent = "Warning";
      anomalyBadge.className =
        "rounded-full bg-amber-200 px-3 py-1 text-xs font-semibold text-amber-800";
    }
  } else {
    if (cardContainer)
      cardContainer.className =
        "h-full rounded-2xl border border-slate-200 bg-slate-50 p-5";
    if (anomalyHeading)
      anomalyHeading.textContent = "No anomaly data recorded";
    if (anomalyDesc)
      anomalyDesc.textContent =
        "Attendance records do not store anomaly results.";
    if (anomalyBadge) {
      anomalyBadge.textContent = "Not tracked";
      anomalyBadge.className =
        "rounded-full bg-slate-200 px-3 py-1 text-xs font-semibold text-slate-600";
    }
  }
}
