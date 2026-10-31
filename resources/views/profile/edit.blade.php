@extends('layouts.app')

@section('title', 'Settings | LEXA Smart Attendance')

@section('content')
  @php
    $settingsTabs = [
      'profile' => ['label' => 'Profil', 'icon' => 'M20 21a8 8 0 0 0-16 0m8-10a4 4 0 1 0 0-8 4 4 0 0 0 0 8Z'],
      'security' => ['label' => 'Keamanan', 'icon' => 'M12 22s8-4 8-11V5l-8-3-8 3v6c0 7 8 11 8 11Zm-3-11 2 2 4-4'],
      'preferences' => ['label' => 'Preferensi', 'icon' => 'M12 8a4 4 0 1 0 0 8 4 4 0 0 0 0-8Zm0-6v2m0 16v2m10-10h-2M4 12H2'],
      'attendance' => ['label' => 'Kehadiran', 'icon' => 'M8 3v4m8-4v4M4 9h16M5 5h14a1 1 0 0 1 1 1v13H4V6a1 1 0 0 1 1-1Z'],
      'notifications' => ['label' => 'Notifikasi', 'icon' => 'M18 9a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9m-8 12a2 2 0 0 0 4 0'],
      'privacy' => ['label' => 'Privasi', 'icon' => 'M12 22s8-4 8-11V5l-8-3-8 3v6c0 7 8 11 8 11Z'],
    ];
  @endphp

  <section class="mx-auto max-w-[1600px] space-y-6 p-4 sm:p-6 lg:p-8">
    <div class="flex flex-col justify-between gap-4 xl:flex-row xl:items-center">
      <div class="flex min-w-0 flex-1 items-start gap-4">
        <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-blue-100 text-blue-600"><svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 8a4 4 0 1 0 0 8 4 4 0 0 0 0-8Zm0-6v2m0 16v2m10-10h-2M4 12H2m16.95-6.95-1.4 1.4M6.45 17.55l-1.4 1.4m12.9 0-1.4-1.4m-10.1-10.1-1.4-1.4"/></svg></span>
        <div class="min-w-0"><h1 class="text-3xl font-bold tracking-tight text-slate-950">Settings</h1><p class="mt-1 max-w-3xl text-sm text-slate-500">Kelola profil, keamanan, preferensi tampilan, aturan kehadiran, dan notifikasi akun Anda.</p></div>
      </div>
      <div class="flex flex-wrap items-center justify-end gap-2 xl:flex-nowrap xl:shrink-0">
        <span id="settingsSaveStatus" class="inline-flex shrink-0 items-center gap-2 whitespace-nowrap rounded-full bg-emerald-50 px-3 py-2 text-xs font-semibold text-emerald-700" role="status" aria-live="polite">
          <span class="h-2 w-2 rounded-full bg-emerald-500"></span>{{ $errors->any() ? 'Gagal menyimpan' : (session('success') ? 'Semua tersimpan' : 'Semua tersimpan') }}
        </span>
        <form method="POST" action="{{ route('settings.reset-preferences') }}" data-confirm="Kembalikan preferensi tampilan, sidebar, dan notifikasi ke nilai default? Data profil dan riwayat tidak akan dihapus.">@csrf<button type="submit" class="inline-flex h-10 shrink-0 items-center justify-center gap-2 whitespace-nowrap rounded-xl border border-slate-200 bg-white px-3 text-sm font-medium text-slate-700 shadow-sm hover:bg-slate-50"><svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12a9 9 0 1 0 3-6.7L3 8m0-5v5h5"/></svg>Reset ke default</button></form>
        <button type="button" id="settingsSaveButton" class="inline-flex h-10 shrink-0 items-center justify-center gap-2 whitespace-nowrap rounded-xl bg-blue-600 px-4 text-sm font-semibold leading-none text-white shadow-sm hover:bg-blue-700"><svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3h12l4 4v14H3V3h2Zm2 0v6h10V3M7 21v-7h10v7"/></svg>Simpan perubahan</button>
      </div>
    </div>

    @if ($errors->any())
      <div role="alert" class="rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700"><p class="font-semibold">Perubahan belum tersimpan. Periksa data berikut:</p><ul class="mt-1 list-inside list-disc">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
    @endif
    <div class="grid gap-4 xl:grid-cols-[minmax(0,1.8fr)_minmax(300px,0.85fr)]">
      <article class="flex min-w-0 flex-col gap-4 rounded-3xl border border-slate-200 bg-white p-5 shadow-sm sm:flex-row sm:items-center sm:p-6">
        <div class="relative shrink-0"><img src="{{ route('profile.avatar') }}" alt="Foto profil {{ $profileUser->name }}" class="h-20 w-20 rounded-3xl object-cover ring-1 ring-slate-200" /><span class="absolute -bottom-1 -right-2 rounded-full bg-emerald-50 px-2 py-1 text-[10px] font-bold text-emerald-700 ring-2 ring-white">Aktif</span></div>
        <div class="min-w-0 flex-1">
          <div class="flex flex-wrap items-center gap-2"><h2 class="text-xl font-bold text-slate-950">{{ $profileUser->name }}</h2><span class="rounded-full bg-emerald-50 px-2.5 py-1 text-[11px] font-semibold text-emerald-700">Akun aktif</span></div>
          <p class="mt-1 text-sm text-slate-500">{{ $profileUser->job_title ?: 'Jabatan belum diatur' }}</p>
          <div class="mt-3 grid gap-x-6 gap-y-2 text-xs text-slate-500 sm:grid-cols-2">
            <span class="flex items-center gap-2 truncate"><svg class="h-4 w-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor"><rect x="3" y="5" width="18" height="14" rx="2"/><path stroke-linecap="round" d="m3 7 9 6 9-6"/></svg>{{ $profileUser->email }}</span>
            <span class="flex items-center gap-2"><svg class="h-4 w-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M5 4h3l2 5-2 1a11 11 0 0 0 6 6l1-2 5 2v3a1 1 0 0 1-1 1A16 16 0 0 1 4 5a1 1 0 0 1 1-1Z"/></svg>{{ $profileUser->phone ?: 'Nomor telepon belum diisi' }}</span>
            <span class="flex items-center gap-2 truncate"><svg class="h-4 w-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M4 20h16M6 20V6l6-3 6 3v14M9 9h1m4 0h1m-6 4h1m4 0h1m-5 7v-4h2v4"/></svg>{{ $profileUser->university ?: 'Universitas belum diisi' }}</span>
            @if ($profileUser->division)<span class="flex items-center gap-2 truncate">Divisi: {{ $profileUser->division }}</span>@endif
          </div>
        </div>
        <a href="#profile-details" data-settings-open-tab="profile" class="inline-flex shrink-0 items-center justify-center rounded-xl border border-slate-200 px-3 py-2 text-sm font-medium text-slate-700 shadow-sm hover:bg-slate-50">Kelola profil</a>
      </article>

      <article class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
        <div class="flex items-center justify-between gap-3"><h2 class="font-semibold text-slate-900">Kelengkapan Profil</h2><span class="text-2xl font-bold text-blue-600">{{ $profileCompleteness }}%</span></div>
        <div class="mt-4 h-2.5 overflow-hidden rounded-full bg-slate-100"><div class="h-full rounded-full bg-blue-600 transition-all" style="width: {{ $profileCompleteness }}%"></div></div>
        <p class="mt-3 text-xs leading-relaxed text-slate-500">{{ $profileCompleteness === 100 ? 'Informasi profil yang tersedia sudah lengkap.' : 'Lengkapi data profil yang tersedia agar informasi pada laporan tetap akurat.' }}</p>
      </article>
    </div>

    <nav class="flex gap-1 overflow-x-auto rounded-full bg-slate-100/80 p-1" aria-label="Tab pengaturan" role="tablist">
      @foreach ($settingsTabs as $tab => $details)
        <button type="button" role="tab" id="settings-tab-{{ $tab }}" aria-controls="settings-panel-{{ $tab }}" aria-selected="{{ $tab === 'profile' ? 'true' : 'false' }}" data-settings-tab="{{ $tab }}" @class(['inline-flex shrink-0 items-center gap-2 rounded-full px-4 py-2.5 text-sm font-medium transition', 'bg-white text-slate-900 shadow-sm' => $tab === 'profile', 'text-slate-600 hover:text-slate-900' => $tab !== 'profile'])><svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="{{ $details['icon'] }}"/></svg>{{ $details['label'] }}</button>
      @endforeach
    </nav>

    <form id="profileSettingsForm" class="contents" method="POST" action="{{ request()->routeIs('profile.edit') ? route('profile.update') : route('settings.update') }}" enctype="multipart/form-data" data-settings-form="profile">
      @csrf @method('PUT')
      <div id="settings-panel-profile" role="tabpanel" aria-labelledby="settings-tab-profile" data-settings-panel="profile" class="grid items-start gap-4 2xl:grid-cols-[minmax(0,1.55fr)_minmax(300px,0.8fr)_minmax(280px,0.75fr)]">
        <article id="profile-details" class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
          <div class="mb-5 flex items-center gap-3"><span class="flex h-10 w-10 items-center justify-center rounded-full bg-blue-50 text-blue-600"><svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M20 21a8 8 0 0 0-16 0m8-10a4 4 0 1 0 0-8 4 4 0 0 0 0 8Z"/></svg></span><div><h2 class="font-semibold text-slate-900">Data Pribadi</h2><p class="text-xs text-slate-500">Informasi ini muncul di laporan kehadiran dan dokumen internship.</p></div></div>
          <div class="grid gap-4 sm:grid-cols-2">
            <label class="settings-field">Nama lengkap<input name="name" value="{{ old('name', $profileUser->name) }}" autocomplete="name" required maxlength="255" /></label>
            <label class="settings-field">Nama panggilan<input name="nickname" value="{{ old('nickname', $profileUser->nickname) }}" maxlength="80" /></label>
            <label class="settings-field">Email<input type="email" name="email" value="{{ old('email', $profileUser->email) }}" autocomplete="email" required maxlength="255" /><small>Gunakan email kantor untuk notifikasi resmi.</small></label>
            <label class="settings-field">Nomor HP<input type="tel" name="phone" value="{{ old('phone', $profileUser->phone) }}" autocomplete="tel" maxlength="40" placeholder="+62 812 3456 7890" /></label>
            <label class="settings-field">Universitas<input name="university" value="{{ old('university', $profileUser->university) }}" maxlength="255" /></label>
            <label class="settings-field">Jurusan / Departemen<input name="major" value="{{ old('major', $profileUser->major) }}" maxlength="255" /></label>
            <label class="settings-field">NIM / Student ID<input name="student_id" value="{{ old('student_id', $profileUser->student_id) }}" maxlength="100" /></label>
            <label class="settings-field">Divisi<input value="{{ $profileUser->division ?: 'Belum ditetapkan' }}" disabled /><small>Dikelola oleh administrator.</small></label>
            <label class="settings-field sm:col-span-2">Jabatan / Posisi<input name="job_title" value="{{ old('job_title', $profileUser->job_title) }}" maxlength="255" /></label>
            <label class="settings-field sm:col-span-2">Alamat<input name="address" value="{{ old('address', $profileUser->address) }}" maxlength="1000" autocomplete="street-address" /></label>
            <label class="settings-field sm:col-span-2">Bio singkat<textarea name="bio" rows="3" maxlength="240">{{ old('bio', $profileUser->bio) }}</textarea><small data-bio-count>{{ mb_strlen(old('bio', $profileUser->bio ?? '')) }}/240 karakter</small></label>
            <div class="settings-field sm:col-span-2"><span>Foto profil</span><div class="flex flex-wrap items-center gap-4"><img id="profilePhotoPreview" src="{{ route('profile.avatar') }}" alt="Pratinjau foto profil" class="h-14 w-14 rounded-2xl object-cover ring-1 ring-slate-200" /><label class="inline-flex cursor-pointer items-center rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">Pilih foto<input id="profilePhotoInput" type="file" name="avatar" accept="image/jpeg,image/png,image/webp" class="sr-only" /></label><small>JPG, PNG, atau WebP · maks. 2 MB</small></div><small data-avatar-error class="hidden text-rose-600">Pilih gambar maksimal 2 MB.</small></div>
          </div>
        </article>

        <div class="space-y-4">
          <article class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
            <div class="mb-5 flex items-start gap-3"><span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-blue-50 text-blue-600">✚</span><div><h2 class="font-semibold text-slate-900">Kontak Darurat</h2><p class="text-xs text-slate-500">Dihubungi jika terjadi keadaan mendesak.</p></div></div>
            <div class="space-y-4">
              <label class="settings-field">Nama<input name="emergency[name]" value="{{ old('emergency.name', $profileUser->emergencyContact?->name) }}" maxlength="255" /></label>
              <label class="settings-field">Hubungan<input name="emergency[relationship]" value="{{ old('emergency.relationship', $profileUser->emergencyContact?->relationship) }}" maxlength="100" /></label>
              <label class="settings-field">Telepon utama<input type="tel" name="emergency[primary_phone]" value="{{ old('emergency.primary_phone', $profileUser->emergencyContact?->primary_phone) }}" maxlength="40" /></label>
              <label class="settings-field">Telepon alternatif<input type="tel" name="emergency[alternative_phone]" value="{{ old('emergency.alternative_phone', $profileUser->emergencyContact?->alternative_phone) }}" maxlength="40" /></label>
            </div>
          </article>
          <article class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
            <div class="mb-3 flex items-center gap-3"><span class="flex h-10 w-10 items-center justify-center rounded-full bg-blue-50 text-blue-600">◎</span><div><h2 class="font-semibold text-slate-900">Mentor &amp; Supervisor</h2><p class="text-xs text-slate-500">Informasi ditetapkan oleh administrator.</p></div></div>
            @if ($mentor)<p class="text-sm font-semibold text-slate-900">{{ $mentor->name }}</p><p class="text-xs text-slate-500">{{ $mentor->job_title }}</p><p class="text-xs text-slate-500">{{ $mentor->email }}</p>@else<p class="rounded-2xl bg-slate-50 p-4 text-sm text-slate-500">Belum ada data mentor yang terhubung ke akun ini.</p>@endif
          </article>
        </div>

        <aside class="space-y-4">
          <article class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6"><div class="mb-4 flex items-center gap-3"><span class="flex h-10 w-10 items-center justify-center rounded-full bg-blue-50 text-blue-600">盾</span><div><h2 class="font-semibold text-slate-900">Tips Keamanan</h2><p class="text-xs text-slate-500">Jaga keamanan akun Anda.</p></div></div><ul class="space-y-3 text-sm text-slate-600"><li>Gunakan kata sandi yang unik dan kuat.</li><li>Jangan membagikan foto absensi atau informasi akun.</li><li>Keluar dari akun pada perangkat bersama.</li><li>Pastikan email profil masih dapat diakses.</li></ul></article>
          <article class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6"><div class="mb-2 flex items-center gap-3"><span class="flex h-10 w-10 items-center justify-center rounded-full bg-blue-50 text-blue-600">?</span><div><h2 class="font-semibold text-slate-900">Butuh bantuan?</h2><p class="text-xs text-slate-500">Hubungi administrator sistem.</p></div></div><p class="rounded-2xl border border-slate-200 bg-slate-50 p-4 text-sm text-slate-600">Untuk pembaruan divisi, jabatan resmi, dan data mentor, silakan minta bantuan administrator LEXA.</p></article>
        </aside>
      </div>
    </form>

      <form id="securitySettingsForm" class="contents" method="POST" action="{{ route('settings.security.update') }}" data-settings-form="security">@csrf @method('PUT')
      <section id="settings-panel-security" role="tabpanel" aria-labelledby="settings-tab-security" data-settings-panel="security" class="hidden grid gap-4 lg:grid-cols-[minmax(0,1.4fr)_minmax(280px,0.8fr)]">
        <article class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6"><h2 class="text-lg font-semibold text-slate-900">Ubah kata sandi</h2><p class="mt-1 text-sm text-slate-500">Verifikasi kata sandi saat ini untuk memperbarui kredensial akun.</p><div class="mt-5 grid gap-4 sm:grid-cols-2"><label class="settings-field sm:col-span-2">Kata sandi saat ini<span class="settings-password-field"><input type="password" name="current_password" autocomplete="current-password" data-password-field /><button type="button" data-password-toggle aria-label="Tampilkan kata sandi">Lihat</button></span></label><label class="settings-field">Kata sandi baru<span class="settings-password-field"><input type="password" name="new_password" autocomplete="new-password" data-password-field /><button type="button" data-password-toggle aria-label="Tampilkan kata sandi">Lihat</button></span></label><label class="settings-field">Konfirmasi kata sandi<span class="settings-password-field"><input type="password" name="new_password_confirmation" autocomplete="new-password" data-password-field /><button type="button" data-password-toggle aria-label="Tampilkan kata sandi">Lihat</button></span></label></div></article>
        <article class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6"><h2 class="font-semibold text-slate-900">Keamanan akun</h2><p class="mt-2 text-sm text-slate-500">Kata sandi disimpan menggunakan hashing Laravel. Two-factor authentication dan daftar sesi aktif belum tersedia pada autentikasi aplikasi ini.</p><p class="mt-4 rounded-xl bg-blue-50 p-3 text-xs text-blue-800">Perubahan kata sandi akan tersimpan bersama tombol “Simpan perubahan”.</p></article>
      </section>
      </form>

      <form id="preferencesSettingsForm" class="contents" method="POST" action="{{ route('settings.preferences.update') }}" data-settings-form="preferences">@csrf @method('PUT')
      <section id="settings-panel-preferences" role="tabpanel" aria-labelledby="settings-tab-preferences" data-settings-panel="preferences" class="hidden grid gap-4 lg:grid-cols-2">
        <article class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6"><h2 class="text-lg font-semibold text-slate-900">Preferensi tampilan</h2><p class="mt-1 text-sm text-slate-500">Preferensi disimpan pada akun dan diterapkan saat halaman berikutnya dibuka.</p><div class="mt-5 grid gap-4 sm:grid-cols-2"><label class="settings-field">Tema<select name="preferences[theme]"><option value="light" @selected($preferences['theme'] === 'light')>Terang</option><option value="dark" @selected($preferences['theme'] === 'dark')>Gelap</option></select></label><label class="settings-field">Bahasa tanggal<select name="preferences[language]"><option value="id" @selected($preferences['language'] === 'id')>Indonesia</option><option value="en" @selected($preferences['language'] === 'en')>English</option></select><small>Memengaruhi format lokal dan tanggal.</small></label><label class="settings-field">Format tanggal<select name="preferences[date_format]"><option value="long" @selected($preferences['date_format'] === 'long')>Sabtu, 31 Oktober 2026</option><option value="short" @selected($preferences['date_format'] === 'short')>31 Okt 2026</option></select></label><label class="settings-field">Format waktu<select name="preferences[time_format]"><option value="24h" @selected($preferences['time_format'] === '24h')>24 jam</option><option value="12h" @selected($preferences['time_format'] === '12h')>12 jam</option></select></label><label class="settings-field sm:col-span-2">Zona waktu<select name="preferences[timezone]">@foreach (['Asia/Jakarta', 'Asia/Makassar', 'Asia/Jayapura', 'Asia/Singapore', 'UTC', 'Europe/London', 'America/New_York'] as $timezoneOption)<option value="{{ $timezoneOption }}" @selected($preferences['timezone'] === $timezoneOption)>{{ $timezoneOption }}</option>@endforeach</select></label><label class="settings-field sm:col-span-2">Preferensi sidebar<select name="preferences[sidebar_collapsed]"><option value="0" @selected(! $preferences['sidebar_collapsed'])>Expanded</option><option value="1" @selected($preferences['sidebar_collapsed'])>Collapsed</option></select><small>Preferensi ini akan diselaraskan dengan tombol collapse pada sidebar.</small></label></div></article>
        <article class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6"><h2 class="font-semibold text-slate-900">Perubahan diterapkan</h2><ul class="mt-3 space-y-3 text-sm text-slate-600"><li>Tema mengubah warna dasar antarmuka.</li><li>Zona waktu digunakan untuk tanggal aplikasi dan perhitungan kehadiran.</li><li>Format tanggal mengikuti bahasa dan pilihan format di atas.</li><li>Preferensi sidebar dipulihkan dari akun setelah login.</li></ul></article>
      </section>
      </form>

      <section id="settings-panel-attendance" role="tabpanel" aria-labelledby="settings-tab-attendance" data-settings-panel="attendance" class="hidden grid gap-4 lg:grid-cols-2">
        <article class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6"><h2 class="text-lg font-semibold text-slate-900">Jadwal kerja hari ini</h2><p class="mt-1 text-sm text-slate-500">Informasi berikut berasal dari jadwal akun dan aturan sistem yang sedang aktif.</p>@if ($workSchedule)<dl class="mt-5 grid grid-cols-2 gap-4 text-sm"><div><dt class="text-slate-500">Jadwal</dt><dd class="mt-1 font-medium text-slate-900">{{ $workSchedule->name ?: $workSchedule->type }}</dd></div><div><dt class="text-slate-500">Status</dt><dd class="mt-1 font-medium text-slate-900">{{ ucfirst($workSchedule->status) }}</dd></div><div><dt class="text-slate-500">Check-in</dt><dd class="mt-1 font-medium text-slate-900">{{ \Illuminate\Support\Carbon::parse($workSchedule->start_time)->format($preferences['time_format'] === '12h' ? 'g:i A' : 'H:i') }}</dd></div><div><dt class="text-slate-500">Check-out</dt><dd class="mt-1 font-medium text-slate-900">{{ \Illuminate\Support\Carbon::parse($workSchedule->end_time)->format($preferences['time_format'] === '12h' ? 'g:i A' : 'H:i') }}</dd></div></dl>@else<p class="mt-5 rounded-2xl bg-slate-50 p-4 text-sm text-slate-500">Belum ada jadwal workshift untuk hari ini.</p>@endif</article>
        <article class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6"><h2 class="font-semibold text-slate-900">Aturan kehadiran sistem</h2><dl class="mt-4 space-y-4 text-sm"><div class="flex justify-between gap-3"><dt class="text-slate-500">Validasi lokasi</dt><dd class="font-medium text-slate-900">{{ $locationRequirementsEnabled ? 'Aktif' : 'Belum dikonfigurasi' }}</dd></div><div class="flex justify-between gap-3"><dt class="text-slate-500">Aturan terlambat</dt><dd class="font-medium text-slate-900">Mengikuti waktu mulai workshift</dd></div><div class="flex justify-between gap-3"><dt class="text-slate-500">Verifikasi</dt><dd class="font-medium text-slate-900">GPS dan jaringan kantor</dd></div></dl><p class="mt-5 rounded-xl bg-amber-50 p-3 text-xs text-amber-800">Konfigurasi global bersifat read-only untuk menjaga validasi Check In/Out.</p></article>
      </section>

      <form id="notificationsSettingsForm" class="contents" method="POST" action="{{ route('settings.notifications.update') }}" data-settings-form="notifications">@csrf @method('PUT')
      <section id="settings-panel-notifications" role="tabpanel" aria-labelledby="settings-tab-notifications" data-settings-panel="notifications" class="hidden grid gap-4 lg:grid-cols-2">
        <article class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6"><h2 class="text-lg font-semibold text-slate-900">Kanal notifikasi</h2><p class="mt-1 text-sm text-slate-500">Preferensi In-App dan kategori diterapkan oleh sistem Notifications.</p><div class="mt-5 space-y-3"><label class="settings-toggle"><span><strong>In-App Notification</strong><small>Notifikasi di pusat informasi aplikasi.</small></span><input type="hidden" name="notifications[channels][in_app]" value="0"><input type="checkbox" name="notifications[channels][in_app]" value="1" @checked($notificationChannels['in_app'])></label><input type="hidden" name="notifications[channels][email]" value="0"><label class="settings-toggle settings-toggle-disabled"><span><strong>Email Notification</strong><small>Belum aktif: layanan email belum terintegrasi.</small></span><input type="checkbox" disabled aria-label="Email notification belum tersedia"></label><input type="hidden" name="notifications[channels][browser]" value="0"><label class="settings-toggle settings-toggle-disabled"><span><strong>Browser / Push Notification</strong><small>Belum aktif: mekanisme push belum tersedia.</small></span><input type="checkbox" disabled aria-label="Push notification belum tersedia"></label></div></article>
        <article class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6"><h2 class="font-semibold text-slate-900">Kategori notifikasi</h2><div class="mt-4 space-y-3">@foreach (['attendance' => 'Attendance', 'approval' => 'Approval', 'schedule' => 'Schedule', 'system' => 'System', 'announcement' => 'Announcement'] as $category => $label)<label class="settings-toggle"><span><strong>{{ $label }}</strong><small>Pemberitahuan kategori {{ strtolower($label) }}.</small></span><input type="hidden" name="notifications[categories][{{ $category }}]" value="0"><input type="checkbox" name="notifications[categories][{{ $category }}]" value="1" @checked($notificationCategories[$category] ?? true)></label>@endforeach</div><a href="{{ route('notifications.index') }}" class="mt-5 inline-flex text-sm font-semibold text-blue-700 hover:text-blue-800">Buka Notifications →</a></article>
      </section>
      </form>

      <section id="settings-panel-privacy" role="tabpanel" aria-labelledby="settings-tab-privacy" data-settings-panel="privacy" class="hidden grid gap-4 lg:grid-cols-2">
        <article class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6"><h2 class="text-lg font-semibold text-slate-900">Privasi dan data akun</h2><p class="mt-2 text-sm leading-relaxed text-slate-600">Halaman profil dan foto hanya dapat diakses setelah autentikasi. Data pengaturan disimpan terpisah untuk akun yang sedang masuk.</p><div class="mt-5 rounded-2xl bg-slate-50 p-4"><h3 class="text-sm font-semibold text-slate-900">Ekspor data akun</h3><p class="mt-1 text-xs text-slate-500">Unduh salinan data profil, kontak darurat, dan preferensi milik akun ini.</p><a href="{{ route('settings.export') }}" class="mt-3 inline-flex rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm font-medium text-slate-700 shadow-sm hover:bg-slate-50">Unduh data saya</a></div></article>
        <article class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6"><h2 class="font-semibold text-slate-900">Kontrol sesi</h2><p class="mt-2 text-sm text-slate-600">Sesi akun dilindungi middleware autentikasi Laravel. Gunakan Logout pada menu profil untuk mengakhiri sesi saat ini. Pengelolaan daftar sesi perangkat belum tersedia.</p></article>
      </section>
  </section>
@endsection

@push('scripts')
  <script src="{{ asset('js/settings.js') }}?v=settings-2"></script>
@endpush
