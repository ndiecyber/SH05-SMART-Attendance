<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class ProfileController extends Controller
{
    public function edit(Request $request): View
    {
        $profileUser = $request->user();
        $profileFields = [
            $profileUser->name,
            $profileUser->nickname,
            $profileUser->email,
            $profileUser->phone,
            $profileUser->job_title,
            $profileUser->bio,
            $profileUser->avatar_path,
        ];
        $profileCompleteness = (int) round(
            collect($profileFields)->filter(fn ($value): bool => filled($value))->count() / count($profileFields) * 100,
        );

        return view('profile.edit', [
            'profileUser' => $profileUser,
            'profileCompleteness' => $profileCompleteness,
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $user = $request->user();
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'nickname' => ['nullable', 'string', 'max:80'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'phone' => ['nullable', 'string', 'max:40'],
            'job_title' => ['nullable', 'string', 'max:255'],
            'bio' => ['nullable', 'string', 'max:2000'],
            'avatar' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);

        unset($data['avatar']);
        $oldAvatarPath = $user->avatar_path;
        $newAvatarPath = null;

        if ($request->hasFile('avatar')) {
            $newAvatarPath = $request->file('avatar')->store('avatars', 'local');

            if (! is_string($newAvatarPath)) {
                return back()->withErrors(['avatar' => 'Foto profil gagal disimpan.'])->withInput();
            }

            $data['avatar_path'] = $newAvatarPath;
        }

        $user->update($data);

        if ($newAvatarPath !== null && $oldAvatarPath !== null && $oldAvatarPath !== $newAvatarPath) {
            Storage::disk('local')->delete($oldAvatarPath);
        }

        return redirect()->route('profile.edit')->with('success', 'Profil berhasil diperbarui.');
    }

    public function avatar(Request $request): Response
    {
        $headers = [
            'Cache-Control' => 'private, no-cache, no-store, must-revalidate',
            'Pragma' => 'no-cache',
            'Expires' => '0',
        ];
        $avatarPath = $request->user()->avatar_path;

        if ($avatarPath !== null && Storage::disk('local')->exists($avatarPath)) {
            return Storage::disk('local')->response($avatarPath, null, $headers);
        }

        return response()->file(public_path('assets/LEXA-logo.png'), $headers);
    }
}
