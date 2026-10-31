<!doctype html>
<html lang="{{ app()->getLocale() }}" data-timezone="{{ $uiPreferences['timezone'] ?? config('app.timezone') }}" data-theme="{{ $uiPreferences['theme'] ?? 'light' }}" data-sidebar-collapsed="{{ ($uiPreferences['sidebar_collapsed'] ?? false) ? 'true' : 'false' }}" data-sidebar-preference-url="{{ route('settings.sidebar-preference.update') }}">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <title>@yield('title', 'Smart Attendance')</title>

    <script>
      try {
        localStorage.setItem('lexa.sidebar.collapsed', document.documentElement.dataset.sidebarCollapsed);
      } catch (error) {
        // The server preference remains available when browser storage is unavailable.
      }
    </script>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
      tailwind.config = {
        theme: {
          extend: {
            fontFamily: { sans: ["Inter", "sans-serif"] },
            colors: { primary: { 50: "#eef2ff", 100: "#e0e7ff", 500: "#6366f1", 600: "#4f46e5", 700: "#4338ca" } },
          },
        },
      };
    </script>
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet" />
    <link href="{{ asset('css/global-sidebar.css') }}?v=sidebar-4" rel="stylesheet" />
    @yield('head')
    @stack('head-scripts')
  </head>
  <body class="min-h-screen bg-slate-100 font-sans text-slate-800">
    <div class="min-h-screen bg-[#f5f7fb]">
      <div id="appShell" class="app-shell flex min-h-screen">
        @include('components.global-sidebar')
        <main id="appMain" class="app-main-canvas min-w-0 flex-1">
          @includeWhen($globalNavbar !== null, 'components.global-navbar', $globalNavbar ?? [])
          @yield('content')
        </main>
      </div>
    </div>
    @include('components.alert-system')
    <script src="{{ asset('js/alert-system.js') }}?v=alerts-1"></script>
    <script src="{{ asset('js/global-sidebar.js') }}?v=sidebar-3"></script>
    <script src="{{ asset('js/global-navbar.js') }}?v=navbar-1"></script>
    @stack('scripts')
  </body>
</html>
