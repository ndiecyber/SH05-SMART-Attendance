<?php

namespace App\Http\Controllers;

use App\Models\AdminInvitation;
use App\Models\User;
use App\Notifications\ActivityNotification;
use App\Services\ActivityNotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function createLogin(): View
    {
        return view('auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate(['email' => ['required', 'email'], 'password' => ['required', 'string']]);

        if (! Auth::attempt($credentials + ['is_active' => true], $request->boolean('remember'))) {
            return back()->withErrors(['email' => 'Email atau password tidak sesuai.'])->onlyInput('email');
        }

        $request->session()->regenerate();

        $defaultDashboard = Auth::user()->isAdmin() ? route('admin.dashboard') : route('dashboard');

        return redirect()->intended($defaultDashboard);
    }

    public function createRegister(): View
    {
        return view('auth.register');
    }

    public function register(Request $request, ActivityNotificationService $notificationService): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', 'min:8'],
            'role' => ['nullable', 'in:worker,admin'],
            'admin_invitation' => ['required_if:role,admin', 'nullable', 'string', 'max:255'],
        ]);

        $user = DB::transaction(function () use ($data): User {
            $requestedRole = $data['role'] ?? 'worker';
            if ($requestedRole === 'admin') {
                $invitation = AdminInvitation::query()
                    ->where('token_hash', hash('sha256', $data['admin_invitation']))
                    ->lockForUpdate()
                    ->first();

                if ($invitation === null
                    || $invitation->used_at !== null
                    || ! $invitation->expires_at->isFuture()
                    || ($invitation->email !== null && Str::lower($invitation->email) !== Str::lower($data['email']))) {
                    throw ValidationException::withMessages([
                        'admin_invitation' => 'Kode undangan Admin tidak valid, sudah digunakan, atau kedaluwarsa.',
                    ]);
                }

                $role = 'admin';
            } else {
                $invitation = null;
                $role = 'worker';
            }

            $user = new User;
            $user->name = $data['name'];
            $user->email = $data['email'];
            $user->password = Hash::make($data['password']);
            $user->role = $role;
            $user->is_active = true;
            $user->save();

            if ($invitation !== null) {
                $invitation->forceFill(['used_by' => $user->id, 'used_at' => now()])->save();
            }

            return $user;
        });
        Auth::login($user);
        $request->session()->regenerate();

        if ($user->isWorker()) {
            User::query()->where('role', 'admin')->where('is_active', true)->each(function (User $admin) use ($notificationService, $user): void {
                $notificationService->send($admin, new ActivityNotification(
                    title: 'Worker baru terdaftar',
                    description: $user->name.' telah membuat akun Worker.',
                    category: 'System',
                    priority: 'normal',
                    requiresAction: false,
                    url: route('admin.workers.index', [], false),
                    source: 'Account Registration',
                    metadata: ['worker_id' => $user->id],
                ));
            });
        }

        return redirect()->route($user->isAdmin() ? 'admin.dashboard' : 'dashboard');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
