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
csvBtn?.addEventListener("click", showToast);
closeToastBtn?.addEventListener("click", hideToast);

// ==========================================
// 4. DATA SOURCE (SINGLE SOURCE OF TRUTH)
// ==========================================
const attendanceData = [
  // --- 14 JUL ---
  {
    id: 1,
    time: "09:01:14",
    date: "2026-07-14",
    rawDate: "2026-07-14",
    event: "Check In",
    location: "Head Office",
    device: "Chrome • Windows 11",
    ip: "192.168.1.12",
    verify: "Face ID",
    status: "Success",
    anomaly: false,
  },
  {
    id: 2,
    time: "08:10:00",
    date: "2026-07-14",
    rawDate: "2026-07-14",
    event: "Check In",
    location: "Branch Office",
    device: "Safari • macOS",
    ip: "10.20.30.41",
    verify: "Fingerprint",
    status: "Success",
    anomaly: false,
  },
  {
    id: 3,
    time: "12:00:05",
    date: "2026-07-14",
    rawDate: "2026-07-14",
    event: "Break",
    location: "Head Office",
    device: "Android App",
    ip: "192.168.1.12",
    verify: "QR Code",
    status: "Success",
    anomaly: false,
  },
  {
    id: 4,
    time: "17:02:48",
    date: "2026-07-14",
    rawDate: "2026-07-14",
    event: "Check Out",
    location: "Head Office",
    device: "Chrome • Windows 11",
    ip: "192.168.1.12",
    verify: "Face ID",
    status: "Success",
    anomaly: false,
  },

  // --- 13 JUL ---
  {
    id: 5,
    time: "07:55:20",
    date: "2026-07-13",
    rawDate: "2026-07-13",
    event: "Check In",
    location: "Remote",
    device: "iOS App",
    ip: "114.120.3.5",
    verify: "Face ID",
    status: "Success",
    anomaly: false,
  },
  {
    id: 6,
    time: "12:15:00",
    date: "2026-07-13",
    rawDate: "2026-07-13",
    event: "Break",
    location: "Remote",
    device: "iOS App",
    ip: "114.120.3.5",
    verify: "PIN",
    status: "Success",
    anomaly: false,
  },
  {
    id: 7,
    time: "17:10:00",
    date: "2026-07-13",
    rawDate: "2026-07-13",
    event: "Check Out",
    location: "Remote",
    device: "iOS App",
    ip: "114.120.3.5",
    verify: "Face ID",
    status: "Success",
    anomaly: false,
  },
  {
    id: 8,
    time: "18:00:00",
    date: "2026-07-13",
    rawDate: "2026-07-13",
    event: "Login",
    location: "Remote",
    device: "Chrome • Windows",
    ip: "114.120.3.5",
    verify: "PIN",
    status: "Success",
    anomaly: false,
  },

  // --- 12 JUL ---
  {
    id: 9,
    time: "08:15:33",
    date: "2026-07-12",
    rawDate: "2026-07-12",
    event: "Check In",
    location: "Head Office",
    device: "Firefox • Linux",
    ip: "192.168.0.50",
    verify: "Face ID",
    status: "Pending",
    anomaly: true,
  },
  {
    id: 10,
    time: "08:20:00",
    date: "2026-07-12",
    rawDate: "2026-07-12",
    event: "Check In",
    location: "Warehouse",
    device: "Android App",
    ip: "172.16.2.4",
    verify: "QR Code",
    status: "Success",
    anomaly: false,
  },
  {
    id: 11,
    time: "17:00:00",
    date: "2026-07-12",
    rawDate: "2026-07-12",
    event: "Check Out",
    location: "Head Office",
    device: "Firefox • Linux",
    ip: "192.168.0.50",
    verify: "Face ID",
    status: "Success",
    anomaly: false,
  },

  // --- 11 JUL ---
  {
    id: 12,
    time: "08:04:07",
    date: "2026-07-11",
    rawDate: "2026-07-11",
    event: "Check In",
    location: "Remote Office",
    device: "Microsoft Edge",
    ip: "36.88.120.8",
    verify: "PIN",
    status: "Success",
    anomaly: false,
  },
  {
    id: 13,
    time: "12:30:00",
    date: "2026-07-11",
    rawDate: "2026-07-11",
    event: "Break",
    location: "Remote Office",
    device: "Microsoft Edge",
    ip: "36.88.120.8",
    verify: "PIN",
    status: "Success",
    anomaly: false,
  },
  {
    id: 14,
    time: "17:30:00",
    date: "2026-07-11",
    rawDate: "2026-07-11",
    event: "Check Out",
    location: "Remote Office",
    device: "Microsoft Edge",
    ip: "36.88.120.8",
    verify: "PIN",
    status: "Success",
    anomaly: false,
  },
  {
    id: 15,
    time: "19:00:00",
    date: "2026-07-11",
    rawDate: "2026-07-11",
    event: "Verification",
    location: "Remote Office",
    device: "Microsoft Edge",
    ip: "36.88.120.8",
    verify: "Face ID",
    status: "Failed",
    anomaly: true,
  },

  // --- 10 JUL ---
  {
    id: 16,
    time: "07:50:11",
    date: "2026-07-10",
    rawDate: "2026-07-10",
    event: "Check In",
    location: "Branch Office",
    device: "Chrome • macOS",
    ip: "10.20.30.45",
    verify: "Fingerprint",
    status: "Success",
    anomaly: false,
  },
  {
    id: 17,
    time: "17:05:00",
    date: "2026-07-10",
    rawDate: "2026-07-10",
    event: "Check Out",
    location: "Branch Office",
    device: "Chrome • macOS",
    ip: "10.20.30.45",
    verify: "Fingerprint",
    status: "Success",
    anomaly: false,
  },

  // --- 09 JUL ---
  {
    id: 18,
    time: "08:12:41",
    date: "2026-07-09",
    rawDate: "2026-07-09",
    event: "Check In",
    location: "Warehouse",
    device: "Android App",
    ip: "172.16.2.4",
    verify: "QR Code",
    status: "Success",
    anomaly: false,
  },
  {
    id: 19,
    time: "12:00:00",
    date: "2026-07-09",
    rawDate: "2026-07-09",
    event: "Break",
    location: "Warehouse",
    device: "Android App",
    ip: "172.16.2.4",
    verify: "QR Code",
    status: "Success",
    anomaly: false,
  },
  {
    id: 20,
    time: "17:15:54",
    date: "2026-07-09",
    rawDate: "2026-07-09",
    event: "Check Out",
    location: "Warehouse",
    device: "Android App",
    ip: "172.16.2.4",
    verify: "QR Code",
    status: "Success",
    anomaly: false,
  },

  // --- 08 JUL ---
  {
    id: 21,
    time: "07:58:00",
    date: "2026-07-08",
    rawDate: "2026-07-08",
    event: "Check In",
    location: "Head Office",
    device: "Chrome • Windows 11",
    ip: "192.168.1.15",
    verify: "Face ID",
    status: "Success",
    anomaly: false,
  },
  {
    id: 22,
    time: "17:00:00",
    date: "2026-07-08",
    rawDate: "2026-07-08",
    event: "Check Out",
    location: "Head Office",
    device: "Chrome • Windows 11",
    ip: "192.168.1.15",
    verify: "Face ID",
    status: "Success",
    anomaly: false,
  },

  // --- 07 JUL ---
  {
    id: 23,
    time: "08:05:00",
    date: "2026-07-07",
    rawDate: "2026-07-07",
    event: "Check In",
    location: "Remote",
    device: "Safari • macOS",
    ip: "114.120.3.10",
    verify: "PIN",
    status: "Success",
    anomaly: false,
  },
  {
    id: 24,
    time: "12:30:00",
    date: "2026-07-07",
    rawDate: "2026-07-07",
    event: "Break",
    location: "Remote",
    device: "Safari • macOS",
    ip: "114.120.3.10",
    verify: "PIN",
    status: "Success",
    anomaly: false,
  },
  {
    id: 25,
    time: "17:35:00",
    date: "2026-07-07",
    rawDate: "2026-07-07",
    event: "Check Out",
    location: "Remote",
    device: "Safari • macOS",
    ip: "114.120.3.10",
    verify: "PIN",
    status: "Success",
    anomaly: false,
  },

  // --- 06 JUL ---
  {
    id: 26,
    time: "08:00:00",
    date: "2026-07-06",
    rawDate: "2026-07-06",
    event: "Check In",
    location: "Branch Office",
    device: "Chrome • macOS",
    ip: "10.20.30.50",
    verify: "Fingerprint",
    status: "Success",
    anomaly: false,
  },
  {
    id: 27,
    time: "12:00:00",
    date: "2026-07-06",
    rawDate: "2026-07-06",
    event: "Break",
    location: "Branch Office",
    device: "Chrome • macOS",
    ip: "10.20.30.50",
    verify: "Fingerprint",
    status: "Success",
    anomaly: false,
  },
  {
    id: 28,
    time: "17:00:00",
    date: "2026-07-06",
    rawDate: "2026-07-06",
    event: "Check Out",
    location: "Branch Office",
    device: "Chrome • macOS",
    ip: "10.20.30.50",
    verify: "Fingerprint",
    status: "Success",
    anomaly: false,
  },
  {
    id: 29,
    time: "18:30:00",
    date: "2026-07-06",
    rawDate: "2026-07-06",
    event: "Login",
    location: "Branch Office",
    device: "iOS App",
    ip: "10.20.30.50",
    verify: "Face ID",
    status: "Success",
    anomaly: false,
  },

  // --- 05 JUL ---
  {
    id: 30,
    time: "08:15:00",
    date: "2026-07-05",
    rawDate: "2026-07-05",
    event: "Check In",
    location: "Head Office",
    device: "Android App",
    ip: "192.168.1.20",
    verify: "QR Code",
    status: "Success",
    anomaly: false,
  },
  {
    id: 31,
    time: "17:10:00",
    date: "2026-07-05",
    rawDate: "2026-07-05",
    event: "Check Out",
    location: "Head Office",
    device: "Android App",
    ip: "192.168.1.20",
    verify: "QR Code",
    status: "Success",
    anomaly: false,
  },

  // --- 04 JUL ---
  {
    id: 32,
    time: "07:50:00",
    date: "2026-07-04",
    rawDate: "2026-07-04",
    event: "Check In",
    location: "Remote",
    device: "Chrome • Windows 11",
    ip: "114.120.5.5",
    verify: "Face ID",
    status: "Success",
    anomaly: false,
  },
  {
    id: 33,
    time: "12:00:00",
    date: "2026-07-04",
    rawDate: "2026-07-04",
    event: "Break",
    location: "Remote",
    device: "Chrome • Windows 11",
    ip: "114.120.5.5",
    verify: "Face ID",
    status: "Success",
    anomaly: false,
  },
  {
    id: 34,
    time: "17:05:00",
    date: "2026-07-04",
    rawDate: "2026-07-04",
    event: "Check Out",
    location: "Remote",
    device: "Chrome • Windows 11",
    ip: "114.120.5.5",
    verify: "Face ID",
    status: "Success",
    anomaly: false,
  },
  {
    id: 35,
    time: "20:00:00",
    date: "2026-07-04",
    rawDate: "2026-07-04",
    event: "Verification",
    location: "Remote",
    device: "Unknown Device",
    ip: "114.120.5.99",
    verify: "PIN",
    status: "Failed",
    anomaly: true,
  },

  // --- 03 JUL ---
  {
    id: 36,
    time: "08:00:00",
    date: "2026-07-03",
    rawDate: "2026-07-03",
    event: "Check In",
    location: "Warehouse",
    device: "Android App",
    ip: "172.16.2.10",
    verify: "QR Code",
    status: "Success",
    anomaly: false,
  },
  {
    id: 37,
    time: "17:00:00",
    date: "2026-07-03",
    rawDate: "2026-07-03",
    event: "Check Out",
    location: "Warehouse",
    device: "Android App",
    ip: "172.16.2.10",
    verify: "QR Code",
    status: "Success",
    anomaly: false,
  },

  // --- 02 JUL ---
  {
    id: 38,
    time: "08:05:00",
    date: "2026-07-02",
    rawDate: "2026-07-02",
    event: "Check In",
    location: "Head Office",
    device: "Safari • macOS",
    ip: "192.168.1.30",
    verify: "Face ID",
    status: "Success",
    anomaly: false,
  },
  {
    id: 39,
    time: "12:15:00",
    date: "2026-07-02",
    rawDate: "2026-07-02",
    event: "Break",
    location: "Head Office",
    device: "Safari • macOS",
    ip: "192.168.1.30",
    verify: "Face ID",
    status: "Success",
    anomaly: false,
  },
  {
    id: 40,
    time: "17:15:00",
    date: "2026-07-02",
    rawDate: "2026-07-02",
    event: "Check Out",
    location: "Head Office",
    device: "Safari • macOS",
    ip: "192.168.1.30",
    verify: "Face ID",
    status: "Success",
    anomaly: false,
  },

  // --- 01 JUL ---
  {
    id: 41,
    time: "08:01:00",
    date: "2026-07-01",
    rawDate: "2026-07-01",
    event: "Check In",
    location: "Branch Office",
    device: "Chrome • Windows 11",
    ip: "10.20.30.12",
    verify: "Fingerprint",
    status: "Success",
    anomaly: false,
  },
  {
    id: 42,
    time: "12:00:00",
    date: "2026-07-01",
    rawDate: "2026-07-01",
    event: "Break",
    location: "Branch Office",
    device: "Chrome • Windows 11",
    ip: "10.20.30.12",
    verify: "Fingerprint",
    status: "Success",
    anomaly: false,
  },
  {
    id: 43,
    time: "17:02:00",
    date: "2026-07-01",
    rawDate: "2026-07-01",
    event: "Check Out",
    location: "Branch Office",
    device: "Chrome • Windows 11",
    ip: "10.20.30.12",
    verify: "Fingerprint",
    status: "Success",
    anomaly: false,
  },
  {
    id: 44,
    time: "21:00:00",
    date: "2026-07-01",
    rawDate: "2026-07-01",
    event: "Login",
    location: "Branch Office",
    device: "Chrome • Windows 11",
    ip: "10.20.30.12",
    verify: "PIN",
    status: "Success",
    anomaly: false,
  },
];

let filteredData = [...attendanceData];
let currentPage = 1;
const rowsPerPage = 5;

// ==========================================
// 5. HELPER FORMATTING
// ==========================================
function getStatusBadge(status) {
  if (status === "Success")
    return `<span class="rounded-full bg-green-100 px-3 py-1 text-xs font-semibold text-green-700">Success</span>`;
  if (status === "Failed")
    return `<span class="rounded-full bg-red-100 px-3 py-1 text-xs font-semibold text-red-700">Failed</span>`;
  return `<span class="rounded-full bg-yellow-100 px-3 py-1 text-xs font-semibold text-yellow-700">Pending</span>`;
}

function getEventBadge(event) {
  const ev = event.toLowerCase();
  if (ev.includes("check in"))
    return `<span class="rounded-full bg-emerald-100 px-3 py-1 text-xs font-semibold text-emerald-700">${event}</span>`;
  if (ev.includes("check out"))
    return `<span class="rounded-full bg-rose-100 px-3 py-1 text-xs font-semibold text-rose-700">${event}</span>`;
  if (ev.includes("break"))
    return `<span class="rounded-full bg-amber-100 px-3 py-1 text-xs font-semibold text-amber-700">${event}</span>`;
  return `<span class="rounded-full bg-blue-100 px-3 py-1 text-xs font-semibold text-blue-700">${event}</span>`;
}

function formatDisplayDate(rawDate) {
  const dateObj = new Date(rawDate);
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
    (d) => d.event === "Check In" && d.time <= "09:00:00",
  ).length;
  const late = data.filter(
    (d) => d.event === "Check In" && d.time > "09:00:00",
  ).length;
  const anomalies = data.filter((d) => d.anomaly).length;

  const elTotal = document.getElementById("totalEvents");
  const elOnTime = document.getElementById("ontimeCheckins");
  const elLate = document.getElementById("lateCheckins");
  const elAnomaly = document.getElementById("anomalyFlags");

  if (elTotal) elTotal.textContent = totalEvents;
  if (elOnTime) elOnTime.textContent = onTime;
  if (elLate) elLate.textContent = late;
  if (elAnomaly) elAnomaly.textContent = anomalies;
}

// ==========================================
// 7. RENDER VERIFICATION METHOD CARD
// ==========================================
function renderVerificationMethod(data) {
  const counts = { "Face ID": 0, Fingerprint: 0, "QR Code": 0, PIN: 0 };
  let totalValid = 0;

  data.forEach((d) => {
    if (counts[d.verify] !== undefined) {
      counts[d.verify]++;
      totalValid++;
    }
  });

  const updateBar = (idPrefix, count) => {
    const percent =
      totalValid === 0 ? 0 : Math.round((count / totalValid) * 100);
    const textEl = document.getElementById(`${idPrefix}Text`);
    const barEl = document.getElementById(`${idPrefix}Bar`);
    if (textEl) textEl.textContent = `${percent}%`;
    if (barEl) barEl.style.width = `${percent}%`;
  };

  updateBar("faceId", counts["Face ID"]);
  updateBar("fingerprint", counts["Fingerprint"]);
  updateBar("qrCode", counts["QR Code"]);
  updateBar("pin", counts["PIN"]);

  const totalVerifEl = document.getElementById("totalVerifications");
  if (totalVerifEl) totalVerifEl.textContent = totalValid.toLocaleString();
}

// ==========================================
// 8. RENDER DYNAMIC ACTIVITY CHART
// ==========================================
let chartInstance = null;
const activityChart = document.getElementById("activityTrendChart");

function renderActivityChart(data) {
  const activityChart = document.getElementById("activityTrendChart");
  if (!activityChart) return;

  // ==== BAGIAN DATA ====
  // (Jika Anda menggunakan data dummy asli 14 hari, abaikan proses agregasi ini dan gunakan labels & series dari data Anda)
  const dateMap = {};
  data.forEach((item) => {
    const dateLabel = formatDisplayDate(item.rawDate).substring(0, 6);
    if (!dateMap[dateLabel]) {
      dateMap[dateLabel] = { checkIn: 0, checkOut: 0, break: 0, other: 0 };
    }
    const ev = item.event.toLowerCase();
    if (ev.includes("check in")) dateMap[dateLabel].checkIn++;
    else if (ev.includes("check out")) dateMap[dateLabel].checkOut++;
    else if (ev.includes("break")) dateMap[dateLabel].break++;
    else dateMap[dateLabel].other++;
  });

  const labels = Object.keys(dateMap).reverse();
  const series = [
    {
      label: "Check In",
      color: "#22c55e",
      values: labels.map((l) => dateMap[l].checkIn),
    },
    {
      label: "Check Out",
      color: "#6366f1",
      values: labels.map((l) => dateMap[l].checkOut),
    },
    {
      label: "Break",
      color: "#f59e0b",
      values: labels.map((l) => dateMap[l].break),
    },
    {
      label: "Other",
      color: "#a855f7",
      values: labels.map((l) => dateMap[l].other),
    },
  ];

  // ==== SETUP CANVAS ====
  activityChart.style.width = "100%";
  activityChart.style.height = "100%";
  activityChart.style.display = "block";

  const ctx = activityChart.getContext("2d");
  if (!ctx) return;

  const rect = activityChart.parentElement.getBoundingClientRect();
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

  const maxValue = Math.max(1, ...series.flatMap((s) => s.values)) + 2;
  const stepY = chartHeight / 5;

  ctx.strokeStyle = "#e2e8f0";
  ctx.lineWidth = 1;
  ctx.font = "12px Inter, sans-serif";
  ctx.fillStyle = "#64748b";

  // Gambar Garis Horizontal (Y-Axis)
  for (let i = 0; i <= 5; i++) {
    const y = padding.top + stepY * i;
    ctx.beginPath();
    ctx.moveTo(padding.left, y);
    ctx.lineTo(width - padding.right, y);
    ctx.stroke();

    const value = Math.round(maxValue - (maxValue / 5) * i);
    ctx.fillText(value, 6, y + 4);
  }

  // ==== PERHITUNGAN BATANG TIPIS (BAR CHART) ====
  if (labels.length > 0) {
    // Agar bisa memuat sampai 14 bagian dengan rapi
    const groupCount = Math.max(labels.length, 14);
    const groupWidth = chartWidth / groupCount;

    // Gunakan hanya 60% dari ruang hari untuk batang agar ada jarak antar hari
    const activeGroupWidth = groupWidth * 0.6;

    // Tentukan lebar per batang & jarak antar batang dalam hari yang sama
    const gap = 1.5;
    const barWidth =
      (activeGroupWidth - gap * (series.length - 1)) / series.length;

    series.forEach((serie, seriesIndex) => {
      const seriesHeight = chartHeight / maxValue;

      serie.values.forEach((value, index) => {
        if (value === 0) return;

        // Posisikan grup batang tepat di tengah-tengah slot hari
        const groupMargin = (groupWidth - activeGroupWidth) / 2;
        const groupX = padding.left + index * groupWidth + groupMargin;

        // Posisikan masing-masing batang secara urut
        const x = groupX + seriesIndex * (barWidth + gap);
        const barHeight = Math.max(4, value * seriesHeight);
        const y = padding.top + chartHeight - barHeight;

        ctx.fillStyle = serie.color;
        ctx.fillRect(x, y, barWidth, barHeight);
      });
    });

    // Gambar Teks Tanggal (X-Axis)
    ctx.fillStyle = "#64748b";
    ctx.textAlign = "center";
    labels.forEach((label, index) => {
      const x = padding.left + index * groupWidth + groupWidth / 2;
      ctx.fillText(label, x, height - 10);
    });
    ctx.textAlign = "left"; // Kembalikan ke default
  } else {
    ctx.fillText(
      "No data match for current filter",
      width / 2 - 70,
      height / 2,
    );
  }

  // Gambar Garis Sumbu Utama
  ctx.beginPath();
  ctx.moveTo(padding.left, padding.top + chartHeight);
  ctx.lineTo(width - padding.right, padding.top + chartHeight);
  ctx.stroke();
  ctx.beginPath();
  ctx.moveTo(padding.left, padding.top);
  ctx.lineTo(padding.left, padding.top + chartHeight);
  ctx.stroke();
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
  detailSidebarContent.innerHTML = `
    <div class="rounded-xl bg-slate-50 p-5 border border-slate-100">
      <div class="mb-5 flex items-center justify-between">
        ${getEventBadge(item.event)}
        ${getStatusBadge(item.status)}
      </div>
      <div class="grid grid-cols-2 gap-y-5">
        <div>
          <p class="text-xs text-slate-400 uppercase tracking-wider">Time</p>
          <p class="font-medium text-slate-800 mt-1">${item.time}</p>
        </div>
        <div>
          <p class="text-xs text-slate-400 uppercase tracking-wider">Date</p>
          <p class="font-medium text-slate-800 mt-1">${formatDisplayDate(item.rawDate)}</p>
        </div>
        <div class="col-span-2">
          <p class="text-xs text-slate-400 uppercase tracking-wider">Location & Device</p>
          <p class="font-medium text-slate-800 mt-1">${item.location}</p>
          <p class="text-xs text-slate-500">${item.device}</p>
        </div>
        <div class="col-span-2">
          <p class="text-xs text-slate-400 uppercase tracking-wider">IP Address</p>
          <p class="font-medium text-slate-800 mt-1">${item.ip}</p>
        </div>
        <div>
          <p class="text-xs text-slate-400 uppercase tracking-wider">Verification</p>
          <p class="font-medium text-slate-800 mt-1">${item.verify}</p>
        </div>
        <div>
          <p class="text-xs text-slate-400 uppercase tracking-wider">Anomaly Warning</p>
          <p class="font-medium mt-1 ${item.anomaly ? "text-red-500 font-bold" : "text-emerald-500"}">
            ${item.anomaly ? "Detected" : "Clear"}
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
          <p class="font-medium text-slate-800">${item.time}</p>
          <p class="text-xs text-slate-500">${formatDisplayDate(item.rawDate)}</p>
        </td>
        <td class="px-4 py-4">${getEventBadge(item.event)}</td>
        <td class="px-4 py-4">
          <p class="font-medium">${item.location}</p>
          <p class="text-xs text-slate-500">${item.device}</p>
        </td>
        <td class="px-4 py-4">${item.ip}</td>
        <td class="px-4 py-4">${item.verify}</td>
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

function applyAllFilters() {
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

    const normalizeType =
      type === "faceid" ? "face id" : type === "qrcode" ? "qr code" : type;
    const matchType = !type || item.verify.toLowerCase() === normalizeType;

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

    // Status On Time / Late (> 09:00)
    if (formattedTime > "09:00") {
      if (avgBadgeEl) {
        avgBadgeEl.textContent = "Late Avg";
        avgBadgeEl.className =
          "rounded-full bg-rose-100 px-3 py-1 text-xs font-semibold text-rose-700";
      }
    } else {
      if (avgBadgeEl) {
        avgBadgeEl.textContent = "On Time";
        avgBadgeEl.className =
          "rounded-full bg-emerald-100 px-3 py-1 text-xs font-semibold text-emerald-700";
      }
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
  const lateCheckIns = checkIns.filter((d) => d.time > "09:00:00");
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
      const dayIndex = new Date(d.rawDate).getDay();
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
      anomalyHeading.textContent = "No suspicious verification";
    if (anomalyDesc)
      anomalyDesc.textContent =
        "All records in the current view appear normal.";
    if (anomalyBadge) {
      anomalyBadge.textContent = "Clear";
      anomalyBadge.className =
        "rounded-full bg-emerald-100 px-3 py-1 text-xs font-semibold text-emerald-700";
    }
  }
}
