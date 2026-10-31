@extends('layouts.app')

@section('title', 'Smart Attendance Dashboard - Leave Request')

@section('content')
          <!-- Header -->
          

          <!-- Content -->
          <section class="space-y-6 p-4 sm:p-6">
            <div
              class="flex flex-col gap-3 rounded-2xl bg-white p-2 shadow-sm md:flex-row md:items-center md:justify-between"
            >
              <div class="pl-4 flex flex-wrap gap-5">
                <h1 class="text-2xl font-semibold text-slate-800">
                  Leave Request
                </h1>
              </div>

              <!-- Right : Add Button -->
              <a
                href="{{ route('leave.create') }}"
                class="flex items-center justify-center gap-2 rounded-xl bg-indigo-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-indigo-700"
              >
                + Request Leave
              </a>
            </div>

            <!-- 4 Cards Stat Status Cuti -->
            <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
              <!-- Pending -->
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
                  <p class="text-sm text-slate-500">Pending</p>
                  <h2
                    id="statPending"
                    class="text-3xl font-bold text-amber-500"
                  >
                    {{ $leaveStats['pending'] }}
                  </h2>
                  <p class="text-sm text-slate-500">Waiting Approval</p>
                </div>
              </div>

              <!-- Approved -->
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
                  <p class="text-sm text-slate-500">Approved (YTD)</p>
                  <h2
                    id="statApproved"
                    class="text-3xl font-bold text-emerald-600"
                  >
                    {{ $leaveStats['approvedYtd'] }}
                  </h2>
                  <p class="text-sm text-slate-500">Approved this year</p>
                </div>
              </div>

              <!-- Rejected -->
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
                  <p class="text-sm text-slate-500">Rejected (YTD)</p>
                  <h2 id="statRejected" class="text-3xl font-bold text-red-600">
                    {{ $leaveStats['rejectedYtd'] }}
                  </h2>
                  <p class="text-sm text-slate-500">Rejected this year</p>
                </div>
              </div>

              <!-- Days Taken -->
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
                  <p class="text-sm text-slate-500">Approved Days (YTD)</p>
                  <h2
                    id="statDaysTaken"
                    class="text-3xl font-bold text-blue-600"
                  >
                    {{ $leaveStats['approvedDaysYtd'] }}
                  </h2>
                  <p class="text-sm text-slate-500">Approved leave days</p>
                </div>
              </div>
            </div>

            <!-- Main grid -->
            <div class="grid gap-6 lg:grid-cols-7">
              <!-- ================= LEFT : upcoming leave ================= -->
              <div class="rounded-3xl bg-white p-6 shadow-sm lg:col-span-4">
                <div class="flex items-center justify-between">
                  <div>
                    <p class="text-sm text-slate-500">Upcoming Leave</p>
                    <h3 class="mt-1 text-xl font-semibold text-slate-800">
                      Approved Leave
                    </h3>
                  </div>

                  <span
                    id="upcomingLeaveCount"
                    class="rounded-full bg-emerald-100 px-3 py-1 text-xs font-medium text-emerald-700"
                  >
                    0 Leaves
                  </span>
                </div>

                <div id="upcomingLeaveList" class="mt-6 space-y-4"></div>
              </div>

              <!-- ================= RIGHT : team leave ================= -->
              <div class="rounded-3xl bg-white p-6 shadow-sm lg:col-span-3">
                <div class="flex items-center justify-between">
                  <div>
                    <p class="text-sm text-slate-500">Team Leave</p>
                    <h3 class="mt-1 text-xl font-semibold text-slate-800">
                      On Leave & Upcoming
                    </h3>
                  </div>

                  <span
                    id="teamLeaveCount"
                    class="rounded-full bg-amber-100 px-3 py-1 text-xs font-medium text-amber-700"
                  >
                    Private
                  </span>
                </div>

                <div id="teamLeaveList" class="mt-6 rounded-2xl border border-slate-200 p-4 text-sm text-slate-500">
                  Team leave details are not available for this account.
                </div>
              </div>
            </div>

            <!-- bottom content Filter -->
            <div class="rounded-3xl bg-white p-5 shadow-sm">
              <div class="flex flex-col gap-4 xl:flex-row xl:items-end">
                <!-- Search -->
                <div class="flex-1">
                  <label
                    for="searchLeave"
                    class="mb-2 block text-sm font-medium text-slate-700"
                  >
                    Search
                  </label>

                  <input
                    id="searchLeave"
                    type="text"
                    placeholder="Search by ID, leave type, reason..."
                    class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm outline-none transition focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100"
                  />
                </div>

                <!-- Leave Type -->
                <div class="min-w-[200px]">
                  <label
                    for="filterType"
                    class="mb-2 block text-sm font-medium text-slate-700"
                  >
                    Leave Type
                  </label>

                  <select
                    id="filterType"
                    class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm outline-none transition focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100"
                  >
                    <option value="">All Types</option>
                    <option value="annual">Annual Leave</option>
                    <option value="sick">Sick Leave</option>
                    <option value="personal">Personal Leave</option>
                    <option value="unpaid">Unpaid Leave</option>
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

                <!-- Year -->
                <div class="min-w-[150px]">
                  <label
                    for="filterYear"
                    class="mb-2 block text-sm font-medium text-slate-700"
                  >
                    Year
                  </label>

                  <select
                    id="filterYear"
                    class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm outline-none transition focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100"
                  >
                    <option value="">All Years</option>
                    @foreach ($availableYears as $year)
                      <option value="{{ $year }}">{{ $year }}</option>
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
                    Leave Request History
                  </h2>
                </div>

                <span
                  id="historyTotalBadge"
                  class="inline-flex items-center rounded-full bg-slate-100 px-3 py-1 text-sm font-medium text-slate-600"
                >
                  0 Requests
                </span>
              </div>

              <!-- Table -->
              <div class="overflow-x-auto">
                <table class="min-w-full">
                  <thead class="border-b border-slate-200">
                    <tr class="text-left text-sm font-semibold text-slate-600">
                      <th class="px-4 py-3">Request ID</th>
                      <th class="px-4 py-3">Leave Type</th>
                      <th class="px-4 py-3">Period</th>
                      <th class="px-4 py-3">Days</th>
                      <th class="px-4 py-3">Reason</th>
                      <th class="px-4 py-3">Status</th>
                      <th class="px-4 py-3">Submitted</th>
                      <th class="px-4 py-3 text-center">Action</th>
                    </tr>
                  </thead>

                  <tbody
                    id="historyTableBody"
                    class="divide-y divide-slate-100 text-sm"
                  >
                    <!-- Dynamic Rows Rendered by JS -->
                  </tbody>
                </table>

                <div id="leaveEmpty" class="hidden px-6 py-12 text-center">
                  <p class="font-medium text-slate-700">No leave requests yet.</p>
                  <p class="mt-1 text-sm text-slate-500">Your submitted requests will appear here.</p>
                </div>

                <!-- Pagination -->
                <div
                  class="mt-6 flex flex-col gap-3 border-t border-slate-200 pt-4 sm:flex-row sm:items-center sm:justify-between"
                >
                  <p class="text-sm text-slate-500">
                    Showing <span id="startItem">0</span> -
                    <span id="endItem">0</span> of
                    <span id="totalItems">0</span> requests
                  </p>

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
                      Page 1 of 1
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

            <!-- kebijakan cuti -->
            <div
              class="rounded-3xl bg-indigo-50 p-6 shadow-xl border border-slate-300"
            >
              <div class="mb-5">
                <p class="text-sm text-slate-500">Information</p>
                <h2 class="text-2xl font-semibold text-slate-800">
                  Leave Policy - LEXA
                </h2>
              </div>

              <div class="grid gap-4 md:grid-cols-2">
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
                      <strong>3 working days</strong> before the leave date
                      unless it is an emergency.
                    </p>
                  </div>
                </div>

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
        
<!-- Modal Popup Detail Rincian Acara -->
    <div
      id="detailModal"
      class="fixed inset-0 z-50 flex hidden items-center justify-center bg-black/50 p-4 backdrop-blur-sm"
    >
      <div
        class="w-full max-w-lg rounded-3xl bg-white p-6 shadow-2xl transition-all"
      >
        <div
          class="flex items-center justify-between border-b border-slate-100 pb-4"
        >
          <h3 class="text-xl font-bold text-slate-800" id="modalTitle">
            Leave Details
          </h3>
          <button
            id="closeModal"
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
          <!-- Populated by JavaScript -->
        </div>

        <div class="mt-6 flex justify-end">
          <button
            id="closeModalBtn"
            class="rounded-xl bg-slate-800 px-5 py-2.5 text-sm font-medium text-white transition hover:bg-slate-700"
          >
            Close
          </button>
        </div>
      </div>
    </div>

@endsection

@push('scripts')
  <script>
      window.smartLeaveRequests = @json($leaveData);
      window.smartUpcomingLeaves = @json($upcomingLeaves);
      window.smartLeaveYears = @json($availableYears);
      window.leaveCancelUrlTemplate = @json(route('leave.cancel', ['leaveRequest' => '__LEAVE_REQUEST_ID__']));
    </script>
  <script src="{{ asset('js/laravel/leave.js') }}"></script>
@endpush
