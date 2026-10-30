document.addEventListener("DOMContentLoaded", () => {
  const startTimeInput = document.getElementById("startTime");
  const endTimeInput = document.getElementById("endTime");
  const durationInput = document.getElementById("duration");
  function calculateDuration() {
    if (!startTimeInput.value || !endTimeInput.value) {
      durationInput.value = "";
      endTimeInput.setCustomValidity("");
      return;
    }
    const [startHours, startMinutes] = startTimeInput.value.split(":").map(Number);
    const [endHours, endMinutes] = endTimeInput.value.split(":").map(Number);
    const minutes = (endHours * 60 + endMinutes) - (startHours * 60 + startMinutes);
    if (minutes <= 0) {
      durationInput.value = "";
      endTimeInput.setCustomValidity("End time must be after start time.");
      return;
    }
    endTimeInput.setCustomValidity("");
    durationInput.value = `${Math.floor(minutes / 60)} Jam ${minutes % 60} Menit`;
  }
  startTimeInput?.addEventListener("change", calculateDuration);
  endTimeInput?.addEventListener("change", calculateDuration);
});
