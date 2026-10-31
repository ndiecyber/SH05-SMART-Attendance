@extends('layouts.app')

@section('title', 'Smart Attendance Dashboard')

@push('head-scripts')
  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
@endpush

@section('content')
          <!-- Header -->
          

          <!-- Content -->
          <section class="space-y-6 p-4 sm:p-6">
            @if (session('attendanceImportSummary'))
              @php
                $importSummary = session('attendanceImportSummary');
              @endphp
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
            <!-- filter -->
            <div
              class="flex flex-col gap-3 rounded-2xl bg-white p-4 shadow-sm md:flex-row md:items-center md:justify-between"
            >
              <!-- Left : Filters -->
              <div class="flex flex-wrap items-center gap-3">
                <!-- Period -->
                <div class="flex w-full items-center gap-3 sm:w-auto">
                  <label
                    for="filterPeriod"
                    class="text-sm font-medium whitespace-nowrap text-slate-600"
                  >
                    Period
                  </label>

                  <select
                    id="filterPeriod"
                    class="min-w-0 flex-1 appearance-none rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 pr-10 text-sm font-medium text-slate-700 shadow-sm outline-none transition hover:border-indigo-300 hover:bg-white focus:border-indigo-500 focus:bg-white focus:ring-4 focus:ring-indigo-100 sm:min-w-40 sm:flex-none"
                  >
                    <option>This Week</option>
                    <option @selected($selectedPeriod === 'This Month')>This Month</option>
                    <option>Last Month</option>
                  </select>
                  <span class="pointer-events-none -ml-10 mr-3 text-slate-400" aria-hidden="true"><svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M5.22 7.22a.75.75 0 0 1 1.06 0L10 10.94l3.72-3.72a.75.75 0 1 1 1.06 1.06l-4.25 4.25a.75.75 0 0 1-1.06 0L5.22 8.28a.75.75 0 0 1 0-1.06Z" clip-rule="evenodd" /></svg></span>
                </div>
              </div>

              <!-- Right : Import Button -->
              <button
                id="openImportModal"
                type="button"
                class="flex items-center justify-center gap-2 rounded-xl bg-indigo-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-indigo-700"
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
                    d="M12 4v12m0 0l-4-4m4 4l4-4M4 20h16"
                  />
                </svg>

                Import CSV
              </button>
            </div>

            @include('attendance.partials.import-modal', ['returnTo' => 'attendance.index'])

            <!-- Main grid -->
            <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
              <!-- Total Present -->
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
                      d="M9 12l2 2 4-4m6 2A9 9 0 1112 3a9 9 0 019 9z"
                    />
                  </svg>
                </div>

                <div>
                  <p class="text-sm text-slate-500">Total Present</p>
                  <h2
                    id="totalPresent"
                    class="text-3xl font-bold text-emerald-600"
                  >
                    {{ $dashboardData['summary']['present'] }}
                  </h2>
                  <p class="text-sm text-slate-500">
                    <span data-period-label>{{ $dashboardData['summary']['period'] }}</span>
                  </p>
                </div>
              </div>

              <!-- Total Late -->
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
                      d="M12 8v4l2 2m8-2A10 10 0 1112 2a10 10 0 0110 10z"
                    />
                  </svg>
                </div>

                <div>
                  <p class="text-sm text-slate-500">Total Late</p>
                  <h2 id="totalLate" class="text-3xl font-bold text-amber-500">
                    {{ $dashboardData['summary']['late'] }}
                  </h2>
                  <p class="text-sm text-slate-500"><span data-period-label>{{ $dashboardData['summary']['period'] }}</span></p>
                </div>
              </div>

              <!-- Total Absent -->
              <div
                class="flex items-center gap-4 rounded-3xl bg-white p-5 shadow-sm"
              >
                <div
                  class="flex h-14 w-14 items-center justify-center rounded-2xl bg-red-100 text-red-500"
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
                      d="M6 18L18 6M6 6l12 12"
                    />
                  </svg>
                </div>

                <div>
                  <p class="text-sm text-slate-500">Total Absent</p>
                  <h2 id="totalAbsent" class="text-3xl font-bold text-red-500">
                    {{ $dashboardData['summary']['absent'] }}
                  </h2>
                  <p class="text-sm text-slate-500">
                    <span data-period-label>{{ $dashboardData['summary']['period'] }}</span>
                  </p>
                </div>
              </div>

              <!-- Attendance Rate -->
              <div
                class="flex items-center gap-4 rounded-3xl bg-gradient-to-br from-indigo-600 to-blue-500 p-5 text-white shadow-sm"
              >
                <div
                  class="flex h-14 w-14 items-center justify-center rounded-2xl bg-white/20"
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
                      d="M9 17v-6m3 6V7m3 10v-3m3 7H6a2 2 0 01-2-2V5a2 2 0 012-2h12a2 2 0 012 2v14a2 2 0 01-2 2z"
                    />
                  </svg>
                </div>

                <div>
                  <p class="text-sm text-indigo-100">Attendance Rate</p>
                  <h2 id="attendanceRate" class="text-3xl font-bold">{{ $dashboardData['summary']['rate'] }}</h2>
                  <p class="text-sm text-indigo-100">
                    <span data-period-label>{{ $dashboardData['summary']['period'] }}</span>
                  </p>
                </div>
              </div>
            </div>

            <div class="grid gap-6 lg:grid-cols-2">
              <!-- ================= LEFT : ATTENDANCE CALENDAR ================= -->
              <div class="rounded-3xl bg-white p-6 shadow-sm">
                <!-- Header -->
                <div class="flex items-center justify-between">
                  <div>
                    <p class="text-sm text-slate-500">Attendance Calendar</p>

                    <h3
                      id="calendarTitle"
                      class="mt-1 text-xl font-semibold text-slate-800"
                    >
                      July 2026
                    </h3>
                  </div>

                  <div class="flex gap-2">
                    <button
                      id="prevMonth"
                      class="rounded-xl border border-slate-200 px-3 py-2 hover:bg-slate-100"
                    >
                      â—€
                    </button>

                    <button
                      id="nextMonth"
                      class="rounded-xl border border-slate-200 px-3 py-2 hover:bg-slate-100"
                    >
                      â–¶
                    </button>
                  </div>
                </div>

                <!-- Week -->
                <div
                  class="mt-6 grid grid-cols-7 gap-2 text-center text-xs font-semibold uppercase tracking-wide text-slate-400"
                >
                  <div>Sun</div>
                  <div>Mon</div>
                  <div>Tue</div>
                  <div>Wed</div>
                  <div>Thu</div>
                  <div>Fri</div>
                  <div>Sat</div>
                </div>

                <!-- Calendar -->
                <div
                  id="attendanceCalendar"
                  class="mt-3 grid grid-cols-7 gap-2"
                ></div>

                <!-- Legend -->
                <div class="mt-14 flex flex-wrap gap-5 text-sm">
                  <div class="flex items-center gap-2">
                    <span class="h-3 w-3 rounded-full bg-emerald-500"></span>
                    Present
                  </div>

                  <div class="flex items-center gap-2">
                    <span class="h-3 w-3 rounded-full bg-amber-500"></span>
                    Late
                  </div>

                  <div class="flex items-center gap-2">
                    <span class="h-3 w-3 rounded-full bg-red-500"></span>
                    Absent
                  </div>
                </div>
              </div>

              <!-- ================= RIGHT : MONTHLY TREND ================= -->
              <div class="rounded-3xl bg-white p-6 shadow-sm">
                <div class="flex items-center justify-between">
                  <div>
                    <p class="text-sm text-slate-500">Monthly Trend</p>
                    <h3 class="mt-1 text-xl font-semibold text-slate-800">
                      Attendance Summary
                    </h3>
                  </div>
                </div>

                <div class="mt-6 h-[420px]">
                  <canvas id="monthlyTrendChart"></canvas>
                  <div id="monthlyTrendEmpty" class="hidden h-full flex-col items-center justify-center rounded-2xl border border-dashed border-slate-200 text-center text-sm text-slate-500">
                    <span class="mb-2 text-2xl text-slate-300" aria-hidden="true">â–¥</span>
                    No attendance records for this period yet.
                  </div>
                </div>
                <div
                  class="mt-5 flex items-center justify-center gap-8 text-sm"
                >
                  <div class="flex items-center gap-2">
                    <span class="h-3 w-3 rounded-full bg-emerald-500"></span>
                    <span class="text-slate-600">Present</span>
                  </div>

                  <div class="flex items-center gap-2">
                    <span class="h-3 w-3 rounded-full bg-amber-500"></span>
                    <span class="text-slate-600">Late</span>
                  </div>

                  <div class="flex items-center gap-2">
                    <span class="h-3 w-3 rounded-full bg-red-500"></span>
                    <span class="text-slate-600">Absent</span>
                  </div>
                </div>
              </div>
            </div>
            <!-- bottom content -->
            <div class="rounded-3xl bg-white p-6 shadow-sm">
              <!-- Header -->
              <div class="mb-5">
                <p class="text-sm text-slate-500">Attendance History</p>
                <h3 class="mt-1 text-xl font-semibold text-slate-800">
                  Last 16 Attendance Records
                </h3>
              </div>

              <div class="overflow-hidden">
                <div id="historySlider" class="flex transition-transform duration-500 ease-in-out">
                  <?php if ($historyRows->isNotEmpty()) { ?>
                    <?php foreach ($historyRows->chunk(8) as $page) { ?>
                    <div class="min-w-full space-y-3">
                      <?php foreach ($page as $historyRow) { ?>
                        @php
                          $record = $historyRow['record'];
                          $historyStatus = $historyRow['status'] === 'late'
                              ? 'Late'
                              : ($record->check_out_at ? 'Present' : 'Checked In');
                          $historyStatusClass = $historyRow['status'] === 'late'
                              ? 'bg-amber-100 text-amber-700'
                              : 'bg-emerald-100 text-emerald-700';
                        @endphp
                        <div class="flex flex-col justify-between gap-3 rounded-xl bg-slate-50 px-4 py-3 shadow-sm sm:flex-row sm:items-center">
                          <div class="min-w-0">
                            <p class="font-medium text-slate-800">{{ $record->attendance_date->format('d M Y') }}</p>
                            <p class="text-xs text-slate-500">{{ $record->check_in_at?->format('H:i') ?? 'â€”' }} â€“ {{ $record->check_out_at?->format('H:i') ?? 'â€”' }}</p>
                          </div>
                          <div class="flex shrink-0 items-center gap-3">
                            <span class="text-xs text-slate-500">{{ $historyRow['duration'] ?? 'In progress' }}</span>
                            <span class="rounded-full px-3 py-1 text-sm {{ $historyStatusClass }}">{{ $historyStatus }}</span>
                          </div>
                        </div>
                      <?php } ?>
                    </div>
                    <?php } ?>
                  <?php } else { ?>
                    <div class="min-w-full rounded-xl border border-dashed border-slate-300 px-4 py-8 text-center text-sm text-slate-500">No attendance records yet.</div>
                  <?php } ?>
                </div>
              </div>
              @if ($historyRows->count() > 8)
                <div class="mt-6 flex items-center justify-center gap-4">
                  <button id="historyPrev" type="button" aria-label="Previous attendance records" class="flex h-10 w-10 items-center justify-center rounded-full border border-slate-200 transition hover:bg-slate-100">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" /></svg>
                  </button>
                  <div id="historyDots" class="flex gap-2">
                    @foreach ($historyRows->chunk(8) as $page)
                      <span class="h-2.5 w-2.5 rounded-full {{ $loop->first ? 'bg-indigo-600' : 'bg-slate-300' }}"></span>
                    @endforeach
                  </div>
                  <button id="historyNext" type="button" aria-label="Next attendance records" class="flex h-10 w-10 items-center justify-center rounded-full border border-slate-200 transition hover:bg-slate-100">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                  </button>
                </div>
              @endif
            </div>
          </section>
        
@endsection

@push('scripts')
  <script>
      window.smartAttendanceData = @json($dashboardData);
      window.attendanceToday = @json($attendanceToday);
      window.attendanceDataUrl = @json(route('attendance.data'));
    </script>
  <script src="{{ asset('js/laravel/attendance.js') }}?v=attendance-data-3"></script>
@endpush
