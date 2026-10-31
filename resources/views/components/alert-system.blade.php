<div id="lexaToastRegion" class="pointer-events-none fixed right-4 top-20 z-[90] flex w-[min(24rem,calc(100vw-2rem))] flex-col gap-3" aria-live="polite" aria-relevant="additions"></div>

<div id="lexaConfirmRoot" class="fixed inset-0 z-[100] hidden items-center justify-center bg-slate-950/55 p-4 backdrop-blur-[2px]" data-open="false">
  <section id="lexaConfirmDialog" class="w-full max-w-md scale-95 rounded-2xl border border-slate-200 bg-white p-5 opacity-0 shadow-2xl transition duration-200 sm:p-6" role="alertdialog" aria-modal="true" aria-labelledby="lexaConfirmTitle" aria-describedby="lexaConfirmMessage">
    <div class="flex items-start gap-4">
      <span id="lexaConfirmIcon" class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-blue-50 text-blue-700" aria-hidden="true">
        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4v6h6M5.6 15a7 7 0 1 0 .2-6.8L4 10"/></svg>
      </span>
      <div class="min-w-0 flex-1">
        <h2 id="lexaConfirmTitle" class="text-lg font-bold text-slate-900">Konfirmasi tindakan</h2>
        <p id="lexaConfirmMessage" class="mt-2 text-sm leading-6 text-slate-600"></p>
        <p id="lexaConfirmDetail" class="mt-2 hidden text-sm leading-6 text-slate-500"></p>
      </div>
      <button id="lexaConfirmClose" type="button" class="rounded-lg p-2 text-slate-400 transition hover:bg-slate-100 hover:text-slate-700" aria-label="Tutup dialog">
        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" d="m6 6 12 12M18 6 6 18"/></svg>
      </button>
    </div>
    <div class="mt-6 flex flex-col-reverse gap-2 border-t border-slate-100 pt-4 sm:flex-row sm:justify-end">
      <button id="lexaConfirmCancel" type="button" class="inline-flex h-10 items-center justify-center rounded-xl border border-slate-200 bg-white px-4 text-sm font-semibold text-slate-700 transition hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">Batal</button>
      <button id="lexaConfirmAccept" type="button" class="inline-flex h-10 items-center justify-center gap-2 rounded-xl bg-blue-600 px-4 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 disabled:cursor-wait disabled:opacity-60">Konfirmasi</button>
    </div>
  </section>
</div>

<script type="application/json" id="lexaAlertFeedback">@json(['success' => session('success'), 'error' => session('error'), 'errors' => $errors->messages()])</script>
<style>@keyframes lexa-toast-progress { from { transform: scaleX(1); } to { transform: scaleX(0); } }</style>
