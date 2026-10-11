@extends('layouts.app')
@section('title', 'Login')

@section('content')
<div class="min-h-[70vh] grid place-items-center py-12 px-4" x-data="loginForm()">
    <div class="w-full max-w-md">
        <div class="rounded-2xl bg-white border border-gray-200 shadow-sm p-8">
            <h1 class="text-2xl font-bold">Welcome back</h1>
            <p class="text-sm text-gray-500 mt-1">Log in to manage your fitness journey.</p>

            {{-- Error box --}}
            <div x-show="error" x-cloak x-transition
                 class="mt-4 rounded-lg bg-red-50 p-3 text-sm text-red-700 border border-red-200"
                 x-text="error"></div>

            <form @submit.prevent="submit()" class="mt-6 space-y-4">
                <div>
                    <label class="text-sm font-medium">Email</label>
                    <input type="email" x-model="email" required
                           :disabled="loading"
                           class="mt-1 w-full rounded-lg border-gray-300 px-3 py-2 border focus:border-brand-500 focus:ring-brand-500 disabled:opacity-50">
                </div>

                {{-- Password with eye toggle --}}
                <div>
                    <label class="text-sm font-medium">Password</label>
                    <div class="relative mt-1">
                        <input :type="showPassword ? 'text' : 'password'"
                               x-model="password"
                               required
                               :disabled="loading"
                               class="w-full rounded-lg border-gray-300 px-3 py-2 pr-10 border focus:border-brand-500 focus:ring-brand-500 disabled:opacity-50">

                        {{-- Eye toggle button --}}
                        <button type="button"
                                @click="showPassword = !showPassword"
                                :disabled="loading"
                                class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-400 hover:text-gray-600 focus:outline-none disabled:opacity-50"
                                :aria-label="showPassword ? 'Hide password' : 'Show password'">

                            {{-- Eye icon (shown when password is hidden) --}}
                            <svg x-show="!showPassword" xmlns="http://www.w3.org/2000/svg"
                                 class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                      d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round"
                                      d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>

                            {{-- Eye-off icon (shown when password is visible) --}}
                            <svg x-show="showPassword" x-cloak xmlns="http://www.w3.org/2000/svg"
                                 class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                      d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                            </svg>
                        </button>
                    </div>
                </div>

                <div class="flex items-center gap-2 text-sm">
                    <input type="checkbox" x-model="remember" id="remember" class="rounded" :disabled="loading">
                    <label for="remember">Remember me</label>
                </div>

                {{-- Submit button with loader --}}
                <button type="submit"
                        :disabled="loading"
                        class="relative w-full rounded-lg bg-brand-600 py-2.5 font-semibold text-white hover:bg-brand-700 disabled:opacity-80 disabled:cursor-not-allowed transition">

                    <span x-show="loading" class="absolute inset-0 flex items-center justify-center">
                        <svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                        </svg>
                        <span class="ml-2">Signing in...</span>
                    </span>

                    <span x-show="!loading">Sign in</span>
                </button>
            </form>

            <p class="mt-6 text-center text-sm text-gray-500">
                No account?
                <a href="{{ route('register') }}" class="text-brand-600 font-medium">Create one</a>
            </p>
        </div>
    </div>
</div>

@push('scripts')
<script>
function loginForm() {
    return {
        email: @json(old('email', '')),
        password: '',
        remember: false,
        loading: false,
        error: '',
        showPassword: false,   // ← New state for eye toggle

        async submit() {
            this.error = '';
            this.loading = true;

            try {
                const res = await fetch('{{ route('login') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    },
                    body: JSON.stringify({
                        email: this.email,
                        password: this.password,
                        remember: this.remember,
                    }),
                });

                const data = await res.json();

                if (res.ok && data.redirect) {
                    window.location.href = data.redirect;
                    return;
                }

                this.error = data.message || data.errors?.email?.[0] || 'Login failed. Please check your credentials.';
            } catch (err) {
                this.error = 'Network error. Please check your connection and try again.';
            } finally {
                this.loading = false;
            }
        }
    }
}
</script>
@endpush
@endsection