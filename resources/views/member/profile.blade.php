@extends('layouts.public')
@section('title', 'My Profile')

@section('content')
@include('partials.member-nav')

@php
    $input = 'w-full rounded-md border border-black/20 px-4 py-2.5 focus:border-brand focus:outline-none focus:ring-2 focus:ring-brand/20';
    $label = 'block text-sm font-semibold text-ink mb-1.5';
@endphp

<div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8 py-10">

    @if (session('status'))
        <div class="mb-6 rounded-md border border-green-300 bg-green-50 px-4 py-3 text-sm text-green-800">{{ session('status') }}</div>
    @endif
    @if ($errors->any())
        <div class="mb-6 rounded-md border border-red-300 bg-red-50 px-4 py-3 text-sm text-red-800">
            <ul class="list-disc space-y-1 pl-4">
                @foreach ($errors->all() as $error) <li>{{ $error }}</li> @endforeach
            </ul>
        </div>
    @endif

    <h1 class="font-display text-4xl font-bold uppercase tracking-tight text-ink">My Profile</h1>
    <p class="mt-2 text-black/55">This is what appears on your dashboard and, if you make it public, in the officials directory.</p>

    <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data" class="mt-8 space-y-8">
        @csrf
        @method('PATCH')

        {{-- images --}}
        <section class="rounded-xl border border-black/10 p-6">
            <h2 class="font-display text-lg font-bold uppercase tracking-wide text-ink">Photos</h2>

            <div class="mt-5 grid gap-6 sm:grid-cols-2">
                <div>
                    <label for="photo" class="{{ $label }}">Profile photo</label>
                    <div class="mb-3 flex items-center gap-4">
                        @if ($user->photoUrl())
                            <img src="{{ $user->photoUrl() }}" alt="" class="h-20 w-20 rounded-lg object-cover ring-2 ring-gold">
                        @else
                            <div class="flex h-20 w-20 items-center justify-center rounded-lg bg-ink ring-2 ring-gold">
                                <span class="font-display text-2xl font-bold text-gold">{{ $user->initials() }}</span>
                            </div>
                        @endif
                    </div>
                    <input id="photo" name="photo" type="file" accept="image/jpeg,image/png,image/webp" class="{{ $input }} text-sm">
                    <p class="mt-1.5 text-xs text-black/50">Square. 600 x 600 or larger; it gets centre-cropped.</p>
                </div>

                <div>
                    <label for="banner" class="{{ $label }}">Profile banner</label>
                    <div class="mb-3 h-20 w-full overflow-hidden rounded-lg bg-gradient-to-br from-brand-dark via-brand to-ink">
                        @if ($user->bannerUrl())
                            <img src="{{ $user->bannerUrl() }}" alt="" class="h-full w-full object-cover">
                        @endif
                    </div>
                    <input id="banner" name="banner" type="file" accept="image/jpeg,image/png,image/webp" class="{{ $input }} text-sm">
                    <p class="mt-1.5 text-xs text-black/50">Wide. 1600 x 500 works well.</p>
                </div>
            </div>
        </section>

        {{-- details --}}
        <section class="rounded-xl border border-black/10 p-6">
            <h2 class="font-display text-lg font-bold uppercase tracking-wide text-ink">Details</h2>

            <div class="mt-5 grid gap-5 sm:grid-cols-2">
                <div>
                    <label for="name" class="{{ $label }}">Full name</label>
                    <input id="name" name="name" type="text" value="{{ old('name', $user->name) }}" required class="{{ $input }}">
                </div>
                <div>
                    <label for="email" class="{{ $label }}">Email address</label>
                    <input id="email" name="email" type="email" value="{{ old('email', $user->email) }}" required class="{{ $input }}">
                </div>
                <div>
                    <label for="phone" class="{{ $label }}">Phone</label>
                    <input id="phone" name="phone" type="text" value="{{ old('phone', $user->phone) }}" class="{{ $input }}">
                </div>
                <div>
                    <label for="city" class="{{ $label }}">Location</label>
                    <input id="city" name="city" type="text" placeholder="Atlanta, GA" value="{{ old('city', $user->city) }}" class="{{ $input }}">
                </div>
                <div>
                    <label for="height" class="{{ $label }}">Height</label>
                    <input id="height" name="height" type="text" placeholder="6'2&quot;" value="{{ old('height', $user->height) }}" class="{{ $input }}">
                </div>
                <div>
                    <label for="weight" class="{{ $label }}">Weight</label>
                    <input id="weight" name="weight" type="text" placeholder="195 lbs" value="{{ old('weight', $user->weight) }}" class="{{ $input }}">
                </div>
                <div>
                    <label for="years_experience" class="{{ $label }}">Years of experience</label>
                    <input id="years_experience" name="years_experience" type="number" min="0" max="80"
                           value="{{ old('years_experience', $user->years_experience) }}" class="{{ $input }}">
                </div>
                <div>
                    <label class="{{ $label }}">Classification</label>
                    <input type="text" value="{{ $user->classification ?: 'Not yet rated' }}" disabled
                           class="{{ $input }} bg-black/[0.04] text-black/45">
                    <p class="mt-1.5 text-xs text-black/50">Set by the evaluation committee.</p>
                </div>
            </div>

            <div class="mt-5">
                <label for="bio" class="{{ $label }}">About you</label>
                <textarea id="bio" name="bio" rows="4" maxlength="2000" class="{{ $input }}">{{ old('bio', $user->bio) }}</textarea>
            </div>
        </section>

        {{-- visibility --}}
        <section class="rounded-xl border border-black/10 p-6">
            <h2 class="font-display text-lg font-bold uppercase tracking-wide text-ink">Visibility &amp; email</h2>

            <label class="mt-5 flex items-start gap-3">
                <input type="checkbox" name="profile_public" value="1" @checked(old('profile_public', $user->profile_public))
                       class="mt-1 rounded border-black/25 text-brand focus:ring-brand/30">
                <span>
                    <span class="block font-semibold text-ink">List me in the officials directory</span>
                    <span class="block text-sm text-black/55">Your name, photo, location, experience and classification become visible to other members.</span>
                </span>
            </label>

            <label class="mt-4 flex items-start gap-3">
                <input type="checkbox" name="email_opt_out" value="1" @checked(old('email_opt_out', $user->email_opt_out))
                       class="mt-1 rounded border-black/25 text-brand focus:ring-brand/30">
                <span>
                    <span class="block font-semibold text-ink">Do not send me association announcements</span>
                    <span class="block text-sm text-black/55">You will still receive essential account email such as password resets and order confirmations.</span>
                </span>
            </label>
        </section>

        <button type="submit"
                class="rounded-md bg-brand px-8 py-3.5 font-display text-sm font-bold uppercase tracking-wider text-white hover:bg-brand-dark transition">
            Save profile
        </button>
    </form>

    {{-- image removal, kept out of the main form so they are separate submits --}}
    <div class="mt-4 flex gap-3">
        @if ($user->photo_path)
            <form method="POST" action="{{ route('profile.photo.remove') }}">
                @csrf @method('DELETE')
                <button type="submit" class="text-sm font-semibold text-brand-red hover:underline">Remove photo</button>
            </form>
        @endif
        @if ($user->banner_path)
            <form method="POST" action="{{ route('profile.banner.remove') }}">
                @csrf @method('DELETE')
                <button type="submit" class="text-sm font-semibold text-brand-red hover:underline">Remove banner</button>
            </form>
        @endif
    </div>

    {{-- password --}}
    <section class="mt-8 rounded-xl border border-black/10 p-6">
        <h2 class="font-display text-lg font-bold uppercase tracking-wide text-ink">Change password</h2>

        <form method="POST" action="{{ route('profile.password') }}" class="mt-5 space-y-5">
            @csrf
            @method('PUT')

            <div>
                <label for="current_password" class="{{ $label }}">Current password</label>
                <input id="current_password" name="current_password" type="password" autocomplete="current-password" class="{{ $input }}">
            </div>
            <div class="grid gap-5 sm:grid-cols-2">
                <div>
                    <label for="new_password" class="{{ $label }}">New password</label>
                    <input id="new_password" name="password" type="password" autocomplete="new-password" class="{{ $input }}">
                </div>
                <div>
                    <label for="password_confirmation" class="{{ $label }}">Confirm new password</label>
                    <input id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" class="{{ $input }}">
                </div>
            </div>

            <button type="submit"
                    class="rounded-md border-2 border-brand px-6 py-2.5 font-display text-sm font-bold uppercase tracking-wider text-brand hover:bg-brand hover:text-white transition">
                Change password
            </button>
        </form>
    </section>
</div>
@endsection
