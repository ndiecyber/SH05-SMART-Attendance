<!doctype html>
<html lang="id">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Form Pengajuan Schedule</title>
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
          href="{{ route('schedules.index') }}"
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
        <h1 class="text-xl font-bold text-slate-900">{{ $schedule ? 'Edit Schedule' : 'Pengajuan Schedule' }}</h1>
        <p class="text-xs text-slate-500 mt-1">
          {{ $schedule ? 'Perbarui informasi jadwal di bawah ini.' : 'Isi formulir di bawah ini untuk mengajukan jadwal baru.' }}
        </p>
      </div>

      <!-- Form -->
      <form id="scheduleForm" action="{{ $schedule ? route('schedules.update', $schedule) : route('schedules.store') }}" method="POST" class="space-y-4">
        @csrf
        @if ($schedule)
          @method('PUT')
        @endif
        @if ($errors->any())
          <div class="rounded-lg border border-rose-200 bg-rose-50 px-3 py-2 text-sm text-rose-700" role="alert">
            <ul class="list-inside list-disc space-y-1">
              @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
              @endforeach
            </ul>
          </div>
        @endif
        <!-- Schedule Name -->
        <div>
          <label for="name" class="block text-xs font-semibold text-slate-600 uppercase tracking-wider mb-1">Schedule Name</label>
          <input type="text" id="name" name="name" value="{{ old('name', $schedule?->name ?? $schedule?->type) }}" placeholder="Contoh: Standard Intern" required maxlength="255" class="w-full px-3 py-2 text-sm bg-slate-50 border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:bg-white transition-all" />
        </div>

        <!-- Date -->
        <div>
          <label
            for="date"
            class="block text-xs font-semibold text-slate-600 uppercase tracking-wider mb-1"
            >Date</label
          >
          <input
            type="date"
            id="date"
            name="schedule_date"
            value="{{ old('schedule_date', $schedule?->schedule_date?->format('Y-m-d')) }}"
            required
            class="w-full px-3 py-2 text-sm bg-slate-50 border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:bg-white transition-all"
          />
        </div>

        <!-- Type -->
        <div>
          <label
            for="type"
            class="block text-xs font-semibold text-slate-600 uppercase tracking-wider mb-1"
            >Type</label
          >
          <select
            id="type"
            name="type"
            required
            class="w-full px-3 py-2 text-sm bg-slate-50 border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:bg-white transition-all"
          >
            <option value="" disabled @selected(old('type', $schedule?->type) === null)>Pilih tipe jadwal</option>
            <option value="Workshift" @selected(old('type', $schedule?->type) === 'Workshift')>Workshift</option>
            <option value="Training" @selected(old('type', $schedule?->type) === 'Training')>Training</option>
            <option value="Meeting" @selected(old('type', $schedule?->type) === 'Meeting')>Meeting</option>
            <option value="Break" @selected(old('type', $schedule?->type) === 'Break')>Break</option>
          </select>
        </div>

        <!-- Time Range (Dari jam berapa ke berapa) -->
        <div class="grid grid-cols-2 gap-3">
          <div>
            <label
              for="startTime"
              class="block text-xs font-semibold text-slate-600 uppercase tracking-wider mb-1"
              >Start Time</label
            >
            <input
              type="time"
              id="startTime"
              name="start_time"
              value="{{ old('start_time', substr($schedule?->start_time ?? '', 0, 5)) }}"
              required
              class="w-full px-3 py-2 text-sm bg-slate-50 border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:bg-white transition-all"
            />
          </div>
          <div>
            <label
              for="endTime"
              class="block text-xs font-semibold text-slate-600 uppercase tracking-wider mb-1"
              >End Time</label
            >
            <input
              type="time"
              id="endTime"
              name="end_time"
              value="{{ old('end_time', substr($schedule?->end_time ?? '', 0, 5)) }}"
              required
              class="w-full px-3 py-2 text-sm bg-slate-50 border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:bg-white transition-all"
            />
          </div>
        </div>

        <!-- Duration (Otomatis terhitung) -->
        <div>
          <label
            for="duration"
            class="block text-xs font-semibold text-slate-600 uppercase tracking-wider mb-1"
            >Duration</label
          >
          <input
            type="text"
            id="duration"
            readonly
            placeholder="Otomatis terhitung"
            class="w-full px-3 py-2 text-sm bg-slate-100 border border-slate-200 text-slate-500 rounded-lg focus:outline-none cursor-not-allowed"
          />
        </div>

        <!-- Location -->
        <div>
          <label
            for="location"
            class="block text-xs font-semibold text-slate-600 uppercase tracking-wider mb-1"
            >Location</label
          >
          <input
            type="text"
            id="location"
            name="location"
            value="{{ old('location', $schedule?->location) }}"
            placeholder="Masukkan lokasi/ruangan"
            required
            class="w-full px-3 py-2 text-sm bg-slate-50 border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:bg-white transition-all"
          />
        </div>

        <!-- Submit and Cancel Buttons -->
        <div class="flex gap-3 pt-2">
          <a href="{{ route('schedules.index') }}" class="flex-1 rounded-lg border border-slate-200 bg-white px-4 py-2.5 text-center text-sm font-medium text-slate-600 transition hover:bg-slate-50">
            Cancel
          </a>
          <button
            type="submit"
            class="flex-1 rounded-lg bg-indigo-600 px-4 py-2.5 text-sm font-medium text-white shadow-sm transition-all duration-200 hover:bg-indigo-700 hover:shadow"
          >
            {{ $schedule ? 'Save Changes' : 'Add Schedule' }}
          </button>
        </div>
      </form>
    </div>

    @include('components.alert-system')
    <script src="{{ asset('js/alert-system.js') }}?v=alerts-1"></script>
    <!-- Menghubungkan File JS External -->
    <script src="{{ asset('js/laravel/schedule-add.js') }}"></script>
  </body>
</html>
