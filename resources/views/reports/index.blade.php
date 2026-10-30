<!doctype html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Smart Attendance Dashboard</title>

    <script src="https://cdn.tailwindcss.com"></script>
    <script>
      tailwind.config = {
        theme: {
          extend: {
            fontFamily: {
              sans: ["Inter", "sans-serif"],
            },
            colors: {
              primary: {
                50: "#eef2ff",
                100: "#e0e7ff",
                500: "#6366f1",
                600: "#4f46e5",
                700: "#4338ca",
              },
            },
          },
        },
      };
    </script>

    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link
      href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap"
      rel="stylesheet"
    />
  </head>

  <body class="bg-slate-100 font-sans text-slate-800">
    <div class="min-h-screen bg-[#f5f7fb]">
      <div class="flex min-h-screen">
        <!-- Overlay mobile -->
        <div
          id="sidebarOverlay"
          class="fixed inset-0 z-30 hidden bg-black/40 md:hidden"
        ></div>

        <!-- Sidebar -->
        <aside
          id="sidebar"
          class="fixed inset-y-0 left-0 z-40 flex w-72 -translate-x-full flex-col border-r border-slate-200 bg-[#000033] shadow-xl transition-transform duration-300 md:translate-x-0"
        >
          <!-- Brand -->
          <div class="flex items-center justify-between px-6 py-5">
            <div class="flex items-center gap-3">
              <div
                class="flex h-15 w-20 items-center justify-center text-white shadow-lg"
              >
                <img
                  src="assets/LEXA-logo.png"
                  alt="LEXA Logo"
                  class="h-15 w-15 rounded-full object-cover shadow-lg"
                />
              </div>
              <div>
                <p class="text-xs uppercase tracking-[0.35em] text-slate-300">
                  LEXA
                </p>
                <h2
                  class="text-sm font-semibold uppercase tracking-[0.35em] text-white"
                >
                  SMART ATTENDANCE
                </h2>
              </div>
            </div>

            <!-- Close mobile -->
            <button
              id="closeSidebar"
              class="rounded-xl p-2 text-white hover:bg-white/10 md:hidden"
              aria-label="Close menu"
            >
              <svg
                xmlns="http://www.w3.org/2000/svg"
                class="h-6 w-6"
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

          <!-- Navigation -->
          <nav class="mt-4 flex-1 space-y-1 px-3">
            <a
              href="{{ route('dashboard.index') }}"
              class="flex items-center gap-3 rounded-2xl px-3 py-1 text-sm font-medium text-slate-200 transition hover:bg-white/10"
            >
              <span
                class="flex h-8 w-8 items-center justify-center rounded-xl bg-white/10"
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
                    d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001 1h6a1 1 0 001-1m-7 0h8"
                  />
                </svg>
              </span>
              Dashboard
            </a>

            <a
              href="{{ route('attendance.check-in') }}"
              class="flex items-center gap-3 rounded-2xl px-3 py-1 text-sm font-medium text-slate-200 transition hover:bg-white/10"
            >
              <span
                class="flex h-8 w-8 items-center justify-center rounded-xl bg-white/10"
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
                    d="M12 6v6l4 2m6-2a10 10 0 11-20 0 10 10 0 0120 0z"
                  />
                </svg>
              </span>
              Check-in / out
            </a>

            <a
              href="{{ route('attendance.index') }}"
              class="flex items-center gap-3 rounded-2xl px-3 py-1 text-sm font-medium text-slate-200 transition hover:bg-white/10"
            >
              <span
                class="flex h-8 w-8 items-center justify-center rounded-xl bg-white/10"
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
                    d="M9 3h6a2 2 0 012 2h1a2 2 0 012 2v12a2 2 0 01-2 2H6a2 2 0 01-2-2V7a2 2 0 012-2h1a2 2 0 012-2zm0 0a2 2 0 002 2h2a2 2 0 002-2M8 11h8M8 15h5"
                  />
                </svg>
              </span>
              Attendance
            </a>

            <a
              href="{{ route('schedules.index') }}"
              class="flex items-center gap-3 rounded-2xl px-3 py-1 text-sm font-medium text-slate-200 transition hover:bg-white/10"
            >
              <span
                class="flex h-8 w-8 items-center justify-center rounded-xl bg-white/10"
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
                    d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"
                  />
                </svg>
              </span>
              Schedule
            </a>

            <a
              href="{{ route('leave.index') }}"
              class="flex items-center gap-3 rounded-2xl px-3 py-1 text-sm font-medium text-slate-200 transition hover:bg-white/10"
            >
              <span
                class="flex h-8 w-8 items-center justify-center rounded-xl bg-white/10"
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
                    d="M3 11L21 3L13 21L11 13L3 11Z"
                  />
                </svg>
              </span>
              Leave Request
            </a>

            <a
              href="{{ route('permissions.index') }}"
              class="flex items-center gap-3 rounded-2xl px-3 py-1 text-sm font-medium text-slate-200 transition hover:bg-white/10"
            >
              <span
                class="flex h-8 w-8 items-center justify-center rounded-xl bg-white/10"
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
                    d="M9 2h6l5 5v13a2 2 0 01-2 2H6a2 2 0 01-2-2V4a2 2 0 012-2h3zm6 0v5h5M8 11h8M8 15h8M8 19h5"
                  />
                </svg>
              </span>
              Permission
            </a>

            <a
              href="{{ route('attendance.logs') }}"
              class="flex items-center gap-3 rounded-2xl px-3 py-1 text-sm font-medium text-slate-200 transition hover:bg-white/10"
            >
              <span
                class="flex h-8 w-8 items-center justify-center rounded-xl bg-white/10"
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
                    d="M8 7h8M8 11h8M8 15h5M6 3h12a2 2 0 012 2v14l-4-2-4 2-4-2-4 2V5a2 2 0 012-2z"
                  />
                </svg>
              </span>
              Attendance Log
            </a>

            <a
              href="#"
              class="flex items-center gap-3 rounded-2xl bg-indigo-50 px-3 py-1 text-sm font-semibold text-indigo-700"
            >
              <span
                class="flex h-8 w-8 items-center justify-center rounded-xl bg-indigo-100"
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
                    d="M9 17v-2m3 2V7m3 10v-6M5 21h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v14a2 2 0 002 2z"
                  />
                </svg>
              </span>
              Report
            </a>

            <a
              href="{{ route('dashboard.index') }}"
              class="flex items-center gap-3 rounded-2xl px-3 py-1 text-sm font-medium text-slate-200 transition hover:bg-white/10"
            >
              <span
                class="flex h-8 w-8 items-center justify-center rounded-xl bg-white/10"
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
                    d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0a3 3 0 11-6 0"
                  />
                </svg>
              </span>
              Notification
            </a>

            <a
              href="{{ route('profile.edit') }}"
              class="flex items-center gap-3 rounded-2xl px-3 py-1 text-sm font-medium text-slate-200 transition hover:bg-white/10"
            >
              <span
                class="flex h-8 w-8 items-center justify-center rounded-xl bg-white/10"
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
                    d="M5.121 17.804A9 9 0 1118.879 6.196 9 9 0 015.121 17.804z"
                  />
                </svg>
              </span>
              Profile
            </a>
          </nav>

          <!-- Profile card -->
          <div class="px-3 pb-6 pt-4">
            <div class="rounded-3xl bg-white/10 p-2 backdrop-blur">
              <div class="flex items-center gap-3">
                <img
                  src="{{ route('profile.avatar', ['v' => auth()->user()->updated_at?->timestamp ?? 0]) }}"
                  alt="Profile"
                  class="h-10 w-10 rounded-2xl object-cover"
                />
                <div>
                  <p class="font-semibold text-white">{{ auth()->user()->name }}</p>
                  <p class="text-sm text-slate-300">{{ auth()->user()->job_title ?: '—' }}</p>
                </div>
              </div>
            </div>
          </div>
        </aside>

        <!-- Main -->
        <main class="flex-1 md:ml-72">
          <!-- Header -->
          <header
            class="sticky top-0 z-20 border-b border-slate-200 bg-white/90 px-4 py-4 backdrop-blur sm:px-6"
          >
            <div class="flex items-center justify-between gap-3">
              <div class="flex items-center gap-3">
                <!-- Burger mobile -->
                <button
                  id="openSidebar"
                  class="flex h-11 w-11 items-center justify-center rounded-2xl border border-slate-200 text-slate-700 hover:bg-slate-50 md:hidden"
                  aria-label="Open menu"
                >
                  <svg
                    xmlns="http://www.w3.org/2000/svg"
                    class="h-6 w-6"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke="currentColor"
                  >
                    <path
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      stroke-width="2"
                      d="M4 6h16M4 12h16M4 18h16"
                    />
                  </svg>
                </button>

                <div>
                  <p class="text-xl text-slate-500">Welcome, {{ auth()->user()->nickname ?: auth()->user()->name }}</p>
                </div>
              </div>

              <div class="flex items-center gap-3">
                <div
                  class="hidden rounded-2xl bg-slate-100 px-4 py-2 text-sm text-slate-600 md:block"
                >
                  <span id="headerTime">--:--</span>
                </div>
                <div
                  class="hidden rounded-2xl bg-slate-100 px-4 py-2 text-sm text-slate-600 md:block"
                >
                  <span id="headerDate">--</span>
                </div>

                <button
                  class="relative flex h-11 w-11 items-center justify-center rounded-2xl bg-indigo-600 text-white shadow-lg"
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
                      d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"
                    />
                  </svg>
                  <span
                    class="absolute right-2 top-2 h-2.5 w-2.5 rounded-full bg-rose-500 ring-2 ring-white"
                  ></span>
                </button>
              </div>
            </div>
          </header>

          <!-- Content -->
          <section class="space-y-6 p-4 sm:p-6">
            <div
              class="flex flex-col gap-3 rounded-2xl bg-white p-2 shadow-sm md:flex-row md:items-center md:justify-between"
            >
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
                        <option value="{{ $period }}" @selected($selectedPeriod === $period)>{{ $period }}</option>
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
                  id="exportCsvBtn"
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
              </div>
            </div>

            <!-- Main grid -->
            <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
              <!-- Attendance Rate -->
              <div
                class="flex items-center gap-4 rounded-3xl bg-white p-5 shadow-sm"
              >
                <div
                  class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-blue-100 text-blue-600"
                >
                  <!-- Icon: Users / Calendar Check -->
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
                      d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"
                    />
                  </svg>
                </div>
                <div class="w-full">
                  <p class="text-sm text-slate-500">Attendance Rate</p>
                  <div class="flex items-baseline justify-between gap-2">
                    <h2
                      id="attendanceRate"
                      class="text-3xl font-bold text-blue-600"
                    >
                      {{ $reportData['summary']['attendance_rate'] }}
                    </h2>
                  </div>
                  <!-- Indikator Tren -->
                  <span
                    class="inline-flex items-center text-xs font-semibold text-emerald-500"
                  >
                    <svg
                      class="mr-0.5 h-3.5 w-3.5"
                      fill="none"
                      stroke="currentColor"
                      viewBox="0 0 24 24"
                    >
                      <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2.5"
                        d="M5 10l7-7 7 7M12 3v18"
                      />
                    </svg>
                    {{ $reportData['summary']['expected_workdays'] }} scheduled days
                  </span>
                </div>
              </div>

              <!-- Punctuality Rate -->
              <div
                class="flex items-center gap-4 rounded-3xl bg-white p-5 shadow-sm"
              >
                <div
                  class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-emerald-100 text-emerald-600"
                >
                  <!-- Icon: Check Circle / On Time -->
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
                      d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"
                    />
                  </svg>
                </div>
                <div class="w-full">
                  <p class="text-sm text-slate-500">Punctuality Rate</p>
                  <div class="flex items-baseline justify-between gap-2">
                    <h2
                      id="punctualityRate"
                      class="text-3xl font-bold text-emerald-600"
                    >
                      {{ $reportData['summary']['punctuality_rate'] }}
                    </h2>
                  </div>
                  <!-- Indikator Tren -->
                  <span
                    class="inline-flex items-center text-xs font-semibold text-emerald-500"
                  >
                    <svg
                      class="mr-0.5 h-3.5 w-3.5"
                      fill="none"
                      stroke="currentColor"
                      viewBox="0 0 24 24"
                    >
                      <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2.5"
                        d="M5 10l7-7 7 7M12 3v18"
                      />
                    </svg>
                    {{ $reportData['summary']['present'] }} on-time days
                  </span>
                </div>
              </div>

              <!-- Total Work Hours -->
              <div
                class="flex items-center gap-4 rounded-3xl bg-white p-5 shadow-sm"
              >
                <div
                  class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-indigo-100 text-indigo-600"
                >
                  <!-- Icon: Clock -->
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
                      d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"
                    />
                  </svg>
                </div>
                <div class="w-full">
                  <p class="text-sm text-slate-500">Total Work Hours</p>
                  <div class="flex items-baseline justify-between gap-2">
                    <h2
                      id="totalWorkHours"
                      class="text-3xl font-bold text-indigo-600"
                    >
                      {{ $reportData['summary']['work_hours'] }}
                    </h2>
                  </div>
                  <!-- Indikator Tren -->
                  <span
                    class="inline-flex items-center text-xs font-semibold text-emerald-600"
                  >
                    <svg
                      class="mr-0.5 h-3.5 w-3.5"
                      fill="none"
                      stroke="currentColor"
                      viewBox="0 0 24 24"
                    >
                      <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2.5"
                        d="M5 10l7-7 7 7M12 3v18"
                      />
                    </svg>
                    {{ $reportData['summary']['work_hours'] }} worked
                  </span>
                </div>
              </div>

              <!-- Overtime Hours -->
              <div
                class="flex items-center gap-4 rounded-3xl bg-white p-5 shadow-sm"
              >
                <div
                  class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-amber-100 text-amber-500"
                >
                  <!-- Icon: Briefcase/Flame -->
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
                      d="M13 10V3L4 14h7v7l9-11h-7z"
                    />
                  </svg>
                </div>
                <div class="w-full">
                  <p class="text-sm text-slate-500">Overtime Hours</p>
                  <div class="flex items-baseline justify-between gap-2">
                    <h2
                      id="overtimeHours"
                      class="text-3xl font-bold text-amber-500"
                    >
                      {{ $reportData['summary']['overtime_hours'] }}
                    </h2>
                  </div>
                  <!-- Indikator Tren (Contoh Penurunan) -->
                  <span
                    class="inline-flex items-center text-xs font-semibold text-rose-600"
                  >
                    <svg
                      class="mr-0.5 h-3.5 w-3.5"
                      fill="none"
                      stroke="currentColor"
                      viewBox="0 0 24 24"
                    >
                      <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2.5"
                        d="M19 14l-7 7-7-7M12 21V3"
                      />
                    </svg>
                    {{ $reportData['summary']['overtime_hours'] }} overtime
                  </span>
                </div>
              </div>
            </div>

            <div class="pl-4 flex flex-wrap gap-5 text-sm">
              <button
                class="rounded-3xl bg-white flex items-center p-2 shadow-sm gap-2"
              >
                <p class="font-semibold">Overview</p>
              </button>

              <button
                class="rounded-3xl bg-white flex items-center p-2 shadow-sm gap-2"
              >
                <p class="font-semibold">Attendance</p>
              </button>

              <button
                class="rounded-3xl bg-white flex items-center p-2 shadow-sm gap-2"
              >
                <p class="font-semibold">Productivity</p>
              </button>

              <button
                class="rounded-3xl bg-white flex items-center p-2 shadow-sm gap-2"
              >
                <p class="font-semibold">Leave & Permission</p>
              </button>
            </div>

            <div class="grid gap-6 lg:grid-cols-3">
              <!-- ================= LEFT ================= -->
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
                    class="h-full w-full block"
                  ></canvas>
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

              <!-- ================= RIGHT ================= -->
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
                    viewBox="0 0 36 36"
                  >
                    <!-- Background Circle (Absent - Rose) -->
                    <circle
                      id="absentComposition"
                      cx="18"
                      cy="18"
                      r="15.9155"
                      fill="none"
                      class="stroke-rose-500"
                      stroke-width="3.8"
                    />
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
                      stroke-dashoffset="0"
                    />
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
                      stroke-dashoffset="0"
                    />
                  </svg>

                  <!-- Content di tengah Donut Chart -->
                  <div
                    class="absolute flex flex-col items-center justify-center text-center"
                  >
                    <span id="compositionAttendanceRate" class="text-2xl font-bold text-slate-800">{{ $reportData['summary']['attendance_rate'] }}</span>
                    <span
                      class="text-[10px] font-medium uppercase tracking-wider text-slate-400"
                    >
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
                      <span class="font-medium text-slate-700"
                        >Absent / Leave</span
                      >
                    </div>
                    <div class="flex items-center gap-3">
                      <span id="absentCompositionCount" class="font-medium text-slate-500">{{ $reportData['summary']['absent'] }} days</span>
                      <span id="absentCompositionPercent" class="w-10 text-right font-semibold text-slate-800">0%</span>
                    </div>
                  </div>
                </div>

                <!-- Total Summary Footer -->
                <div
                  class="mt-6 border-t border-slate-100 pt-4 flex justify-between items-center"
                >
                  <p class="text-xs text-slate-400">Total Working Days</p>
                  <p id="totalWorkingDays" class="text-sm font-bold text-slate-700">{{ $reportData['summary']['expected_workdays'] }} Days</p>
                </div>
              </div>
            </div>

            <!-- bottom content -->
            <!-- Container Table -->
            <div
              class="overflow-hidden rounded-3xl border border-slate-100 bg-white shadow-sm"
            >
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
                <table class="w-full text-left text-sm text-slate-600">
                  <thead
                    class="bg-slate-50 text-xs font-semibold uppercase text-slate-400"
                  >
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
                    class="divide-y divide-slate-100 border-t border-slate-100"
                  ></tbody>
                </table>
              </div>
            </div>

            <div
              class="rounded-3xl border border-slate-100 bg-white p-6 md:p-8 shadow-sm"
            >
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
                    <span id="toast-message">Meng-generate laporan:</span>
                    <span id="file-name">...</span>
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

            <!-- insights -->
            <div
              class="grid gap-4 bg-white p-6 rounded-3xl shadow-sm sm:grid-cols-2 xl:grid-cols-4"
              id="insightsGrid"
            ></div>
          </section>
        </main>
      </div>
    </div>

    <script>
      window.smartReportData = @json($reportData);
      window.reportExportUrl = @json(route('reports.export'));
    </script>
    <script src="{{ asset('js/report.js') }}?v=attendance-report-data-1"></script>
  </body>
</html>
