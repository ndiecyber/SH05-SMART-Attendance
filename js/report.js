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
  "Sep",
  "Oct",
  "Nov",
  "Dec",
  "Jan",
  "Feb",
  "Mar",
  "Apr",
  "May",
  "Jun",
  "Jul",
  "Aug",
];
const activitySeries = [
  {
    label: "regular Hours",
    values: [18, 19, 20, 18, 20, 21, 18, 20, 19, 18, 17, 21],
    color: "#6366f1",
  },
  {
    label: "overtime",
    values: [8, 7, 9, 8, 7, 8, 7, 8, 9, 8, 7, 8],
    color: "#f59e0b",
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

const mockData = [
  {
    id: "1",
    year: 2026,
    month: 8,
    workDays: 20,
    present: 20,
    late: 0,
    absent: 0,
    hours: 160,
    overtime: 18,
  },
  {
    id: "2",
    year: 2026,
    month: 7,
    workDays: 23,
    present: 21,
    late: 1,
    absent: 1,
    hours: 184,
    overtime: 14,
  },
  {
    id: "3",
    year: 2026,
    month: 6,
    workDays: 22,
    present: 22,
    late: 0,
    absent: 0,
    hours: 176,
    overtime: 20,
  },
  {
    id: "4",
    year: 2026,
    month: 5,
    workDays: 19,
    present: 15,
    late: 2,
    absent: 2,
    hours: 152,
    overtime: 4,
  },
  {
    id: "5",
    year: 2026,
    month: 4,
    workDays: 21,
    present: 19,
    late: 1,
    absent: 1,
    hours: 168,
    overtime: 10,
  },
  {
    id: "6",
    year: 2026,
    month: 3,
    workDays: 22,
    present: 21,
    late: 0,
    absent: 1,
    hours: 176,
    overtime: 15,
  },
  {
    id: "7",
    year: 2026,
    month: 2,
    workDays: 20,
    present: 18,
    late: 2,
    absent: 0,
    hours: 160,
    overtime: 8,
  },
  {
    id: "8",
    year: 2026,
    month: 1,
    workDays: 21,
    present: 20,
    late: 1,
    absent: 0,
    hours: 168,
    overtime: 12,
  },
  {
    id: "9",
    year: 2025,
    month: 12,
    workDays: 20,
    present: 16,
    late: 1,
    absent: 3,
    hours: 160,
    overtime: 5,
  },
  {
    id: "10",
    year: 2025,
    month: 11,
    workDays: 21,
    present: 18,
    late: 1,
    absent: 2,
    hours: 168,
    overtime: 6,
  },
  {
    id: "11",
    year: 2025,
    month: 10,
    workDays: 22,
    present: 20,
    late: 2,
    absent: 0,
    hours: 176,
    overtime: 12,
  },
  {
    id: "12",
    year: 2025,
    month: 9,
    workDays: 21,
    present: 19,
    late: 1,
    absent: 1,
    hours: 168,
    overtime: 10,
  },
];

const monthNames = [
  "Jan",
  "Feb",
  "Mar",
  "Apr",
  "May",
  "Jun",
  "Jul",
  "Aug",
  "Sep",
  "Oct",
  "Nov",
  "Dec",
];

// 2. Deteksi Bulan & Tahun Saat Ini
const now = new Date();
const currentMonth = now.getMonth() + 1; // 1 - 12
const currentYear = now.getFullYear();

// 3. Urutkan Data: Bulan Saat ini di Posisi Pertama (-1)
const sortedData = [...mockData].sort((a, b) => {
  const isACurrent = a.month === currentMonth && a.year === currentYear;
  const isBCurrent = b.month === currentMonth && b.year === currentYear;

  if (isACurrent) return -1;
  if (isBCurrent) return 1;

  // Sisanya diurutkan mundur (terbaru ke terlama)
  if (a.year !== b.year) return b.year - a.year;
  return b.month - a.month;
});

// 4. Render Data ke HTML Table Body
const tbody = document.getElementById("attendanceTableBody");

tbody.innerHTML = sortedData
  .map((row) => {
    // Hitung persentase kehadiran
    const rateNum = row.workDays > 0 ? (row.present / row.workDays) * 100 : 0;
    const rateFormatted = rateNum.toFixed(1);

    const isCurrent = row.month === currentMonth && row.year === currentYear;

    // Tentukan warna progress bar berdasarkan performa
    let barColorClass = "bg-emerald-500";
    if (rateNum < 80) {
      barColorClass = "bg-rose-500";
    } else if (rateNum < 90) {
      barColorClass = "bg-amber-500";
    }

    return `
        <tr class="transition-colors hover:bg-slate-50/80 ${isCurrent ? "bg-blue-50/40 font-semibold" : ""}">
          <!-- Month -->
          <td class="px-6 py-4 font-semibold text-slate-800">
            <div class="flex items-center gap-2">
              <span>${monthNames[row.month - 1]} ${row.year}</span>
              ${
                isCurrent
                  ? `
                <span class="rounded-full bg-blue-100 px-2 py-0.5 text-[10px] font-bold uppercase text-blue-600">
                  Current
                </span>
              `
                  : ""
              }
            </div>
          </td>

          <!-- Metrics -->
          <td class="px-6 py-4 text-center font-medium">${row.workDays}</td>
          <td class="px-6 py-4 text-center font-medium text-emerald-600">${row.present}</td>
          <td class="px-6 py-4 text-center font-medium text-amber-500">${row.late}</td>
          <td class="px-6 py-4 text-center font-medium text-rose-500">${row.absent}</td>
          <td class="px-6 py-4 text-center font-medium">${row.hours}h</td>
          <td class="px-6 py-4 text-center font-medium text-slate-500">${row.overtime}h</td>

          <!-- Progress Bar & Rate -->
          <td class="px-6 py-4">
            <div class="flex items-center gap-3">
              <span class="w-12 text-right font-semibold text-slate-700">${rateFormatted}%</span>
              <div class="h-2 w-full rounded-full bg-slate-100">
                <div
                  class="h-2 rounded-full transition-all duration-500 ${barColorClass}"
                  style="width: ${Math.min(rateNum, 100)}%"
                ></div>
              </div>
            </div>
          </td>
        </tr>
      `;
  })
  .join("");

// Data Saved Reports
const savedReports = [
  {
    id: "rpt-1",
    title: "Monthly Attendance Summary",
    description:
      "Ringkasan total kehadiran, keterlambatan, dan ketidakhadiran dalam satu bulan.",
  },
  {
    id: "rpt-2",
    title: "Overtime Report",
    description:
      "Rincian akumulasi jam lembur harian beserta persetujuan supervisor.",
  },
  {
    id: "rpt-3",
    title: "Punctuality Scorecard",
    description:
      "Skor ketepatan waktu jam masuk kerja dan rekam jejak kedisiplinan.",
  },
  {
    id: "rpt-4",
    title: "Leave Balance Statement",
    description:
      "Pernyataan sisa jatah cuti tahunan, cuti sakit, dan riwayat pengajuan.",
  },
  {
    id: "rpt-5",
    title: "Full Attendance Audit",
    description:
      "Laporan audit menyeluruh mencakup log presensi, lokasi, dan verifikasi biometrik.",
  },
];

// Function Render List
const reportsContainer = document.getElementById("reportsList");

reportsContainer.innerHTML = savedReports
  .map(
    (report) => `
      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 rounded-2xl border border-slate-200 bg-slate-50/100 p-4 transition-all duration-200 hover:border-slate-200 hover:bg-slate-50">
        <!-- KIRI: Judul & Keterangan -->
        <div class="pr-2">
          <h4 class="font-semibold text-slate-800 text-base">${report.title}</h4>
          <p class="text-sm text-slate-500 mt-0.5 leading-relaxed">${report.description}</p>
        </div>

        <!-- KANAN: Tombol Generate dengan Ikon -->
        <div class="shrink-0">
          <button 
            onclick="showToast('${report.id}', '${report.title}')"
            class="inline-flex items-center gap-2 rounded-xl bg-blue-600 px-4 py-2.5 text-sm font-medium text-white transition-all hover:bg-blue-700 active:scale-95 shadow-sm shadow-blue-200"
          >
            <!-- Lucide Icon: FileText/Download -->
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 stroke-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
            <span>Generate</span>
          </button>
        </div>
      </div>
    `,
  )
  .join("");

const toast = document.getElementById("toast");
const closeToastBtn = document.getElementById("closeToastBtn");
let toastTimer;

function showToast(id, title, message = "Meng-generate laporan:") {
  // 1. Ambil nilai title (gunakan fallback jika dipanggil tanpa parameter)
  const reportTitle = title || "Default Report";
  document.getElementById("file-name").textContent = reportTitle;

  const toastMessageEl = document.getElementById("toast-message");
  if (toastMessageEl) {
    toastMessageEl.textContent = message;
  }

  // 2. Batalkan timer penutupan sebelumnya jika ada
  clearTimeout(toastTimer);

  // 3. Hapus 'hidden' terlebih dahulu agar elemen masuk ke DOM
  toast.classList.remove("hidden");

  // 4. Gunakan requestAnimationFrame / setTimeout tipis
  // agar browser sempat me-render elemen sebelum memicu animasi transisi
  setTimeout(() => {
    toast.classList.remove("-translate-y-10", "opacity-0");
    toast.classList.add("translate-y-0", "opacity-100");
  }, 20);

  // 5. Atur penutupan otomatis setelah 3 detik
  toastTimer = setTimeout(hideToast, 3000);
}

function hideToast() {
  // 1. Stop timer otomatis agar tidak memicu hideToast dua kali
  clearTimeout(toastTimer);

  // 2. Jalankan animasi keluar (slide up & fade out)
  toast.classList.remove("translate-y-0", "opacity-100");
  toast.classList.add("-translate-y-10", "opacity-0");

  // 3. Tunggu hingga animasi CSS selesai (300ms sesuai duration-300), baru pasang class 'hidden'
  setTimeout(() => {
    toast.classList.add("hidden");
  }, 300);
}

// Event Listener tombol close (X)
if (closeToastBtn) {
  closeToastBtn.addEventListener("click", hideToast);
}

// Action Handler
function generateReport(id, title) {
  alert(`Mulai meng-generate laporan: ${title}`);
}

// 1. Data Mentah Dinamis (Simulasi Data dari API/State)
const attendanceLogs = [
  {
    month: "Jan 2026",
    workDays: 21,
    present: 20,
    hours: 168,
    overtime: 12,
    avgCheckIn: "08:45",
  },
  {
    month: "Feb 2026",
    workDays: 20,
    present: 18,
    hours: 160,
    overtime: 8,
    avgCheckIn: "08:50",
  },
  {
    month: "Mar 2026",
    workDays: 22,
    present: 21,
    hours: 176,
    overtime: 15,
    avgCheckIn: "08:40",
  },
  {
    month: "Apr 2026",
    workDays: 21,
    present: 19,
    hours: 168,
    overtime: 10,
    avgCheckIn: "08:42",
  },
  {
    month: "Mei 2026",
    workDays: 19,
    present: 15,
    hours: 152,
    overtime: 4,
    avgCheckIn: "08:55",
  },
  {
    month: "Jun 2026",
    workDays: 22,
    present: 22,
    hours: 176,
    overtime: 20,
    avgCheckIn: "08:35",
  },
  {
    month: "Jul 2026",
    workDays: 23,
    present: 21,
    hours: 184,
    overtime: 14,
    avgCheckIn: "08:41",
  },
  {
    month: "Agu 2026",
    workDays: 20,
    present: 20,
    hours: 160,
    overtime: 18,
    avgCheckIn: "08:30",
  },
];

// 2. Fungsi Kalkulasi Data Dinamis
function calculateInsights(data) {
  let totalWorkDays = 0;
  let totalPresent = 0;
  let totalOvertime = 0;
  let bestMonth = data[0];
  let bestRate = 0;

  data.forEach((item) => {
    totalWorkDays += item.workDays;
    totalPresent += item.present;
    totalOvertime += item.overtime;

    // Hitung rate bulan ini untuk mencari bulan terbaik
    const currentRate = (item.present / item.workDays) * 100;
    if (currentRate >= bestRate) {
      bestRate = currentRate;
      bestMonth = item;
    }
  });

  // Overall Rate
  const overallRate =
    totalWorkDays > 0
      ? ((totalPresent / totalWorkDays) * 100).toFixed(1)
      : "0.0";

  // Mengambil data check-in bulan terakhir sebagai acuan rata-rata
  const latestCheckIn = data[data.length - 1].avgCheckIn;

  return {
    attendanceRate: `${overallRate}%`,
    avgCheckIn: `${latestCheckIn} AM`,
    bestMonthName: bestMonth.month.split(" ")[0],
    bestMonthRate: `${bestRate.toFixed(0)}%`,
    bestMonthHours: `${bestMonth.hours} Jam`,
    totalOvertime: `${totalOvertime} Jam`,
  };
}

// 3. Render Komponen HTML
function renderInsights() {
  const stats = calculateInsights(attendanceLogs);
  const container = document.getElementById("insightsGrid");

  container.innerHTML = `
        <!-- 1. Attendance Rate -->
        <div class="flex flex-col justify-between rounded-3xl border border-slate-100 bg-white p-5 shadow-sm">
          <div>
            <div class="flex items-center justify-between">
              <span class="rounded-xl bg-blue-100 p-2.5 text-blue-600">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 002 2h2a2 2 0 002-2z" />
                </svg>
              </span>
              <span class="inline-flex items-center gap-1 rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-semibold text-emerald-600">
                <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 10l7-7 7 7M12 3v18"/></svg>
                +2.4%
              </span>
            </div>
            <p class="mt-4 text-sm font-medium text-slate-500">Attendance Rate</p>
            <h3 class="text-2xl font-bold text-slate-800">${stats.attendanceRate}</h3>
          </div>
          <div class="mt-3 border-t border-slate-100 pt-2">
            <p class="text-xs text-slate-400">Rata-rata presensi kumulatif</p>
          </div>
        </div>

        <!-- 2. Avg. Check-in -->
        <div class="flex flex-col justify-between rounded-3xl border border-slate-100 bg-white p-5 shadow-sm">
          <div>
            <div class="flex items-center justify-between">
              <span class="rounded-xl bg-emerald-100 p-2.5 text-emerald-600">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
              </span>
              <span class="inline-flex items-center gap-1 rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-semibold text-emerald-600">
                On Time
              </span>
            </div>
            <p class="mt-4 text-sm font-medium text-slate-500">Avg. Check-in Time</p>
            <h3 class="text-2xl font-bold text-slate-800">${stats.avgCheckIn}</h3>
          </div>
          <div class="mt-3 border-t border-slate-100 pt-2">
            <p class="text-xs text-slate-400">Berdasarkan log bulan terakhir</p>
          </div>
        </div>

        <!-- 3. Bulan Terbaik -->
        <div class="flex flex-col justify-between rounded-3xl border border-slate-100 bg-white p-5 shadow-sm">
          <div>
            <div class="flex items-center justify-between">
              <span class="rounded-xl bg-amber-100 p-2.5 text-amber-600">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z" />
                </svg>
              </span>
              <span class="inline-flex items-center gap-1 rounded-full bg-amber-50 px-2.5 py-1 text-xs font-semibold text-amber-600">
                Top Performance
              </span>
            </div>
            <p class="mt-4 text-sm font-medium text-slate-500">Best Performing Month</p>
            <div class="flex items-baseline gap-2">
              <h3 class="text-2xl font-bold text-slate-800">${stats.bestMonthName}</h3>
              <span class="text-xs font-semibold text-emerald-600">(${stats.bestMonthRate} Rate)</span>
            </div>
          </div>
          <div class="mt-3 border-t border-slate-100 pt-2 flex justify-between items-center text-xs">
            <span class="text-slate-400">Total Jam Kerja:</span>
            <span class="font-bold text-slate-700">${stats.bestMonthHours}</span>
          </div>
        </div>

        <!-- 4. Overtime Hours -->
        <div class="flex flex-col justify-between rounded-3xl border border-slate-100 bg-white p-5 shadow-sm">
          <div>
            <div class="flex items-center justify-between">
              <span class="rounded-xl bg-indigo-100 p-2.5 text-indigo-600">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                </svg>
              </span>
              <span class="inline-flex items-center gap-1 rounded-full bg-indigo-50 px-2.5 py-1 text-xs font-semibold text-indigo-600">
                Akumulasi
              </span>
            </div>
            <p class="mt-4 text-sm font-medium text-slate-500">Total Overtime Hours</p>
            <h3 class="text-2xl font-bold text-slate-800">${stats.totalOvertime}</h3>
          </div>
          <div class="mt-3 border-t border-slate-100 pt-2">
            <p class="text-xs text-slate-400">Total jam lembur tercatat</p>
          </div>
        </div>
      `;
}

// Jalankan render saat halaman siap
renderInsights();
