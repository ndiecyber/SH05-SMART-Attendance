<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Sign in · LEXA Smart Attendance</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-slate-50 flex items-center justify-center p-4">
  <main class="w-full max-w-md rounded-2xl bg-white p-8 shadow-sm border border-slate-100">
    <img src="{{ asset('assets/LEXA-logo.png') }}" alt="LEXA" class="h-14 object-contain mb-6">
    <h1 class="text-2xl font-bold text-slate-900">Welcome back</h1>
    <p class="mt-1 text-sm text-slate-500">Sign in to your attendance account.</p>
    @if ($errors->any())<div class="mt-5 rounded-lg bg-rose-50 p-3 text-sm text-rose-700">{{ $errors->first() }}</div>@endif
    <form method="post" action="{{ route('login.store') }}" class="mt-6 space-y-4">
      @csrf
      <label class="block text-sm font-medium text-slate-700">Email<input name="email" type="email" value="{{ old('email') }}" required autofocus autocomplete="email" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2"></label>
      <label class="block text-sm font-medium text-slate-700">Password<input name="password" type="password" required autocomplete="current-password" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2"></label>
      <label class="flex items-center gap-2 text-sm text-slate-600"><input name="remember" type="checkbox" value="1"> Remember me</label>
      <button class="w-full rounded-lg bg-indigo-600 px-4 py-2.5 font-semibold text-white hover:bg-indigo-700">Sign in</button>
    </form>
    <p class="mt-5 text-sm text-slate-500">New here? <a class="font-semibold text-indigo-600" href="{{ route('register') }}">Create an account</a></p>
  </main>
  @include('components.alert-system')
  <script src="{{ asset('js/alert-system.js') }}?v=alerts-1"></script>
</body>
</html>
