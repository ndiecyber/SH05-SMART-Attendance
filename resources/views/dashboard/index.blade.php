@extends('layouts.app')

@section('title', 'Smart Attendance Dashboard')

@section('content')
          <!-- Header -->
          

          <!-- Content -->
          <section class="space-y-6 p-4 sm:p-6">
            <!-- Quick actions -->
            <div class="rounded-3xl bg-white p-6 shadow-sm">
              <p class="text-sm text-slate-500">Quick Actions</p>
              <h3 class="mt-1 text-xl font-semibold text-slate-800">
                Quick Menu
              </h3>

              <div class="mt-5 grid grid-cols-2 gap-3">
                <a
                  href="{{ route('attendance.check-in') }}"
                  class="rounded-2xl bg-indigo-50 px-4 py-4 text-left transition hover:bg-indigo-100"
                >
                  <p class="font-semibold text-indigo-700">
                    Check-in / Check-out
                  </p>
                  <p class="mt-1 text-xs text-indigo-500">
                    Record your attendance.
                  </p>
                </a>

                <a
                  href="{{ route('reports.index') }}"
                  class="rounded-2xl bg-emerald-50 px-4 py-4 text-left transition hover:bg-emerald-100"
                >
                  <p class="font-semibold text-emerald-700">Report</p>
                  <p class="mt-1 text-xs text-emerald-500">
                    View previous attendance records
                  </p>
                </a>

                <a
                  href="{{ route('schedules.index') }}"
                  class="rounded-2xl bg-amber-50 px-4 py-4 text-left transition hover:bg-amber-100"
                >
                  <p class="font-semibold text-amber-700">Schedule</p>
                  <p class="mt-1 text-xs text-amber-500">
                    Check your work shift
                  </p>
                </a>

                <a
                  href="{{ route('profile.edit') }}"
                  class="rounded-2xl bg-sky-50 px-4 py-4 text-left transition hover:bg-sky-100"
                >
                  <p class="font-semibold text-sky-700">Profile</p>
                  <p class="mt-1 text-xs text-sky-500">
                    Manage account information
                  </p>
                </a>
              </div>
            </div>
          </section>
        
@endsection

@push('scripts')
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <script src="{{ asset('js/script.js') }}"></script>
@endpush
