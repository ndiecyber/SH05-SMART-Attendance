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
const monthlyTrendCanvas = document.getElementById("monthlyTrendChart");
const attendanceCalendar = document.getElementById("attendanceCalendar");
const calendarTitle = document.getElementById("calendarTitle");

const prevMonthBtn = document.getElementById("prevMonth");
const nextMonthBtn = document.getElementById("nextMonth");

const historySlider = document.getElementById("historySlider");

const dots = [document.getElementById("dot0"), document.getElementById("dot1")];

let historyPage = 0;

let currentDate = new Date();

let checkInTimestamp = null;
let checkOutTimestamp = null;

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

// Dummy attendance
// nanti tinggal ganti dari API
const attendanceData = {
  "2026-07-01": "present",
  "2026-07-02": "late",
  "2026-07-03": "present",
  "2026-07-04": "absent",
  "2026-07-05": "present",
  "2026-07-08": "late",
  "2026-07-10": "present",
  "2026-07-12": "present",
  "2026-07-15": "present",
  "2026-07-18": "absent",
  "2026-07-20": "present",
  "2026-07-24": "late",
  "2026-08-28": "late",
};

const colors = {
  present: "bg-emerald-100 border-emerald-300",
  late: "bg-amber-100 border-amber-300",
  absent: "bg-red-100 border-red-300",
  none: "bg-white border-slate-200",
};

function renderCalendar(date) {
  attendanceCalendar.innerHTML = "";

  const year = date.getFullYear();
  const month = date.getMonth();

  calendarTitle.textContent = date.toLocaleString("en-US", {
    month: "long",
    year: "numeric",
  });

  const firstDay = new Date(year, month, 1).getDay();

  const totalDays = new Date(year, month + 1, 0).getDate();

  const previousMonthDays = new Date(year, month, 0).getDate();

  // Previous month
  for (let i = firstDay; i > 0; i--) {
    attendanceCalendar.innerHTML += `
            <div class="rounded-xl bg-slate-50 p-3 text-center text-slate-300">
                ${previousMonthDays - i + 1}
            </div>
        `;
  }

  // Current month

  for (let day = 1; day <= totalDays; day++) {
    const key = `${year}-${String(month + 1).padStart(2, "0")}-${String(day).padStart(2, "0")}`;

    const status = attendanceData[key] || "none";

    const today = new Date();

    const isToday =
      today.getDate() === day &&
      today.getMonth() === month &&
      today.getFullYear() === year;

    let dot = "";

    attendanceCalendar.innerHTML += `
            <div
                class="
                    rounded-xl
                    border
                    ${colors[status]}
                    p-3
                    text-center
                    transition
                    hover:scale-105
                    hover:shadow-md
                    ${isToday ? "ring-2 ring-indigo-500" : ""}
                "
            >

                <p class="font-semibold text-slate-700">
                    ${day}
                </p>
            </div>
        `;
  }

  // Next month

  const cells = attendanceCalendar.children.length;

  const remain = 42 - cells;

  for (let i = 1; i <= remain; i++) {
    attendanceCalendar.innerHTML += `
            <div class="rounded-xl bg-slate-50 p-3 text-center text-slate-300">
                ${i}
            </div>
        `;
  }
}

prevMonthBtn.onclick = () => {
  currentDate.setMonth(currentDate.getMonth() - 1);

  renderCalendar(currentDate);
};

nextMonthBtn.onclick = () => {
  currentDate.setMonth(currentDate.getMonth() + 1);

  renderCalendar(currentDate);
};

function updateHistorySlider() {
  historySlider.style.transform = `translateX(-${historyPage * 100}%)`;

  dots.forEach((dot, index) => {
    dot.classList.toggle("bg-indigo-600", index === historyPage);
    dot.classList.toggle("bg-slate-300", index !== historyPage);
  });
}

document.getElementById("historyPrev").addEventListener("click", () => {
  historyPage = historyPage === 0 ? 1 : 0;
  updateHistorySlider();
});

document.getElementById("historyNext").addEventListener("click", () => {
  historyPage = historyPage === 1 ? 0 : 1;
  updateHistorySlider();
});

updateHistorySlider();

renderCalendar(currentDate);

updateClock();

function renderMonthlyTrend() {
  if (!monthlyTrendCanvas || typeof window.Chart === "undefined") {
    return;
  }

  const ctx = monthlyTrendCanvas.getContext("2d");

  if (!ctx) {
    return;
  }

  if (window.monthlyTrendChartInstance) {
    window.monthlyTrendChartInstance.destroy();
  }

  window.monthlyTrendChartInstance = new Chart(ctx, {
    type: "bar",
    data: {
      labels: ["Week 1", "Week 2", "Week 3", "Week 4", "Week 5"],
      datasets: [
        {
          label: "Present",
          data: [5, 5, 4, 5, 2],
          backgroundColor: "#10b981",
          borderRadius: 6,
          barThickness: 18,
        },
        {
          label: "Late",
          data: [0, 1, 1, 0, 1],
          backgroundColor: "#f59e0b",
          borderRadius: 6,
          barThickness: 18,
        },
        {
          label: "Absent",
          data: [0, 0, 1, 0, 0],
          backgroundColor: "#ef4444",
          borderRadius: 6,
          barThickness: 18,
        },
      ],
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: {
          display: false, // Sembunyikan legend bawaan
        },
      },
      scales: {
        x: {
          stacked: false, // Batang sejajar
          grid: {
            display: false,
          },
        },
        y: {
          stacked: false,
          beginAtZero: true,
          ticks: {
            stepSize: 1,
          },
        },
      },
    },
  });
}

renderMonthlyTrend();
