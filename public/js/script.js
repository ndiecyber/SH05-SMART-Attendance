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

if (currentDate) {
  currentDate.textContent = formatDate(new Date());
}
