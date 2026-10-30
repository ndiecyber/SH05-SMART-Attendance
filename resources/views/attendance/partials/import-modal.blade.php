<div id="importCsvModal" class="fixed inset-0 z-[100] hidden items-center justify-center bg-slate-950/40 p-4" role="dialog" aria-modal="true" aria-labelledby="importCsvTitle">
  <div class="w-full max-w-xl rounded-3xl bg-white p-5 shadow-2xl sm:p-7">
    <div class="mb-5 flex items-start justify-between gap-4">
      <div>
        <h2 id="importCsvTitle" class="text-xl font-semibold text-slate-900">Import attendance CSV</h2>
        <p class="mt-1 text-sm text-slate-500">Records will be added to your attendance history.</p>
      </div>
      <button id="closeImportModal" type="button" class="rounded-xl p-2 text-slate-400 transition hover:bg-slate-100 hover:text-slate-700" aria-label="Close import dialog">
        <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path d="m6.28 5.22 3.72 3.72 3.72-3.72a.75.75 0 1 1 1.06 1.06l-3.72 3.72 3.72 3.72a.75.75 0 1 1-1.06 1.06L10 11.06l-3.72 3.72a.75.75 0 1 1-1.06-1.06l3.72-3.72-3.72-3.72a.75.75 0 1 1 1.06-1.06Z" /></svg>
      </button>
    </div>

    <form method="POST" action="{{ route('attendance.import') }}" enctype="multipart/form-data">
      @csrf
      <input type="hidden" name="return_to" value="{{ $returnTo ?? 'attendance.index' }}" />
      <input id="attendanceCsvFile" type="file" name="file" accept=".csv,text/csv" class="sr-only" required />
      <label id="csvDropzone" for="attendanceCsvFile" class="flex min-h-56 cursor-pointer flex-col items-center justify-center rounded-2xl border-2 border-dashed border-slate-300 bg-slate-50 px-6 py-8 text-center transition hover:border-indigo-400 hover:bg-indigo-50/60">
        <span class="mb-4 flex h-14 w-14 items-center justify-center rounded-2xl bg-indigo-100 text-indigo-600">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 16V4m0 0L7 9m5-5l5 5M4 16v3a1 1 0 001 1h14a1 1 0 001-1v-3" /></svg>
        </span>
        <span class="font-semibold text-slate-800">Drag and drop your CSV file here</span>
        <span class="mt-1 text-sm text-slate-500">or <span class="font-medium text-indigo-600">Browse Files</span></span>
        <span id="csvFileName" class="mt-3 hidden max-w-full truncate text-sm font-medium text-indigo-700"></span>
        <span id="csvFileSize" class="mt-1 hidden text-xs text-slate-500"></span>
      </label>
      <div id="csvPreview" class="mt-4 hidden rounded-2xl border border-slate-200 bg-white p-3">
        <p id="csvPreviewSummary" class="mb-2 text-xs font-semibold text-slate-700"></p>
        <div class="overflow-x-auto">
          <table class="min-w-full text-left text-xs text-slate-600">
            <thead id="csvPreviewHeader" class="border-b border-slate-100 text-slate-500"></thead>
            <tbody id="csvPreviewRows" class="divide-y divide-slate-100"></tbody>
          </table>
        </div>
      </div>
      <p class="mt-4 text-xs leading-5 text-slate-500">
        CSV columns: <code class="rounded bg-slate-100 px-1">attendance_date, check_in_at, check_out_at</code>.<br />
        Date: YYYY-MM-DD; timestamps: YYYY-MM-DD HH:MM[:SS]. Leave check_out_at empty for an open attendance.
      </p>
      <p id="csvImportError" class="mt-3 hidden text-sm text-rose-600" role="alert"></p>
      <div class="mt-6 flex flex-col-reverse justify-end gap-3 sm:flex-row">
        <button id="cancelImportModal" type="button" class="rounded-xl border border-slate-200 px-4 py-2.5 text-sm font-medium text-slate-600 transition hover:bg-slate-50">Cancel</button>
        <button id="submitCsvImport" type="submit" class="rounded-xl bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-indigo-700 disabled:cursor-not-allowed disabled:bg-slate-300" disabled>Import CSV</button>
      </div>
    </form>
  </div>
</div>
