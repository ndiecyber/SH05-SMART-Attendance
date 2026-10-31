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
                  Permission
                </h1>
              </div>

              <!-- Right : Add Button -->
              <a
                href="{{ route('permissions.create') }}"
                class="flex items-center justify-center gap-2 rounded-xl bg-indigo-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-indigo-700"
              >
                + Request Permission
              </a>
            </div>

            <!-- Main grid -->
            <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
              <!-- Pending -->
              <div
                class="flex items-center gap-4 rounded-3xl bg-white p-5 shadow-sm"
              >
                <div
                  class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-amber-100 text-amber-500"
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
                  <p class="text-sm text-slate-500">Pending</p>
                  <h2
                    id="pendingCount"
                    class="text-3xl font-bold text-amber-500"
                  >
                    0
                  </h2>
                  <p class="text-sm text-slate-500">Waiting Approval</p>
                </div>
              </div>

              <!-- Approved -->
              <div
                class="flex items-center gap-4 rounded-3xl bg-white p-5 shadow-sm"
              >
                <div
                  class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-emerald-100 text-emerald-600"
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
                  <p class="text-sm text-slate-500">Approved</p>
                  <h2
                    id="approvedCount"
                    class="text-3xl font-bold text-emerald-600"
                  >
                    8
                  </h2>
                  <p class="text-sm text-slate-500">Approved this month</p>
                </div>
              </div>

              <!-- reject -->
              <div
                class="flex items-center gap-4 rounded-3xl bg-white p-5 shadow-sm"
              >
                <div
                  class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-red-100 text-red-600"
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
                      d="M12 21a9 9 0 100-18 9 9 0 000 18z"
                    />
                    <path
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      stroke-width="2"
                      d="M9 9l6 6"
                    />
                    <path
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      stroke-width="2"
                      d="M15 9l-6 6"
                    />
                  </svg>
                </div>

                <div>
                  <p class="text-sm text-slate-500">Rejected</p>
                  <h2
                    id="rejectedCount"
                    class="text-3xl font-bold text-red-600"
                  >
                    1
                  </h2>
                  <p class="text-sm text-slate-500">Rejected this month</p>
                </div>
              </div>

              <!-- hour taken -->
              <div
                class="flex items-center gap-4 rounded-3xl bg-white p-5 shadow-sm"
              >
                <div
                  class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-blue-100 text-blue-600"
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
                      d="M12 8v5l3 2M9 2h6M12 5v3M19.07 6.93l1.41-1.41M12 22a8 8 0 100-16 8 8 0 000 16z"
                    />
                  </svg>
                </div>

                <div>
                  <p class="text-sm text-slate-500">Total Hours</p>
                  <h2
                    id="totalHoursCount"
                    class="text-3xl font-bold text-blue-600"
                  >
                    31h 30m
                  </h2>
                  <p class="text-sm text-slate-500">
                    this month's accumulation
                  </p>
                </div>
              </div>
            </div>

            <div class="grid gap-6 lg:grid-cols-11">
              <!-- ================= LEFT : permission ================= -->
              <div class="rounded-3xl bg-white p-6 shadow-sm lg:col-span-6">
                <div class="flex items-center justify-between">
                  <div>
                    <h3 class="mt-1 text-xl font-semibold text-slate-800">
                      Permission Today's
                    </h3>
                    <p class="text-sm text-slate-500">
                      Permission Timeline for today
                    </p>
                  </div>

                  <!-- Tambahan opsional: Penanda jumlah permission hari ini -->
                  <span
                    id="todayPermissionCount"
                    class="rounded-full bg-indigo-100 px-3 py-1 text-xs font-medium text-indigo-700"
                  >
                    0 Today
                  </span>
                </div>

                <!-- Container tempat data JS di-render -->
                <div
                  id="upcomingPermissionList"
                  class="mt-6 space-y-3 max-h-[420px] overflow-y-auto pr-1"
                >
                  <!-- Data dari JS akan muncul di sini secara otomatis -->
                </div>
              </div>

              <!-- ================= RIGHT : team permission ================= -->
              <div class="rounded-3xl bg-white p-6 shadow-sm lg:col-span-5">
                <div class="flex items-center justify-between">
                  <div>
                    <h3 class="mt-1 text-xl font-semibold text-slate-800">
                      On Permission Team
                    </h3>
                    <p class="text-sm text-slate-500">
                      Team members on permission / WFH today
                    </p>
                  </div>

                  <span
                    id="teamPermissionCount"
                    class="rounded-full bg-amber-100 px-3 py-1 text-xs font-medium text-amber-700"
                  >
                    0 Members
                  </span>
                </div>

                <div id="teamPermissionList" class="mt-6 rounded-2xl border border-dashed border-slate-200 p-6 text-center text-sm text-slate-500">
                  No team members assigned yet. Your team will appear here once assigned by your administrator.
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
                    placeholder="Search by permission type, reason..."
                    class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm outline-none transition focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100"
                  />
                </div>

                <!-- Leave Type -->
                <div class="min-w-[200px]">
                  <label
                    for="filterType"
                    class="mb-2 block text-sm font-medium text-slate-700"
                  >
                    Permission Type
                  </label>

                  <select
                    id="filterType"
                    class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm outline-none transition focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100"
                  >
                    <option value="">All Types</option>
                    <option value="Arrive Late">Arrive late</option>
                    <option value="Leave Early">Leave early</option>
                    <option value="WFH">WFH</option>
                    <option value="Pop Out">Pop out</option>
                    <option value="Business Trip">Business Trip</option>
                  </select>
                </div>

                <!-- Status -->
                <div class="min-w-[180px]">
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
                    <option value="approved">Approved</option>
                    <option value="pending">Pending</option>
                    <option value="rejected">Rejected</option>
                    <option value="cancelled">Cancelled</option>
                  </select>
                </div>

                <!-- month -->
                <div class="min-w-[150px]">
                  <label
                    for="filterMonth"
                    class="mb-2 block text-sm font-medium text-slate-700"
                  >
                    Month
                  </label>

                  <select
                    id="filterMonth"
                    class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm outline-none transition focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100"
                  >
                    <option value="">All Months</option>
                    @foreach (range(1, 12) as $month)
                      <option value="{{ $month }}">{{ \Illuminate\Support\Carbon::create()->month($month)->format('F') }}</option>
                    @endforeach
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
                    Permission Request History
                  </h2>
                </div>

                <span
                  class="inline-flex items-center rounded-full bg-slate-100 px-3 py-1 text-sm font-medium text-slate-600"
                >
                  Requests
                </span>
              </div>

              <!-- Table Container -->
              <!-- Table Wrapper (Tanpa Scroll Bar) -->
              <div class="w-full">
                <table class="w-full text-left text-sm">
                  <thead
                    class="border-b border-slate-200 text-xs font-semibold uppercase text-slate-500"
                  >
                    <tr>
                      <th class="py-3 px-2">ID</th>
                      <th class="py-3 px-2">Type</th>
                      <th class="py-3 px-2">Date</th>
                      <th class="py-3 px-2 hidden sm:table-cell">Time</th>
                      <th class="py-3 px-2 hidden lg:table-cell">Duration</th>
                      <th class="py-3 px-2 hidden md:table-cell">Reason</th>
                      <th class="py-3 px-2">Status</th>
                      <th class="py-3 px-2 hidden xl:table-cell">Submitted</th>
                      <th class="py-3 px-2 text-center">Action</th>
                    </tr>
                  </thead>

                  <tbody
                    id="historyTableBody"
                    class="divide-y divide-slate-100"
                  >
                    <!-- Data akan dirender melalui JavaScript -->
                  </tbody>
                </table>
              </div>

              <!-- Pagination -->
              <div
                class="mt-6 flex flex-col gap-3 border-t border-slate-200 pt-4 sm:flex-row sm:items-center sm:justify-between"
              >
                <!-- Info -->
                <p class="text-sm text-slate-500">
                  Showing <span id="startItem">1</span> -
                  <span id="endItem">8</span> of
                  <span id="totalItems">15</span> requests
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

            <!-- kebijakan cuti -->
            <div
              class="rounded-3xl bg-indigo-50 p-6 shadow-xl border border-slate-300"
            >
              <div class="mb-5">
                <p class="text-sm text-slate-500">Information</p>
                <h2 class="text-2xl font-semibold text-slate-800">
                  Permission Policy - LEXA
                </h2>
              </div>

              <div class="grid gap-4 md:grid-cols-2">
                <!-- Policy 1 -->
                <div
                  class="flex items-start gap-4 rounded-2xl border border-slate-300 p-4"
                >
                  <div
                    class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-indigo-100 font-semibold text-indigo-600"
                  >
                    1
                  </div>

                  <div>
                    <h3 class="font-semibold text-slate-800">
                      Submit Leave in Advance
                    </h3>
                    <p class="mt-1 text-sm leading-6 text-slate-500">
                      Submit annual or personal leave at least
                      <strong>3 working days</strong>
                      before the leave date unless it is an emergency.
                    </p>
                  </div>
                </div>

                <!-- Policy 2 -->
                <div
                  class="flex items-start gap-4 rounded-2xl border border-slate-300 p-4"
                >
                  <div
                    class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-indigo-100 font-semibold text-indigo-600"
                  >
                    2
                  </div>

                  <div>
                    <h3 class="font-semibold text-slate-800">
                      Medical Certificate
                    </h3>
                    <p class="mt-1 text-sm leading-6 text-slate-500">
                      Sick leave exceeding
                      <strong>2 consecutive days</strong> must be supported by a
                      valid medical certificate.
                    </p>
                  </div>
                </div>

                <!-- Policy 3 -->
                <div
                  class="flex items-start gap-4 rounded-2xl border border-slate-300 p-4"
                >
                  <div
                    class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-indigo-100 font-semibold text-indigo-600"
                  >
                    3
                  </div>

                  <div>
                    <h3 class="font-semibold text-slate-800">
                      Approval Required
                    </h3>
                    <p class="mt-1 text-sm leading-6 text-slate-500">
                      Leave requests are valid only after receiving approval
                      from the employee's direct supervisor.
                    </p>
                  </div>
                </div>

                <!-- Policy 4 -->
                <div
                  class="flex items-start gap-4 rounded-2xl border border-slate-300 p-4"
                >
                  <div
                    class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-indigo-100 font-semibold text-indigo-600"
                  >
                    4
                  </div>

                  <div>
                    <h3 class="font-semibold text-slate-800">Unpaid Leave</h3>
                    <p class="mt-1 text-sm leading-6 text-slate-500">
                      Unpaid leave is subject to management approval and company
                      operational requirements.
                    </p>
                  </div>
                </div>
              </div>
            </div>
          </section>
        
<!-- Modal Detail Permission -->
    <div
      id="permissionModal"
      class="fixed inset-0 z-50 hidden items-center justify-center bg-black/40 p-4 backdrop-blur-sm opacity-0 transition-opacity duration-300"
    >
      <div
        class="w-full max-w-md scale-95 rounded-3xl bg-white p-6 shadow-xl transition-transform duration-300"
        id="modalContent"
      >
        <div
          class="flex items-center justify-between border-b border-slate-100 pb-4"
        >
          <h3 class="text-lg font-semibold text-slate-800">
            Permission Details
          </h3>
          <button
            onclick="closeModal()"
            class="rounded-xl p-2 text-slate-400 hover:bg-slate-100 hover:text-slate-600"
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
                d="M6 18L18 6M6 6l12 12"
              />
            </svg>
          </button>
        </div>
        <div class="mt-4 space-y-4" id="modalBody">
          <!-- Konten detail dimasukkan via JS -->
        </div>
        <div class="mt-6 flex justify-end">
          <button
            onclick="closeModal()"
            class="rounded-xl bg-slate-100 px-5 py-2 text-sm font-medium text-slate-600 hover:bg-slate-200"
          >
            Close
          </button>
        </div>
      </div>
    </div>

@endsection

@push('scripts')
  <script>
      window.smartPermissionData = @json($permissionRequests);
      window.smartPermissionToday = @json(today()->toDateString());
    </script>
  <script src="{{ asset('js/permission.js') }}"></script>
@endpush
