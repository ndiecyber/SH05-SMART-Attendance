document.addEventListener("DOMContentLoaded", () => {
  const leaveForm = document.getElementById("leaveForm");
  const startDateInput = document.getElementById("startDate");
  const endDateInput = document.getElementById("endDate");
  const daysInput = document.getElementById("days");
  function calculateDays() {
    if (!startDateInput.value || !endDateInput.value) {
      daysInput.value = "";
      endDateInput.setCustomValidity("");
      return;
    }

    const [startYear, startMonth, startDay] = startDateInput.value.split("-").map(Number);
    const [endYear, endMonth, endDay] = endDateInput.value.split("-").map(Number);
    const start = Date.UTC(startYear, startMonth - 1, startDay);
    const end = Date.UTC(endYear, endMonth - 1, endDay);
    const duration = Math.floor((end - start) / 86400000) + 1;

    if (duration < 1) {
      daysInput.value = "Tanggal selesai tidak valid";
      endDateInput.setCustomValidity("End date must be on or after start date.");
      return;
    }

    endDateInput.setCustomValidity("");
    daysInput.value = `${duration} Hari`;
  }
  startDateInput?.addEventListener("change", calculateDays);
  endDateInput?.addEventListener("change", calculateDays);
  calculateDays();
});
