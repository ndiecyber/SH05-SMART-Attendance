@extends('layouts.app')
@section('title', 'Admin Settings · LEXA')
@section('content')
<section class="space-y-5 p-4 sm:p-6"><header><h1 class="text-2xl font-bold">Admin Settings</h1><p class="mt-1 text-sm text-slate-500">Pengaturan personal akun Admin dan kebijakan attendance global.</p></header><div class="grid gap-4 md:grid-cols-2"><a href="{{ route('settings.index') }}" class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm hover:border-indigo-300"><h2 class="font-semibold">Admin Profile & Security</h2><p class="mt-1 text-sm text-slate-500">Kelola profil, keamanan akun, dan preferensi pribadi.</p></a><a href="{{ route('admin.settings.attendance') }}" class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm hover:border-indigo-300"><h2 class="font-semibold">Attendance Configuration</h2><p class="mt-1 text-sm text-slate-500">Lokasi, GPS radius, IP allowlist, dan aturan kehadiran Worker.</p></a></div></section>
@endsection
