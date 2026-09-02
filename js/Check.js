// ==========================================
// 1. NAVIGATION & SIDEBAR MANAGEMENT
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

// Initialization
handleResize();

// ==========================================
// 2. REAL-TIME CLOCK SYSTEM
// ==========================================
const headerTime = document.getElementById("headerTime");
const headerDate = document.getElementById("headerDate");
const heroTime = document.getElementById("heroTime");
const heroDay = document.getElementById("heroDay");
const heroDate = document.getElementById("heroDate");

function updateClock() {
  const now = new Date();

  const timeText = now.toLocaleTimeString("en-US", {
    hour: "2-digit",
    minute: "2-digit",
    second: "2-digit",
    hour12: false,
  });

  const dayText = now.toLocaleDateString("en-US", { weekday: "long" });
  const dateText = now.toLocaleDateString("en-US", {
    day: "numeric",
    month: "long",
    year: "numeric",
  });

  if (headerTime) headerTime.textContent = timeText;
  if (headerDate) headerDate.textContent = dateText;
  if (heroTime) heroTime.textContent = timeText;
  if (heroDay) heroDay.textContent = dayText;
  if (heroDate) heroDate.textContent = dateText;
}

updateClock();
setInterval(updateClock, 1000);

// ==========================================
// 3. GEOLOCATION & MAP SYSTEM
// ==========================================
const locationText = document.getElementById("locationText");
const locationAddress = document.getElementById("locationAddress");
const coordinates = document.getElementById("coordinates");
const refreshLocation = document.getElementById("refreshLocation");
const attendanceMap = document.getElementById("attendanceMap");

async function getAddress(latitude, longitude) {
  try {
    // Menggunakan API BigDataCloud (Bisa diakses langsung dari browser tanpa kendala User-Agent)
    const response = await fetch(
      `https://api.bigdatacloud.net/data/reverse-geocode-client?latitude=${latitude}&longitude=${longitude}&localityLanguage=en`,
    );

    if (!response.ok) throw new Error("Failed to retrieve address data");

    const data = await response.json();
    const formattedAddress =
      `${data.locality || ""}, ${data.principalSubdivision || ""}, ${data.countryName || ""}`.replace(
        /^,\s*/,
        "",
      );

    if (locationAddress) {
      locationAddress.textContent =
        formattedAddress || data.locality || "Address found";
    }
  } catch (error) {
    console.error("Geocoding Error:", error);
    if (locationAddress) {
      locationAddress.textContent = "Failed to load address details.";
    }
  }
}

function setLocation(position) {
  const { latitude, longitude } = position.coords;

  if (locationText) {
    locationText.textContent = "Location detected";
  }

  if (coordinates) {
    coordinates.innerHTML = `Latitude: ${latitude.toFixed(6)}<br>Longitude: ${longitude.toFixed(6)}`;
  }

  if (attendanceMap) {
    attendanceMap.src = `https://maps.google.com/maps?q=${latitude},${longitude}&z=16&output=embed`;
  }

  getAddress(latitude, longitude);
}

function showLocationError(error) {
  console.warn("Geolocation Warning:", error?.message || error);

  if (locationText) {
    locationText.textContent = "Location not available";
  }

  if (locationAddress) {
    locationAddress.textContent =
      "Please enable location permissions in your browser.";
  }

  if (coordinates) {
    coordinates.textContent = "Latitude: - | Longitude: -";
  }
}

function requestLocation() {
  if (!navigator.geolocation) {
    showLocationError("Geolocation is not supported by this browser.");
    return;
  }

  if (locationText) {
    locationText.textContent = "Searching for location...";
  }

  navigator.geolocation.getCurrentPosition(setLocation, showLocationError, {
    enableHighAccuracy: true,
    timeout: 10000,
    maximumAge: 0, // Memaksa browser mengambil lokasi terbaru
  });
}

refreshLocation?.addEventListener("click", requestLocation);
requestLocation();

// ==========================================
// 4. CAMERA MEDIA STREAM
// ==========================================
const cameraPreview = document.getElementById("cameraPreview");
const cameraOverlay = document.getElementById("cameraOverlay");
const toggleCameraBtn = document.getElementById("toggleCameraBtn");
let stream = null;

async function startCamera() {
  if (!navigator.mediaDevices?.getUserMedia) {
    alert("Camera is not supported in this browser");
    return;
  }

  try {
    stream = await navigator.mediaDevices.getUserMedia({
      video: { facingMode: "user" },
      audio: false,
    });

    if (cameraPreview) {
      cameraPreview.srcObject = stream;
      cameraPreview.classList.remove("hidden");
    }

    cameraOverlay?.classList.add("hidden");
    if (toggleCameraBtn) toggleCameraBtn.textContent = "Turn Off Camera";
  } catch (error) {
    console.error("Camera Error:", error);
    alert("Cannot access camera. Please ensure permissions are granted.");
  }
}

function stopCamera() {
  if (stream) {
    stream.getTracks().forEach((track) => track.stop());
    stream = null;
  }

  if (cameraPreview) {
    cameraPreview.srcObject = null;
    cameraPreview.classList.add("hidden");
  }

  cameraOverlay?.classList.remove("hidden");
  if (toggleCameraBtn) toggleCameraBtn.textContent = "Turn On Camera";
}

toggleCameraBtn?.addEventListener("click", () => {
  stream ? stopCamera() : startCamera();
});

// ==========================================
// 5. ATTENDANCE ACTIONS (CHECK-IN / CHECK-OUT)
// ==========================================
const checkInBtn = document.getElementById("checkInBtn");
const checkOutBtn = document.getElementById("checkOutBtn");
const checkInStatus = document.getElementById("checkInStatus");
const checkOutStatus = document.getElementById("checkOutStatus");

let checkedIn = false;
let checkedOut = false;

checkInBtn?.addEventListener("click", () => {
  if (checkedIn) return;

  checkedIn = true;
  checkedOut = false;

  checkInBtn.disabled = true;
  checkInBtn.classList.remove("bg-emerald-600", "hover:bg-emerald-700");
  checkInBtn.classList.add("bg-slate-300", "cursor-not-allowed");

  if (checkInStatus) {
    checkInStatus.textContent = "Status: Checked In";
    checkInStatus.className = "text-center text-xs text-emerald-600";
  }

  if (checkOutBtn) {
    checkOutBtn.disabled = false;
    checkOutBtn.classList.remove("bg-slate-300", "cursor-not-allowed");
    checkOutBtn.classList.add("bg-rose-600", "hover:bg-rose-700");
  }

  if (checkOutStatus) {
    checkOutStatus.textContent = "Status: Ready";
    checkOutStatus.className = "text-center text-xs text-slate-500";
  }
});

checkOutBtn?.addEventListener("click", () => {
  if (!checkedIn || checkedOut) return;

  checkedOut = true;

  checkOutBtn.disabled = true;
  checkOutBtn.classList.remove("bg-rose-600", "hover:bg-rose-700");
  checkOutBtn.classList.add("bg-slate-300", "cursor-not-allowed");

  if (checkOutStatus) {
    checkOutStatus.textContent = "Status: Completed";
    checkOutStatus.className = "text-center text-xs text-emerald-600";
  }
});

// ==========================================
// 6. TOAST NOTIFICATION SYSTEM
// ==========================================
const toast = document.getElementById("toast");
const triggerBtn = document.getElementById("triggerBtn");
const closeToastBtn = document.getElementById("closeToastBtn");
let toastTimer;

function showToast() {
  if (!toast) return;

  clearTimeout(toastTimer);
  toast.classList.remove("hidden");

  setTimeout(() => {
    toast.classList.remove("-translate-y-10", "opacity-0");
    toast.classList.add("translate-y-0", "opacity-100");
  }, 10);

  toastTimer = setTimeout(hideToast, 3000);
}

function hideToast() {
  if (!toast) return;

  clearTimeout(toastTimer);
  toast.classList.remove("translate-y-0", "opacity-100");
  toast.classList.add("-translate-y-10", "opacity-0");

  setTimeout(() => {
    toast.classList.add("hidden");
  }, 300);
}

triggerBtn?.addEventListener("click", showToast);
closeToastBtn?.addEventListener("click", hideToast);
