document.addEventListener("DOMContentLoaded", () => {
  const scheduleForm = document.getElementById("scheduleForm");
  const startTimeInput = document.getElementById("startTime");
  const endTimeInput = document.getElementById("endTime");
  const durationInput = document.getElementById("duration");
  const toast = document.getElementById("toast");
  const backBtn = document.getElementById("backBtn");

  // Event handler untuk tombol Back
  backBtn.addEventListener("click", () => {
    window.history.back();
  });

  // Event listener saat nilai jam berubah untuk kalkulasi durasi
  startTimeInput.addEventListener("change", calculateDuration);
  endTimeInput.addEventListener("change", calculateDuration);

  // Fungsi kalkulasi durasi waktu
  function calculateDuration() {
    const startTime = startTimeInput.value;
    const endTime = endTimeInput.value;

    if (startTime && endTime) {
      const start = new Date(`1970-01-01T${startTime}:00`);
      const end = new Date(`1970-01-01T${endTime}:00`);

      let diffMs = end - start;

      // Handling jika jam selesai melewati tengah malam (misal 23:00 - 01:00)
      if (diffMs < 0) {
        diffMs += 24 * 60 * 60 * 1000;
      }

      const diffMins = Math.floor(diffMs / (1000 * 60));
      const hours = Math.floor(diffMins / 60);
      const minutes = diffMins % 60;

      let durationText = "";
      if (hours > 0) durationText += `${hours} Jam `;
      if (minutes > 0 || hours === 0) durationText += `${minutes} Menit`;

      durationInput.value = durationText;
    }
  }

  // Event handler saat form disubmit
  scheduleForm.addEventListener("submit", (event) => {
    event.preventDefault();

    // Tampilkan Popup Toast
    toast.classList.remove(
      "opacity-0",
      "pointer-events-none",
      "-translate-y-4",
    );
    toast.classList.add("opacity-100", "translate-y-0");

    // Reset Form
    scheduleForm.reset();

    // Sembunyikan Toast setelah 3 detik
    setTimeout(() => {
      toast.classList.remove("opacity-100", "translate-y-0");
      toast.classList.add("opacity-0", "pointer-events-none", "-translate-y-4");
    }, 3000);
  });
});
