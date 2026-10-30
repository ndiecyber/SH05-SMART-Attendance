document.addEventListener("DOMContentLoaded", () => {
  // ==========================================
  // 1. SIDEBAR MOBILE
  // ==========================================
  const sidebar = document.getElementById("sidebar");
  const sidebarOverlay = document.getElementById("sidebarOverlay");
  const openSidebar = document.getElementById("openSidebar");
  const closeSidebar = document.getElementById("closeSidebar");

  function showSidebar() {
    sidebar?.classList.remove("-translate-x-full");
    sidebarOverlay?.classList.remove("hidden");
    document.body.classList.add("overflow-hidden");
  }

  function hideSidebar() {
    sidebar?.classList.add("-translate-x-full");
    sidebarOverlay?.classList.add("hidden");
    document.body.classList.remove("overflow-hidden");
  }

  function handleResize() {
    if (window.innerWidth >= 768) {
      sidebarOverlay?.classList.add("hidden");
      document.body.classList.remove("overflow-hidden");
      sidebar?.classList.remove("-translate-x-full");
    } else {
      sidebar?.classList.add("-translate-x-full");
    }
  }

  openSidebar?.addEventListener("click", showSidebar);
  closeSidebar?.addEventListener("click", hideSidebar);
  sidebarOverlay?.addEventListener("click", hideSidebar);
  window.addEventListener("resize", handleResize);
  handleResize();

  // ==========================================
  // 2. JAM & TANGGAL REALTIME
  // ==========================================
  const headerTime = document.getElementById("headerTime");
  const headerDate = document.getElementById("headerDate");

  function updateClock() {
    const now = new Date();
    if (headerTime) {
      headerTime.textContent = now.toLocaleTimeString("en-US", {
        hour: "2-digit",
        minute: "2-digit",
        second: "2-digit",
        hour12: false,
      });
    }
    if (headerDate) {
      headerDate.textContent = now.toLocaleDateString("en-US", {
        day: "numeric",
        month: "long",
        year: "numeric",
      });
    }
  }

  updateClock();
  setInterval(updateClock, 1000);

  // ==========================================
  // 3. SETTINGS & FORM TRACKING
  // ==========================================
  const trackedInputs = document.querySelectorAll(".track-change");
  const btnSave = document.getElementById("btnSave");
  const btnReset = document.getElementById("btnReset");
  const statusBadge = document.getElementById("statusBadge");
  const toastNotification = document.getElementById("toastNotification");

  const profileImageInput = document.getElementById("profileImageInput");
  const profileImagePreview = document.getElementById("profileImagePreview");
  const bioInput = document.getElementById("inp-bio");
  const bioCharCount = document.getElementById("bioCharCount");

  // State Awal
  let initialValues = {};
  let initialImageSrc = profileImagePreview ? profileImagePreview.src : "";
  let isImageChanged = false;
  let toastTimeout = null;

  // Hitung Karakter Bio Real-time
  function updateBioCharCount() {
    if (bioInput && bioCharCount) {
      const currentLength = bioInput.value.length;
      bioCharCount.textContent = `${currentLength}/240 characters`;
    }
  }

  // Simpan nilai awal dari seluruh input
  trackedInputs.forEach((input) => {
    initialValues[input.id] = input.value;
    input.addEventListener("input", () => {
      if (input.id === "inp-bio") updateBioCharCount();
      checkFormChanges();
    });
  });

  updateBioCharCount();

  // Fungsi Cek Perubahan (Input & Foto Profil)
  function checkFormChanges() {
    let isInputsChanged = false;

    trackedInputs.forEach((input) => {
      if (input.value !== initialValues[input.id]) {
        isInputsChanged = true;
      }
    });

    const hasChanged = isInputsChanged || isImageChanged;

    if (hasChanged) {
      if (btnSave) {
        btnSave.disabled = false;
        btnSave.classList.remove("opacity-50", "cursor-not-allowed");
        btnSave.classList.add("hover:bg-blue-600", "shadow-md");
      }

      if (statusBadge) {
        statusBadge.innerHTML =
          '<span class="h-2 w-2 rounded-full bg-amber-500"></span> Unsaved changes';
        statusBadge.className =
          "flex items-center gap-1.5 rounded-full bg-amber-50 px-3 py-1.5 text-xs font-semibold text-amber-700 transition-colors";
      }
    } else {
      if (btnSave) {
        btnSave.disabled = true;
        btnSave.classList.add("opacity-50", "cursor-not-allowed");
        btnSave.classList.remove("hover:bg-blue-600", "shadow-md");
      }

      if (statusBadge) {
        statusBadge.innerHTML =
          '<span class="h-2 w-2 rounded-full bg-emerald-500"></span> All changes saved';
        statusBadge.className =
          "flex items-center gap-1.5 rounded-full bg-emerald-50 px-3 py-1.5 text-xs font-semibold text-emerald-700 transition-colors";
      }
    }
  }

  // ==========================================
  // 4. UBAH FOTO PROFIL
  // ==========================================
  if (profileImageInput && profileImagePreview) {
    profileImageInput.addEventListener("change", (event) => {
      const file = event.target.files[0];
      if (file) {
        const reader = new FileReader();
        reader.onload = (e) => {
          profileImagePreview.src = e.target.result;
          isImageChanged = true;
          checkFormChanges();
        };
        reader.readAsDataURL(file);
      }
    });
  }

  // ==========================================
  // 5. TOMBOL SIMPAN (TOAST NOTIFICATION)
  // ==========================================
  btnSave?.addEventListener("click", () => {
    if (btnSave.disabled) return;

    // Tampilkan Toast
    if (toastNotification) {
      clearTimeout(toastTimeout);
      toastNotification.classList.remove(
        "opacity-0",
        "pointer-events-none",
        "-translate-y-4",
      );
      toastNotification.classList.add("opacity-100", "translate-y-0");

      toastTimeout = setTimeout(() => {
        toastNotification.classList.remove("opacity-100", "translate-y-0");
        toastNotification.classList.add(
          "opacity-0",
          "pointer-events-none",
          "-translate-y-4",
        );
      }, 3000);
    }

    // Perbarui State Awal
    trackedInputs.forEach((input) => {
      initialValues[input.id] = input.value;
    });

    if (profileImagePreview) {
      initialImageSrc = profileImagePreview.src;
    }
    isImageChanged = false;

    checkFormChanges();
  });

  // ==========================================
  // 6. TOMBOL RESET
  // ==========================================
  btnReset?.addEventListener("click", () => {
    trackedInputs.forEach((input) => {
      input.value = initialValues[input.id] || "";
    });

    if (profileImagePreview) {
      profileImagePreview.src = initialImageSrc;
    }
    if (profileImageInput) {
      profileImageInput.value = "";
    }
    isImageChanged = false;

    updateBioCharCount();
    checkFormChanges();
  });
});
