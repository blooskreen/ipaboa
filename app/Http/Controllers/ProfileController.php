<?php

namespace App\Http\Controllers;

use App\Support\Images;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class ProfileController
{
    public function edit(): View
    {
        return view('member.profile', ['user' => Auth::user()]);
    }

    public function update(Request $request): RedirectResponse
    {
        $user = Auth::user();

        $data = $request->validate([
            'name'             => ['required', 'string', 'max:255'],
            'email'            => ['required', 'email', 'max:255', Rule::unique('users')->ignore($user->getKey())],
            'phone'            => ['nullable', 'string', 'max:40'],
            'city'             => ['nullable', 'string', 'max:120'],
            'height'           => ['nullable', 'string', 'max:20'],
            'weight'           => ['nullable', 'string', 'max:20'],
            'years_experience' => ['nullable', 'integer', 'min:0', 'max:80'],
            'bio'              => ['nullable', 'string', 'max:2000'],
            'photo'            => ['nullable', 'image', 'mimes:jpeg,jpg,png,webp', 'max:8192'],
            'banner'           => ['nullable', 'image', 'mimes:jpeg,jpg,png,webp', 'max:8192'],
        ]);

        $dir = 'profiles/' . $user->getKey();
        Storage::disk('public')->makeDirectory($dir);

        if ($request->hasFile('photo')) {
            $path = $dir . '/' . Str::ulid() . '.jpg';

            if (! Images::square($request->file('photo')->getRealPath(), Storage::disk('public')->path($path), 600)) {
                return back()->withErrors(['photo' => 'That image could not be processed.'])->withInput();
            }

            $this->forget($user->photo_path);
            $data['photo_path'] = $path;
        }

        if ($request->hasFile('banner')) {
            $path = $dir . '/' . Str::ulid() . '_banner.jpg';

            if (! Images::scaleDown($request->file('banner')->getRealPath(), Storage::disk('public')->path($path), 1600)) {
                return back()->withErrors(['banner' => 'That image could not be processed.'])->withInput();
            }

            $this->forget($user->banner_path);
            $data['banner_path'] = $path;
        }

        unset($data['photo'], $data['banner']);

        $data['profile_public'] = $request->boolean('profile_public');
        $data['email_opt_out']  = $request->boolean('email_opt_out');

        $user->fill($data)->save();

        return back()->with('status', 'Profile updated.');
    }

    public function removePhoto(): RedirectResponse
    {
        $user = Auth::user();
        $this->forget($user->photo_path);
        $user->forceFill(['photo_path' => null])->save();

        return back()->with('status', 'Photo removed.');
    }

    public function removeBanner(): RedirectResponse
    {
        $user = Auth::user();
        $this->forget($user->banner_path);
        $user->forceFill(['banner_path' => null])->save();

        return back()->with('status', 'Banner removed.');
    }

    public function password(Request $request): RedirectResponse
    {
        $request->validate([
            'current_password' => ['required', 'current_password'],
            'password'         => ['required', 'confirmed', Password::min(8)],
        ]);

        Auth::user()->forceFill([
            'password' => Hash::make($request->input('password')),
        ])->save();

        return back()->with('status', 'Password changed.');
    }

    protected function forget(?string $path): void
    {
        if ($path && Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }
}
