@extends('layouts.app')

@section('title', 'Notifications | Smart Attendance')

@section('content')
      <div id="notificationsLoadingIndicator" class="pointer-events-none fixed inset-x-0 top-0 z-[60] hidden h-1 bg-indigo-100" aria-hidden="true"><div class="h-full w-1/3 animate-pulse bg-indigo-600"></div></div>
      @php
      $channels = array_merge(['in_app' => true, 'email' => false, 'browser' => false], $preferences?->channels ?? []);
      $channels['email'] = false;
      $channels['browser'] = false;
      $categories = array_merge(['attendance' => true, 'approval' => true, 'schedule' => true, 'system' => true, 'announcement' => true], $preferences?->categories ?? []);
      $filterChips = [
        ['label' => 'Semua', 'value' => 'all'],
        ['label' => 'Belum Dibaca', 'value' => 'unread'],
        ['label' => 'Tindakan', 'value' => 'action'],
        ['label' => 'Approval', 'value' => 'approval'],
        ['label' => 'Jadwal', 'value' => 'schedule'],
        ['label' => 'Sistem', 'value' => 'system'],
        ['label' => 'Pengumuman', 'value' => 'announcement'],
      ];
      $activityMax = max(1, $activity->max('count'));
    @endphp

          

          <section class="space-y-6 p-4 sm:p-6">
            @if ($errors->any())<div role="alert" class="rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">Periksa kembali pengaturan notifikasi Anda.</div>@endif

            <div class="flex flex-col justify-between gap-4 lg:flex-row lg:items-center">
              <div><p class="text-sm font-medium text-indigo-600">Pusat informasi</p><h1 class="mt-1 text-2xl font-bold text-slate-900 sm:text-3xl">Notifications</h1><p class="mt-2 max-w-2xl text-sm text-slate-500">Semua pemberitahuan terkait aktivitas, jadwal, persetujuan, dan pembaruan sistem Anda.</p></div>
              <div class="flex flex-wrap gap-2">
                <form method="POST" action="{{ route('notifications.read-all') }}" data-confirm="Tandai semua notifikasi aktif sebagai sudah dibaca?">@csrf @method('PATCH')<button class="inline-flex items-center gap-2 rounded-xl bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-700"><svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m5 12 4 4L19 6" /></svg>Tandai Semua Dibaca</button></form>
                <a href="#notification-preferences" class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-50"><svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 2m6-2a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>Pengaturan</a>
              </div>
            </div>

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
              @foreach ([['label' => 'Belum Dibaca', 'value' => $stats['unread'], 'description' => 'Notifikasi yang perlu dibaca.', 'icon' => '●', 'tone' => 'blue'], ['label' => 'Perlu Tindakan', 'value' => $stats['action'], 'description' => 'Perlu respons atau persetujuan.', 'icon' => '!', 'tone' => 'rose'], ['label' => 'Hari Ini', 'value' => $stats['today'], 'description' => 'Notifikasi dalam 24 jam.', 'icon' => '◷', 'tone' => 'violet'], ['label' => '30 Hari Terakhir', 'value' => $stats['last30Days'], 'description' => 'Termasuk yang sudah dibaca.', 'icon' => '↗', 'tone' => 'emerald']] as $card)
                <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                  <div class="flex items-start justify-between"><div><p class="text-sm font-medium text-slate-500">{{ $card['label'] }}</p><p class="mt-3 text-3xl font-bold text-slate-900">{{ $card['value'] }}</p></div><span class="flex h-11 w-11 items-center justify-center rounded-xl bg-{{ $card['tone'] }}-50 text-lg font-bold text-{{ $card['tone'] }}-600">{{ $card['icon'] }}</span></div>
                  <p class="mt-3 text-xs text-slate-500">{{ $card['description'] }}</p>
                </article>
              @endforeach
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm sm:p-5">
              <form id="notificationSearchForm" method="GET" action="{{ route('notifications.index') }}" class="flex flex-col gap-3 md:flex-row">
                <label class="relative min-w-0 flex-1"><span class="sr-only">Cari notifikasi</span><svg class="absolute left-3 top-1/2 h-5 w-5 -translate-y-1/2 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor"><circle cx="11" cy="11" r="7" stroke-width="2"/><path stroke-linecap="round" stroke-width="2" d="m16 16 4 4"/></svg><input id="notificationSearch" name="search" value="{{ $filters['search'] }}" placeholder="Cari notifikasi, pengirim, atau kata kunci..." class="w-full rounded-xl border border-slate-200 bg-slate-50 py-3 pl-10 pr-4 text-sm outline-none transition placeholder:text-slate-400 focus:border-indigo-400 focus:bg-white focus:ring-4 focus:ring-indigo-50" /></label>
                <input type="hidden" name="category" value="{{ $filters['category'] }}" />
                <label class="md:w-52"><span class="sr-only">Filter status</span><select name="status" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-3 text-sm text-slate-700 outline-none focus:border-indigo-400 focus:ring-4 focus:ring-indigo-50" data-submit-on-change><option value="all" @selected($filters['status'] === 'all')>Semua Notifikasi</option><option value="unread" @selected($filters['status'] === 'unread')>Belum Dibaca</option><option value="read" @selected($filters['status'] === 'read')>Sudah Dibaca</option><option value="action" @selected($filters['status'] === 'action')>Perlu Tindakan</option></select></label>
              </form>
              <div class="mt-4 flex gap-2 overflow-x-auto pb-1">
                @foreach ($filterChips as $chip)
                  @php $query = array_filter(['search' => $filters['search'], 'status' => $filters['status'] !== 'all' ? $filters['status'] : null, 'category' => $chip['value'] !== 'all' ? $chip['value'] : null], fn ($value) => $value !== null && $value !== ''); @endphp
                  <a href="{{ route('notifications.index', $query) }}" @class(['shrink-0 rounded-full px-4 py-2 text-xs font-semibold transition', 'bg-indigo-600 text-white shadow-sm' => $filters['category'] === $chip['value'], 'bg-slate-100 text-slate-600 hover:bg-slate-200' => $filters['category'] !== $chip['value']])>{{ $chip['label'] }}</a>
                @endforeach
              </div>
            </div>

            <div class="grid items-start gap-6 xl:grid-cols-[minmax(0,1.65fr)_minmax(300px,0.9fr)]">
              <div class="space-y-5">
                <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                  <div class="flex items-center justify-between border-b border-slate-100 px-5 py-4"><div><h2 class="font-semibold text-slate-900">Semua Notifikasi</h2><p class="mt-1 text-xs text-slate-500">{{ $notifications->total() }} hasil · terbaru lebih dahulu</p></div><span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-medium text-slate-600">{{ $stats['active'] }} aktif</span></div>
                  @forelse ($groupedNotifications as $group)
                    <div class="border-b border-slate-100 last:border-b-0">
                      <div class="flex items-center justify-between bg-slate-50/80 px-5 py-3"><h3 class="text-xs font-bold uppercase tracking-wide text-slate-500">{{ $group['label'] }}</h3><span class="text-xs text-slate-400">{{ $group['count'] }} notifikasi</span></div>
                      <div class="divide-y divide-slate-100">
                        @foreach ($group['items'] as $notification)
                          @php
                            $data = $notification->data;
                            $category = $data['category'] ?? 'Lainnya';
                            $categoryTone = match ($category) { 'Attendance' => 'blue', 'Approval' => 'rose', 'Schedule' => 'violet', 'System' => 'slate', 'Announcement' => 'amber', default => 'slate' };
                          @endphp
                          <article class="flex gap-3 px-4 py-4 transition sm:px-5 {{ $notification->read_at ? 'bg-white' : 'bg-indigo-50/40' }}">
                            <span class="mt-0.5 flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-{{ $categoryTone }}-100 text-sm font-bold text-{{ $categoryTone }}-700">{{ $category === 'Attendance' ? '◷' : ($category === 'Approval' ? '✓' : ($category === 'Schedule' ? '▦' : ($category === 'Announcement' ? '!' : '•'))) }}</span>
                            <div class="min-w-0 flex-1">
                              <div class="flex items-start gap-2">
                                <a href="{{ route('notifications.open', $notification->id) }}" class="min-w-0 flex-1 rounded focus:outline-none focus:ring-2 focus:ring-indigo-300"><h3 class="text-sm {{ $notification->read_at ? 'font-medium text-slate-700' : 'font-bold text-slate-900' }}">{{ $data['title'] ?? 'Notifikasi' }}</h3><p class="mt-1 text-sm leading-6 text-slate-600">{{ $data['description'] ?? '' }}</p></a>
                                @if (!$notification->read_at)<span aria-label="Belum dibaca" class="mt-1.5 h-2.5 w-2.5 shrink-0 rounded-full bg-indigo-600"></span>@endif
                              </div>
                              <div class="mt-3 flex flex-wrap items-center gap-2">
                                <span class="rounded-full bg-{{ $categoryTone }}-50 px-2.5 py-1 text-[11px] font-semibold text-{{ $categoryTone }}-700">{{ $category }}</span>
                                @if (!empty($data['requires_action']))<span class="rounded-full bg-rose-50 px-2.5 py-1 text-[11px] font-semibold text-rose-700">Perlu Tindakan</span>@endif
                                @if (($data['priority'] ?? 'normal') === 'high')<span class="rounded-full bg-amber-50 px-2.5 py-1 text-[11px] font-semibold text-amber-700">Penting</span>@endif
                                <span class="text-xs text-slate-400">{{ $data['source'] ?? 'Smart Attendance' }} · {{ $notification->created_at->timezone(config('app.timezone'))->format('H:i') }} WIB · {{ $notification->created_at->locale('id')->diffForHumans() }}</span>
                              </div>
                            </div>
                            <div class="flex shrink-0 flex-col gap-1 sm:flex-row">
                              @if ($notification->read_at)
                                <form method="POST" action="{{ route('notifications.unread', $notification->id) }}">@csrf @method('PATCH')<button title="Tandai belum dibaca" aria-label="Tandai belum dibaca" class="rounded-lg p-2 text-slate-400 hover:bg-slate-100 hover:text-indigo-600"><svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12s3-7 9-7 9 7 9 7-3 7-9 7-9-7-9-7Z"/><circle cx="12" cy="12" r="3" stroke-width="2"/></svg></button></form>
                              @else
                                <form method="POST" action="{{ route('notifications.read', $notification->id) }}">@csrf @method('PATCH')<button title="Tandai sudah dibaca" aria-label="Tandai sudah dibaca" class="rounded-lg p-2 text-slate-400 hover:bg-slate-100 hover:text-indigo-600"><svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m5 12 4 4L19 6"/></svg></button></form>
                              @endif
                              <form method="POST" action="{{ route('notifications.archive', $notification->id) }}" data-confirm="Notifikasi ini akan dipindahkan ke arsip dan dapat dipulihkan nanti." data-confirm-title="Arsipkan Notifikasi?" data-confirm-text="Ya, Arsipkan">@csrf @method('PATCH')<button title="Arsipkan" aria-label="Arsipkan" class="rounded-lg p-2 text-slate-400 hover:bg-slate-100 hover:text-amber-600"><svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4h18v4H3zm2 4v12h14V8m-9 4h4"/></svg></button></form>
                              <form method="POST" action="{{ route('notifications.destroy', $notification->id) }}" data-confirm="Hapus notifikasi ini secara permanen?" data-confirm-title="Hapus Notifikasi?" data-confirm-detail="Notifikasi yang dihapus tidak dapat dipulihkan." data-confirm-tone="danger" data-confirm-text="Ya, Hapus Permanen">@csrf @method('DELETE')<button title="Hapus" aria-label="Hapus" class="rounded-lg p-2 text-slate-400 hover:bg-rose-50 hover:text-rose-600"><svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7h16m-10 4v6m4-6v6M5 7l1 14h12l1-14M9 7V4h6v3"/></svg></button></form>
                            </div>
                          </article>
                        @endforeach
                      </div>
                    </div>
                  @empty
                    <div class="px-6 py-16 text-center"><span class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-slate-100 text-2xl text-slate-400">♧</span><h3 class="mt-4 font-semibold text-slate-800">Belum ada notifikasi</h3><p class="mt-1 text-sm text-slate-500">Notifikasi baru akan muncul saat ada aktivitas di akun Anda.</p></div>
                  @endforelse
                  @if ($notifications->hasPages())<div class="border-t border-slate-100 px-5 py-4">{{ $notifications->onEachSide(1)->links() }}</div>@endif
                </section>

                <details class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm" {{ request()->boolean('show_archive') ? 'open' : '' }}>
                  <summary class="flex cursor-pointer list-none items-center justify-between px-5 py-4"><div><h2 class="font-semibold text-slate-900">Arsip Notifikasi</h2><p class="mt-1 text-xs text-slate-500">Notifikasi yang Anda arsipkan tersimpan di sini.</p></div><span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-600">{{ $archivedNotifications->total() }}</span></summary>
                  <div class="border-t border-slate-100">
                    @forelse ($archivedNotifications as $notification)
                      @php $data = $notification->data; @endphp
                      <div class="flex items-center gap-3 border-b border-slate-100 px-5 py-4 last:border-b-0"><div class="min-w-0 flex-1"><p class="truncate text-sm font-semibold text-slate-800">{{ $data['title'] ?? 'Notifikasi' }}</p><p class="mt-1 truncate text-xs text-slate-500">{{ $data['description'] ?? '' }}</p></div><form method="POST" action="{{ route('notifications.restore', $notification->id) }}">@csrf @method('PATCH')<button class="rounded-lg border border-slate-200 px-3 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-50">Pulihkan</button></form><form method="POST" action="{{ route('notifications.destroy', $notification->id) }}" data-confirm="Hapus notifikasi arsip ini secara permanen?" data-confirm-title="Hapus Notifikasi Arsip?" data-confirm-detail="Notifikasi yang dihapus tidak dapat dipulihkan." data-confirm-tone="danger" data-confirm-text="Ya, Hapus Permanen">@csrf @method('DELETE')<button class="rounded-lg px-2 py-2 text-xs font-semibold text-rose-600 hover:bg-rose-50">Hapus</button></form></div>
                    @empty
                      <p class="px-5 py-8 text-center text-sm text-slate-500">Arsip masih kosong.</p>
                    @endforelse
                    @if ($archivedNotifications->hasPages())<div class="px-5 py-4">{{ $archivedNotifications->onEachSide(1)->links() }}</div>@endif
                  </div>
                </details>
              </div>

              <aside class="space-y-5">
                <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                  <div><h2 class="font-semibold text-slate-900">Perlu Tindakan</h2><p class="mt-1 text-xs text-slate-500">Notifikasi yang memerlukan respons segera.</p></div>
                  <div class="mt-4 space-y-3">
                    @forelse ($actionNotifications as $notification)
                      @php $data = $notification->data; @endphp
                      <a href="{{ route('notifications.open', $notification->id) }}" class="flex gap-3 rounded-xl border border-rose-100 bg-rose-50/50 p-3 transition hover:border-rose-200"><span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-rose-100 text-sm font-bold text-rose-700">!</span><span class="min-w-0"><span class="block truncate text-sm font-semibold text-slate-800">{{ $data['title'] ?? 'Perlu tindakan' }}</span><span class="mt-1 block line-clamp-2 text-xs text-slate-500">{{ $data['description'] ?? '' }}</span><span class="mt-2 block text-[11px] text-slate-400">{{ $notification->created_at->locale('id')->diffForHumans() }}</span></span></a>
                    @empty
                      <p class="rounded-xl bg-slate-50 px-3 py-5 text-center text-xs text-slate-500">Tidak ada tindakan yang tertunda.</p>
                    @endforelse
                  </div>
                </section>

                <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                  <div><h2 class="font-semibold text-slate-900">Aktivitas 7 Hari</h2><p class="mt-1 text-xs text-slate-500">Ringkasan notifikasi dalam seminggu.</p></div>
                  <div class="mt-5 grid grid-cols-7 items-end gap-2" role="img" aria-label="Grafik jumlah notifikasi tujuh hari terakhir">
                    @foreach ($activity as $day)
                      <div class="flex flex-col items-center gap-2" title="{{ $day['count'] }} notifikasi pada {{ $day['label'] }}"><span class="text-[10px] font-semibold text-slate-500">{{ $day['count'] }}</span><div class="flex h-24 w-full items-end justify-center rounded-lg bg-slate-50 p-1"><div class="w-full rounded-md bg-indigo-500 transition hover:bg-indigo-600" style="height: {{ $day['count'] === 0 ? 3 : max(8, (int) round(($day['count'] / $activityMax) * 88)) }}px"></div></div><span class="text-[10px] text-slate-500">{{ $day['label'] }}</span></div>
                    @endforeach
                  </div>
                  <div class="mt-5 space-y-3 border-t border-slate-100 pt-4">
                    @foreach ($categorySummary as $item)
                      <div><div class="mb-1 flex justify-between text-xs"><span class="text-slate-600">{{ $item['label'] }}</span><span class="font-medium text-slate-500">{{ $item['count'] }}</span></div><div class="h-1.5 overflow-hidden rounded-full bg-slate-100"><div class="h-full rounded-full bg-indigo-500" style="width: {{ $item['percent'] }}%"></div></div></div>
                    @endforeach
                  </div>
                </section>

                <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                  <div><h2 class="font-semibold text-slate-900">Penting</h2><p class="mt-1 text-xs text-slate-500">Notifikasi berprioritas tinggi.</p></div>
                  <div class="mt-4 space-y-3">
                    @forelse ($importantNotifications as $notification)
                      @php $data = $notification->data; @endphp
                      <a href="{{ route('notifications.open', $notification->id) }}" class="block rounded-xl border border-amber-100 bg-amber-50/50 p-3 hover:bg-amber-50"><div class="flex items-center justify-between gap-2"><span class="rounded-full bg-amber-100 px-2 py-0.5 text-[10px] font-bold uppercase text-amber-800">{{ $data['category'] ?? 'Penting' }}</span><span class="text-[10px] text-slate-400">{{ $notification->created_at->locale('id')->diffForHumans() }}</span></div><p class="mt-2 text-sm font-semibold text-slate-800">{{ $data['title'] ?? 'Notifikasi penting' }}</p><p class="mt-1 line-clamp-2 text-xs text-slate-500">{{ $data['description'] ?? '' }}</p></a>
                    @empty
                      <p class="rounded-xl bg-slate-50 px-3 py-5 text-center text-xs text-slate-500">Belum ada notifikasi penting.</p>
                    @endforelse
                  </div>
                </section>

                <section id="notification-preferences" class="scroll-mt-24 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                  <div><h2 class="font-semibold text-slate-900">Preferensi Notifikasi</h2><p class="mt-1 text-xs text-slate-500">Atur kategori dan kanal notifikasi akun Anda.</p></div>
                  <form method="POST" action="{{ route('notifications.preferences.update') }}" class="mt-4 space-y-5">
                    @csrf @method('PUT')
                    <fieldset><legend class="mb-2 text-xs font-bold uppercase tracking-wide text-slate-500">Kanal</legend><div class="space-y-3">
                      @foreach ([['key' => 'in_app', 'label' => 'In-App Notification', 'hint' => 'Simpan notifikasi di halaman ini.'], ['key' => 'email', 'label' => 'Email', 'hint' => 'Belum tersedia: pengiriman email belum dikonfigurasi.'], ['key' => 'browser', 'label' => 'Browser Notification', 'hint' => 'Belum tersedia: layanan push belum dikonfigurasi.']] as $channel)
                        <label class="flex items-center justify-between gap-3"><span><span class="block text-sm font-medium text-slate-700">{{ $channel['label'] }}</span><span class="mt-0.5 block text-[11px] text-slate-400">{{ $channel['hint'] }}</span></span><span class="relative inline-flex shrink-0 items-center"><input type="hidden" name="channels[{{ $channel['key'] }}]" value="0"><input type="checkbox" name="channels[{{ $channel['key'] }}]" value="1" @checked($channels[$channel['key']]) @disabled($channel['key'] !== 'in_app') @if($channel['key'] === 'browser') data-browser-permission @endif class="h-5 w-9 cursor-pointer accent-indigo-600"></span></label>
                      @endforeach
                    </div><p id="browserPermissionMessage" class="mt-2 hidden text-xs text-rose-600"></p></fieldset>
                    <fieldset><legend class="mb-2 text-xs font-bold uppercase tracking-wide text-slate-500">Kategori</legend><div class="space-y-3">
                      @foreach ([['key' => 'attendance', 'label' => 'Kehadiran'], ['key' => 'approval', 'label' => 'Approval'], ['key' => 'schedule', 'label' => 'Jadwal'], ['key' => 'system', 'label' => 'Sistem'], ['key' => 'announcement', 'label' => 'Pengumuman']] as $category)
                        <label class="flex items-center justify-between text-sm text-slate-700"><span>{{ $category['label'] }}</span><span class="relative inline-flex items-center"><input type="hidden" name="categories[{{ $category['key'] }}]" value="0"><input type="checkbox" name="categories[{{ $category['key'] }}]" value="1" @checked($categories[$category['key']]) class="h-5 w-9 cursor-pointer accent-indigo-600"></span></label>
                      @endforeach
                    </div></fieldset>
                    <button class="w-full rounded-xl bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-indigo-700">Simpan Preferensi</button>
                  </form>
                </section>
              </aside>
            </div>
          </section>
        
@endsection

@push('scripts')
  <script src="{{ asset('js/notification.js') }}?v=database-notifications-1" defer></script>
@endpush
