document.addEventListener("DOMContentLoaded", () => {
  const leaveForm = document.getElementById("leaveForm");
  const startDateInput = document.getElementById("startDate");
  const endDateInput = document.getElementById("endDate");
  const daysInput = document.getElementById("days");
  const toast = document.getElementById("toast");
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
    event.preventDefault();

    // Skenario validasi jika tanggal selesai lebih awal dari tanggal mulai
    const start = new Date(startDateInput.value);
    const end = new Date(endDateInput.value);
    if (end < start) {
      alert("Tanggal selesai tidak boleh lebih awal dari tanggal mulai.");
      return;
    }

    // Tampilkan Popup Toast
    toast.classList.remove(
      "opacity-0",
      "pointer-events-none",
      "-translate-y-4",
    );
    toast.classList.add("opacity-100", "translate-y-0");

    // Reset isi form
    leaveForm.reset();

    // Sembunyikan toast setelah 2 detik
    setTimeout(() => {
      toast.classList.remove("opacity-100", "translate-y-0");
      toast.classList.add("opacity-0", "pointer-events-none", "-translate-y-4");

      // OPSIONAL: Buka komentar kode di bawah jika ingin langsung pindah halaman setelah submit
      // window.location.href = 'halaman-tujuan.html';
    }, 2000);
  });
});
