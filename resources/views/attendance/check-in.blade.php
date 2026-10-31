@extends('layouts.app')

@section('title', 'Smart Attendance Dashboard')

@section('head')
    <meta name="attendance-endpoint" content="{{ route('attendance.record') }}" />
@endsection

@section('content')
  <section class="mx-auto max-w-[1600px] space-y-6 p-4 sm:p-6 lg:p-8">
    <article class="flex min-w-0 flex-col gap-4 rounded-3xl bg-gradient-to-br from-white to-indigo-100 p-5 text-blue-500 shadow-sm sm:flex-row sm:items-center sm:justify-between sm:p-6">
      <div class="min-w-0">
        <p class="text-sm text-slate-500">Current Time</p>
        <h2 id="heroTime" class="mt-1 text-4xl font-bold tracking-wide sm:text-5xl">09:45</h2>
        <p id="heroDay" class="mt-3 text-lg font-medium text-slate-700">Wednesday</p>
        <p id="heroDate" class="text-sm text-slate-500">July 8, 2026</p>
      </div>
      <div class="min-w-0 rounded-3xl bg-slate-100 p-4 shadow-sm sm:max-w-[22rem] sm:text-right">
        <p class="text-sm text-slate-500">Today's Shift</p>
        <h3 id="todayShiftName" class="mt-1 break-words text-xl font-bold text-slate-800 sm:text-2xl">Standard Intern</h3>
        <p id="todayShiftTime" class="mt-2 text-lg font-medium text-blue-600">08:00 - 17:00</p>
        <p class="text-sm text-slate-500">Scheduled Working Hours</p>
      </div>
    </article>

    <div class="grid min-w-0 items-stretch gap-6 md:grid-cols-2 xl:grid-cols-3">
      <article class="min-w-0 rounded-3xl bg-white p-5 shadow-sm sm:p-6 md:col-span-2 xl:col-span-2">
        <div class="flex flex-wrap items-center justify-between gap-3">
          <div class="min-w-0">
            <p class="text-sm text-slate-500">Current Location</p>
            <h3 class="mt-1 text-xl font-semibold text-slate-800">Attendance Location</h3>
          </div>
          <button id="refreshLocation" type="button" class="shrink-0 rounded-xl border border-slate-200 px-4 py-2 text-sm transition hover:bg-slate-100">Refresh</button>
        </div>

        <div class="mt-5 overflow-hidden rounded-2xl border border-slate-200">
          <iframe id="attendanceMap" title="Attendance location map" src="https://maps.google.com/maps?q=-6.9175,107.6191&z=16&output=embed" class="h-[280px] w-full sm:h-[350px]" loading="lazy"></iframe>
        </div>

        <div class="mt-5 min-w-0 rounded-2xl bg-slate-50 p-4">
          <h4 class="font-semibold text-slate-700">Current Position</h4>
          <p id="locationText" class="mt-2 break-words text-slate-700">Searching location...</p>
          <p id="locationAddress" class="mt-2 break-words text-sm leading-6 text-slate-600">Detecting address...</p>
          <p id="coordinates" class="mt-2 break-words text-sm text-slate-500">Latitude, Longitude</p>
          <p id="locationDistance" class="mt-2 break-words text-sm font-medium text-slate-700">Jarak dari kantor: menunggu GPS</p>
          <p id="networkStatus" class="mt-2 break-words text-sm font-medium text-slate-700">{{ $networkLocalEnvironment ? 'Memeriksa public IP dari browser...' : 'Memeriksa IP request...' }}</p>
          <p id="attendanceEligibility" class="mt-2 break-words text-sm font-semibold text-slate-700">Menunggu validasi GPS dan network.</p>
        </div>
      </article>

      <article class="flex min-w-0 flex-col rounded-3xl bg-white p-5 shadow-sm sm:p-6 md:col-span-2 xl:col-span-1">
        <div>
          <p class="text-sm text-slate-500">Camera Verification</p>
          <h3 class="mt-1 text-xl font-semibold text-slate-800">Selfie Verification</h3>
        </div>

        <div class="relative mt-5 overflow-hidden rounded-2xl border border-slate-200 bg-slate-100">
          <video id="cameraPreview" autoplay playsinline class="hidden h-[420px] w-full object-cover"></video>
          <div id="cameraOverlay" class="flex h-[350px] flex-col items-center justify-center text-slate-400 sm:h-[420px]">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-20 w-20" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14m-9 5h8a2 2 0 002-2V7a2 2 0 00-2-2H6a2 2 0 00-2 2v10a2 2 0 002 2z" />
            </svg>
            <p class="mt-4 text-lg font-medium">Camera is Off</p>
          </div>
        </div>

        <button id="toggleCameraBtn" type="button" class="mt-4 w-full rounded-xl border border-slate-200 py-3 font-medium transition hover:bg-slate-100">Turn Camera On</button>
        <p class="mt-4 text-sm leading-6 text-slate-500">Position your face clearly inside the camera frame before submitting attendance.</p>

        <div class="mt-auto space-y-3 pt-5">
          <button id="checkInBtn" type="button" @disabled($todayRecord?->check_out_at && !$todayRecord?->check_in_at) class="w-full rounded-2xl bg-emerald-600 py-4 font-semibold text-white transition hover:bg-emerald-700 disabled:cursor-not-allowed disabled:opacity-50">Check In</button>
          <p id="checkInStatus" class="text-center text-xs text-slate-500">Status: {{ $todayRecord?->check_out_at && !$todayRecord?->check_in_at ? 'Reset Check Out first' : ($todayRecord?->check_in_at ? 'Checked In' : 'Not Checked In') }}</p>
          <button id="checkOutBtn" type="button" disabled class="w-full cursor-not-allowed rounded-2xl bg-slate-300 py-4 font-semibold text-white">Check Out</button>
          <p id="checkOutStatus" class="text-center text-xs text-slate-500">Status: Not Available</p>
        </div>
      </article>
    </div>

    <article class="min-w-0 rounded-3xl bg-white p-5 shadow-sm sm:p-6">
      <header class="mb-6">
        <h2 class="text-xl font-semibold text-slate-800">Today's Activity</h2>
        <p class="mt-1 text-sm text-slate-500">Attendance Timeline</p>
      </header>
      <div class="relative">
        <span class="absolute bottom-3 left-[8%] right-[8%] top-2 hidden h-px bg-slate-200 lg:block" aria-hidden="true"></span>
        <div class="grid min-w-0 gap-5 sm:grid-cols-2 lg:grid-cols-4">
          <div class="relative flex min-w-0 gap-4 pb-2 before:absolute before:bottom-[-0.75rem] before:left-[5px] before:top-3 before:w-px before:bg-slate-200 last:before:hidden lg:flex-col lg:gap-3 lg:pb-0 lg:before:hidden">
            <span id="dot-checkin" class="relative z-10 mt-1 h-3 w-3 shrink-0 rounded-full bg-slate-300 ring-4 ring-white transition-colors duration-500"></span>
            <div class="min-w-0"><p class="font-medium text-slate-800">Check In</p><p id="time-checkin" class="mt-1 break-words text-sm font-semibold text-slate-500">Pending</p></div>
          </div>
          <div class="relative flex min-w-0 gap-4 pb-2 before:absolute before:bottom-[-0.75rem] before:left-[5px] before:top-3 before:w-px before:bg-slate-200 last:before:hidden lg:flex-col lg:gap-3 lg:pb-0 lg:before:hidden">
            <span id="dot-breakstart" class="relative z-10 mt-1 h-3 w-3 shrink-0 rounded-full bg-slate-300 ring-4 ring-white transition-colors duration-500"></span>
            <div class="min-w-0"><p class="font-medium text-slate-800">Break Start</p><p class="mt-1 text-sm font-semibold text-amber-600">12:00</p></div>
          </div>
          <div class="relative flex min-w-0 gap-4 pb-2 before:absolute before:bottom-[-0.75rem] before:left-[5px] before:top-3 before:w-px before:bg-slate-200 last:before:hidden lg:flex-col lg:gap-3 lg:pb-0 lg:before:hidden">
            <span id="dot-breakend" class="relative z-10 mt-1 h-3 w-3 shrink-0 rounded-full bg-slate-300 ring-4 ring-white transition-colors duration-500"></span>
            <div class="min-w-0"><p class="font-medium text-slate-800">Break End</p><p class="mt-1 text-sm font-semibold text-blue-600">13:00</p></div>
          </div>
          <div class="relative flex min-w-0 gap-4 pb-2 before:absolute before:bottom-[-0.75rem] before:left-[5px] before:top-3 before:w-px before:bg-slate-200 last:before:hidden lg:flex-col lg:gap-3 lg:pb-0 lg:before:hidden">
            <span id="dot-checkout" class="relative z-10 mt-1 h-3 w-3 shrink-0 rounded-full bg-slate-300 ring-4 ring-white transition-colors duration-500"></span>
            <div class="min-w-0"><p class="font-medium text-slate-800">Check Out</p><p id="time-checkout" class="mt-1 break-words text-sm font-semibold text-slate-500">Pending</p></div>
          </div>
        </div>
      </div>
    </article>

    <article class="min-w-0 rounded-3xl bg-white p-5 shadow-sm sm:p-6">
      <header class="mb-5">
        <h2 class="text-xl font-semibold text-slate-800">Attendance Statistics</h2>
      </header>
      <div class="grid min-w-0 grid-cols-1 divide-y divide-slate-100 sm:grid-cols-2 sm:divide-y-0 xl:grid-cols-4 xl:divide-x xl:divide-slate-100">
        <div class="flex min-w-0 items-center gap-4 py-4 sm:pr-5 xl:py-2 xl:pr-6">
          <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-orange-100 text-orange-600"><svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" /></svg></span>
          <div class="min-w-0"><p class="text-sm text-slate-500">Current Streak</p><h3 id="currentStreak" class="mt-1 whitespace-nowrap text-2xl font-bold text-orange-600">{{ $attendanceStatistics['current_streak'] }}</h3><p class="mt-1 break-words text-sm text-slate-500">Consecutive attendance</p></div>
        </div>
        <div class="flex min-w-0 items-center gap-4 py-4 sm:pl-5 sm:pr-5 xl:py-2 xl:pl-6 xl:pr-6">
          <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-emerald-100 text-emerald-600"><svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2A9 9 0 1112 3a9 9 0 019 9z" /></svg></span>
          <div class="min-w-0"><p class="text-sm text-slate-500">On-Time Rate</p><h3 id="onTimeRate" class="mt-1 whitespace-nowrap text-2xl font-bold text-emerald-600">{{ $attendanceStatistics['on_time_rate'] }}</h3><p class="mt-1 break-words text-sm text-slate-500">This month</p></div>
        </div>
        <div class="flex min-w-0 items-center gap-4 py-4 sm:pr-5 xl:py-2 xl:pl-6 xl:pr-6">
          <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-blue-100 text-blue-600"><svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m7-3A10 10 0 1112 2a10 10 0 0110 10z" /></svg></span>
          <div class="min-w-0"><p class="text-sm text-slate-500">Avg Check-In</p><h3 id="avgCheckIn" class="mt-1 whitespace-nowrap text-2xl font-bold text-blue-600">{{ $attendanceStatistics['average_check_in'] }}</h3><p class="mt-1 break-words text-sm text-slate-500">Average arrival time</p></div>
        </div>
        <div class="flex min-w-0 items-center gap-4 py-4 sm:pl-5 xl:py-2 xl:pl-6">
          <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-purple-100 text-purple-600"><svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-6m3 6V7m3 10v-3m3 7H6a2 2 0 01-2-2V5a2 2 0 012-2h12a2 2 0 012 2v14z" /></svg></span>
          <div class="min-w-0"><p class="text-sm text-slate-500">This Week</p><h3 id="weeklyWorkHours" class="mt-1 whitespace-nowrap text-2xl font-bold text-purple-600">{{ $attendanceStatistics['weekly_work_hours'] }}</h3><p class="mt-1 break-words text-sm text-slate-500">Total working hours</p></div>
        </div>
      </div>
    </article>

    <article class="min-w-0 rounded-3xl bg-white p-5 shadow-sm sm:p-6">
      <div class="mb-6 flex min-w-0 flex-col gap-4 xl:flex-row xl:items-center xl:justify-between">
        <div class="min-w-0">
          <p class="text-sm text-slate-500">Attendance History</p>
          <h2 class="mt-1 text-xl font-semibold text-slate-800">Recent Check-ins</h2>
          <p class="mt-1 text-sm text-slate-500">Last 5 saved attendance records</p>
        </div>
        @if (app()->environment(['local', 'development', 'testing']))
          <div class="flex w-full flex-wrap gap-2 xl:w-auto xl:shrink-0">
            <form method="POST" action="{{ route('attendance.reset-check-in') }}" data-confirm="Apakah Anda yakin ingin mereset Check In hari ini?" data-confirm-title="Reset Check In Hari Ini?" data-confirm-message="Data Check In hari ini akan dihapus agar Anda dapat melakukan Check In kembali." data-confirm-tone="danger" data-confirm-text="Ya, Reset Check In">
              @csrf
              @method('DELETE')
              <button id="resetCheckInBtn" type="submit" @disabled($todayRecord?->check_in_at === null) class="min-h-10 rounded-xl border border-amber-200 px-4 py-2 text-sm font-medium text-amber-700 transition hover:bg-amber-50 disabled:cursor-not-allowed disabled:opacity-50">Reset Check In</button>
            </form>
            <form method="POST" action="{{ route('attendance.reset-check-out') }}" data-confirm="Apakah Anda yakin ingin mereset Check Out hari ini?" data-confirm-title="Reset Check Out Hari Ini?" data-confirm-message="Data Check Out hari ini akan dihapus agar Anda dapat melakukan Check Out kembali." data-confirm-tone="danger" data-confirm-text="Ya, Reset Check Out">
              @csrf
              @method('DELETE')
              <button id="resetCheckOutBtn" type="submit" @disabled($todayRecord?->check_in_at === null || $todayRecord?->check_out_at === null) class="min-h-10 rounded-xl border border-rose-200 px-4 py-2 text-sm font-medium text-rose-700 transition hover:bg-rose-50 disabled:cursor-not-allowed disabled:opacity-50">Reset Check Out</button>
            </form>
          </div>
        @endif
      </div>
      @if (session('status'))
        <p role="status" class="mb-4 rounded-xl bg-emerald-50 px-4 py-3 text-sm text-emerald-700">{{ session('status') }}</p>
      @endif
      <div id="recentAttendanceRecords" class="min-w-0 space-y-3">
        @forelse ($recentRecords as $record)
          @php
            $recordStatus = $record->check_in_at && $record->check_out_at
                ? 'Completed'
                : ($record->check_in_at ? 'Checked In' : ($record->check_out_at ? 'Check Out only' : 'No Check In'));
            $statusClass = $record->check_in_at && $record->check_out_at
                ? 'bg-emerald-100 text-emerald-700'
                : ($record->check_in_at ? 'bg-indigo-100 text-indigo-700' : 'bg-slate-100 text-slate-600');
          @endphp
          <article data-attendance-date="{{ $record->attendance_date->format('Y-m-d') }}" class="grid min-w-0 gap-3 rounded-2xl border border-slate-200 p-4 sm:grid-cols-[minmax(0,1fr)_auto_auto] sm:items-center">
            <div class="min-w-0">
              <p class="recent-weekday break-words font-semibold text-slate-800">{{ $record->attendance_date->format('l') }}</p>
              <p class="recent-date text-sm text-slate-500">{{ $record->attendance_date->format('d M Y') }}</p>
              @if ($record->location)<p class="recent-location max-w-full break-words text-sm text-slate-500">{{ $record->location }}</p>@endif
            </div>
            <div class="min-w-0 sm:text-center">
              <p class="recent-times whitespace-nowrap font-medium text-slate-700">{{ $record->check_in_at?->format('H:i') ?? '--:--' }} ? {{ $record->check_out_at?->format('H:i') ?? '--:--' }}</p>
              <p class="text-xs text-slate-500">Check In / Check Out</p>
            </div>
            <div class="sm:text-right"><span class="recent-status inline-flex whitespace-nowrap rounded-full px-3 py-1 text-sm font-medium {{ $statusClass }}">{{ $recordStatus }}</span></div>
          </article>
        @empty
          <div id="noRecentAttendance" class="flex min-h-32 items-center justify-center rounded-2xl border border-dashed border-slate-300 p-6 text-center text-sm text-slate-500">No attendance records yet.</div>
        @endforelse
      </div>
    </article>
  </section>
  <!-- Confirmation Modal Overlay -->
      <div
        id="attendanceModal"
        class="fixed inset-0 z-50 hidden flex items-center justify-center bg-black/60 p-4 backdrop-blur-sm transition-opacity"
      >
        <div
          class="flex w-full max-w-3xl flex-col overflow-hidden rounded-3xl bg-white shadow-2xl md:flex-row"
        >
          <!-- Kolom Kiri: Preview Kamera -->
          <div
            class="relative flex w-full flex-col items-center justify-center bg-slate-100 p-4 md:w-1/2 md:border-r border-slate-200"
          >
            <canvas id="snapshotCanvas" class="hidden"></canvas>
            <img
              id="snapshotPreview"
              alt="Snapshot"
              class="h-64 w-full rounded-2xl object-cover shadow-sm md:h-full border border-slate-200"
            />
          </div>
  
          <!-- Kolom Kanan: Detail & Aksi -->
          <div class="flex w-full flex-col justify-between p-6 md:w-1/2">
            <div>
              <h3 id="modalTitle" class="mb-5 text-xl font-bold text-slate-800">
                Confirm Attendance
              </h3>
              <div class="space-y-4">
                <div>
                  <p
                    class="text-xs font-semibold uppercase tracking-wider text-slate-500"
                  >
                    Current Time
                  </p>
                  <p
                    id="modalTimeText"
                    class="text-lg font-medium text-slate-800"
                  >
                    --:--
                  </p>
                </div>
                <div>
                  <p
                    class="text-xs font-semibold uppercase tracking-wider text-slate-500"
                  >
                    Location
                  </p>
                  <p
                    id="modalLocationText"
                    class="text-sm font-medium text-slate-800 leading-snug"
                  >
                    Detecting...
                  </p>
                </div>
                <div>
                  <p
                    class="text-xs font-semibold uppercase tracking-wider text-slate-500"
                  >
                    User Profile
                  </p>
                  <p class="text-sm font-medium text-slate-800">{{ auth()->user()->name }}</p>
                  <p class="text-sm text-slate-500">{{ auth()->user()->job_title ?: '—' }}</p>
                </div>
              </div>
            </div>
  
            <!-- Action Buttons -->
            <div class="mt-8 flex gap-3">
              <button
                id="cancelModalBtn"
                class="flex-1 rounded-xl border border-slate-200 py-3 font-semibold text-slate-600 transition hover:bg-slate-50"
              >
                Cancel
              </button>
              <button
                id="confirmModalBtn"
                class="flex-1 rounded-xl bg-indigo-600 py-3 font-semibold text-white shadow-md transition hover:bg-indigo-700"
              >
                Confirm
              </button>
            </div>
          </div>
        </div>
      </div>
@endsection

@push('scripts')
  @php
    $attendanceValidation = [
        'officeConfigured' => $officeCoordinates !== null,
        'officeLatitude' => $officeCoordinates['latitude'] ?? null,
        'officeLongitude' => $officeCoordinates['longitude'] ?? null,
        'radiusMeters' => $officeRadiusMeters,
        'gpsWarningAccuracyMeters' => $gpsWarningAccuracyMeters,
        'networkConfigured' => $networkConfigured,
        'networkIpDetected' => $networkIpDetected,
        'networkAllowed' => $networkAllowed,
        'requestIp' => $requestIp,
        'detectedPublicIp' => $detectedPublicIp,
        'officeAllowedIp' => $networkDebug['office_allowed_ip'] ?? config('attendance.office_allowed_ip'),
        'networkLocalEnvironment' => $networkLocalEnvironment,
        'networkFailureMessage' => $networkFailureMessage,
    ];
    $todayAttendance = [
        'check_in_at' => $todayRecord?->check_in_at?->format('Y-m-d H:i:s'),
        'check_out_at' => $todayRecord?->check_out_at?->format('Y-m-d H:i:s'),
    ];
  @endphp
  <script>
      window.attendanceValidation = @json($attendanceValidation);
      window.todayAttendance = @json($todayAttendance);
    </script>
  <script src="{{ asset('js/laravel/Check.js') }}?v=check-in-layout-1"></script>
@endpush
