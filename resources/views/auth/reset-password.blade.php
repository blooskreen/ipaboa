@extends('layouts.auth')
@section('title', 'Set a new password')
@section('heading', 'Set a new password')
@section('subheading', 'Choose something you have not used before.')

@section('form')
<form method="POST" action="/reset-password" class="space-y-5">
    @csrf
    <input type="hidden" name="token" value="{{ $request->route('token') }}">

    <div>
        <label for="email" class="block text-sm font-semibold text-ink mb-1.5">Email address</label>
        <input id="email" name="email" type="email" value="{{ old('email', $request->email) }}" required
               class="w-full rounded-md border border-black/20 px-4 py-2.5 focus:border-brand focus:outline-none focus:ring-2 focus:ring-brand/20">
    </div>

    <div>
        <label for="password" class="block text-sm font-semibold text-ink mb-1.5">New password</label>
        <input id="password" name="password" type="password" required autocomplete="new-password"
               class="w-full rounded-md border border-black/20 px-4 py-2.5 focus:border-brand focus:outline-none focus:ring-2 focus:ring-brand/20">
    </div>

    <div>
        <label for="password_confirmation" class="block text-sm font-semibold text-ink mb-1.5">Confirm new password</label>
        <input id="password_confirmation" name="password_confirmation" type="password" required autocomplete="new-password"
               class="w-full rounded-md border border-black/20 px-4 py-2.5 focus:border-brand focus:outline-none focus:ring-2 focus:ring-brand/20">
    </div>

    <button type="submit"
            class="w-full rounded-md bg-brand px-6 py-3 font-display text-base font-bold uppercase tracking-wider text-white hover:bg-brand-dark transition">
        Reset password
    </button>
</form>
@endsection

@section('footer')
    <a href="/login" class="font-semibold text-brand hover:text-brand-red">Back to log in</a>
@endsection
