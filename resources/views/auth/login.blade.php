@extends('layouts.auth')
@section('title', 'Log in')
@section('heading', 'Welcome back')
@section('subheading', 'Sign in to your IPABOA account.')

@section('form')
<form method="POST" action="/login" class="space-y-5">
    @csrf

    <div>
        <label for="email" class="block text-sm font-semibold text-ink mb-1.5">Email address</label>
        <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus autocomplete="username"
               class="w-full rounded-md border border-black/20 px-4 py-2.5 focus:border-brand focus:outline-none focus:ring-2 focus:ring-brand/20">
    </div>

    <div>
        <label for="password" class="block text-sm font-semibold text-ink mb-1.5">Password</label>
        <input id="password" name="password" type="password" required autocomplete="current-password"
               class="w-full rounded-md border border-black/20 px-4 py-2.5 focus:border-brand focus:outline-none focus:ring-2 focus:ring-brand/20">
    </div>

    <div class="flex items-center justify-between">
        <label class="flex items-center gap-2 text-sm text-black/70">
            <input type="checkbox" name="remember" class="rounded border-black/25 text-brand focus:ring-brand/30">
            Remember me
        </label>
        <a href="/forgot-password" class="text-sm font-semibold text-brand hover:text-brand-red">Forgot password?</a>
    </div>

    <button type="submit"
            class="w-full rounded-md bg-brand px-6 py-3 font-display text-base font-bold uppercase tracking-wider text-white hover:bg-brand-dark transition">
        Log in
    </button>
</form>
@endsection

@section('footer')
    Not a member yet? <a href="/register" class="font-semibold text-brand hover:text-brand-red">Create an account</a>
@endsection
