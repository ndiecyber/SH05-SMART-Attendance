// ==========================================
// 0. GLOBAL STATE (DEKLARASI DI AWAL)
// ==========================================
let checkedIn = Boolean(window.todayAttendance?.check_in_at);
let checkedOut = Boolean(
  window.todayAttendance?.check_in_at && window.todayAttendance?.check_out_at,
);
let isLocationActive = false;
let pendingAction = null; // 'checkin' atau 'checkout'
let stream = null;
let currentCoordinates = null;
let currentDistanceFromOffice = null;

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

handleResize();

// ==========================================
// 2. REAL-TIME CLOCK SYSTEM & TIMELINE AUTO-UPDATE
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

  // Auto-update timeline break status jika sudah check in
  if (checkedIn && !checkedOut) {
    const currentHour = now.getHours();

    // Break Start Trigger (12:00)
    if (currentHour >= 12) {
      const dotBreakStart = document.getElementById("dot-breakstart");
      if (dotBreakStart && dotBreakStart.classList.contains("bg-slate-300")) {
        dotBreakStart.classList.replace("bg-slate-300", "bg-amber-500");
      }
    }

    // Break End Trigger (13:00)
    if (currentHour >= 13) {
      const dotBreakEnd = document.getElementById("dot-breakend");
      if (dotBreakEnd && dotBreakEnd.classList.contains("bg-slate-300")) {
        dotBreakEnd.classList.replace("bg-slate-300", "bg-blue-500");
      }
    }
  }
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
const locationDistance = document.getElementById("locationDistance");
const networkStatus = document.getElementById("networkStatus");
const attendanceEligibility = document.getElementById("attendanceEligibility");
const attendanceValidation = window.attendanceValidation || {};
const debugLatitude = document.getElementById("debugLatitude");
const debugLongitude = document.getElementById("debugLongitude");
const debugDistance = document.getElementById("debugDistance");
const debugAccuracy = document.getElementById("debugAccuracy");
const debugGpsStatus = document.getElementById("debugGpsStatus");
const debugRequestIp = document.getElementById("debugRequestIp");
const debugDetectedIp = document.getElementById("debugDetectedIp");
const debugNetworkStatus = document.getElementById("debugNetworkStatus");
const debugAttendanceStatus = document.getElementById("debugAttendanceStatus");
let clientPublicIp = attendanceValidation.detectedPublicIp || null;

async function detectClientPublicIp() {
  if (!attendanceValidation.networkLocalEnvironment) return clientPublicIp;

  attendanceValidation.networkAllowed = false;
  attendanceValidation.networkIpDetected = false;
  attendanceValidation.networkFailureMessage = "Memeriksa public IP dari browser...";
  if (networkStatus) {
    networkStatus.textContent = "Memeriksa public IP dari browser...";
    networkStatus.className = "mt-2 text-sm font-medium text-amber-700";
  }
  if (debugDetectedIp) debugDetectedIp.textContent = "Detected Public IP: memeriksa...";
  updateAttendanceEligibility();

  const controller = new AbortController();
  const timeout = window.setTimeout(() => controller.abort(), 7000);
  try {
    const response = await fetch("https://api.ipify.org?format=json", {
      method: "GET",
      mode: "cors",
      cache: "no-store",
      signal: controller.signal,
      headers: { Accept: "application/json" },
    });
    if (!response.ok) throw new Error("Public IP service returned an error.");

    const result = await response.json();
    const publicIp = typeof result.ip === "string" ? result.ip.trim() : "";
    if (!publicIp || publicIp.length > 45) throw new Error("Public IP response was invalid.");

    clientPublicIp = publicIp;
    attendanceValidation.detectedPublicIp = publicIp;
    attendanceValidation.networkIpDetected = true;
    attendanceValidation.networkAllowed = Boolean(
      attendanceValidation.networkConfigured &&
      publicIp === attendanceValidation.officeAllowedIp,
    );
    attendanceValidation.networkFailureMessage = attendanceValidation.networkAllowed
      ? null
      : `Public IP ${publicIp} berbeda dari OFFICE_ALLOWED_IP.`;
  } catch {
    clientPublicIp = null;
    attendanceValidation.detectedPublicIp = null;
    attendanceValidation.networkIpDetected = false;
    attendanceValidation.networkAllowed = false;
    attendanceValidation.networkFailureMessage = "Public IP tidak dapat dideteksi.";
  } finally {
    window.clearTimeout(timeout);
    updateAttendanceEligibility();
    if (debugDetectedIp) {
      debugDetectedIp.textContent = `Detected Public IP: ${clientPublicIp || "tidak tersedia"}`;
    }
  }

  return clientPublicIp;
}

function updateNetworkStatus() {
  if (!networkStatus) return;

  if (!attendanceValidation.networkConfigured) {
    networkStatus.textContent = "Network INVALID: OFFICE_ALLOWED_IP belum dikonfigurasi sebagai IP publik.";
    networkStatus.className = "mt-2 text-sm font-medium text-rose-700";
  } else if (!attendanceValidation.networkIpDetected) {
    networkStatus.textContent = attendanceValidation.networkLocalEnvironment
      ? `Network INVALID: ${attendanceValidation.networkFailureMessage}`
      : "Network INVALID: IP request tidak valid atau bukan IP publik.";
    networkStatus.className = "mt-2 text-sm font-medium text-rose-700";
  } else if (attendanceValidation.networkAllowed) {
    networkStatus.textContent = `Network VALID: ${attendanceValidation.detectedPublicIp} cocok dengan OFFICE_ALLOWED_IP.`;
    networkStatus.className = "mt-2 text-sm font-medium text-emerald-700";
  } else {
    networkStatus.textContent = `Network INVALID: ${attendanceValidation.networkFailureMessage}`;
    networkStatus.className = "mt-2 text-sm font-medium text-rose-700";
  }

  if (debugNetworkStatus) {
    debugNetworkStatus.textContent = `Network Status: ${attendanceValidation.networkAllowed ? "VALID" : "INVALID"}`;
  }
}

function calculateDistanceInMeters(latitude, longitude, officeLatitude, officeLongitude) {
  const earthRadius = 6371000;
  const radians = (degrees) => (degrees * Math.PI) / 180;
  const latitudeDelta = radians(officeLatitude - latitude);
  const longitudeDelta = radians(officeLongitude - longitude);
  const a =
    Math.sin(latitudeDelta / 2) ** 2 +
    Math.cos(radians(latitude)) *
      Math.cos(radians(officeLatitude)) *
      Math.sin(longitudeDelta / 2) ** 2;
  const boundedA = Math.min(1, Math.max(0, a));

  return earthRadius * 2 * Math.atan2(Math.sqrt(boundedA), Math.sqrt(1 - boundedA));
}

function hasValidAttendanceLocation() {
  return Boolean(
    attendanceValidation.officeConfigured &&
      attendanceValidation.networkConfigured &&
      attendanceValidation.networkAllowed &&
      isLocationActive &&
      currentCoordinates &&
      currentDistanceFromOffice !== null &&
      currentDistanceFromOffice <= attendanceValidation.radiusMeters,
  );
}

function updateAttendanceEligibility() {
  updateNetworkStatus();
  if (!locationDistance || !attendanceEligibility) return;

  if (!attendanceValidation.officeConfigured) {
    locationDistance.textContent = "Lokasi: koordinat kantor belum dikonfigurasi.";
    attendanceEligibility.textContent = "Attendance: DENIED. Konfigurasi OFFICE_LATITUDE/OFFICE_LONGITUDE belum lengkap.";
    attendanceEligibility.className = "mt-2 text-sm font-semibold text-rose-700";
    if (debugGpsStatus) debugGpsStatus.textContent = "GPS Status: konfigurasi kantor tidak tersedia";
    if (debugAttendanceStatus) debugAttendanceStatus.textContent = "Attendance: DENIED";
    return;
  }

  if (!currentCoordinates || currentDistanceFromOffice === null) {
    locationDistance.textContent = "Jarak dari kantor: menunggu GPS";
    attendanceEligibility.textContent = "Attendance: menunggu lokasi GPS terbaru.";
    attendanceEligibility.className = "mt-2 text-sm font-semibold text-amber-700";
    return;
  }

  const distance = currentDistanceFromOffice;
  const accuracy = currentCoordinates.accuracy;
  const gpsAllowed = distance <= attendanceValidation.radiusMeters;
  const networkAllowed = Boolean(attendanceValidation.networkAllowed);
  const accuracyWarning = accuracy > attendanceValidation.gpsWarningAccuracyMeters
    ? " Akurasi GPS rendah; posisi bisa kurang presisi, tetapi tidak menggantikan pemeriksaan radius."
    : "";
  locationDistance.textContent = `Jarak dari kantor: ${distance.toFixed(1)} meter (akurasi GPS \u00b1${accuracy.toFixed(1)} meter; batas radius ${attendanceValidation.radiusMeters} meter).${accuracyWarning}`;

  if (debugLatitude) debugLatitude.textContent = `User Latitude: ${latitudeText(currentCoordinates.latitude)}`;
  if (debugLongitude) debugLongitude.textContent = `User Longitude: ${longitudeText(currentCoordinates.longitude)}`;
  if (debugDistance) debugDistance.textContent = `Calculated Distance: ${distance.toFixed(1)} m`;
  if (debugAccuracy) debugAccuracy.textContent = `GPS Accuracy: ?${accuracy.toFixed(1)} m`;
  if (debugGpsStatus) debugGpsStatus.textContent = `GPS Status: ${gpsAllowed ? "VALID" : "INVALID"}`;

  if (gpsAllowed && networkAllowed) {
    attendanceEligibility.textContent = "Attendance: ALLOWED. Server akan memvalidasi ulang saat dikirim.";
    attendanceEligibility.className = "mt-2 text-sm font-semibold text-emerald-700";
  } else {
    const failures = [];
    if (!gpsAllowed) failures.push(`GPS INVALID: jarak ${distance.toFixed(1)} m melebihi radius ${attendanceValidation.radiusMeters} m.`);
    if (!networkAllowed) failures.push(attendanceValidation.networkFailureMessage || "Network INVALID.");
    attendanceEligibility.textContent = `Attendance: DENIED. ${failures.join(" ")}`;
    attendanceEligibility.className = "mt-2 text-sm font-semibold text-rose-700";
  }
  if (debugAttendanceStatus) {
    debugAttendanceStatus.textContent = `Attendance: ${gpsAllowed && networkAllowed ? "ALLOWED" : "DENIED"}`;
  }
}

function latitudeText(value) {
  return Number(value).toFixed(6);
}

function longitudeText(value) {
  return Number(value).toFixed(6);
}

updateAttendanceEligibility();
if (attendanceValidation.networkLocalEnvironment) {
  detectClientPublicIp();
}

async function getAddress(latitude, longitude) {
  try {
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
    if (locationAddress)
      locationAddress.textContent = "Failed to load address details.";
  }
}

function setLocation(position) {
  const { latitude, longitude, accuracy } = position.coords;
  currentCoordinates = { latitude, longitude, accuracy };
  currentDistanceFromOffice = attendanceValidation.officeConfigured
    ? calculateDistanceInMeters(
        latitude,
        longitude,
        attendanceValidation.officeLatitude,
        attendanceValidation.officeLongitude,
      )
    : null;
  isLocationActive = true; // Sensus status lokasi aktif

  if (locationText) locationText.textContent = "Location detected";
  if (coordinates)
    coordinates.innerHTML = `Latitude: ${latitude.toFixed(6)}<br>Longitude: ${longitude.toFixed(6)}`;
  if (attendanceMap)
    attendanceMap.src = `https://maps.google.com/maps?q=${latitude},${longitude}&z=16&output=embed`;

  updateAttendanceEligibility();

  getAddress(latitude, longitude);
}

function showLocationError(error) {
  console.warn("Geolocation Warning:", error?.message || error);
  isLocationActive = false;

  if (locationText) locationText.textContent = "Location not available";
  if (locationAddress) {
    locationAddress.textContent = error?.code === 1
      ? "Akses lokasi ditolak. Izinkan lokasi di browser untuk absensi."
      : error?.code === 3
        ? "Waktu pengambilan GPS habis. Coba ambil lokasi kembali."
        : "GPS tidak tersedia. Periksa pengaturan lokasi perangkat.";
  }
  if (coordinates) coordinates.textContent = "Latitude: - | Longitude: -";
  currentCoordinates = null;
  currentDistanceFromOffice = null;
  if (debugLatitude) debugLatitude.textContent = "User Latitude: tidak tersedia";
  if (debugLongitude) debugLongitude.textContent = "User Longitude: tidak tersedia";
  if (debugDistance) debugDistance.textContent = "Calculated Distance: tidak tersedia";
  if (debugAccuracy) debugAccuracy.textContent = "GPS Accuracy: tidak tersedia";
  if (debugGpsStatus) debugGpsStatus.textContent = "GPS Status: INVALID (lokasi tidak tersedia)";
  if (debugAttendanceStatus) debugAttendanceStatus.textContent = "Attendance: DENIED";
  updateAttendanceEligibility();
}

function getFreshLocation() {
  if (!navigator.geolocation) {
    const error = new Error("Geolocation is not supported by this browser.");
    showLocationError(error);
    return Promise.reject(error);
  }

  isLocationActive = false;
  currentCoordinates = null;
  currentDistanceFromOffice = null;
  if (locationText) locationText.textContent = "Searching for location...";
  if (debugLatitude) debugLatitude.textContent = "User Latitude: mengambil lokasi terbaru...";
  if (debugLongitude) debugLongitude.textContent = "User Longitude: mengambil lokasi terbaru...";
  if (debugDistance) debugDistance.textContent = "Calculated Distance: menghitung...";
  if (debugGpsStatus) debugGpsStatus.textContent = "GPS Status: menunggu lokasi terbaru";
  if (debugAttendanceStatus) debugAttendanceStatus.textContent = "Attendance: menunggu validasi";
  updateAttendanceEligibility();

  return new Promise((resolve, reject) => {
    navigator.geolocation.getCurrentPosition(
      (position) => {
        setLocation(position);
        resolve(currentCoordinates);
      },
      (error) => {
        showLocationError(error);
        reject(error);
      },
      { enableHighAccuracy: true, timeout: 15000, maximumAge: 0 },
    );
  });
}

function requestLocation() {
  getFreshLocation().catch(() => {});
}

refreshLocation?.addEventListener("click", requestLocation);
requestLocation();

// ==========================================
// 4. CAMERA MEDIA STREAM
// ==========================================
const cameraPreview = document.getElementById("cameraPreview");
const cameraOverlay = document.getElementById("cameraOverlay");
const toggleCameraBtn = document.getElementById("toggleCameraBtn");

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
// 5. ATTENDANCE ACTIONS & MODAL FLOW
// ==========================================
const checkInBtn = document.getElementById("checkInBtn");
const checkOutBtn = document.getElementById("checkOutBtn");
const resetCheckInBtn = document.getElementById("resetCheckInBtn");
const resetCheckOutBtn = document.getElementById("resetCheckOutBtn");
const checkInStatus = document.getElementById("checkInStatus");
const checkOutStatus = document.getElementById("checkOutStatus");

function updateResetButtonStates() {
  const hasCheckIn = Boolean(window.todayAttendance?.check_in_at);
  const hasCheckOut = Boolean(window.todayAttendance?.check_out_at);

  if (resetCheckInBtn) resetCheckInBtn.disabled = !hasCheckIn;
  if (resetCheckOutBtn) resetCheckOutBtn.disabled = !hasCheckIn || !hasCheckOut;
}

updateResetButtonStates();

if (checkedIn) {
  checkInBtn.disabled = true;
  checkInBtn.classList.remove("bg-emerald-600", "hover:bg-emerald-700");
  checkInBtn.classList.add("bg-slate-300", "cursor-not-allowed");
  checkOutBtn.disabled = checkedOut;
  if (checkedOut) {
    checkOutBtn.classList.remove("bg-rose-600", "hover:bg-rose-700");
    checkOutBtn.classList.add("bg-slate-300", "cursor-not-allowed");
  } else {
    checkOutBtn.classList.remove("bg-slate-300", "cursor-not-allowed");
    checkOutBtn.classList.add("bg-rose-600", "hover:bg-rose-700");
  }
  if (checkInStatus) checkInStatus.textContent = "Status: Checked In";
  if (checkOutStatus) checkOutStatus.textContent = checkedOut ? "Status: Completed" : "Status: Ready";
  const checkInTime = document.getElementById("time-checkin");
  const checkOutTime = document.getElementById("time-checkout");
  if (checkInTime && window.todayAttendance.check_in_at) checkInTime.textContent = window.todayAttendance.check_in_at.slice(11, 16);
  if (checkOutTime && window.todayAttendance.check_out_at) checkOutTime.textContent = window.todayAttendance.check_out_at.slice(11, 16);
} else if (window.todayAttendance?.check_out_at) {
  checkInBtn.disabled = true;
  checkInBtn.classList.remove("bg-emerald-600", "hover:bg-emerald-700");
  checkInBtn.classList.add("bg-slate-300", "cursor-not-allowed");
  if (checkInStatus) checkInStatus.textContent = "Status: Reset Check Out first";
  const checkOutTime = document.getElementById("time-checkout");
  if (checkOutTime) checkOutTime.textContent = window.todayAttendance.check_out_at.slice(11, 16);
}

// Modal Elements
const attendanceModal = document.getElementById("attendanceModal");
const cancelModalBtn = document.getElementById("cancelModalBtn");
const confirmModalBtn = document.getElementById("confirmModalBtn");
const snapshotCanvas = document.getElementById("snapshotCanvas");
const snapshotPreview = document.getElementById("snapshotPreview");

function updateRecentAttendance(attendance) {
  const container = document.getElementById("recentAttendanceRecords");
  if (!container || !attendance?.attendance_date) return;

  const date = attendance.attendance_date;
  let row = container.querySelector(`[data-attendance-date="${date}"]`);
  if (!row) {
    document.getElementById("noRecentAttendance")?.remove();
    row = document.createElement("article");
    row.dataset.attendanceDate = date;
    row.className = "flex items-center justify-between gap-3 rounded-2xl border border-slate-200 p-4";
    row.innerHTML = `
      <div class="min-w-0 flex-1">
        <p class="recent-weekday font-semibold text-slate-800"></p>
        <p class="recent-date text-sm text-slate-500"></p>
        <p class="recent-location truncate text-sm text-slate-500"></p>
      </div>
      <div class="text-center">
        <p class="recent-times font-medium text-slate-700"></p>
        <p class="text-xs text-slate-500">Check In / Check Out</p>
      </div>
      <div class="text-right"><span class="recent-status rounded-full px-3 py-1 text-sm font-medium"></span></div>
    `;
    container.prepend(row);
  }

  const [year, month, day] = date.split("-").map(Number);
  const localDate = new Date(year, month - 1, day);
  const checkInAt = attendance.check_in_at || "";
  const checkOutAt = attendance.check_out_at || "";
  const hasCheckIn = Boolean(checkInAt);
  const hasCheckOut = Boolean(checkOutAt);
  const statusElement = row.querySelector(".recent-status");

  row.querySelector(".recent-weekday").textContent = localDate.toLocaleDateString("en-US", { weekday: "long" });
  row.querySelector(".recent-date").textContent = localDate.toLocaleDateString("en-GB", { day: "2-digit", month: "short", year: "numeric" });
  row.querySelector(".recent-location").textContent = attendance.location || "Location not recorded";
  row.querySelector(".recent-times").textContent = `${checkInAt.slice(11, 16) || "—"} – ${checkOutAt.slice(11, 16) || "—"}`;
  statusElement.textContent = hasCheckIn && hasCheckOut ? "Completed" : hasCheckIn ? "Checked In" : hasCheckOut ? "Check Out only" : "No Check In";
  statusElement.classList.remove("bg-emerald-100", "text-emerald-700", "bg-indigo-100", "text-indigo-700", "bg-slate-100", "text-slate-600");
  statusElement.classList.add(hasCheckIn && hasCheckOut ? "bg-emerald-100" : hasCheckIn ? "bg-indigo-100" : "bg-slate-100");
  statusElement.classList.add(hasCheckIn && hasCheckOut ? "text-emerald-700" : hasCheckIn ? "text-indigo-700" : "text-slate-600");

  while (container.children.length > 5) container.lastElementChild.remove();
}

function updateAttendanceStatistics(statistics) {
  if (!statistics) return;

  const statisticElements = {
    current_streak: "currentStreak",
    on_time_rate: "onTimeRate",
    average_check_in: "avgCheckIn",
    weekly_work_hours: "weeklyWorkHours",
  };

  Object.entries(statisticElements).forEach(([key, elementId]) => {
    const element = document.getElementById(elementId);
    if (element && typeof statistics[key] === "string") {
      element.textContent = statistics[key];
    }
  });
}

function capturePhoto() {
  if (!stream || !cameraPreview || !snapshotCanvas || !snapshotPreview) return;
  snapshotCanvas.width = cameraPreview.videoWidth || 640;
  snapshotCanvas.height = cameraPreview.videoHeight || 480;
  const ctx = snapshotCanvas.getContext("2d");
  ctx.drawImage(
    cameraPreview,
    0,
    0,
    snapshotCanvas.width,
    snapshotCanvas.height,
  );
  snapshotPreview.src = snapshotCanvas.toDataURL("image/png");
}

async function initAttendanceFlow(action) {
  if (attendanceValidation.networkLocalEnvironment) {
    await detectClientPublicIp();
  }

  try {
    await getFreshLocation();
  } catch {
    alert(attendanceEligibility?.textContent || "GPS terbaru tidak tersedia.");
    return;
  }

  if (!hasValidAttendanceLocation()) {
    alert(attendanceEligibility?.textContent || "Attendance DENIED: GPS atau network tidak valid.");
    return;
  }

  if (!stream) {
    alert("Aktifkan kamera sebelum melakukan absensi.");
    return;
  }

  pendingAction = action;
  capturePhoto();

  const nowTime = new Date().toLocaleTimeString("en-US", {
    hour: "2-digit",
    minute: "2-digit",
    hour12: false,
  });
  const modalTimeText = document.getElementById("modalTimeText");
  const modalLocationText = document.getElementById("modalLocationText");
  const modalTitle = document.getElementById("modalTitle");

  if (modalTimeText) modalTimeText.textContent = nowTime;
  if (modalLocationText) {
    modalLocationText.textContent = locationAddress?.textContent || "Location details unknown";
  }
  if (modalTitle) {
    modalTitle.textContent = action === "checkin" ? "Confirm Check In" : "Confirm Check Out";
  }

  attendanceModal?.classList.remove("hidden");
}

checkInBtn?.addEventListener("click", () => {
  if (!checkedIn) initAttendanceFlow("checkin");
});

checkOutBtn?.addEventListener("click", () => {
  if (checkedIn && !checkedOut) initAttendanceFlow("checkout");
});

cancelModalBtn?.addEventListener("click", () => {
  attendanceModal?.classList.add("hidden");
  pendingAction = null;
});

function applyServerValidation(result) {
  if (typeof result.network_ip_detected === "boolean") {
    attendanceValidation.networkIpDetected = result.network_ip_detected;
  }
  if (result.debug) {
    attendanceValidation.requestIp = result.debug.request_ip;
    attendanceValidation.detectedPublicIp = result.debug.detected_public_ip === "tidak tersedia"
      ? null
      : result.debug.detected_public_ip;
  }
  if (typeof result.network_valid === "boolean") {
    attendanceValidation.networkAllowed = result.network_valid;
    if (networkStatus) {
      networkStatus.textContent = result.network_valid
        ? `Network VALID: ${attendanceValidation.detectedPublicIp || "public IP"} cocok dengan OFFICE_ALLOWED_IP.`
        : `Network INVALID: ${result.network_message || "public IP tidak cocok atau tidak tersedia."}`;
      networkStatus.className = result.network_valid
        ? "mt-2 text-sm font-medium text-emerald-700"
        : "mt-2 text-sm font-medium text-rose-700";
    }
  }
  if (typeof result.gps_valid === "boolean" && debugGpsStatus) {
    debugGpsStatus.textContent = `GPS Status: ${result.gps_valid ? "VALID" : "INVALID"}`;
  }
  if (typeof result.network_valid === "boolean" && debugNetworkStatus) {
    debugNetworkStatus.textContent = `Network Status: ${result.network_valid ? "VALID" : "INVALID"}`;
  }
  if (result.debug) {
    if (debugLatitude) debugLatitude.textContent = `User Latitude: ${latitudeText(result.debug.user_latitude)}`;
    if (debugLongitude) debugLongitude.textContent = `User Longitude: ${longitudeText(result.debug.user_longitude)}`;
    if (debugAccuracy) debugAccuracy.textContent = `GPS Accuracy: \u00b1${Number(result.debug.gps_accuracy).toFixed(1)} m`;
    if (debugRequestIp) debugRequestIp.textContent = `Request IP: ${result.debug.request_ip}`;
    if (debugDetectedIp) debugDetectedIp.textContent = `Detected Public IP: ${result.debug.detected_public_ip}`;
    if (debugDistance) debugDistance.textContent = `Calculated Distance: ${Number(result.debug.distance_from_office).toFixed(1)} m`;
    if (debugGpsStatus) debugGpsStatus.textContent = `GPS Status: ${result.debug.gps_status}`;
    if (debugNetworkStatus) debugNetworkStatus.textContent = `Network Status: ${result.debug.network_status}`;
    if (debugAttendanceStatus) debugAttendanceStatus.textContent = `Attendance: ${result.debug.attendance_status}`;
  }
  if (typeof result.distance_from_office === "number" && locationDistance) {
    locationDistance.textContent = `Jarak hasil hitung server: ${result.distance_from_office.toFixed(1)} meter (radius ${result.radius_meters} meter).`;
  }
  if (attendanceEligibility && result.message) {
    const hasValidationResult = typeof result.gps_valid === "boolean"
      && typeof result.network_valid === "boolean"
      && typeof result.allowed === "boolean";
    const validationPassed = hasValidationResult
      && result.success === true
      && result.allowed === true
      && result.gps_valid
      && result.network_valid;
    attendanceEligibility.textContent = hasValidationResult
      ? `Attendance: ${validationPassed ? "ALLOWED" : "DENIED"}. ${result.message}`
      : `Attendance: tidak tercatat. ${result.message}`;
    attendanceEligibility.className = validationPassed
      ? "mt-2 text-sm font-semibold text-emerald-700"
      : "mt-2 text-sm font-semibold text-rose-700";
  }
}

confirmModalBtn?.addEventListener("click", async () => {
  if (!pendingAction || !snapshotPreview?.src) return;
  confirmModalBtn.disabled = true;
  try {
    if (attendanceValidation.networkLocalEnvironment) {
      await detectClientPublicIp();
      if (!clientPublicIp) {
        throw new Error("Network INVALID: public IP tidak dapat dideteksi.");
      }
    }
    await getFreshLocation();
    if (!currentCoordinates) throw new Error("GPS terbaru tidak tersedia.");
    if (!hasValidAttendanceLocation()) {
      throw new Error(attendanceEligibility?.textContent || "Attendance DENIED: GPS atau network tidak valid.");
    }
    const photo = await fetch(snapshotPreview.src).then((response) => response.blob());
    const formData = new FormData();
    formData.append("action", pendingAction);
    formData.append("latitude", currentCoordinates.latitude);
    formData.append("longitude", currentCoordinates.longitude);
    formData.append("accuracy", currentCoordinates.accuracy);
    if (attendanceValidation.networkLocalEnvironment) {
      formData.append("client_public_ip", clientPublicIp);
    }
    formData.append("location", locationAddress?.textContent || "");
    formData.append("photo", photo, "attendance.png");
    const endpoint = document.querySelector('meta[name="attendance-endpoint"]')?.content;
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
    if (!endpoint) throw new Error("Endpoint presensi tidak tersedia. Muat ulang halaman lalu coba lagi.");
    if (!csrfToken) throw new Error("Token keamanan tidak tersedia. Muat ulang halaman lalu coba lagi.");
    const response = await fetch(endpoint, {
      method: "POST",
      headers: { "X-CSRF-TOKEN": csrfToken, Accept: "application/json" },
      body: formData,
    });
    let result;
    try {
      result = await response.json();
    } catch {
      throw new Error("Server mengirim respons presensi yang tidak valid.");
    }
    applyServerValidation(result);
    if (!response.ok || result.success !== true || result.allowed !== true) {
      const validationMessage = result.errors
        ? Object.values(result.errors).flat()[0]
        : null;
      throw new Error(validationMessage || result.message || "Absensi tidak dapat disimpan.");
    }
    window.todayAttendance = result.attendance || result;
    updateResetButtonStates();
    updateRecentAttendance(window.todayAttendance);
    updateAttendanceStatistics(result.statistics);
  } catch (error) {
    alert(error instanceof TypeError
      ? "Koneksi terputus. Periksa jaringan internet lalu coba kembali."
      : error.message || "Absensi tidak dapat disimpan.");
    confirmModalBtn.disabled = false;
    return;
  }
  attendanceModal?.classList.add("hidden");
  const savedTimestamp = pendingAction === "checkin"
    ? window.todayAttendance?.check_in_at
    : window.todayAttendance?.check_out_at;
  const savedTime = savedTimestamp?.slice(11, 16) || "--:--";

  if (pendingAction === "checkin") {
    checkedIn = true;
    checkedOut = false;

    // Update Status Tombol Check In
    checkInBtn.disabled = true;
    checkInBtn.classList.remove("bg-emerald-600", "hover:bg-emerald-700");
    checkInBtn.classList.add("bg-slate-300", "cursor-not-allowed");
    if (checkInStatus) {
      checkInStatus.textContent = "Status: Checked In";
      checkInStatus.className = "text-center text-xs text-emerald-600";
    }

    // Aktifkan Tombol Check Out
    checkOutBtn.disabled = false;
    checkOutBtn.classList.remove("bg-slate-300", "cursor-not-allowed");
    checkOutBtn.classList.add("bg-rose-600", "hover:bg-rose-700");
    if (checkOutStatus) {
      checkOutStatus.textContent = "Status: Ready";
      checkOutStatus.className = "text-center text-xs text-slate-500";
    }

    // Update Timeline Check In
    const dotCheckin = document.getElementById("dot-checkin");
    const timeCheckin = document.getElementById("time-checkin");
    if (dotCheckin)
      dotCheckin.classList.replace("bg-slate-300", "bg-emerald-500");
    if (timeCheckin) timeCheckin.textContent = savedTime;
  } else if (pendingAction === "checkout") {
    checkedOut = true;

    // Update Status Tombol Check Out
    checkOutBtn.disabled = true;
    checkOutBtn.classList.remove("bg-rose-600", "hover:bg-rose-700");
    checkOutBtn.classList.add("bg-slate-300", "cursor-not-allowed");
    if (checkOutStatus) {
      checkOutStatus.textContent = "Status: Completed";
      checkOutStatus.className = "text-center text-xs text-emerald-600";
    }

    // Update Timeline Check Out
    const dotCheckout = document.getElementById("dot-checkout");
    const timeCheckout = document.getElementById("time-checkout");
    if (dotCheckout)
      dotCheckout.classList.replace("bg-slate-300", "bg-rose-500");
    if (timeCheckout) timeCheckout.textContent = savedTime;
  }

  // Pemicu Toast Sukses
  showToast();
  pendingAction = null;
  confirmModalBtn.disabled = false;
});

// ==========================================
// 6. TOAST NOTIFICATION SYSTEM
// ==========================================
const toast = document.getElementById("toast");
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

closeToastBtn?.addEventListener("click", hideToast);
