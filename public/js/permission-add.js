document.addEventListener("DOMContentLoaded", () => {
  const permissionForm = document.getElementById("permissionForm");
  const startTimeInput = document.getElementById("startTime");
  const endTimeInput = document.getElementById("endTime");
  const durationInput = document.getElementById("duration");
  const toast = document.getElementById("toast");
  const backBtn = document.getElementById("backBtn");

  // Event handler untuk tombol Kembali
  backBtn.addEventListener("click", () => {
    window.history.back();
  });

  // Event listener kalkulasi durasi otomatis saat input waktu berubah
  startTimeInput.addEventListener("change", calculateDuration);
  endTimeInput.addEventListener("change", calculateDuration);

  // Fungsi kalkulasi durasi
  function calculateDuration() {
    const startTime = startTimeInput.value;
    const endTime = endTimeInput.value;

    if (startTime && endTime) {
      const start = new Date(`1970-01-01T${startTime}:00`);
      const end = new Date(`1970-01-01T${endTime}:00`);

      let diffMs = end - start;

      // Handling jika waktu melintasi tengah malam
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

  // Event handler pengiriman form
  permissionForm.addEventListener("submit", (event) => {
    event.preventDefault();

    // Tampilkan Popup Toast
    toast.classList.remove(
      "opacity-0",
      "pointer-events-none",
      "-translate-y-4",
    );
    toast.classList.add("opacity-100", "translate-y-0");

    // Reset isi form
    permissionForm.reset();

    // Sembunyikan toast setelah 2,5 detik
    setTimeout(() => {
      toast.classList.remove("opacity-100", "translate-y-0");
      toast.classList.add("opacity-0", "pointer-events-none", "-translate-y-4");
    }, 2500);
  });
});
