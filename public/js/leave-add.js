document.addEventListener("DOMContentLoaded", () => {
  const leaveForm = document.getElementById("leaveForm");
  const startDateInput = document.getElementById("startDate");
  const endDateInput = document.getElementById("endDate");
  const daysInput = document.getElementById("days");
  const backBtn = document.getElementById("backBtn");

  // Event handler untuk tombol Kembali
  backBtn.addEventListener("click", () => {
    window.history.back();
  });

  // Listener untuk kalkulasi otomatis jumlah hari
  startDateInput.addEventListener("change", calculateDays);
  endDateInput.addEventListener("change", calculateDays);

  // Fungsi menghitung total hari
  function calculateDays() {
    const startDateVal = startDateInput.value;
    const endDateVal = endDateInput.value;

    if (startDateVal && endDateVal) {
      const start = new Date(startDateVal);
      const end = new Date(endDateVal);

      if (end < start) {
        daysInput.value = "Tanggal selesai tidak valid";
        return;
      }

      // Menghitung selisih hari (termasuk hari pertama)
      const diffTime = Math.abs(end - start);
      const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24)) + 1;

      daysInput.value = `${diffDays} Hari`;
    }
  }

  // Event handler pengiriman form
  leaveForm.addEventListener("submit", (event) => {
    // Skenario validasi jika tanggal selesai lebih awal dari tanggal mulai
    const start = new Date(startDateInput.value);
    const end = new Date(endDateInput.value);
    if (end < start) {
      event.preventDefault();
      window.LexaAlerts?.warning("Tanggal selesai tidak boleh lebih awal dari tanggal mulai.", "Periksa tanggal cuti");
      return;
    }
  });
});
