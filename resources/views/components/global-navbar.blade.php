<header class="global-navbar sticky top-0 z-20 border-b border-slate-200 bg-white/95 px-4 backdrop-blur sm:px-6" data-unread-endpoint="{{ route('notifications.unread-count') }}">
  <div class="global-navbar-inner flex min-h-[76px] items-center justify-between gap-4">
    <div class="flex min-w-0 items-center gap-3">
      <button id="openSidebar" type="button" class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl border border-slate-200 text-slate-700 hover:bg-slate-50 md:hidden" aria-label="Buka menu">
        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" /></svg>
      </button>
      <div class="min-w-0">
        <p class="truncate text-sm text-slate-500">Halo, <span class="font-semibold text-slate-900">{{ $user->nickname ?: $user->name }}</span></p>
        <p class="text-xs text-slate-400" data-navbar-date data-timezone="{{ $timezone }}" data-date-format="{{ $user->preference?->settings['date_format'] ?? 'long' }}">--</p>
      </div>
    </div>

    <div class="global-navbar-actions flex shrink-0 items-center gap-2 sm:gap-3">
      <div class="global-navbar-worktime inline-flex h-11 items-center gap-2 rounded-full border border-slate-200 bg-white px-3 shadow-sm sm:px-4" data-worktime data-started-at="{{ $workStartedAt ?? '' }}" aria-live="off">
        <span class="h-2.5 w-2.5 rounded-full {{ $workStartedAt ? 'bg-emerald-500' : 'bg-slate-300' }}" data-worktime-indicator></span>
        <span class="hidden text-sm text-slate-500 sm:inline">Work Time</span>
        <span class="font-mono text-sm font-semibold tabular-nums text-slate-900" data-worktime-value>{{ $workStartedAt ? '00:00:00' : '—' }}</span>
      </div>

      <div class="global-navbar-date hidden h-11 items-center gap-2 rounded-full border border-slate-200 bg-white px-3 text-sm font-medium text-slate-700 shadow-sm sm:flex sm:px-4">
        <svg class="h-5 w-5 text-blue-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true"><rect x="3" y="5" width="18" height="16" rx="2" stroke-width="1.8"/><path stroke-linecap="round" stroke-width="1.8" d="M16 3v4M8 3v4M3 10h18"/></svg>
        <span data-navbar-full-date data-timezone="{{ $timezone }}" data-date-format="{{ $user->preference?->settings['date_format'] ?? 'long' }}">--</span>
      </div>

      <a href="{{ route('notifications.index') }}" aria-label="Buka notifikasi" class="relative flex h-11 w-11 items-center justify-center rounded-full border border-slate-200 bg-white text-slate-700 shadow-sm transition hover:bg-slate-50">
        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M18 9a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9m-8 12a2 2 0 0 0 4 0" /></svg>
        <span data-unread-badge data-count="{{ $unreadCount }}" @class(['absolute -right-1 -top-1 flex h-5 min-w-5 items-center justify-center rounded-full bg-blue-600 px-1 text-[10px] font-bold text-white ring-2 ring-white', 'hidden' => $unreadCount < 1])>{{ $unreadCount > 99 ? '99+' : $unreadCount }}</span>
      </a>

      <details class="global-profile-menu relative">
        <summary class="flex h-11 cursor-pointer list-none items-center gap-2 rounded-full border border-slate-200 bg-white py-1 pl-1 pr-2 shadow-sm transition hover:bg-slate-50 sm:gap-3 sm:pr-3">
          <img src="{{ route('profile.avatar') }}" alt="" class="h-9 w-9 rounded-full object-cover" />
          <span class="hidden min-w-0 text-left sm:block"><span class="block max-w-32 truncate text-xs font-semibold text-slate-900">{{ $user->name }}</span><span class="block max-w-32 truncate text-[11px] text-slate-500">{{ $user->job_title ?: 'LEXA Smart Attendance' }}</span></span>
          <svg class="hidden h-4 w-4 text-slate-500 sm:block" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m7 10 5 5 5-5" /></svg>
        </summary>
        <div class="global-profile-dropdown absolute right-0 top-full z-30 mt-2 w-56 rounded-2xl border border-slate-200 bg-white p-2 shadow-xl">
          <div class="border-b border-slate-100 px-3 py-2"><p class="truncate text-sm font-semibold text-slate-900">{{ $user->name }}</p><p class="truncate text-xs text-slate-500">{{ $user->job_title ?: 'LEXA Smart Attendance' }}</p></div>
          <a href="{{ route('profile.edit') }}#profile-details" class="block rounded-xl px-3 py-2 text-sm text-slate-700 hover:bg-slate-50">My Profile</a>
          <a href="{{ route('settings.index') }}" class="block rounded-xl px-3 py-2 text-sm text-slate-700 hover:bg-slate-50">Settings</a>
          <form method="POST" action="{{ route('logout') }}" data-confirm="Apakah Anda yakin ingin mengakhiri sesi dan keluar dari akun LEXA Smart Attendance?" data-confirm-title="Keluar dari Akun?" data-confirm-text="Ya, Keluar">@csrf<button type="submit" class="w-full rounded-xl px-3 py-2 text-left text-sm text-rose-700 hover:bg-rose-50">Logout</button></form>
        </div>
      </details>

      <form method="POST" action="{{ route('logout') }}" class="hidden lg:block" data-confirm="Apakah Anda yakin ingin mengakhiri sesi dan keluar dari akun LEXA Smart Attendance?" data-confirm-title="Keluar dari Akun?" data-confirm-text="Ya, Keluar">@csrf<button type="submit" class="inline-flex h-11 items-center gap-2 rounded-full border border-slate-200 bg-white px-4 text-sm font-medium text-slate-600 shadow-sm transition hover:bg-slate-50"><svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M10 17l5-5-5-5m5 5H3m9-9h6a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-6" /></svg>Keluar</button></form>
    </div>
  </div>
</header>
