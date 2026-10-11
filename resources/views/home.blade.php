@extends('layouts.app')
@section('title', 'Home')

@section('content')
<!-- Hero -->
<section class="bg-gradient-to-br from-brand-700 to-brand-500 text-white">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-24">
        <div class="grid lg:grid-cols-2 gap-12 items-center">
            <div>
                <span class="inline-block rounded-full bg-white/20 px-3 py-1 text-xs font-semibold mb-4">De-Manchy Gym</span>
                <h1 class="text-4xl sm:text-5xl lg:text-6xl font-bold leading-tight">Transform Your Body, Elevate Your Life.</h1>
                <p class="mt-6 text-lg text-white/90">Join Ibadan's most modern fitness center. World-class trainers, cutting-edge equipment, and flexible memberships tailored for you.</p>
                <div class="mt-8 flex flex-wrap gap-4">
                    <a href="{{ route('register') }}" class="rounded-lg bg-white px-6 py-3 font-semibold text-brand-700 hover:bg-gray-100">Get Started Free</a>
                    <a href="#plans" class="rounded-lg border border-white/40 px-6 py-3 font-semibold hover:bg-white/10">View Plans</a>
                </div>
                <div class="mt-10 grid grid-cols-3 gap-6 max-w-lg">
                    <div><div class="text-3xl font-bold">1K+</div><div class="text-sm text-white/70">Active Members</div></div>
                    <div><div class="text-3xl font-bold">10+</div><div class="text-sm text-white/70">Trainers</div></div>
                    <div><div class="text-3xl font-bold">24/7</div><div class="text-sm text-white/70">Access</div></div>
                </div>
            </div>
            <div class="hidden lg:block">
                <div class="rounded-2xl bg-white/10 backdrop-blur p-2 border border-white/20">
                    <img src="{{ asset('images/homepage.jpg') }}" class="rounded-xl" alt="Gym">
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Announcements -->
@if($announcements->count())
<section class="bg-yellow-50 border-y border-yellow-200">
    <div class="mx-auto max-w-7xl px-4 py-4 space-y-1">
        @foreach($announcements as $a)
            <div class="text-sm"><strong>{{ $a->title }}:</strong> {{ $a->content }}</div>
        @endforeach
    </div>
</section>
@endif

<!-- Plans -->
<section id="plans" class="py-20">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-14">
            <h2 class="text-3xl font-bold">Membership Plans</h2>
            <p class="mt-3 text-gray-600">Choose a plan that fits your fitness goals.</p>
        </div>

        <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-6">
            @foreach($plans as $plan)
                <div class="rounded-2xl bg-white border {{ $plan->is_featured ? 'border-brand-500 ring-2 ring-brand-500/20' : 'border-gray-200' }} p-6 shadow-sm flex flex-col relative">

                    @if($plan->is_featured)
                        <span class="absolute -top-3 left-6 rounded-full bg-brand-600 text-white text-[10px] font-bold px-3 py-1 uppercase tracking-wide">
                            Most Popular
                        </span>
                    @endif

                    <h3 class="text-lg font-semibold {{ $plan->is_featured ? 'mt-2' : '' }}">{{ $plan->name }}</h3>

                    {{-- ✅ Plan description --}}
                    @if($plan->description)
                        <p class="text-xs text-gray-500 mt-1 leading-snug line-clamp-2">
                            {{ $plan->description }}
                        </p>
                    @endif

                    <div class="mt-3 text-3xl font-bold">₦{{ number_format($plan->price) }}</div>
                    <div class="text-sm text-gray-500">/ {{ $plan->duration_days }} days</div>

                    <ul class="mt-5 space-y-2 text-sm text-gray-600 flex-1">
                        @foreach($plan->features ?? [] as $f)
                            <li class="flex gap-2">
                                <span class="text-brand-600 shrink-0">✓</span>
                                <span>{{ $f }}</span>
                            </li>
                        @endforeach
                    </ul>

                    <a href="{{ auth()->check() ? route('member.membership') : route('register') }}"
                       class="mt-6 rounded-lg bg-brand-600 px-4 py-2 text-center font-semibold text-white hover:bg-brand-700 transition">
                        Subscribe
                    </a>
                </div>
            @endforeach
        </div>
    </div>
</section>

<!-- Classes -->
<section class="py-20 bg-gray-100">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <h2 class="text-3xl font-bold text-center mb-14">Our Classes</h2>
        <div class="grid md:grid-cols-3 gap-6">
            @forelse($classes as $class)
                <div class="rounded-2xl bg-white overflow-hidden shadow-sm border border-gray-200">
                    <div class="h-40 bg-gradient-to-br from-brand-500 to-brand-700 grid place-items-center text-white text-3xl font-bold">
                        {{ strtoupper(substr($class->name, 0, 1)) }}
                    </div>
                    <div class="p-5">
                        <h3 class="font-semibold">{{ $class->name }}</h3>
                        <p class="text-sm text-gray-500 mt-1">{{ Str::limit($class->description, 80) }}</p>
                        <div class="mt-3 flex items-center justify-between text-sm">
                            <span class="rounded-full bg-gray-100 px-3 py-1 text-gray-700 text-xs">
                                {{ ucfirst(str_replace('_', ' ', $class->difficulty)) }}
                            </span>
                            <span class="font-semibold text-brand-600">
                                {{ $class->price > 0 ? '₦' . number_format($class->price) : 'Free' }}
                            </span>
                        </div>
                    </div>
                </div>
            @empty
                <p class="text-gray-500 col-span-3 text-center py-8">Classes coming soon.</p>
            @endforelse
        </div>
    </div>
</section>

<!-- Trainers -->
<section class="py-20">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <h2 class="text-3xl font-bold text-center mb-14">Meet Our Trainers</h2>
        <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-6">
            @php
                $visibleTrainers = $trainers->filter(fn($t) => $t->user !== null);
            @endphp

            @forelse($visibleTrainers as $t)
                <div class="rounded-2xl bg-white p-6 text-center border border-gray-200 hover:shadow-md transition">
                    <img src="{{ $t->user->avatar_url }}"
                         class="h-24 w-24 rounded-full mx-auto object-cover"
                         alt="{{ $t->user->full_name }}"
                         onerror="this.src='https://ui-avatars.com/api/?name={{ urlencode($t->user->full_name) }}&background=6366f1&color=fff&size=128'">
                    <h3 class="mt-4 font-semibold">{{ $t->user->full_name }}</h3>
                    @if(!empty($t->specializations))
                        <p class="text-sm text-gray-500">
                            {{ implode(', ', array_slice($t->specializations, 0, 2)) }}
                        </p>
                    @else
                        <p class="text-sm text-gray-400">Personal Trainer</p>
                    @endif
                    <div class="mt-2 text-yellow-500 text-sm">
                        ★ {{ number_format((float) ($t->rating ?? 0), 1) }}
                    </div>
                </div>
            @empty
                <p class="text-gray-500 col-span-4 text-center py-8">
                    Trainers coming soon.
                </p>
            @endforelse
        </div>
    </div>
</section>

<!-- Testimonials -->
@php
    $visibleTestimonials = $testimonials->filter(fn($t) => $t->user !== null);
@endphp

@if($visibleTestimonials->count())
<section class="py-20 bg-gray-100">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <h2 class="text-3xl font-bold text-center mb-14">What Our Members Say</h2>
        <div class="grid md:grid-cols-3 gap-6">
            @foreach($visibleTestimonials as $t)
                <div class="rounded-2xl bg-white p-6 border border-gray-200">
                    <div class="text-yellow-500 mb-2">
                        {{ str_repeat('★', max(0, min(5, $t->rating))) }}{{ str_repeat('☆', max(0, 5 - $t->rating)) }}
                    </div>
                    <p class="text-gray-700 italic">"{{ $t->message }}"</p>
                    <div class="mt-4 flex items-center gap-3">
                        <img src="{{ $t->user->avatar_url }}"
                             class="h-10 w-10 rounded-full"
                             alt="{{ $t->user->full_name }}"
                             onerror="this.src='https://ui-avatars.com/api/?name={{ urlencode($t->user->full_name) }}&background=6366f1&color=fff'">
                        <div class="font-semibold text-sm">{{ $t->user->full_name }}</div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>
@endif

<!-- CTA Section -->
<section class="bg-gradient-to-br from-brand-700 to-brand-500 text-white py-16">
    <div class="mx-auto max-w-4xl px-4 text-center">
        <h2 class="text-3xl font-bold">Ready to Start Your Fitness Journey?</h2>
        <p class="mt-4 text-lg text-white/90">Join De-Manchy GymCenter today and transform your body, mind, and life.</p>
        <div class="mt-8 flex justify-center gap-4 flex-wrap">
            <a href="{{ route('register') }}" class="rounded-lg bg-white px-6 py-3 font-semibold text-brand-700 hover:bg-gray-100">
                Get Started Free
            </a>
            <a href="#plans" class="rounded-lg border border-white/40 px-6 py-3 font-semibold hover:bg-white/10">
                View Plans
            </a>
        </div>
    </div>
</section>

<footer class="bg-gray-900 text-gray-300 py-12">
    <div class="mx-auto max-w-7xl px-4 grid md:grid-cols-4 gap-8">
        <div>
            <div class="flex items-center gap-2 text-white font-bold text-lg mb-3">
                <div class="h-8 w-8 rounded-lg bg-brand-600 grid place-items-center">G</div>
                De-Manchy GymCenter
            </div>
            <p class="text-sm text-gray-400">Ibadan's premier fitness destination.</p>
        </div>
        <div>
            <h4 class="font-semibold text-white mb-3">Quick Links</h4>
            <ul class="space-y-2 text-sm">
                <li><a href="#plans" class="hover:text-white">Plans</a></li>
                <li><a href="#" class="hover:text-white">Classes</a></li>
                <li><a href="#" class="hover:text-white">Trainers</a></li>
            </ul>
        </div>
        <div>
            <h4 class="font-semibold text-white mb-3">Contact</h4>
            <p class="text-sm">
                No 19 Oyegbami Street, Felele Barracks, Ibadan<br>
                +234 800 000 0000<br>
                info@gymcenter.com
            </p>
        </div>
        <div>
            <h4 class="font-semibold text-white mb-3">Follow</h4>
            <p class="text-sm">Instagram · Twitter · Facebook</p>
        </div>
    </div>
    <div class="mt-8 border-t border-gray-800 pt-6 text-center text-xs text-gray-500">
        © {{ date('Y') }} De-Manchy GymCenter Ibadan. All rights reserved.
    </div>
</footer>
@endsection