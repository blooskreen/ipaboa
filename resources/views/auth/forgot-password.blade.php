@extends('layouts.auth')
@section('title', 'Reset password')
@section('heading', 'Forgot your password?')
@section('subheading', 'Enter your email and we will send you a reset link.')

@section('form')
<form method="POST" action="/forgot-password" class="space-y-5">
    @csrf
    <div>
        <label for="email" class="block text-sm font-semibold text-ink mb-1.5">Email address</label>
        <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus
               class="w-full rounded-md border border-black/20 px-4 py-2.5 focus:border-brand focus:outline-none focus:ring-2 focus:ring-brand/20">
    </div>
    <button type="submit"
            class="w-full rounded-md bg-brand px-6 py-3 font-display text-base font-bold uppercase tracking-wider text-white hover:bg-brand-dark transition">
        Send reset link
    </button>
</form>
@endsection

@section('footer')
    <a href="/login" class="font-semibold text-brand hover:text-brand-red">Back to log in</a>
@endsection
