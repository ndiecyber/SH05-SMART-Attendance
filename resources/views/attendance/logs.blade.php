@extends('layouts.app')

@section('title', 'Smart Attendance Dashboard')

@section('content')
          <!-- Header -->
          

          <!-- Content -->
          <section class="space-y-6 p-4 sm:p-6">
            <div
              class="flex flex-col gap-3 rounded-2xl bg-white p-2 shadow-sm md:flex-row md:items-center md:justify-between"
            >
              <!-- Left : Legend -->

              <div class="pl-4 flex flex-wrap gap-5">
                <h1 class="text-2xl font-semibold text-slate-800">
                  Attendance Log
                </h1>
              </div>

              <!-- Right : Add Button -->
              <div class="flex flex-wrap items-center gap-3">
                <!-- Export PDF -->
                <button
                  id="triggerBtn"
                  onclick="ubahTekspdf()"
                  class="flex items-center justify-center gap-2 rounded-xl border bg-slate-50 px-4 py-2 text-sm font-medium text-slate-700 shadow-sm transition hover:bg-slate-200"
                >
                  <svg
                    xmlns="http://www.w3.org/2000/svg"
                    class="h-5 w-5"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke="currentColor"
                  >
                    <path
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      stroke-width="2"
                      d="M12 3v12m0 0l-4-4m4 4l4-4M4 17v2a2 2 0 002 2h12a2 2 0 002-2v-2"
                    />
                  </svg>

                  Export PDF
                </button>

                <!-- Export CSV -->
                <button
                  id="csvBtn"
                  type="button"
                  class="flex items-center justify-center gap-2 rounded-xl bg-indigo-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-indigo-700"
                >
                  <svg
                    xmlns="http://www.w3.org/2000/svg"
                    class="h-5 w-5"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke="currentColor"
                  >
                    <path
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      stroke-width="2"
                      d="M12 3v12m0 0l-4-4m4 4l4-4M4 17v2a2 2 0 002 2h12a2 2 0 002-2v-2"
                    />
                  </svg>

                  Export CSV
                </button>

                <button
                  id="openImportModal"
                  type="button"
                  class="flex items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-medium text-slate-700 transition hover:bg-slate-50"
                >
                  Import CSV
                </button>

                <!-- Container Toast Notification (Atas Tengah) -->
                <div
                  class="fixed top-5 left-1/2 -translate-x-1/2 z-50 pointer-events-none"
                >
                  <div
                    id="toast"
                    class="pointer-events-auto flex items-center gap-3 bg-gray-900 text-white px-4 py-3 rounded-xl shadow-xl border border-gray-800 transition-all duration-300 -translate-y-10 opacity-0 hidden"
                  >
                    <!-- Icon Status (Success Checkmark) -->
                    <span
                      class="flex h-6 w-6 items-center justify-center rounded-full bg-emerald-500/20 text-emerald-400"
                    >
                      <svg
                        xmlns="http://www.w3.org/2000/svg"
                        class="h-4 w-4"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor"
                      >
                        <path
                          stroke-linecap="round"
                          stroke-linejoin="round"
                          stroke-width="2.5"
                          d="M5 13l4 4L19 7"
                        />
                      </svg>
                    </span>

                    <!-- Teks Pesan -->
                    <p class="text-sm font-medium">
                      Mengexport file:<span id="teksPopup">...</span>
                    </p>

                    <!-- Tombol Tutup (X) -->
                    <button
                      id="closeToastBtn"
                      class="ml-2 text-gray-400 hover:text-white transition"
                    >
                      <svg
                        xmlns="http://www.w3.org/2000/svg"
                        class="h-4 w-4"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor"
                      >
                        <path
                          stroke-linecap="round"
                          stroke-linejoin="round"
                          stroke-width="2"
                          d="M6 18L18 6M6 6l12 12"
                        />
                      </svg>
                    </button>
                  </div>
                </div>
              </div>
            </div>

            @include('attendance.partials.import-modal', ['returnTo' => 'attendance.logs'])

            @if (session('attendanceImportSummary'))
              @php($importSummary = session('attendanceImportSummary'))
              <div class="rounded-2xl border border-indigo-100 bg-indigo-50 p-4 text-sm text-indigo-950" role="status">
                <p class="font-semibold">CSV import selesai: {{ $importSummary['imported'] }} record ditambahkan, {{ $importSummary['skipped'] }} dilewati.</p>
                @if (!empty($importSummary['errors']))
                  <details class="mt-2">
                    <summary class="cursor-pointer font-medium">Lihat baris yang dilewati</summary>
                    <ul class="mt-2 max-h-40 space-y-1 overflow-y-auto pl-5">
                      @foreach ($importSummary['errors'] as $importError)
                        <li class="list-disc">Baris {{ $importError['line'] }}: {{ $importError['reason'] }}</li>
                      @endforeach
                    </ul>
                    @if ($importSummary['error_list_truncated'])
                      <p class="mt-2">Hanya 50 alasan pertama yang ditampilkan.</p>
                    @endif
                  </details>
                @endif
              </div>
            @endif
            @if ($errors->has('file'))
              <div class="rounded-2xl border border-rose-200 bg-rose-50 p-4 text-sm text-rose-700" role="alert">
                {{ $errors->first('file') }}
              </div>
            @endif

            <!-- Main grid -->
            <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
              <!-- event taken -->
              <div
                class="flex items-center gap-4 rounded-3xl bg-white p-5 shadow-sm"
              >
                <div
                  class="flex h-14 w-14 items-center justify-center rounded-2xl bg-blue-100 text-blue-600"
                >
                  <svg
                    xmlns="http://www.w3.org/2000/svg"
                    class="h-7 w-7"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke="currentColor"
                  >
                    <path
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      stroke-width="2"
                      d="M6 3v6m0 3v9M12 3v3m0 3v12M18 3v10m0 3v5M3 6h18M3 18h18"
                    />
                  </svg>
                </div>

                <div>
                  <p class="text-sm text-slate-500">Total Events</p>
                  <h2 id="totalEvents" class="text-3xl font-bold text-blue-600">
                    64
                  </h2>
                  <p class="text-sm text-slate-500">last 14 days</p>
                </div>
              </div>

              <!-- ontime -->
              <div
                class="flex items-center gap-4 rounded-3xl bg-white p-5 shadow-sm"
              >
                <div
                  class="flex h-14 w-14 items-center justify-center rounded-2xl bg-emerald-100 text-emerald-600"
                >
                  <svg
                    xmlns="http://www.w3.org/2000/svg"
                    class="h-7 w-7"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke="currentColor"
                  >
                    <path
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      stroke-width="2"
                      d="M12 21C7.03 21 3 16.97 3 12S7.03 3 12 3s9 4.03 9 9-4.03 9-9 9zm-3-9l2 2 4-4"
                    />
                  </svg>
                </div>

                <div>
                  <p class="text-sm text-slate-500">On-time Check-ins</p>
                  <h2
                    id="ontimeCheckins"
                    class="text-3xl font-bold text-emerald-600"
                  >
                    10
                  </h2>
                  <p class="text-sm text-slate-500">before 09:00</p>
                </div>
              </div>

              <!-- late -->
              <div
                class="flex items-center gap-4 rounded-3xl bg-white p-5 shadow-sm"
              >
                <div
                  class="flex h-14 w-14 items-center justify-center rounded-2xl bg-amber-100 text-amber-500"
                >
                  <svg
                    xmlns="http://www.w3.org/2000/svg"
                    class="h-7 w-7"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke="currentColor"
                  >
                    <path
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      stroke-width="2"
                      d="M12 8v4l3 3m7-3A10 10 0 1112 2a10 10 0 0110 10z"
                    />
                  </svg>
                </div>

                <div>
                  <p class="text-sm text-slate-500">Late Check-ins</p>
                  <h2
                    id="lateCheckins"
                    class="text-3xl font-bold text-amber-500"
                  >
                    3
                  </h2>
                  <p class="text-sm text-slate-500">after 09:00</p>
                </div>
              </div>

              <!-- anomaly -->
              <div
                class="flex items-center gap-4 rounded-3xl bg-white p-5 shadow-sm"
              >
                <div
                  class="flex h-14 w-14 items-center justify-center rounded-2xl bg-red-100 text-red-600"
                >
                  <svg
                    xmlns="http://www.w3.org/2000/svg"
                    class="h-7 w-7"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke="currentColor"
                  >
                    <path
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      stroke-width="2"
                      d="M12 9v4"
                    />
                    <path
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      stroke-width="2"
                      d="M12 17h.01"
                    />
                    <path
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      stroke-width="2"
                      d="M10.3 3.9L1.8 18.2A2 2 0 003.5 21h17a2 2 0 001.7-2.8L13.7 3.9a2 2 0 00-3.4 0z"
                    />
                  </svg>
                </div>

                <div>
                  <p class="text-sm text-slate-500">Anomaly Flags</p>
                  <h2 id="anomalyFlags" class="text-3xl font-bold text-red-600">
                    1
                  </h2>
                  <p class="text-sm text-slate-500">Requires review</p>
                </div>
              </div>
            </div>

            <div class="grid gap-6 lg:grid-cols-3">
              <!-- ================= LEFT ================= -->
              <div class="rounded-3xl bg-white p-6 shadow-sm lg:col-span-3">
                <div class="flex items-center justify-between">
                  <div>
                    <p class="text-sm text-slate-500">Last 14 Days</p>
                    <h3 class="mt-1 text-xl font-semibold text-slate-800">
                      Activity Trend
                    </h3>
                  </div>
                </div>

                <!-- Chart -->
                <div class="mt-6 h-80">
                  <canvas
                    id="activityTrendChart"
                    class="h-full w-full block"
                  ></canvas>
                </div>

                <div class="mt-6">
                  <p class="mb-3 text-sm font-medium text-slate-700">Daily attendance</p>
                  <div id="last14DaysList" class="grid grid-cols-1 gap-2 sm:grid-cols-2 lg:grid-cols-7"></div>
                </div>

                <!-- Legend -->
                <div class="mt-6 flex flex-wrap gap-6 text-sm">
                  <div class="flex items-center gap-2">
                    <span class="h-3 w-3 rounded-full bg-emerald-500"></span>
                    Check In
                  </div>

                  <div class="flex items-center gap-2">
                    <span class="h-3 w-3 rounded-full bg-indigo-500"></span>
                    Check Out
                  </div>

                </div>
              </div>

            </div>

            <!-- bottom content -->
            <div class="rounded-3xl bg-white p-5 shadow-sm">
              <div class="flex flex-col gap-4 xl:flex-row xl:items-end">
                <!-- Search -->
                <div class="flex-1">
                  <label
                    for="searchPermission"
                    class="mb-2 block text-sm font-medium text-slate-700"
                  >
                    Search
                  </label>

                  <input
                    id="searchPermission"
                    type="text"
                    placeholder="Search by ID ,..."
                    class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm outline-none transition focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100"
                  />
                </div>

                <div class="grid min-w-[0px] grid-cols-2 gap-3">
                  <div>
                    <label
                      for="startDate"
                      class="mb-2 block text-sm font-medium text-slate-700"
                    >
                      From
                    </label>

                    <input
                      type="date"
                      id="startDate"
                      class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm outline-none transition focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100"
                    />
                  </div>

                  <div>
                    <label
                      for="endDate"
                      class="mb-2 block text-sm font-medium text-slate-700"
                    >
                      To
                    </label>

                    <input
                      type="date"
                      id="endDate"
                      class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm outline-none transition focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100"
                    />
                  </div>
                </div>

                <!-- evnt -->
                <div class="min-w-[150px]">
                  <label
                    for="filterEvent"
                    class="mb-2 block text-sm font-medium text-slate-700"
                  >
                    Event
                  </label>

                  <select
                    id="filterEvent"
                    class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm outline-none transition focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100"
                  >
                    <option value="">All Event</option>
                    <option value="checkIn">Check in</option>
                    <option value="checkOut">Check out</option>
                    <option value="break">Break</option>
                    <option value="other">Other</option>
                  </select>
                </div>

                <!-- verify Type -->
                <div class="min-w-[120px]">
                  <label
                    for="filterType"
                    class="mb-2 block text-sm font-medium text-slate-700"
                  >
                    Verify Type
                  </label>

                  <select
                    id="filterType"
                    class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm outline-none transition focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100"
                  >
                    <option value="">All Types</option>
                    <option value="gps">GPS</option>
                    <option value="network">Network</option>
                    <option value="photo">Photo</option>
                    <option value="other">Other</option>
                  </select>
                </div>

                <!-- Status -->
                <div class="min-w-[120px]">
                  <label
                    for="filterStatus"
                    class="mb-2 block text-sm font-medium text-slate-700"
                  >
                    Status
                  </label>

                  <select
                    id="filterStatus"
                    class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm outline-none transition focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100"
                  >
                    <option value="">All Status</option>
                    <option value="present">Present</option>
                    <option value="late">Late</option>
                    <option value="absent">Absent</option>
                  </select>
                </div>

                <!-- location -->
                <div class="min-w-[120px]">
                  <label
                    for="filterLocation"
                    class="mb-2 block text-sm font-medium text-slate-700"
                  >
                    Location
                  </label>

                  <select
                    id="filterLocation"
                    class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm outline-none transition focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100"
                  >
                    <option value="">All Locations</option>
                  </select>
                </div>

                <!-- Reset Button -->
                <button
                  id="resetFilter"
                  type="button"
                  class="inline-flex items-center justify-center rounded-xl border border-slate-200 px-5 py-2.5 text-sm font-medium text-slate-600 transition hover:border-red-200 hover:bg-red-50 hover:text-red-600"
                >
                  Reset
                </button>
              </div>
            </div>

            <div class="rounded-3xl bg-white p-6 shadow-sm">
              <!-- Header -->
              <div
                class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between"
              >
                <div>
                  <p class="text-sm text-slate-500">History</p>
                  <h2 class="text-2xl font-semibold text-slate-800">
                    Log Entries
                  </h2>
                </div>

                <span
                  class="inline-flex items-center rounded-full bg-slate-100 px-3 py-1 text-sm font-medium text-slate-600"
                >
                  <span id="attendanceEventCount">0 Events</span>
                </span>
              </div>

              <!-- Table -->
              <div class="overflow-x-auto">
                <table class="min-w-full">
                  <thead class="border-b border-slate-200">
                    <tr class="text-left text-sm font-semibold text-slate-600">
                      <th class="px-4 py-3">Time</th>
                      <th class="px-4 py-3">Event</th>
                      <th class="px-4 py-3">Location / Device</th>
                      <th class="px-4 py-3">IP Address</th>
                      <th class="px-4 py-3">Verify</th>
                      <th class="px-4 py-3">Status</th>
                      <th class="px-4 py-3 text-center">Action</th>
                    </tr>
                  </thead>

                  <tbody
                    id="tableBody"
                    class="divide-y divide-slate-100 text-sm"
                  >
                    <!-- Baris akan dirender otomatis oleh Javascript -->
                  </tbody>
                </table>
                <!-- Pagination -->
                <div
                  class="mt-6 flex flex-col gap-3 border-t border-slate-200 pt-4 sm:flex-row sm:items-center sm:justify-between"
                >
                  <!-- Info -->
                  <p class="text-sm text-slate-500">
                    Showing <span id="startItem">1</span> -
                    <span id="endItem">0</span> of
                    <span id="totalItems">0</span> log entries
                  </p>

                  <!-- Controls -->
                  <div class="flex items-center justify-end gap-2">
                    <button
                      id="prevPage"
                      class="rounded-xl border border-slate-200 px-4 py-2 text-sm font-medium text-slate-600 transition hover:bg-slate-100 disabled:cursor-not-allowed disabled:opacity-50"
                      disabled
                    >
                      â† Previous
                    </button>

                    <span
                      id="pageIndicator"
                      class="rounded-xl bg-slate-100 px-4 py-2 text-sm font-medium text-slate-700"
                    >
                      Page 1 of 2
                    </span>

                    <button
                      id="nextPage"
                      class="rounded-xl border border-slate-200 px-4 py-2 text-sm font-medium text-slate-600 transition hover:bg-slate-100"
                    >
                      Next â†’
                    </button>
                  </div>
                </div>
              </div>
            </div>

            <!-- INSIGHTS SECTION -->
            <div class="rounded-3xl bg-white p-6 shadow-sm">
              <div class="mb-6">
                <p class="text-sm text-slate-500">Insights</p>
                <h2 class="text-2xl font-semibold text-slate-800">
                  Attendance Pattern Summary
                </h2>
                <p class="mt-1 text-sm text-slate-500">
                  Generated from filtered attendance activity.
                </p>
              </div>

              <div class="grid gap-4 lg:grid-cols-3">
                <!-- Average Check In -->
                <div
                  class="h-full rounded-2xl border border-slate-200 bg-slate-50 p-5"
                >
                  <div class="flex items-start justify-between">
                    <div>
                      <p class="text-sm text-slate-500">Average Check-In</p>
                      <h3
                        id="avgCheckInTime"
                        class="mt-1 text-3xl font-bold text-slate-800"
                      >
                        --:--
                      </h3>
                      <p class="mt-2 text-sm text-slate-600">
                        Average arrival time for current filtered data.
                      </p>
                    </div>
                    <span
                      id="avgCheckInBadge"
                      class="rounded-full bg-emerald-100 px-3 py-1 text-xs font-semibold text-emerald-700"
                    >
                      On Time
                    </span>
                  </div>
                </div>

                <!-- Most Late Day -->
                <div
                  class="h-full rounded-2xl border border-slate-200 bg-slate-50 p-5"
                >
                  <div class="flex items-start justify-between">
                    <div>
                      <p class="text-sm text-slate-500">
                        Most Frequent Late Day
                      </p>
                      <h3
                        id="mostLateDay"
                        class="mt-1 text-2xl font-bold text-slate-800"
                      >
                        â€”
                      </h3>
                      <p
                        id="mostLateDayDesc"
                        class="mt-2 text-sm text-slate-600"
                      >
                        No dominant late-arrival pattern detected.
                      </p>
                    </div>
                    <span
                      id="mostLateDayBadge"
                      class="rounded-full bg-slate-200 px-3 py-1 text-xs font-semibold text-slate-600"
                    >
                      No Pattern
                    </span>
                  </div>
                </div>

                <!-- Anomaly Warning -->
                <div
                  id="anomalyCardContainer"
                  class="h-full rounded-2xl border border-amber-200 bg-amber-50 p-5"
                >
                  <div class="flex items-start justify-between">
                    <div>
                      <p
                        id="anomalyCardTitle"
                        class="text-sm font-medium text-amber-700"
                      >
                        Anomaly Event Warning
                      </p>
                      <h3
                        id="anomalyCardHeading"
                        class="mt-1 text-lg font-semibold text-slate-800"
                      >
                        0 suspicious verification detected
                      </h3>
                      <p
                        id="anomalyCardDesc"
                        class="mt-2 text-sm text-slate-600"
                      >
                        No anomaly flags detected in current filtered log.
                      </p>
                    </div>
                    <span
                      id="anomalyCardBadge"
                      class="rounded-full bg-amber-200 px-3 py-1 text-xs font-semibold text-amber-800"
                    >
                      Warning
                    </span>
                  </div>
                </div>
              </div>
            </div>
          </section>
        
@endsection

@push('scripts')
  <script>
      window.smartAttendanceLogData = @json($attendanceEvents);
      window.last14AttendanceData = @json($last14Days);
      window.attendanceExportUrl = @json(route('attendance.logs.export'));
    </script>
  <script src="{{ asset('js/attendance-log.js') }}?v=attendance-data-3"></script>
  <script src="{{ asset('js/laravel/attendance-import.js') }}?v=attendance-import-1"></script>
@endpush
