@extends('layouts.auth')
@section('title', 'Join')
@section('heading', 'Join IPABOA')
@section('subheading', 'Create your account. It takes about a minute.')

@section('form')
<form method="POST" action="/register" class="space-y-5">
    @csrf

    <div>
        <label for="name" class="block text-sm font-semibold text-ink mb-1.5">Full name</label>
        <input id="name" name="name" type="text" value="{{ old('name') }}" required autofocus autocomplete="name"
               class="w-full rounded-md border border-black/20 px-4 py-2.5 focus:border-brand focus:outline-none focus:ring-2 focus:ring-brand/20">
    </div>

    <div>
        <label for="email" class="block text-sm font-semibold text-ink mb-1.5">Email address</label>
        <input id="email" name="email" type="email" value="{{ old('email') }}" required autocomplete="username"
               class="w-full rounded-md border border-black/20 px-4 py-2.5 focus:border-brand focus:outline-none focus:ring-2 focus:ring-brand/20">
    </div>

    <div>
        <label for="password" class="block text-sm font-semibold text-ink mb-1.5">Password</label>
        <input id="password" name="password" type="password" required autocomplete="new-password"
               class="w-full rounded-md border border-black/20 px-4 py-2.5 focus:border-brand focus:outline-none focus:ring-2 focus:ring-brand/20">
        <p class="mt-1.5 text-xs text-black/50">At least 8 characters.</p>
    </div>

    <div>
        <label for="password_confirmation" class="block text-sm font-semibold text-ink mb-1.5">Confirm password</label>
        <input id="password_confirmation" name="password_confirmation" type="password" required autocomplete="new-password"
               class="w-full rounded-md border border-black/20 px-4 py-2.5 focus:border-brand focus:outline-none focus:ring-2 focus:ring-brand/20">
    </div>

    <button type="submit"
            class="w-full rounded-md bg-gold px-6 py-3 font-display text-base font-bold uppercase tracking-wider text-ink hover:bg-gold-soft transition">
        Create account
    </button>
</form>
@endsection

@section('footer')
    Already have an account? <a href="/login" class="font-semibold text-brand hover:text-brand-red">Log in</a>
@endsection
