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
      <fieldset><legend class="text-sm font-semibold text-slate-700">Select Account Type</legend><div class="mt-2 grid gap-3 sm:grid-cols-2"><label class="cursor-pointer rounded-xl border border-slate-200 p-3 transition has-[:checked]:border-indigo-500 has-[:checked]:bg-indigo-50"><span class="flex items-center gap-2 font-semibold text-slate-800"><input id="accountTypeWorker" type="radio" name="role" value="worker" @checked(old('role', 'worker') === 'worker')>Worker</span><span class="mt-1 block text-xs leading-5 text-slate-500">Attendance, schedules, leave, and personal settings.</span></label><label class="cursor-pointer rounded-xl border border-slate-200 p-3 transition has-[:checked]:border-indigo-500 has-[:checked]:bg-indigo-50"><span class="flex items-center gap-2 font-semibold text-slate-800"><input id="accountTypeAdmin" type="radio" name="role" value="admin" @checked(old('role') === 'admin')>Admin</span><span class="mt-1 block text-xs leading-5 text-slate-500">Manage workers and attendance policies. Invitation required.</span></label></div></fieldset>
      <div id="adminInvitationField" class="{{ old('role') === 'admin' ? '' : 'hidden' }}"><label class="block text-sm font-medium text-slate-700">Admin invitation code<input name="admin_invitation" value="{{ old('admin_invitation') }}" autocomplete="off" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2"></label></div>
      <label class="block text-sm font-medium text-slate-700">Full name<input name="name" value="{{ old('name') }}" required autocomplete="name" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2"></label>
      <label class="block text-sm font-medium text-slate-700">Email<input name="email" type="email" value="{{ old('email') }}" required autocomplete="email" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2"></label>
      <label class="block text-sm font-medium text-slate-700">Password<input name="password" type="password" required minlength="8" autocomplete="new-password" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2"></label>
      <label class="block text-sm font-medium text-slate-700">Confirm password<input name="password_confirmation" type="password" required minlength="8" autocomplete="new-password" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2"></label>
      <button class="w-full rounded-lg bg-indigo-600 px-4 py-2.5 font-semibold text-white hover:bg-indigo-700">Create account</button>
    </form>
    <p class="mt-5 text-sm text-slate-500">Already registered? <a class="font-semibold text-indigo-600" href="{{ route('login') }}">Sign in</a></p>
  </main>
  @include('components.alert-system')
  <script src="{{ asset('js/alert-system.js') }}?v=alerts-1"></script>
  <script>document.querySelectorAll('input[name="role"]').forEach(input => input.addEventListener('change', event => document.getElementById('adminInvitationField').classList.toggle('hidden', event.target.value !== 'admin')));</script>
</body>
</html>

