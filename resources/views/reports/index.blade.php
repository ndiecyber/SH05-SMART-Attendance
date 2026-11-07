@extends('layouts.app')

@section('title', 'Smart Attendance Dashboard')

@section('content')
<!-- Header -->


<!-- Content -->
<section class="space-y-6 p-4 sm:p-6">
  <div
    class="flex flex-col gap-3 rounded-2xl bg-white p-2 shadow-sm md:flex-row md:items-center md:justify-between">
    <!-- Left : Legend -->

    <div class="pl-4 flex flex-wrap gap-5">
      <h1 class="text-2xl font-semibold text-slate-800">Report</h1>
    </div>

    <!-- Right : Add Button -->
    <div class="flex flex-wrap items-center gap-3">
      <form id="reportPeriodForm" method="GET" action="{{ route('reports.index') }}" class="flex flex-wrap items-end gap-2">
        <div>
          <label for="reportPeriod" class="mb-1 block text-xs font-medium text-slate-500">Period</label>
          <select id="reportPeriod" name="period" class="rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-700 outline-none transition focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100">
            @foreach (['This Week', 'This Month', 'Last Month', 'Custom Date Range'] as $period)
            <option value="{{ $period }}" @selected($selectedPeriod===$period)>{{ $period }}</option>
            @endforeach
          </select>
        </div>
        <div id="customReportDates" class="{{ $selectedPeriod === 'Custom Date Range' ? 'flex' : 'hidden' }} items-end gap-2">
          <label class="text-xs text-slate-500">From<input id="reportStartDate" type="date" name="start_date" value="{{ $reportStartDate }}" class="mt-1 block rounded-xl border border-slate-200 px-2 py-2 text-sm" /></label>
          <label class="text-xs text-slate-500">To<input id="reportEndDate" type="date" name="end_date" value="{{ $reportEndDate }}" class="mt-1 block rounded-xl border border-slate-200 px-2 py-2 text-sm" /></label>
          <button id="applyReportDates" type="submit" class="rounded-xl border border-slate-200 px-3 py-2 text-sm text-slate-700 hover:bg-slate-50">Apply</button>
        </div>
      </form>
      <!-- Export PDF -->
      <button
        id="triggerBtn"
        onclick="
                    showToast(
                      'pdf',
                      'Attendance_Report.pdf',
                      'Mengeksport file:',
                    )
                  "
        class="flex items-center justify-center gap-2 rounded-xl border bg-slate-50 px-4 py-2 text-sm font-medium text-slate-700 shadow-sm transition hover:bg-slate-200">
        <svg
          xmlns="http://www.w3.org/2000/svg"
          class="h-5 w-5"
          fill="none"
          viewBox="0 0 24 24"
          stroke="currentColor">
          <path
            stroke-linecap="round"
            stroke-linejoin="round"
            stroke-width="2"
            d="M12 3v12m0 0l-4-4m4 4l4-4M4 17v2a2 2 0 002 2h12a2 2 0 002-2v-2" />
        </svg>
        Export PDF
      </button>

      <!-- Export CSV -->
      <button
        id="exportCsvBtn"
        type="button"
        class="flex items-center justify-center gap-2 rounded-xl bg-indigo-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-indigo-700">
        <svg
          xmlns="http://www.w3.org/2000/svg"
          class="h-5 w-5"
          fill="none"
          viewBox="0 0 24 24"
          stroke="currentColor">
          <path
            stroke-linecap="round"
            stroke-linejoin="round"
            stroke-width="2"
            d="M12 3v12m0 0l-4-4m4 4l4-4M4 17v2a2 2 0 002 2h12a2 2 0 002-2v-2" />
        </svg>
        Export CSV
      </button>
    </div>
  </div>

  <!-- Main grid -->
  <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
    <!-- Attendance Rate -->
    <div
      class="flex items-center gap-4 rounded-3xl bg-white p-5 shadow-sm">
      <div
        class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-blue-100 text-blue-600">
        <!-- Icon: Users / Calendar Check -->
        <svg
          xmlns="http://www.w3.org/2000/svg"
          class="h-7 w-7"
          fill="none"
          viewBox="0 0 24 24"
          stroke="currentColor">
          <path
            stroke-linecap="round"
            stroke-linejoin="round"
            stroke-width="2"
            d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
        </svg>
      </div>
      <div class="w-full">
        <p class="text-sm text-slate-500">Attendance Rate</p>
        <div class="flex items-baseline justify-between gap-2">
          <h2
            id="attendanceRate"
            class="text-3xl font-bold text-blue-600">
            {{ $reportData['summary']['attendance_rate'] }}
          </h2>
        </div>
        <!-- Indikator Tren -->
        <span
          class="inline-flex items-center text-xs font-semibold text-emerald-500">
          <svg
            class="mr-0.5 h-3.5 w-3.5"
            fill="none"
            stroke="currentColor"
            viewBox="0 0 24 24">
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2.5"
              d="M5 10l7-7 7 7M12 3v18" />
          </svg>
          {{ $reportData['summary']['expected_workdays'] }} scheduled days
        </span>
      </div>
    </div>

    <!-- Punctuality Rate -->
    <div
      class="flex items-center gap-4 rounded-3xl bg-white p-5 shadow-sm">
      <div
        class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-emerald-100 text-emerald-600">
        <!-- Icon: Check Circle / On Time -->
        <svg
          xmlns="http://www.w3.org/2000/svg"
          class="h-7 w-7"
          fill="none"
          viewBox="0 0 24 24"
          stroke="currentColor">
          <path
            stroke-linecap="round"
            stroke-linejoin="round"
            stroke-width="2"
            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
      </div>
      <div class="w-full">
        <p class="text-sm text-slate-500">Punctuality Rate</p>
        <div class="flex items-baseline justify-between gap-2">
          <h2
            id="punctualityRate"
            class="text-3xl font-bold text-emerald-600">
            {{ $reportData['summary']['punctuality_rate'] }}
          </h2>
        </div>
        <!-- Indikator Tren -->
        <span
          class="inline-flex items-center text-xs font-semibold text-emerald-500">
          <svg
            class="mr-0.5 h-3.5 w-3.5"
            fill="none"
            stroke="currentColor"
            viewBox="0 0 24 24">
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2.5"
              d="M5 10l7-7 7 7M12 3v18" />
          </svg>
          {{ $reportData['summary']['present'] }} on-time days
        </span>
      </div>
    </div>

    <!-- Total Work Hours -->
    <div
      class="flex items-center gap-4 rounded-3xl bg-white p-5 shadow-sm">
      <div
        class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-indigo-100 text-indigo-600">
        <!-- Icon: Clock -->
        <svg
          xmlns="http://www.w3.org/2000/svg"
          class="h-7 w-7"
          fill="none"
          viewBox="0 0 24 24"
          stroke="currentColor">
          <path
            stroke-linecap="round"
            stroke-linejoin="round"
            stroke-width="2"
            d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
      </div>
      <div class="w-full">
        <p class="text-sm text-slate-500">Total Work Hours</p>
        <div class="flex items-baseline justify-between gap-2">
          <h2
            id="totalWorkHours"
            class="text-3xl font-bold text-indigo-600">
            {{ $reportData['summary']['work_hours'] }}
          </h2>
        </div>
        <!-- Indikator Tren -->
        <span
          class="inline-flex items-center text-xs font-semibold text-emerald-600">
          <svg
            class="mr-0.5 h-3.5 w-3.5"
            fill="none"
            stroke="currentColor"
            viewBox="0 0 24 24">
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2.5"
              d="M5 10l7-7 7 7M12 3v18" />
          </svg>
          {{ $reportData['summary']['work_hours'] }} worked
        </span>
      </div>
    </div>

    <!-- Overtime Hours -->
    <div
      class="flex items-center gap-4 rounded-3xl bg-white p-5 shadow-sm">
      <div
        class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-amber-100 text-amber-500">
        <!-- Icon: Briefcase/Flame -->
        <svg
          xmlns="http://www.w3.org/2000/svg"
          class="h-7 w-7"
          fill="none"
          viewBox="0 0 24 24"
          stroke="currentColor">
          <path
            stroke-linecap="round"
            stroke-linejoin="round"
            stroke-width="2"
            d="M13 10V3L4 14h7v7l9-11h-7z" />
        </svg>
      </div>
      <div class="w-full">
        <p class="text-sm text-slate-500">Overtime Hours</p>
        <div class="flex items-baseline justify-between gap-2">
          <h2
            id="overtimeHours"
            class="text-3xl font-bold text-amber-500">
            {{ $reportData['summary']['overtime_hours'] }}
          </h2>
        </div>
        <!-- Indikator Tren (Contoh Penurunan) -->
        <span
          class="inline-flex items-center text-xs font-semibold text-rose-600">
          <svg
            class="mr-0.5 h-3.5 w-3.5"
            fill="none"
            stroke="currentColor"
            viewBox="0 0 24 24">
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2.5"
              d="M19 14l-7 7-7-7M12 21V3" />
          </svg>
          {{ $reportData['summary']['overtime_hours'] }} overtime
        </span>
      </div>
    </div>
  </div>

  <!-- DYNAMIC MENU TABS CONTAINER -->
  <div>
    <!-- Tab Navigation Buttons -->
    <div class="flex flex-wrap gap-2 text-sm font-semibold border-b border-slate-200 pb-3 overflow-x-auto scrollbar-hide" style="-webkit-overflow-scrolling: touch;">
      <button
        id="tab-overview"
        onclick="switchTab('overview')"
        class="tab-btn rounded-2xl px-5 py-2.5 transition shadow-sm bg-white text-indigo-600 border border-indigo-200 font-bold">
        Overview
      </button>

      <button
        id="tab-attendance"
        onclick="switchTab('attendance')"
        class="tab-btn rounded-2xl px-5 py-2.5 transition text-slate-600 border border-indigo-200 hover:bg-white/80">
        Attendance
      </button>

      <button
        id="tab-productivity"
        onclick="switchTab('productivity')"
        class="tab-btn rounded-2xl px-5 py-2.5 transition text-slate-600 border border-indigo-200 hover:bg-white/80">
        Productivity
      </button>

      <button
        id="tab-leave"
        onclick="switchTab('leave')"
        class="tab-btn rounded-2xl px-5 py-2.5 transition text-slate-600 border border-indigo-200 hover:bg-white/80">
        Leave & Permission
      </button>
    </div>

    <!-- Tab Content Panels -->
    <div class="mt-5">

      <!-- ================= TAB 1: OVERVIEW ================= -->
      <div id="panel-overview" class="tab-panel">
        <div class="grid gap-6 grid-cols-1 lg:grid-cols-3">
          <!-- LEFT -->
          <div class="rounded-3xl bg-white p-6 shadow-sm lg:col-span-2">
            <div class="flex items-center justify-between">
              <div>
                <h3 class="mt-1 text-xl font-semibold text-slate-800">
                  Daily Work Hours
                </h3>
              </div>
            </div>

            <!-- Chart -->
            <div class="mt-6 h-80">
              <canvas
                id="activityTrendChart"
                class="h-full w-full block"></canvas>
              <div id="reportChartEmpty" class="hidden h-full items-center justify-center text-sm text-slate-400">No attendance records for this period.</div>
            </div>

            <!-- Legend -->
            <div class="mt-6 flex flex-wrap gap-6 text-sm">
              <div class="flex items-center gap-2">
                <span class="h-3 w-3 rounded-full bg-blue-500"></span>
                Regular Hours
              </div>

              <div class="flex items-center gap-2">
                <span class="h-3 w-3 rounded-full bg-amber-500"></span>
                Overtime
              </div>
            </div>
          </div>

          <!-- RIGHT -->
          <div class="rounded-3xl bg-white p-6 shadow-sm">
            <!-- Header -->
            <div>
              <h3 class="mt-1 text-xl font-semibold text-slate-800">
                Attendance Composition
              </h3>
            </div>

            <!-- Donut Chart Container -->
            <div class="relative my-6 flex items-center justify-center">
              <svg
                class="h-44 w-44 -rotate-90 transform"
                viewBox="0 0 36 36">
                <!-- Background Circle (Absent - Rose) -->
                <circle
                  id="absentComposition"
                  cx="18"
                  cy="18"
                  r="15.9155"
                  fill="none"
                  class="stroke-rose-500"
                  stroke-width="3.8" />
                <!-- Late attendance segment. -->
                <circle
                  id="lateComposition"
                  cx="18"
                  cy="18"
                  r="15.9155"
                  fill="none"
                  class="stroke-amber-500 transition-all duration-500"
                  stroke-width="3.8"
                  stroke-dasharray="0 100"
                  stroke-dashoffset="0" />
                <!-- Present attendance segment. -->
                <circle
                  id="presentComposition"
                  cx="18"
                  cy="18"
                  r="15.9155"
                  fill="none"
                  class="stroke-emerald-500 transition-all duration-500"
                  stroke-width="3.8"
                  stroke-dasharray="0 100"
                  stroke-dashoffset="0" />
              </svg>

              <!-- Content di tengah Donut Chart -->
              <div
                class="absolute flex flex-col items-center justify-center text-center">
                <span id="compositionAttendanceRate" class="text-2xl font-bold text-slate-800">{{ $reportData['summary']['attendance_rate'] }}</span>
                <span
                  class="text-[10px] font-medium uppercase tracking-wider text-slate-400">
                  Attendance Rate
                </span>
              </div>
            </div>

            <!-- Legend Detail List -->
            <div class="space-y-3">
              <!-- On-time -->
              <div class="flex items-center justify-between text-sm">
                <div class="flex items-center gap-2.5">
                  <span class="h-3 w-3 rounded-full bg-emerald-500"></span>
                  <span class="font-medium text-slate-700">On-time</span>
                </div>
                <div class="flex items-center gap-3">
                  <span id="presentCompositionCount" class="font-medium text-slate-500">{{ $reportData['summary']['present'] }} days</span>
                  <span id="presentCompositionPercent" class="w-10 text-right font-semibold text-slate-800">0%</span>
                </div>
              </div>

              <!-- Late -->
              <div class="flex items-center justify-between text-sm">
                <div class="flex items-center gap-2.5">
                  <span class="h-3 w-3 rounded-full bg-amber-500"></span>
                  <span class="font-medium text-slate-700">Late</span>
                </div>
                <div class="flex items-center gap-3">
                  <span id="lateCompositionCount" class="font-medium text-slate-500">{{ $reportData['summary']['late'] }} days</span>
                  <span id="lateCompositionPercent" class="w-10 text-right font-semibold text-slate-800">0%</span>
                </div>
              </div>

              <!-- Absent -->
              <div class="flex items-center justify-between text-sm">
                <div class="flex items-center gap-2.5">
                  <span class="h-3 w-3 rounded-full bg-rose-500"></span>
                  <span class="font-medium text-slate-700">Absent / Leave</span>
                </div>
                <div class="flex items-center gap-3">
                  <span id="absentCompositionCount" class="font-medium text-slate-500">{{ $reportData['summary']['absent'] }} days</span>
                  <span id="absentCompositionPercent" class="w-10 text-right font-semibold text-slate-800">0%</span>
                </div>
              </div>
            </div>

            <!-- Total Summary Footer -->
            <div
              class="mt-6 border-t border-slate-100 pt-4 flex justify-between items-center">
              <p class="text-xs text-slate-400">Total Working Days</p>
              <p id="totalWorkingDays" class="text-sm font-bold text-slate-700">{{ $reportData['summary']['expected_workdays'] }} Days</p>
            </div>
          </div>
        </div>
      </div>

      <!-- ================= TAB 2: ATTENDANCE ================= -->
      <div id="panel-attendance" class="tab-panel hidden">
        <div class="grid gap-6 grid-cols-1 lg:grid-cols-3">
          <!-- Left: Weekday Check-in Pattern -->
          <div class="rounded-3xl bg-white p-6 shadow-sm border border-slate-100 flex flex-col justify-between">
            <div>
              <div class="flex items-center justify-between">
                <h3 class="text-lg font-semibold text-slate-800">
                  Weekday Check-in Pattern
                </h3>
                <span class="rounded-xl bg-purple-100 p-2 text-purple-600">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                  </svg>
                </span>
              </div>
              <p class="text-xs text-slate-400 mt-1">Average check-in time per workday (08:00 shift)</p>

              <div class="mt-6 space-y-4">
                <div>
                  <div class="flex justify-between text-xs font-medium text-slate-600 mb-1">
                    <span>Mon</span>
                    <span class="font-bold text-amber-600">08:21</span>
                  </div>
                  <div class="h-2.5 w-full rounded-full bg-slate-100">
                    <div class="h-2.5 rounded-full bg-amber-500 w-[85%]"></div>
                  </div>
                </div>
                <div>
                  <div class="flex justify-between text-xs font-medium text-slate-600 mb-1">
                    <span>Tue</span>
                    <span class="font-bold text-amber-600">08:05</span>
                  </div>
                  <div class="h-2.5 w-full rounded-full bg-slate-100">
                    <div class="h-2.5 rounded-full bg-amber-500 w-[65%]"></div>
                  </div>
                </div>
                <div>
                  <div class="flex justify-between text-xs font-medium text-slate-600 mb-1">
                    <span>Wed</span>
                    <span class="font-bold text-emerald-600">07:58</span>
                  </div>
                  <div class="h-2.5 w-full rounded-full bg-slate-100">
                    <div class="h-2.5 rounded-full bg-emerald-500 w-[50%]"></div>
                  </div>
                </div>
                <div>
                  <div class="flex justify-between text-xs font-medium text-slate-600 mb-1">
                    <span>Thu</span>
                    <span class="font-bold text-amber-600">08:12</span>
                  </div>
                  <div class="h-2.5 w-full rounded-full bg-slate-100">
                    <div class="h-2.5 rounded-full bg-amber-500 w-[72%]"></div>
                  </div>
                </div>
                <div>
                  <div class="flex justify-between text-xs font-medium text-slate-600 mb-1">
                    <span>Fri</span>
                    <span class="font-bold text-amber-600">08:02</span>
                  </div>
                  <div class="h-2.5 w-full rounded-full bg-slate-100">
                    <div class="h-2.5 rounded-full bg-amber-500 w-[60%]"></div>
                  </div>
                </div>
              </div>
            </div>

            <div class="mt-6 rounded-2xl bg-amber-50 p-4 border border-amber-200">
              <p class="text-xs text-amber-800 font-medium leading-relaxed">
                <strong>Mon</strong> is the most vulnerable day for being late — average check-in time is
                <strong>08:21</strong>. Try setting an alarm 20 minutes earlier on that day.
              </p>
            </div>
          </div>

          <!-- Right: Punctuality Heatmap -->
          <div class="rounded-3xl bg-white p-6 shadow-sm border border-slate-100 lg:col-span-2 flex flex-col justify-between">
            <div>
              <div class="flex items-center justify-between">
                <div>
                  <h3 class="text-lg font-semibold text-slate-800">Punctuality Heatmap</h3>
                  <p class="text-xs text-slate-400 mt-1">Last 12 weeks — each box represents one workday (Calendar Grid View)</p>
                </div>
                <span class="rounded-full bg-rose-50 p-2 text-rose-500">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 18.657A8 8 0 016.343 7.343S7 9 9 10c0-2 .5-5 2.986-7C14 5 16.09 5.777 17.656 7.343A7.975 7.975 0 0120 13a7.975 7.975 0 01-2.343 5.657z" />
                  </svg>
                </span>
              </div>

              <!-- Calendar Grid Heatmap Container -->
              <div class="mt-6 overflow-x-auto">
                <div id="heatmapGrid" class="min-w-[500px]"></div>
              </div>
            </div>

            <div class="mt-6 flex flex-wrap items-center justify-between gap-4 border-t border-slate-100 pt-4 text-xs">
              <div class="flex flex-wrap items-center gap-4 text-slate-600">
                <div class="flex items-center gap-1.5"><span class="h-3 w-3 rounded-md bg-emerald-500"></span><span>On time</span></div>
                <div class="flex items-center gap-1.5"><span class="h-3 w-3 rounded-md bg-emerald-200"></span><span>≤ 5 min</span></div>
                <div class="flex items-center gap-1.5"><span class="h-3 w-3 rounded-md bg-amber-400"></span><span>≤ 20 min</span></div>
                <div class="flex items-center gap-1.5"><span class="h-3 w-3 rounded-md bg-rose-500"></span><span>&gt; 20 min</span></div>
              </div>
              <span class="rounded-full bg-emerald-100 px-3 py-1 text-xs font-bold text-emerald-700">58% on time</span>
            </div>
          </div>
        </div>
      </div>

      <!-- ================= TAB 3: PRODUCTIVITY ================= -->
      <div id="panel-productivity" class="tab-panel hidden">
        <div class="grid gap-6 grid-cols-1 lg:grid-cols-2">
          <!-- Left: Work Hours Breakdown -->
          <div class="rounded-3xl bg-white p-6 shadow-sm border border-slate-100 flex flex-col justify-between">
            <div>
              <div class="flex items-center justify-between">
                <div>
                  <h3 class="text-lg font-semibold text-slate-800">Work Hours Breakdown</h3>
                  <p class="text-xs text-slate-400 mt-0.5">Composition of regular, overtime, and break hours</p>
                </div>
                <span class="rounded-xl bg-blue-50 p-2 text-blue-600">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                  </svg>
                </span>
              </div>

              <div class="mt-6 space-y-4">
                <div>
                  <div class="flex justify-between text-xs font-medium text-slate-600 mb-1">
                    <span>May 2026</span><span class="font-bold text-slate-800">171h total</span>
                  </div>
                  <div class="flex h-3 w-full overflow-hidden rounded-full bg-slate-100">
                    <div class="bg-blue-600 h-full w-[80%]"></div>
                    <div class="bg-amber-400 h-full w-[5%]"></div>
                    <div class="bg-purple-300 h-full w-[15%]"></div>
                  </div>
                </div>
                <div>
                  <div class="flex justify-between text-xs font-medium text-slate-600 mb-1">
                    <span>Jun 2026</span><span class="font-bold text-slate-800">203h total</span>
                  </div>
                  <div class="flex h-3 w-full overflow-hidden rounded-full bg-slate-100">
                    <div class="bg-blue-600 h-full w-[82%]"></div>
                    <div class="bg-amber-400 h-full w-[8%]"></div>
                    <div class="bg-purple-300 h-full w-[10%]"></div>
                  </div>
                </div>
                <div>
                  <div class="flex justify-between text-xs font-medium text-slate-600 mb-1">
                    <span>Jul 2026</span><span class="font-bold text-slate-800">196h total</span>
                  </div>
                  <div class="flex h-3 w-full overflow-hidden rounded-full bg-slate-100">
                    <div class="bg-blue-600 h-full w-[78%]"></div>
                    <div class="bg-amber-400 h-full w-[7%]"></div>
                    <div class="bg-purple-300 h-full w-[15%]"></div>
                  </div>
                </div>
                <div>
                  <div class="flex justify-between text-xs font-medium text-slate-600 mb-1">
                    <span>Aug 2026</span><span class="font-bold text-slate-800">183h total</span>
                  </div>
                  <div class="flex h-3 w-full overflow-hidden rounded-full bg-slate-100">
                    <div class="bg-blue-600 h-full w-[85%]"></div>
                    <div class="bg-amber-400 h-full w-[3%]"></div>
                    <div class="bg-purple-300 h-full w-[12%]"></div>
                  </div>
                </div>
                <div>
                  <div class="flex justify-between text-xs font-medium text-slate-600 mb-1">
                    <span>Sep 2026</span><span class="font-bold text-slate-800">191h total</span>
                  </div>
                  <div class="flex h-3 w-full overflow-hidden rounded-full bg-slate-100">
                    <div class="bg-blue-600 h-full w-[81%]"></div>
                    <div class="bg-amber-400 h-full w-[5%]"></div>
                    <div class="bg-purple-300 h-full w-[14%]"></div>
                  </div>
                </div>
                <div>
                  <div class="flex justify-between text-xs font-medium text-slate-600 mb-1">
                    <span>Oct 2026</span><span class="font-bold text-slate-800">135h total</span>
                  </div>
                  <div class="flex h-3 w-full overflow-hidden rounded-full bg-slate-100">
                    <div class="bg-blue-600 h-full w-[80%]"></div>
                    <div class="bg-amber-400 h-full w-[2%]"></div>
                    <div class="bg-purple-300 h-full w-[18%]"></div>
                  </div>
                </div>
              </div>
            </div>

            <div class="mt-6 flex items-center gap-6 text-xs text-slate-600 border-t border-slate-100 pt-4">
              <div class="flex items-center gap-2"><span class="h-3 w-3 rounded-full bg-blue-600"></span><span>Regular</span></div>
              <div class="flex items-center gap-2"><span class="h-3 w-3 rounded-full bg-amber-400"></span><span>Overtime</span></div>
              <div class="flex items-center gap-2"><span class="h-3 w-3 rounded-full bg-purple-300"></span><span>Break</span></div>
            </div>
          </div>

          <!-- Right: Goal Progress -->
          <div class="rounded-3xl bg-white p-6 shadow-sm border border-slate-100 flex flex-col justify-between">
            <div>
              <div class="flex items-center justify-between">
                <div>
                  <h3 class="text-lg font-semibold text-slate-800">Goal Progress</h3>
                  <p class="text-xs text-slate-400 mt-0.5">Achievement towards current period targets</p>
                </div>
                <span class="rounded-full bg-emerald-100 p-2 text-emerald-600">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                  </svg>
                </span>
              </div>

              <div class="mt-6 space-y-6">
                <div>
                  <div class="flex justify-between text-xs font-semibold text-slate-700 mb-1">
                    <span>Work Hours Target</span><span>451h / 480h</span>
                  </div>
                  <div class="h-2.5 w-full rounded-full bg-slate-100">
                    <div class="h-2.5 rounded-full bg-blue-600 w-[94%]"></div>
                  </div>
                  <span class="text-[11px] font-medium text-slate-400 mt-1 block">94% achieved</span>
                </div>
                <div>
                  <div class="flex justify-between text-xs font-semibold text-slate-700 mb-1">
                    <span>Attendance Goal</span><span>100% / 95%</span>
                  </div>
                  <div class="h-2.5 w-full rounded-full bg-slate-100">
                    <div class="h-2.5 rounded-full bg-emerald-500 w-[100%]"></div>
                  </div>
                  <span class="text-[11px] font-medium text-slate-400 mt-1 block">100% achieved</span>
                </div>
                <div>
                  <div class="flex justify-between text-xs font-semibold text-slate-700 mb-1">
                    <span>Punctuality Goal</span><span>94.8% / 90%</span>
                  </div>
                  <div class="h-2.5 w-full rounded-full bg-slate-100">
                    <div class="h-2.5 rounded-full bg-purple-500 w-[95%]"></div>
                  </div>
                  <span class="text-[11px] font-medium text-slate-400 mt-1 block">100% achieved</span>
                </div>
                <div>
                  <div class="flex justify-between text-xs font-semibold text-slate-700 mb-1">
                    <span>Overtime Budget</span><span>5h / 60h</span>
                  </div>
                  <div class="h-2.5 w-full rounded-full bg-slate-100">
                    <div class="h-2.5 rounded-full bg-amber-500 w-[8%]"></div>
                  </div>
                  <span class="text-[11px] font-medium text-slate-400 mt-1 block">8% achieved</span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- ================= TAB 4: LEAVE & PERMISSION ================= -->
      <div id="panel-leave" class="tab-panel hidden">
        <div class="grid gap-6 grid-cols-1 lg:grid-cols-2">
          <!-- Left: Leave Usage -->
          <div class="rounded-3xl bg-white p-6 shadow-sm border border-slate-100 flex flex-col justify-between">
            <div>
              <div class="flex items-center justify-between">
                <div>
                  <h3 class="text-lg font-semibold text-slate-800">Leave Usage</h3>
                  <p class="text-xs text-slate-400 mt-0.5">Leave quota usage for current year</p>
                </div>
                <span class="rounded-full bg-purple-100 p-2 text-purple-600">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                  </svg>
                </span>
              </div>

              <div class="mt-6 space-y-5">
                <div>
                  <div class="flex justify-between text-xs font-semibold text-slate-700 mb-1">
                    <span>Annual Leave</span><span>6 / 12 days</span>
                  </div>
                  <div class="h-2.5 w-full rounded-full bg-slate-100">
                    <div class="h-2.5 rounded-full bg-blue-600 w-[50%]"></div>
                  </div>
                </div>
                <div>
                  <div class="flex justify-between text-xs font-semibold text-slate-700 mb-1">
                    <span>Sick Leave</span><span>3 / 8 days</span>
                  </div>
                  <div class="h-2.5 w-full rounded-full bg-slate-100">
                    <div class="h-2.5 rounded-full bg-purple-500 w-[37.5%]"></div>
                  </div>
                </div>
                <div>
                  <div class="flex justify-between text-xs font-semibold text-slate-700 mb-1">
                    <span>Unpaid Leave</span><span>1 / 5 days</span>
                  </div>
                  <div class="h-2.5 w-full rounded-full bg-slate-100">
                    <div class="h-2.5 rounded-full bg-amber-500 w-[20%]"></div>
                  </div>
                </div>
                <div>
                  <div class="flex justify-between text-xs font-semibold text-slate-700 mb-1">
                    <span>Special Leave</span><span>0 / 3 days</span>
                  </div>
                  <div class="h-2.5 w-full rounded-full bg-slate-100">
                    <div class="h-2.5 rounded-full bg-slate-300 w-[0%]"></div>
                  </div>
                </div>
              </div>
            </div>

            <div class="mt-6 rounded-2xl bg-slate-50 p-4 border border-slate-100">
              <p class="text-xs text-slate-600 leading-relaxed">
                Used <strong>10</strong> out of <strong>28</strong> days quota. Remaining <strong>18</strong> days can still be requested.
              </p>
            </div>
          </div>

          <!-- Right: Permission Summary -->
          <div class="rounded-3xl bg-white p-6 shadow-sm border border-slate-100 flex flex-col justify-between">
            <div>
              <div class="flex items-center justify-between">
                <div>
                  <h3 class="text-lg font-semibold text-slate-800">Permission Summary</h3>
                  <p class="text-xs text-slate-400 mt-0.5">Short permissions breakdown by type</p>
                </div>
                <span class="rounded-xl bg-amber-100 p-2 text-amber-600">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                  </svg>
                </span>
              </div>

              <!-- Permission Chips Grid -->
              <div class="mt-6 grid grid-cols-2 gap-3">
                <div class="flex items-center justify-between rounded-2xl bg-slate-50 p-3.5 border border-slate-200">
                  <span class="text-xs font-semibold text-slate-700">Late Arrival</span>
                  <span class="flex h-6 w-6 items-center justify-center rounded-full bg-amber-100 text-xs font-bold text-amber-700">5</span>
                </div>
                <div class="flex items-center justify-between rounded-2xl bg-slate-50 p-3.5 border border-slate-200">
                  <span class="text-xs font-semibold text-slate-700">Early Departure</span>
                  <span class="flex h-6 w-6 items-center justify-center rounded-full bg-purple-100 text-xs font-bold text-purple-700">3</span>
                </div>
                <div class="flex items-center justify-between rounded-2xl bg-slate-50 p-3.5 border border-slate-200">
                  <span class="text-xs font-semibold text-slate-700">Work From Home</span>
                  <span class="flex h-6 w-6 items-center justify-center rounded-full bg-blue-100 text-xs font-bold text-blue-700">7</span>
                </div>
                <div class="flex items-center justify-between rounded-2xl bg-slate-50 p-3.5 border border-slate-200">
                  <span class="text-xs font-semibold text-slate-700">Out of Office</span>
                  <span class="flex h-6 w-6 items-center justify-center rounded-full bg-emerald-100 text-xs font-bold text-emerald-700">2</span>
                </div>
                <div class="col-span-2 flex items-center justify-between rounded-2xl bg-slate-50 p-3.5 border border-slate-200">
                  <span class="text-xs font-semibold text-slate-700">Official Duty</span>
                  <span class="flex h-6 w-6 items-center justify-center rounded-full bg-rose-100 text-xs font-bold text-rose-700">1</span>
                </div>
              </div>
            </div>

            <div class="mt-6 grid grid-cols-2 gap-3 pt-4 border-t border-slate-100">
              <div class="rounded-2xl bg-slate-50 p-3 border border-slate-100">
                <p class="text-[11px] text-slate-400">Total Requests</p>
                <p class="text-xl font-bold text-slate-800 mt-1">18</p>
              </div>
              <div class="rounded-2xl bg-emerald-100/70 p-3 border border-emerald-200">
                <p class="text-[11px] text-emerald-800 font-medium">Approval Rate</p>
                <p class="text-xl font-bold text-emerald-700 mt-1">89%</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- bottom content -->
  <!-- Container Table -->
  <div
    class="overflow-hidden rounded-3xl border border-slate-100 bg-white shadow-sm">
    <!-- Table Header Title -->
    <div class="p-6 pb-4">
      <h3 class="text-xl font-semibold text-slate-800">
        Attendance by Day
      </h3>
      <p class="text-sm text-slate-500">
        Rekapitulasi statistik kehadiran. Bulan berjalan ditampilkan
        paling atas.
      </p>
    </div>

    <!-- Table Responsive Container -->
    <div class="overflow-x-auto">
      <table class="mobile-card-table w-full text-left text-sm text-slate-600">
        <thead
          class="bg-slate-50 text-xs font-semibold uppercase text-slate-400">
          <tr>
            <th scope="col" class="px-6 py-4">Date</th>
            <th scope="col" class="px-6 py-4 text-center">
              Workday
            </th>
            <th scope="col" class="px-6 py-4 text-center">Present</th>
            <th scope="col" class="px-6 py-4 text-center">Late</th>
            <th scope="col" class="px-6 py-4 text-center">Absent</th>
            <th scope="col" class="px-6 py-4 text-center">Hours</th>
            <th scope="col" class="px-6 py-4 text-center">
              Overtime
            </th>
            <th scope="col" class="px-6 py-4 min-w-[200px]">
              Attendance Rate
            </th>
          </tr>
        </thead>

        <!-- Attendance rows are rendered from records in the selected period. -->
        <tbody
          id="attendanceTableBody"
          class="divide-y divide-slate-100 border-t border-slate-100"></tbody>
      </table>
    </div>
  </div>

  <div
    class="rounded-3xl border border-slate-100 bg-white p-6 md:p-8 shadow-sm">
    <!-- Section Header -->
    <div class="mb-6">
      <h3 class="text-xl font-semibold text-slate-800">
        Selected Period Summary
      </h3>
      <p class="text-sm text-slate-500">
        Summary from the selected attendance period.
      </p>
    </div>

    <!-- Reports List Container -->
    <div class="space-y-4" id="reportsList"></div>

    <!-- Container Toast Notification (Atas Tengah) -->
    <div
      class="fixed top-5 left-1/2 -translate-x-1/2 z-50 pointer-events-none">
      <div
        id="toast"
        class="pointer-events-auto flex items-center gap-3 bg-gray-900 text-white px-4 py-3 rounded-xl shadow-xl border border-gray-800 transition-all duration-300 -translate-y-10 opacity-0 hidden">
        <!-- Icon Status (Success Checkmark) -->
        <span
          class="flex h-6 w-6 items-center justify-center rounded-full bg-emerald-500/20 text-emerald-400">
          <svg
            xmlns="http://www.w3.org/2000/svg"
            class="h-4 w-4"
            fill="none"
            viewBox="0 0 24 24"
            stroke="currentColor">
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2.5"
              d="M5 13l4 4L19 7" />
          </svg>
        </span>

        <!-- Teks Pesan -->
        <p class="text-sm font-medium">
          <span id="toast-message">Meng-generate laporan:</span>
          <span id="file-name">...</span>
        </p>

        <!-- Tombol Tutup (X) -->
        <button
          id="closeToastBtn"
          class="ml-2 text-gray-400 hover:text-white transition">
          <svg
            xmlns="http://www.w3.org/2000/svg"
            class="h-4 w-4"
            fill="none"
            viewBox="0 0 24 24"
            stroke="currentColor">
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>
      </div>
    </div>
  </div>

  <!-- insights -->
  <div
    class="grid gap-4 bg-white p-6 rounded-3xl shadow-sm sm:grid-cols-2 xl:grid-cols-4"
    id="insightsGrid"></div>
</section>

@endsection

@push('scripts')
<script>
  window.smartReportData = @json($reportData);
  window.reportExportUrl = @json(route('reports.export'));
</script>
<script src="{{ asset('js/report.js') }}?v=attendance-report-data-1"></script>
@endpush