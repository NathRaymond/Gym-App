@extends('layouts.app')
@section('title', 'Register')
@section('content')
<div class="min-h-[70vh] grid place-items-center py-12 px-4">
    <div class="w-full max-w-md">
        <div class="rounded-2xl bg-white border border-gray-200 shadow-sm p-8">
            <h1 class="text-2xl font-bold">Create your account</h1>
            <p class="text-sm text-gray-500 mt-1">Join De-manchy GymCenter today.</p>
            @if($errors->any())<div class="mt-4 rounded-lg bg-red-50 p-3 text-sm text-red-700">{{ $errors->first() }}</div>@endif
            <form method="POST" action="{{ route('register') }}" class="mt-6 space-y-4">
                @csrf
                <div class="grid grid-cols-2 gap-3">
                    <div><label class="text-sm font-medium">First name</label><input name="first_name" value="{{ old('first_name') }}" required class="mt-1 w-full rounded-lg border-gray-300 px-3 py-2 border"></div>
                    <div><label class="text-sm font-medium">Last name</label><input name="last_name" value="{{ old('last_name') }}" required class="mt-1 w-full rounded-lg border-gray-300 px-3 py-2 border"></div>
                </div>
                <div><label class="text-sm font-medium">Email</label><input type="email" name="email" value="{{ old('email') }}" required class="mt-1 w-full rounded-lg border-gray-300 px-3 py-2 border"></div>
                <div><label class="text-sm font-medium">Phone</label><input name="phone" value="{{ old('phone') }}" class="mt-1 w-full rounded-lg border-gray-300 px-3 py-2 border"></div>
                <div><label class="text-sm font-medium">Password</label><input type="password" name="password" required class="mt-1 w-full rounded-lg border-gray-300 px-3 py-2 border"></div>
                <div><label class="text-sm font-medium">Confirm password</label><input type="password" name="password_confirmation" required class="mt-1 w-full rounded-lg border-gray-300 px-3 py-2 border"></div>
                <button class="w-full rounded-lg bg-brand-600 py-2.5 font-semibold text-white hover:bg-brand-700">Create account</button>
            </form>
            <p class="mt-6 text-center text-sm text-gray-500">Already have an account? <a href="{{ route('login') }}" class="text-brand-600 font-medium">Log in</a></p>
        </div>
    </div>
</div>
@endsection
