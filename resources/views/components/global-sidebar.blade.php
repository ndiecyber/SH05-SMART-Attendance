@php
  $navigationItems = auth()->user()->isAdmin() ? [
    ['label' => 'Dashboard', 'url' => route('admin.dashboard'), 'routes' => ['admin.dashboard', 'dashboard'], 'icon' => 'M3 10.5 12 3l9 7.5M5 9v11h14V9M9 20v-6h6v6'],
    ['label' => 'Worker Management', 'url' => route('admin.workers.index'), 'routes' => ['admin.workers.*'], 'icon' => 'M16 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2m6-10a4 4 0 1 0 0-8 4 4 0 0 0 0 8Zm8-1v6m3-3h-6'],
    ['label' => 'Worker Attendance', 'url' => route('admin.worker-attendance.index'), 'routes' => ['admin.worker-attendance.*'], 'icon' => 'M8 4h8M8 8h8M8 12h8M8 16h5M5 2h14a2 2 0 0 1 2 2v16l-4-2-5 2-5-2-4 2V4a2 2 0 0 1 2-2Z'],
    ['label' => 'Leave Requests', 'url' => route('admin.leave-requests.index'), 'routes' => ['admin.leave-requests.*'], 'icon' => 'M4 5h16v14H4zM4 7l8 6 8-6'],
    ['label' => 'Permission Requests', 'url' => route('admin.permission-requests.index'), 'routes' => ['admin.permission-requests.*'], 'icon' => 'M7 3h7l5 5v13H7a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2Zm7 0v5h5M9 13h6m-6 4h6'],
    ['label' => 'Notifications', 'url' => route('notifications.index'), 'routes' => ['notifications.*'], 'icon' => 'M18 9a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9m-8 12a2 2 0 0 0 4 0'],
    ['label' => 'Settings', 'url' => route('admin.settings.index'), 'routes' => ['admin.settings.*'], 'icon' => 'M12 8a4 4 0 1 0 0 8 4 4 0 0 0 0-8Zm0-6v2m0 16v2m10-10h-2M4 12H2'],
  ] : [
    ['label' => 'Dashboard', 'url' => route('dashboard.index'), 'routes' => ['dashboard', 'dashboard.index'], 'icon' => 'M3 10.5 12 3l9 7.5M5 9v11h14V9M9 20v-6h6v6'],
    ['label' => 'Check In/Out', 'url' => route('attendance.check-in'), 'routes' => ['attendance.check-in'], 'icon' => 'M12 6v6l4 2m6-2a10 10 0 1 1-20 0 10 10 0 0 1 20 0Z'],
    ['label' => 'My Attendance', 'url' => route('attendance.index'), 'routes' => ['attendance.index'], 'icon' => 'M8 4h8M8 8h8M8 12h8M8 16h5M5 2h14a2 2 0 0 1 2 2v16l-4-2-5 2-5-2-4 2V4a2 2 0 0 1 2-2Z'],
    ['label' => 'Schedule', 'url' => route('schedules.index'), 'routes' => ['schedules.*'], 'icon' => 'M8 3v4m8-4v4M4 9h16M5 5h14a1 1 0 0 1 1 1v13H4V6a1 1 0 0 1 1-1Z'],
    ['label' => 'Leave Request', 'url' => route('leave.index'), 'routes' => ['leave.*'], 'icon' => 'M4 5h16v14H4zM4 7l8 6 8-6'],
    ['label' => 'Permission', 'url' => route('permissions.index'), 'routes' => ['permissions.*'], 'icon' => 'M7 3h7l5 5v13H7a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2Zm7 0v5h5M9 13h6m-6 4h6'],
    ['label' => 'Attendance Log', 'url' => route('attendance.logs'), 'routes' => ['attendance.logs*'], 'icon' => 'M8 6h12M8 12h12M8 18h12M4 6h.01M4 12h.01M4 18h.01'],
    ['label' => 'Reports', 'url' => route('reports.index'), 'routes' => ['reports.*'], 'icon' => 'M4 20h16M6 16v-5m6 5V4m6 12V9'],
    ['label' => 'Notifications', 'url' => route('notifications.index'), 'routes' => ['notifications.*'], 'icon' => 'M18 9a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9m-8 12a2 2 0 0 0 4 0'],
    ['label' => 'Settings', 'url' => route('settings.index'), 'routes' => ['profile.*', 'settings.*'], 'icon' => 'M12 8a4 4 0 1 0 0 8 4 4 0 0 0 0-8Zm0-6v2m0 16v2m10-10h-2M4 12H2m17.1-7.1-1.4 1.4M6.3 17.7l-1.4 1.4m14.2 0-1.4-1.4M6.3 6.3 4.9 4.9'],
  ];
@endphp

<div id="sidebarOverlay" class="fixed inset-0 z-30 hidden bg-black/40 md:hidden"></div>

<aside id="sidebar" class="fixed inset-y-0 left-0 z-40 flex w-72 -translate-x-full flex-col border-r border-slate-200 bg-[#000033] shadow-xl transition-transform duration-300 md:translate-x-0">
  <div class="sidebar-brand flex items-center justify-between px-6 py-5">
    <a href="{{ auth()->user()->isAdmin() ? route('admin.dashboard') : route('dashboard.index') }}" class="flex min-w-0 items-center gap-3" aria-label="LEXA Smart Attendance">
      <span class="flex h-12 w-12 shrink-0 items-center justify-center text-white shadow-lg">
        <img src="{{ asset('assets/LEXA-logo.png') }}" alt="LEXA Logo" class="sidebar-logo h-12 w-12 rounded-full object-contain shadow-lg" />
      </span>
      <span class="sidebar-brand-copy min-w-0" aria-hidden="true">
        <span class="sidebar-brand-line sidebar-brand-lexa">LEXA</span>
        <span class="sidebar-brand-line sidebar-brand-smart">SMART</span>
        <span class="sidebar-brand-line sidebar-brand-attendance">ATTENDANCE</span>
      </span>
    </a>

    <button id="sidebarCollapseButton" type="button" class="sidebar-collapse-button hidden h-9 w-9 shrink-0 items-center justify-center rounded-xl text-slate-200 transition hover:bg-white/10 hover:text-white md:flex" aria-label="Perkecil sidebar" aria-expanded="true" title="Perkecil sidebar">
      <svg class="sidebar-collapse-icon h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m14 18-6-6 6-6" /></svg>
    </button>

    <button id="closeSidebar" type="button" class="rounded-xl p-2 text-white hover:bg-white/10 md:hidden" aria-label="Tutup menu">
      <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18 18 6M6 6l12 12" /></svg>
    </button>
  </div>

  <nav class="sidebar-navigation mt-4 flex-1 space-y-1 overflow-y-auto px-3 pb-4" aria-label="Navigasi utama">
    @foreach ($navigationItems as $navigationItem)
      @php $isActive = request()->routeIs(...$navigationItem['routes']); @endphp
      <a href="{{ $navigationItem['url'] }}" data-sidebar-tooltip="{{ $navigationItem['label'] }}" @if($isActive) aria-current="page" @endif @class(['sidebar-nav-link flex items-center gap-3 rounded-2xl px-3 py-1 text-sm transition', 'bg-indigo-50 font-semibold text-indigo-700' => $isActive, 'font-medium text-slate-200 hover:bg-white/10' => !$isActive])>
        <span @class(['sidebar-nav-icon flex h-8 w-8 shrink-0 items-center justify-center rounded-xl', 'bg-indigo-100' => $isActive, 'bg-white/10' => !$isActive])>
          <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $navigationItem['icon'] }}" /></svg>
        </span>
        <span class="sidebar-nav-label whitespace-nowrap">{{ $navigationItem['label'] }}</span>
      </a>
    @endforeach
  </nav>

  <div class="sidebar-profile-wrap px-3 pb-6 pt-4">
    <div class="sidebar-profile-card rounded-3xl bg-white/10 p-2 backdrop-blur">
      <div class="flex items-center gap-3">
        <img src="{{ route('profile.avatar', ['v' => auth()->user()->updated_at?->timestamp ?? 0]) }}" alt="Foto profil {{ auth()->user()->name }}" class="h-10 w-10 shrink-0 rounded-2xl object-cover" />
        <div class="sidebar-profile-copy min-w-0">
          <p class="block max-w-full truncate font-semibold text-white" title="{{ auth()->user()->name }}">{{ auth()->user()->name }}</p>
          <p class="block max-w-full truncate text-sm text-slate-300" title="{{ auth()->user()->job_title ?: '—' }}">{{ auth()->user()->job_title ?: '—' }}</p>
        </div>
      </div>
    </div>
  </div>
</aside>
