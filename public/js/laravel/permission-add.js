document.addEventListener("DOMContentLoaded", () => {
  const startTimeInput = document.getElementById("startTime");
  const endTimeInput = document.getElementById("endTime");
  const durationInput = document.getElementById("duration");

  function calculateDuration() {
    const startTime = startTimeInput.value;
    const endTime = endTimeInput.value;

    if (!startTime || !endTime || startTime === endTime) {
      durationInput.value = "";
      return;
    }

    const [startHour, startMinute] = startTime.split(":").map(Number);
    const [endHour, endMinute] = endTime.split(":").map(Number);
    let durationMinutes = endHour * 60 + endMinute - (startHour * 60 + startMinute);

    if (durationMinutes < 0) {
      durationMinutes += 24 * 60;
    }

    const hours = Math.floor(durationMinutes / 60);
    const minutes = durationMinutes % 60;
    durationInput.value = `${hours} Jam ${minutes} Menit`;
  }

  startTimeInput.addEventListener("change", calculateDuration);
  endTimeInput.addEventListener("change", calculateDuration);
  calculateDuration();
});
