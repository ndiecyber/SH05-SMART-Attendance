// =========================
// Sidebar Mobile
// =========================
const sidebar = document.getElementById("sidebar");
const sidebarOverlay = document.getElementById("sidebarOverlay");
const openSidebar = document.getElementById("openSidebar");
const closeSidebar = document.getElementById("closeSidebar");

function showSidebar() {
  sidebar.classList.remove("-translate-x-full");
  sidebarOverlay.classList.remove("hidden");
  document.body.classList.add("overflow-hidden");
}

function hideSidebar() {
  sidebar.classList.add("-translate-x-full");
  sidebarOverlay.classList.add("hidden");
  document.body.classList.remove("overflow-hidden");
}

openSidebar?.addEventListener("click", showSidebar);
closeSidebar?.addEventListener("click", hideSidebar);
sidebarOverlay?.addEventListener("click", hideSidebar);

window.addEventListener("resize", () => {
  if (window.innerWidth >= 768) {
    sidebarOverlay.classList.add("hidden");
    document.body.classList.remove("overflow-hidden");
    sidebar.classList.remove("-translate-x-full");
  } else {
    sidebar.classList.add("-translate-x-full");
  }
});

const currentDate = document.getElementById("currentDate");
const currentTime = document.getElementById("currentTime");

function formatTime(date) {
  return date.toLocaleTimeString("en-US", {
    hour: "2-digit",
    minute: "2-digit",
    hour12: false,
  });
}

function formatDate(date) {
  return date.toLocaleDateString("en-US", {
    weekday: "long",
    day: "numeric",
    month: "long",
    year: "numeric",
  });
}

function updateClock() {
  if (currentTime) {
    currentTime.textContent = formatTime(new Date());
  }
}

updateClock();
setInterval(updateClock, 1000);

currentDate.textContent = formatDate(new Date());
updateAttendanceSummary();
updateWorkDuration();
updateAttendanceStatus();
requestLocation();
