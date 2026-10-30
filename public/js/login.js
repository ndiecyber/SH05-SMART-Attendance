// Tunggu hingga seluruh elemen HTML (DOM) selesai dimuat
document.addEventListener("DOMContentLoaded", function () {
  // Inisialisasi elemen-elemen yang dibutuhkan dari DOM
  const togglePassword = document.querySelector("#togglePassword");
  const password = document.querySelector("#password");
  const eyeIcon = document.querySelector("#eyeIcon");

  // Tambahkan event listener saat tombol mata diklik
  togglePassword.addEventListener("click", function (e) {
    // Cek tipe atribut saat ini, jika 'password' ubah jadi 'text' (tampilkan), dan sebaliknya
    const type =
      password.getAttribute("type") === "password" ? "text" : "password";
    password.setAttribute("type", type);

    // Toggle warna ikon untuk memberikan feedback visual kepada pengguna
    if (type === "text") {
      // Saat password terlihat, ubah warna ikon menjadi biru
      eyeIcon.classList.add("text-blue-500");
      eyeIcon.classList.remove("text-gray-400");
    } else {
      // Saat password tersembunyi, kembalikan warna ikon menjadi abu-abu
      eyeIcon.classList.add("text-gray-400");
      eyeIcon.classList.remove("text-blue-500");
    }
  });
});
