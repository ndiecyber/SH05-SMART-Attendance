<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Create account Â· LEXA Smart Attendance</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-slate-50 flex items-center justify-center p-4">
  <main class="w-full max-w-md rounded-2xl bg-white p-8 shadow-sm border border-slate-100">
    <img src="{{ asset('assets/LEXA-logo.png') }}" alt="LEXA" class="h-14 object-contain mb-6">
    <h1 class="text-2xl font-bold text-slate-900">Create account</h1>
    <p class="mt-1 text-sm text-slate-500">Register to use Smart Attendance.</p>
    @if ($errors->any())<div class="mt-5 rounded-lg bg-rose-50 p-3 text-sm text-rose-700">{{ $errors->first() }}</div>@endif
    <form method="post" action="{{ route('register.store') }}" class="mt-6 space-y-4">
      @csrf
      <label class="block text-sm font-medium text-slate-700">Full name<input name="name" value="{{ old('name') }}" required autocomplete="name" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2"></label>
      <label class="block text-sm font-medium text-slate-700">Email<input name="email" type="email" value="{{ old('email') }}" required autocomplete="email" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2"></label>
      <label class="block text-sm font-medium text-slate-700">Password<input name="password" type="password" required minlength="8" autocomplete="new-password" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2"></label>
      <label class="block text-sm font-medium text-slate-700">Confirm password<input name="password_confirmation" type="password" required minlength="8" autocomplete="new-password" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2"></label>
      <button class="w-full rounded-lg bg-indigo-600 px-4 py-2.5 font-semibold text-white hover:bg-indigo-700">Create account</button>
    </form>
    <p class="mt-5 text-sm text-slate-500">Already registered? <a class="font-semibold text-indigo-600" href="{{ route('login') }}">Sign in</a></p>
  </main>
</body>
</html>

