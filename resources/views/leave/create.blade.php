<!doctype html>
<html lang="id">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Form Pengajuan Leave</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
  </head>
  <body
    class="bg-slate-100 min-h-screen py-8 px-4 flex justify-center items-start text-slate-800 font-sans"
  >
    <!-- Container Utama -->
    <div
      class="w-full max-w-md bg-white rounded-2xl shadow-sm border border-slate-200 p-6 relative"
    >
      <!-- Tombol Back Kiri Atas -->
      <div class="mb-4">
        <a
          href="{{ route('leave.index') }}"
          id="backBtn"
          class="inline-flex items-center text-sm font-medium text-slate-500 hover:text-slate-800 transition-colors"
        >
          <svg
            class="w-4 h-4 mr-1"
            fill="none"
            stroke="currentColor"
            viewBox="0 0 24 24"
            xmlns="http://www.w3.org/2000/svg"
          >
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M15 19l-7-7 7-7"
            ></path>
          </svg>
          Kembali
        </a>
      </div>

      <!-- Header Form -->
      <div class="border-b border-slate-100 pb-4 mb-5">
        <h1 class="text-xl font-bold text-slate-900">Pengajuan Leave</h1>
        <p class="text-xs text-slate-500 mt-1">
          Isi formulir di bawah ini untuk mengajukan permohonan cuti/izin.
        </p>
      </div>

      <!-- Form -->
      <form id="leaveForm" action="{{ route('leave.store') }}" method="POST" class="space-y-4">
        @csrf
        @if ($errors->any())
          <div class="rounded-lg border border-rose-200 bg-rose-50 px-3 py-2 text-sm text-rose-700" role="alert">
            <ul class="list-inside list-disc space-y-1">
              @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
              @endforeach
            </ul>
          </div>
        @endif
        <!-- Leave Type -->
        <div>
          <label
            for="leaveType"
            class="block text-xs font-semibold text-slate-600 uppercase tracking-wider mb-1"
            >Leave Type</label
          >
          <select
            id="leaveType"
            name="leave_type"
            required
            class="w-full px-3 py-2 text-sm bg-slate-50 border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:bg-white transition-all"
          >
            <option value="" disabled @selected(old('leave_type') === null)>Pilih jenis cuti</option>
            <option value="Annual Leave" @selected(old('leave_type') === 'Annual Leave')>Annual Leave</option>
            <option value="Sick Leave" @selected(old('leave_type') === 'Sick Leave')>Sick Leave</option>
            <option value="Personal Leave" @selected(old('leave_type') === 'Personal Leave')>Personal Leave</option>
            <option value="Unpaid Leave" @selected(old('leave_type') === 'Unpaid Leave')>Unpaid Leave</option>
          </select>
        </div>

        <!-- Period (Rentang Tanggal) -->
        <div class="grid grid-cols-2 gap-3">
          <div>
            <label
              for="startDate"
              class="block text-xs font-semibold text-slate-600 uppercase tracking-wider mb-1"
              >Start Date</label
            >
            <input
              type="date"
              id="startDate"
              name="start_date"
              value="{{ old('start_date') }}"
              required
              class="w-full px-3 py-2 text-sm bg-slate-50 border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:bg-white transition-all"
            />
          </div>
          <div>
            <label
              for="endDate"
              class="block text-xs font-semibold text-slate-600 uppercase tracking-wider mb-1"
              >End Date</label
            >
            <input
              type="date"
              id="endDate"
              name="end_date"
              value="{{ old('end_date') }}"
              required
              class="w-full px-3 py-2 text-sm bg-slate-50 border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:bg-white transition-all"
            />
          </div>
        </div>

        <!-- Days (Total Hari) -->
        <div>
          <label
            for="days"
            class="block text-xs font-semibold text-slate-600 uppercase tracking-wider mb-1"
            >Days</label
          >
          <input
            type="text"
            id="days"
            readonly
            placeholder="Otomatis terhitung"
            class="w-full px-3 py-2 text-sm bg-slate-100 border border-slate-200 text-slate-500 rounded-lg focus:outline-none cursor-not-allowed"
          />
        </div>

        <!-- Reason -->
        <div>
          <label
            for="reason"
            class="block text-xs font-semibold text-slate-600 uppercase tracking-wider mb-1"
            >Reason</label
          >
          <textarea
            id="reason"
            name="reason"
            rows="3"
            placeholder="Tuliskan alasan pengajuan..."
            required
            class="w-full px-3 py-2 text-sm bg-slate-50 border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:bg-white transition-all resize-none"
          >{{ old('reason') }}</textarea>
        </div>

        <!-- Submit Button -->
        <div class="flex gap-3 pt-2">
          <a href="{{ route('leave.index') }}" class="flex-1 rounded-lg border border-slate-200 bg-white px-4 py-2.5 text-center text-sm font-medium text-slate-600 transition hover:bg-slate-50">
            Cancel
          </a>
          <button
            type="submit"
            class="flex-1 rounded-lg bg-indigo-600 px-4 py-2.5 text-sm font-medium text-white shadow-sm transition-all duration-200 hover:bg-indigo-700 hover:shadow"
          >
            Submit Request
          </button>
        </div>
      </form>
    </div>

    @include('components.alert-system')
    <script src="{{ asset('js/alert-system.js') }}?v=alerts-1"></script>
    <!-- Menghubungkan JavaScript -->
    <script src="{{ asset('js/laravel/leave-add.js') }}"></script>
  </body>
</html>
