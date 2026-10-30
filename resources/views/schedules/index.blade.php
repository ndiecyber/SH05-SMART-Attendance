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
              href="{{ route('reports.index') }}"
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
            @if (session('success'))
              <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700" role="status">
                {{ session('success') }}
              </div>
            @endif
            @if (session('error'))
              <div class="rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-medium text-rose-700" role="alert">
                {{ session('error') }}
              </div>
            @endif

            <!-- Clock cards -->
            <div
              class="flex flex-col gap-3 rounded-2xl bg-white p-2 shadow-sm md:flex-row md:items-center md:justify-between"
            >
              <!-- Left : Legend -->

              <div class="pl-4 flex flex-wrap gap-5 text-sm">
                LEGEND:
                <div class="flex items-center gap-2">
                  <span class="h-3 w-3 rounded-full bg-blue-500"></span>
                  Work Shift
                </div>

                <div class="flex items-center gap-2">
                  <span class="h-3 w-3 rounded-full bg-purple-500"></span>
                  Meeting
                </div>

                <div class="flex items-center gap-2">
                  <span class="h-3 w-3 rounded-full bg-amber-500"></span>
                  Training
                </div>

                <div class="flex items-center gap-2">
                  <span class="h-3 w-3 rounded-full bg-emerald-500"></span>
                  Break
                </div>

                <div class="flex items-center gap-2">
                  <span class="h-3 w-3 rounded-full bg-red-500"></span>
                  Off
                </div>
              </div>

              <!-- Right : Add Button -->
              <a
                href="{{ route('schedules.create') }}"
                class="flex items-center justify-center gap-2 rounded-xl bg-indigo-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-indigo-700"
              >
                + Add Schedule
              </a>
            </div>

            <!-- Main grid -->
            <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
              <!-- Today's Shift -->
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
                      d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"
                    />
                  </svg>
                </div>

                <div>
                  <p class="text-sm text-slate-500">Today's Shift</p>
                  <h2 id="todayShift" class="text-3xl font-bold text-blue-600">
                    {{ $scheduleStats['todayShift'] ?? 'No Schedule Today' }}
                  </h2>
                  <p class="text-sm text-slate-500">Scheduled for today</p>
                </div>
              </div>

              <!-- Shifts This Week -->
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
                      d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"
                    />
                  </svg>
                </div>

                <div>
                  <p class="text-sm text-slate-500">Shifts This Week</p>
                  <h2
                    id="weeklyShifts"
                    class="text-3xl font-bold text-emerald-600"
                  >
                    {{ $scheduleStats['weeklyShifts'] }}
                  </h2>
                  <p class="text-sm text-slate-500">Total scheduled shifts</p>
                </div>
              </div>

              <!-- Upcoming -->
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
                  <p class="text-sm text-slate-500">Upcoming</p>
                  <h2
                    id="upcomingShifts"
                    class="text-3xl font-bold text-amber-500"
                  >
                    {{ $scheduleStats['upcomingShifts'] }}
                  </h2>
                  <p class="text-sm text-slate-500">Upcoming shifts & events</p>
                </div>
              </div>

              <!-- Hours / Week -->
              <div
                class="flex items-center gap-4 rounded-3xl bg-white p-5 shadow-sm"
              >
                <div
                  class="flex h-14 w-14 items-center justify-center rounded-2xl bg-purple-100 text-purple-600"
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
                  <p class="text-sm text-slate-500">Hours / Week</p>
                  <h2
                    id="weeklyHours"
                    class="text-3xl font-bold text-purple-600"
                  >
                    {{ $scheduleStats['weeklyHours'] }}
                  </h2>
                  <p class="text-sm text-slate-500">Total scheduled hours</p>
                </div>
              </div>
            </div>

            <div class="grid gap-6 lg:grid-cols-5">
              <!-- ================= LEFT : Today's Schedule (Overlapping Grid Design) ================= -->
              <div
                class="rounded-3xl bg-white shadow-sm lg:col-span-3 flex flex-col overflow-hidden"
              >
                <!-- Header -->
                <div
                  class="flex items-center justify-between border-b border-slate-100 p-6 pb-4"
                >
                  <div>
                    <div class="flex items-center gap-2">
                      <h3 class="text-lg font-bold text-slate-800">
                        Today's Schedule
                      </h3>
                    </div>
                    <p class="text-xs text-slate-500 mt-0.5">
                      07:00 - 19:00 • Interactive Overlapping Timeline
                    </p>
                  </div>

                  <span
                    class="rounded-2xl bg-indigo-50 px-3 py-1 text-xs font-semibold text-indigo-600 shadow-sm"
                  >
                    Today
                  </span>
                </div>

                <!-- Legend Badges -->
                <div
                  class="px-6 py-3 bg-slate-50/50 border-b border-slate-100 flex flex-wrap items-center gap-x-4 gap-y-2 text-[11px] text-slate-600"
                >
                  <div class="flex items-center gap-1.5">
                    <span class="h-2.5 w-2.5 rounded-sm bg-blue-500"></span>
                    Work Shift
                  </div>
                  <div class="flex items-center gap-1.5">
                    <span class="h-2.5 w-2.5 rounded-sm bg-indigo-500"></span>
                    Meeting
                  </div>
                  <div class="flex items-center gap-1.5">
                    <span class="h-2.5 w-2.5 rounded-sm bg-violet-500"></span>
                    Training
                  </div>
                  <div class="flex items-center gap-1.5">
                    <span class="h-2.5 w-2.5 rounded-sm bg-amber-500"></span>
                    Break
                  </div>
                </div>

                <!-- Overlapping Timeline Grid Container -->
                <div class="p-6 overflow-x-auto">
                  <div class="min-w-[450px] grid grid-cols-[52px_1fr] relative">
                    <!-- Kolom Jam (Background Lines) -->
                    <div class="relative">
                      <div
                        class="h-12 border-b border-slate-100 pt-1 text-[10px] text-slate-400"
                      >
                        07:00
                      </div>
                      <div
                        class="h-12 border-b border-slate-100 pt-1 text-[10px] text-slate-400"
                      >
                        08:00
                      </div>
                      <div
                        class="h-12 border-b border-slate-100 pt-1 text-[10px] text-slate-400"
                      >
                        09:00
                      </div>
                      <div
                        class="h-12 border-b border-slate-100 pt-1 text-[10px] text-slate-400"
                      >
                        10:00
                      </div>
                      <div
                        class="h-12 border-b border-slate-100 pt-1 text-[10px] text-slate-400"
                      >
                        11:00
                      </div>
                      <div
                        class="h-12 border-b border-slate-100 pt-1 text-[10px] text-slate-400"
                      >
                        12:00
                      </div>
                      <div
                        class="h-12 border-b border-slate-100 pt-1 text-[10px] text-slate-400"
                      >
                        13:00
                      </div>
                      <div
                        class="h-12 border-b border-slate-100 pt-1 text-[10px] text-slate-400"
                      >
                        14:00
                      </div>
                      <div
                        class="h-12 border-b border-slate-100 pt-1 text-[10px] text-slate-400"
                      >
                        15:00
                      </div>
                      <div
                        class="h-12 border-b border-slate-100 pt-1 text-[10px] text-slate-400"
                      >
                        16:00
                      </div>
                      <div
                        class="h-12 border-b border-slate-100 pt-1 text-[10px] font-medium text-slate-500"
                      >
                        17:00
                      </div>
                      <div
                        class="h-12 border-b border-slate-100 pt-1 text-[10px] text-slate-400"
                      >
                        18:00
                      </div>
                      <div class="h-12 pt-1 text-[10px] text-slate-400">
                        19:00
                      </div>
                    </div>

                    <!-- Area Tumpang Tindih Jadwal -->
                    <div
                      id="todayOverlapCol"
                      class="relative border-l border-slate-100 bg-slate-50/20"
                    >
                      <!-- Background Row Lines Sync -->
                      <div
                        class="absolute inset-0 grid grid-rows-12 pointer-events-none"
                      >
                        <div class="border-b border-slate-100"></div>
                        <div class="border-b border-slate-100"></div>
                        <div class="border-b border-slate-100"></div>
                        <div class="border-b border-slate-100"></div>
                        <div class="border-b border-slate-100"></div>
                        <div class="border-b border-slate-100"></div>
                        <div class="border-b border-slate-100"></div>
                        <div class="border-b border-slate-100"></div>
                        <div class="border-b border-slate-100"></div>
                        <div class="border-b border-slate-100"></div>
                        <div class="border-b border-slate-100"></div>
                        <div class="border-b border-slate-100"></div>
                      </div>

                      <!-- Kartu Event Tumpang Tindih diisi oleh JavaScript -->
                    </div>
                  </div>
                </div>
              </div>

              <!-- ================= RIGHT : upcoming ================= -->
              <div
                class="rounded-3xl bg-white p-6 shadow-sm lg:col-span-2 flex flex-col"
              >
                <div class="mb-5 flex items-center justify-between">
                  <div>
                    <h3 class="text-lg font-semibold text-slate-800">
                      Upcoming Events
                    </h3>
                    <p class="text-xs text-slate-400">Next 5 scheduled items</p>
                  </div>
                  <span
                    class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-600"
                  >
                      {{ $scheduleStats['upcomingShifts'] }} Events
                  </span>
                </div>

                <div id="upcomingEventsContainer" class="space-y-3 flex-1">
                  <!-- Diisi secara dinamis oleh JavaScript -->
                </div>
              </div>
            </div>

            <!-- bottom content -->
            <!-- Weekly Schedule Timeline -->
            <div class="rounded-3xl bg-white shadow-sm">
              <!-- Header -->
              <div
                class="flex items-center justify-between border-b border-slate-100 px-5 py-4"
              >
                <div>
                  <h2 class="text-base font-semibold text-slate-800">
                    Weekly Schedule
                  </h2>
                  <p id="weekRange" class="mt-0.5 text-xs text-slate-500">
                    {{ \Carbon\CarbonImmutable::parse($weekStart)->format('d M') }} - {{ \Carbon\CarbonImmutable::parse($weekEnd)->format('d M Y') }}
                  </p>
                </div>

                <!-- Week Pagination -->
                <div class="flex items-center gap-1.5">
                  <button
                    id="prevWeek"
                    type="button"
                    class="flex h-8 w-8 items-center justify-center rounded-lg border border-slate-200 text-slate-500 transition hover:bg-slate-50"
                    aria-label="Previous week"
                  >
                    <svg
                      xmlns="http://www.w3.org/2000/svg"
                      class="h-3.5 w-3.5"
                      viewBox="0 0 24 24"
                      fill="none"
                      stroke="currentColor"
                      stroke-width="2"
                    >
                      <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        d="M15 19l-7-7 7-7"
                      />
                    </svg>
                  </button>

                  <button
                    id="todayWeek"
                    type="button"
                    class="rounded-lg border border-slate-200 px-3 py-1.5 text-xs font-medium text-slate-600 transition hover:bg-slate-50"
                  >
                    Today
                  </button>

                  <button
                    id="nextWeek"
                    type="button"
                    class="flex h-8 w-8 items-center justify-center rounded-lg border border-slate-200 text-slate-500 transition hover:bg-slate-50"
                    aria-label="Next week"
                  >
                    <svg
                      xmlns="http://www.w3.org/2000/svg"
                      class="h-3.5 w-3.5"
                      viewBox="0 0 24 24"
                      fill="none"
                      stroke="currentColor"
                      stroke-width="2"
                    >
                      <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        d="M9 5l7 7-7 7"
                      />
                    </svg>
                  </button>
                </div>
              </div>

              <!-- Timeline Grid -->
              <div class="px-4 py-4 overflow-x-auto">
                <div
                  class="min-w-[700px] grid grid-cols-[52px_repeat(7,minmax(0,1fr))]"
                >
                  <!-- Header Hari & Tanggal -->
                  <div class="border-b border-slate-200"></div>

                  <div
                    class="border-b border-l border-slate-200 px-1 pb-2 text-center"
                  >
                    <p class="text-[10px] font-medium uppercase text-slate-400">
                      Mon
                    </p>
                    <p
                      id="dayNum-0"
                      class="text-sm font-semibold text-slate-700"
                    >
                      -
                    </p>
                  </div>
                  <div
                    class="border-b border-l border-slate-200 px-1 pb-2 text-center"
                  >
                    <p class="text-[10px] font-medium uppercase text-slate-400">
                      Tue
                    </p>
                    <p
                      id="dayNum-1"
                      class="text-sm font-semibold text-slate-700"
                    >
                      -
                    </p>
                  </div>
                  <div
                    class="border-b border-l border-slate-200 px-1 pb-2 text-center"
                  >
                    <p class="text-[10px] font-medium uppercase text-slate-400">
                      Wed
                    </p>
                    <p
                      id="dayNum-2"
                      class="text-sm font-semibold text-slate-700"
                    >
                      -
                    </p>
                  </div>
                  <div
                    class="border-b border-l border-slate-200 px-1 pb-2 text-center"
                  >
                    <p class="text-[10px] font-medium uppercase text-slate-400">
                      Thu
                    </p>
                    <p
                      id="dayNum-3"
                      class="text-sm font-semibold text-slate-700"
                    >
                      -
                    </p>
                  </div>
                  <div
                    class="border-b border-l border-slate-200 px-1 pb-2 text-center"
                  >
                    <p class="text-[10px] font-medium uppercase text-slate-400">
                      Fri
                    </p>
                    <p
                      id="dayNum-4"
                      class="text-sm font-semibold text-slate-700"
                    >
                      -
                    </p>
                  </div>
                  <div
                    class="border-b border-l border-slate-200 px-1 pb-2 text-center"
                  >
                    <p class="text-[10px] font-medium uppercase text-slate-400">
                      Sat
                    </p>
                    <p
                      id="dayNum-5"
                      class="text-sm font-semibold text-slate-700"
                    >
                      -
                    </p>
                  </div>
                  <div
                    class="border-b border-l border-slate-200 px-1 pb-2 text-center"
                  >
                    <p class="text-[10px] font-medium uppercase text-slate-400">
                      Sun
                    </p>
                    <p
                      id="dayNum-6"
                      class="text-sm font-semibold text-slate-700"
                    >
                      -
                    </p>
                  </div>

                  <!-- Time column -->
                  <div class="relative">
                    <div
                      class="h-12 border-b border-slate-100 pt-1 text-[10px] text-slate-400"
                    >
                      07:00
                    </div>
                    <div
                      class="h-12 border-b border-slate-100 pt-1 text-[10px] text-slate-400"
                    >
                      08:00
                    </div>
                    <div
                      class="h-12 border-b border-slate-100 pt-1 text-[10px] text-slate-400"
                    >
                      09:00
                    </div>
                    <div
                      class="h-12 border-b border-slate-100 pt-1 text-[10px] text-slate-400"
                    >
                      10:00
                    </div>
                    <div
                      class="h-12 border-b border-slate-100 pt-1 text-[10px] text-slate-400"
                    >
                      11:00
                    </div>
                    <div
                      class="h-12 border-b border-slate-100 pt-1 text-[10px] text-slate-400"
                    >
                      12:00
                    </div>
                    <div
                      class="h-12 border-b border-slate-100 pt-1 text-[10px] text-slate-400"
                    >
                      13:00
                    </div>
                    <div
                      class="h-12 border-b border-slate-100 pt-1 text-[10px] text-slate-400"
                    >
                      14:00
                    </div>
                    <div
                      class="h-12 border-b border-slate-100 pt-1 text-[10px] text-slate-400"
                    >
                      15:00
                    </div>
                    <div
                      class="h-12 border-b border-slate-100 pt-1 text-[10px] text-slate-400"
                    >
                      16:00
                    </div>
                    <div
                      class="h-12 border-b border-slate-100 pt-1 text-[10px] font-medium text-slate-500"
                    >
                      17:00
                    </div>
                    <div
                      class="h-12 border-b border-slate-100 pt-1 text-[10px] text-slate-400"
                    >
                      18:00
                    </div>
                    <div class="h-12 pt-1 text-[10px] text-slate-400">
                      19:00
                    </div>
                  </div>

                  <!-- Columns for 7 Days (Generated dynamically by JS) -->
                  <div
                    id="weeklyCol-0"
                    class="relative border-l border-slate-100"
                  ></div>
                  <div
                    id="weeklyCol-1"
                    class="relative border-l border-slate-100"
                  ></div>
                  <div
                    id="weeklyCol-2"
                    class="relative border-l border-slate-100"
                  ></div>
                  <div
                    id="weeklyCol-3"
                    class="relative border-l border-slate-100"
                  ></div>
                  <div
                    id="weeklyCol-4"
                    class="relative border-l border-slate-100"
                  ></div>
                  <div
                    id="weeklyCol-5"
                    class="relative border-l border-slate-100 bg-slate-50/50"
                  ></div>
                  <div
                    id="weeklyCol-6"
                    class="relative border-l border-slate-100 bg-slate-50/50"
                  ></div>
                </div>
              </div>

              <!-- Legend -->
              <div
                class="flex flex-wrap items-center gap-x-5 gap-y-2 border-t border-slate-100 px-5 py-3"
              >
                <div
                  class="flex items-center gap-1.5 text-[10px] text-slate-500"
                >
                  <span class="h-2 w-2 rounded-full bg-emerald-500"></span> Work
                  Shift
                </div>
                <div
                  class="flex items-center gap-1.5 text-[10px] text-slate-500"
                >
                  <span class="h-2 w-2 rounded-full bg-indigo-500"></span>
                  Meeting
                </div>
                <div
                  class="flex items-center gap-1.5 text-[10px] text-slate-500"
                >
                  <span class="h-2 w-2 rounded-full bg-amber-500"></span> Break
                </div>
                <div
                  class="flex items-center gap-1.5 text-[10px] text-slate-500"
                >
                  <span class="h-2 w-2 rounded-full bg-violet-500"></span>
                  Training
                </div>
              </div>
            </div>

            <!-- All Schedule Section -->
            <div class="rounded-3xl bg-white shadow-sm">
              <!-- Header & Filters -->
              <div
                class="flex flex-col gap-4 border-b border-slate-100 p-6 sm:flex-row sm:items-center sm:justify-between"
              >
                <div>
                  <h2 class="text-lg font-semibold text-slate-800">
                    All Schedule
                  </h2>
                  <p class="mt-1 text-sm text-slate-500">
                    View and manage all upcoming and completed schedules.
                  </p>
                </div>

                <!-- Filters & Search -->
                <div class="flex flex-wrap items-center gap-3">
                  <!-- Search Box -->
                  <div class="relative">
                    <input
                      type="text"
                      id="scheduleSearch"
                      placeholder="Search schedule..."
                      class="w-full sm:w-48 rounded-xl border border-slate-200 bg-white pl-9 pr-4 py-2 text-sm text-slate-700 outline-none transition focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10"
                    />
                    <svg
                      class="absolute left-3 top-2.5 h-4 w-4 text-slate-400"
                      fill="none"
                      viewBox="0 0 24 24"
                      stroke="currentColor"
                    >
                      <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"
                      />
                    </svg>
                  </div>

                  <!-- Type Filter -->
                  <div>
                    <label for="scheduleType" class="sr-only"
                      >Filter Type</label
                    >
                    <select
                      id="scheduleType"
                      class="rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm text-slate-700 outline-none transition focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10"
                    >
                      <option value="all">All Type</option>
                      <option value="Work Shift">Work Shift</option>
                      <option value="Meeting">Meeting</option>
                      <option value="Break">Break</option>
                      <option value="Training">Training</option>
                    </select>
                  </div>

                  <!-- Status Filter -->
                  <div>
                    <label for="scheduleStatus" class="sr-only"
                      >Filter Status</label
                    >
                    <select
                      id="scheduleStatus"
                      class="rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm text-slate-700 outline-none transition focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10"
                    >
                      <option value="all">All Status</option>
                      <option value="Scheduled">Scheduled</option>
                      <option value="Carried Out">Carried Out</option>
                    </select>
                  </div>
                </div>
              </div>

              <!-- Table -->
              <div class="overflow-x-auto">
                <table class="min-w-full">
                  <thead class="border-b border-slate-200 bg-slate-50/70">
                    <tr class="text-left">
                      <th
                        class="px-6 py-4 text-xs font-semibold uppercase tracking-wider text-slate-500"
                      >
                        Date
                      </th>
                      <th
                        class="px-6 py-4 text-xs font-semibold uppercase tracking-wider text-slate-500"
                      >
                        Type
                      </th>
                      <th
                        class="px-6 py-4 text-xs font-semibold uppercase tracking-wider text-slate-500"
                      >
                        Title
                      </th>
                      <th
                        class="px-6 py-4 text-xs font-semibold uppercase tracking-wider text-slate-500"
                      >
                        Time
                      </th>
                      <th
                        class="px-6 py-4 text-xs font-semibold uppercase tracking-wider text-slate-500"
                      >
                        Duration
                      </th>
                      <th
                        class="px-6 py-4 text-xs font-semibold uppercase tracking-wider text-slate-500"
                      >
                        Location
                      </th>
                      <th
                        class="px-6 py-4 text-xs font-semibold uppercase tracking-wider text-slate-500"
                      >
                        Status
                      </th>
                      <th
                        class="px-6 py-4 text-xs font-semibold uppercase tracking-wider text-slate-500"
                      >
                        Actions
                      </th>
                    </tr>
                  </thead>
                  <tbody
                    id="scheduleTableBody"
                    class="divide-y divide-slate-100"
                  >
                    <!-- Rendered dynamically via JavaScript -->
                  </tbody>
                </table>
              </div>

              <!-- Empty State -->
              <div id="scheduleEmpty" class="hidden px-6 py-12 text-center">
                <div
                  class="mx-auto mb-3 flex h-12 w-12 items-center justify-center rounded-2xl bg-slate-100 text-slate-400"
                >
                  <svg
                    class="h-6 w-6"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke="currentColor"
                  >
                    <path
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      stroke-width="2"
                      d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"
                    />
                  </svg>
                </div>
                <p class="font-medium text-slate-700">No schedules found</p>
                <p class="mt-1 text-sm text-slate-500">
                  Try changing the search keyword or selected filters.
                </p>
              </div>

              <!-- Pagination -->
              <div
                class="flex items-center justify-between border-t border-slate-100 px-6 py-4"
              >
                <p class="text-sm text-slate-500">
                  Showing
                  <span id="scheduleStart" class="font-medium text-slate-700"
                    >0</span
                  >
                  -
                  <span id="scheduleEnd" class="font-medium text-slate-700"
                    >0</span
                  >
                  of
                  <span id="scheduleTotal" class="font-medium text-slate-700"
                    >0</span
                  >
                  schedules
                </p>

                <div class="flex items-center gap-2">
                  <button
                    id="schedulePrev"
                    type="button"
                    class="flex h-9 w-9 items-center justify-center rounded-xl border border-slate-200 text-slate-500 transition hover:bg-slate-50 disabled:cursor-not-allowed disabled:opacity-40"
                    aria-label="Previous page"
                  >
                    <svg
                      class="h-4 w-4"
                      viewBox="0 0 24 24"
                      fill="none"
                      stroke="currentColor"
                      stroke-width="2"
                    >
                      <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        d="M15 19l-7-7 7-7"
                      />
                    </svg>
                  </button>

                  <span
                    id="schedulePage"
                    class="flex h-9 min-w-9 items-center justify-center rounded-xl bg-indigo-50 px-3 text-sm font-medium text-indigo-600"
                  >
                    1
                  </span>

                  <button
                    id="scheduleNext"
                    type="button"
                    class="flex h-9 w-9 items-center justify-center rounded-xl border border-slate-200 text-slate-500 transition hover:bg-slate-50 disabled:cursor-not-allowed disabled:opacity-40"
                    aria-label="Next page"
                  >
                    <svg
                      class="h-4 w-4"
                      viewBox="0 0 24 24"
                      fill="none"
                      stroke="currentColor"
                      stroke-width="2"
                    >
                      <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        d="M9 5l7 7-7 7"
                      />
                    </svg>
                  </button>
                </div>
              </div>
            </div>
          </section>
        </main>
      </div>
    </div>

    <!-- Modal Detail Event -->
    <div
      id="eventDetailModal"
      class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/40 p-4 backdrop-blur-sm opacity-0 pointer-events-none transition-opacity duration-300"
    >
      <div
        id="eventModalContent"
        class="w-full max-w-md scale-95 transform rounded-3xl bg-white p-6 shadow-2xl transition-all duration-300"
      >
        <!-- Modal Header -->
        <div
          class="flex items-start justify-between border-b border-slate-100 pb-4"
        >
          <div class="flex items-center gap-3">
            <div
              id="modalBadgeIcon"
              class="flex h-10 w-10 items-center justify-center rounded-2xl bg-indigo-50 text-indigo-600"
            >
              <!-- Icon injected by JS -->
            </div>
            <div>
              <span
                id="modalCategory"
                class="inline-block rounded-full bg-indigo-100 px-2.5 py-0.5 text-[11px] font-semibold text-indigo-700"
              >
                Meeting
              </span>
              <h3 id="modalTitle" class="mt-1 text-lg font-bold text-slate-800">
                Event Title
              </h3>
            </div>
          </div>
          <button
            id="closeModalBtn"
            class="rounded-xl p-1.5 text-slate-400 transition hover:bg-slate-100 hover:text-slate-600"
            aria-label="Close modal"
          >
            <svg
              class="h-5 w-5"
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

        <!-- Modal Body -->
        <div class="mt-4 space-y-4 text-sm">
          <div class="flex items-start gap-3 text-slate-600">
            <svg
              class="mt-0.5 h-5 w-5 text-slate-400 shrink-0"
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
            <div>
              <p class="text-xs text-slate-400">Waktu & Tanggal</p>
              <p id="modalDateTime" class="font-medium text-slate-800">-</p>
            </div>
          </div>

          <div class="flex items-start gap-3 text-slate-600">
            <svg
              class="mt-0.5 h-5 w-5 text-slate-400 shrink-0"
              fill="none"
              viewBox="0 0 24 24"
              stroke="currentColor"
            >
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"
              />
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"
              />
            </svg>
            <div>
              <p class="text-xs text-slate-400">Lokasi</p>
              <p id="modalLocation" class="font-medium text-slate-800">-</p>
            </div>
          </div>

          <div class="flex items-start gap-3 text-slate-600">
            <svg
              class="mt-0.5 h-5 w-5 text-slate-400 shrink-0"
              fill="none"
              viewBox="0 0 24 24"
              stroke="currentColor"
            >
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
              />
            </svg>
            <div>
              <p class="text-xs text-slate-400">Deskripsi / Catatan</p>
              <p
                id="modalDescription"
                class="mt-0.5 text-slate-600 leading-relaxed"
              >
                -
              </p>
            </div>
          </div>
        </div>

        <!-- Modal Footer -->
        <div class="mt-6 border-t border-slate-100 pt-4 flex justify-end">
          <button
            id="confirmModalBtn"
            class="rounded-xl bg-indigo-600 px-5 py-2 text-sm font-medium text-white transition hover:bg-indigo-700 shadow-md shadow-indigo-200"
          >
            Tutup
          </button>
        </div>
      </div>
    </div>

    <!-- Delete Schedule Confirmation -->
    <div
      id="deleteScheduleModal"
      class="fixed inset-0 z-[60] flex items-center justify-center bg-slate-900/40 p-4 backdrop-blur-sm opacity-0 pointer-events-none transition-opacity duration-200"
      role="dialog"
      aria-modal="true"
      aria-labelledby="deleteScheduleTitle"
    >
      <div class="w-full max-w-md rounded-3xl bg-white p-6 shadow-2xl">
        <div class="flex items-start justify-between">
          <div>
            <h2 id="deleteScheduleTitle" class="text-lg font-bold text-slate-800">Delete Schedule?</h2>
            <p class="mt-2 text-sm text-slate-500">Are you sure you want to delete this schedule?</p>
          </div>
          <button id="closeDeleteScheduleModal" type="button" class="rounded-xl p-1.5 text-slate-400 transition hover:bg-slate-100 hover:text-slate-600" aria-label="Close delete confirmation">
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>
        <div class="mt-4 rounded-xl bg-slate-50 px-4 py-3">
          <p class="text-xs text-slate-400">Schedule</p>
          <p id="deleteScheduleName" class="mt-1 break-words text-sm font-semibold text-slate-700"></p>
        </div>
        <p class="mt-3 text-xs text-rose-600">This action cannot be undone.</p>
        <div class="mt-6 flex justify-end gap-3 border-t border-slate-100 pt-4">
          <button id="cancelDeleteSchedule" type="button" class="rounded-xl border border-slate-200 px-4 py-2 text-sm font-medium text-slate-600 transition hover:bg-slate-50">Cancel</button>
          <button id="confirmDeleteSchedule" type="button" class="rounded-xl bg-rose-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-rose-700">Delete Schedule</button>
        </div>
      </div>
    </div>

    <script>
      window.smartSchedules = @json($scheduleData);
      window.scheduleToday = @json($todayDate);
      window.scheduleWeekStart = @json($weekStart);
      window.scheduleUpcoming = @json($upcomingEvents);
      window.scheduleEditUrlTemplate = @json(route('schedules.edit', ['schedule' => '__SCHEDULE_ID__']));
      window.scheduleDeleteUrlTemplate = @json(route('schedules.destroy', ['schedule' => '__SCHEDULE_ID__']));
      window.scheduleCsrfToken = @json(csrf_token());
    </script>
    <script src="{{ asset('js/laravel/schedule.js') }}"></script>
  </body>
</html>
