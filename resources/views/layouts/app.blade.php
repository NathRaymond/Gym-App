<!DOCTYPE html>
<html lang="en" class="h-full">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'De-Manchy GymCenter') - De-Manchy GymCenter Ibadan</title>

    {{-- Fonts --}}
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700" rel="stylesheet">

    {{-- Tailwind CSS --}}
    <script src="{{ asset('assets/tailwind.js') }}"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        brand: {
                            50: '#eef2ff',
                            100: '#e0e7ff',
                            500: '#6366f1',
                            600: '#4f46e5',
                            700: '#4338ca',
                            800: '#3730a3'
                        }
                    },
                    fontFamily: {
                        sans: ['Inter', 'sans-serif']
                    }
                }
            }
        }
    </script>

    {{-- SweetAlert2 --}}
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    {{-- Alpine.js --}}
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    {{-- Chart.js --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    {{-- x-cloak fix --}}
    <style>
        [x-cloak] {
            display: none !important;
        }
    </style>

    @stack('head')
</head>

<body class="h-full bg-gray-50 text-gray-900 font-sans antialiased">
    <div class="min-h-full">

        {{-- ============ NAVIGATION ============ --}}
        <nav class="bg-white border-b border-gray-200 sticky top-0 z-40" x-data="{ mobileOpen: false }">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="flex h-16 justify-between items-center">

                    {{-- LEFT: Logo + Desktop Nav --}}
                    <div class="flex items-center gap-8">
                        <a href="{{ url('/') }}" class="flex items-center gap-2 shrink-0">
                            <div class="h-8 w-8 rounded-lg bg-brand-600 text-white grid place-items-center font-bold">G
                            </div>
                            <span class="font-semibold text-lg hidden sm:inline">De-Manchy GymCenter</span>
                        </a>

                        {{-- Desktop nav --}}
                        <div class="hidden md:flex items-center gap-1 text-sm">
                            @auth
                                {{-- MEMBER --}}
                                @if (auth()->user()->hasRole('member'))
                                    <a href="{{ route('member.dashboard') }}"
                                        class="px-3 py-2 rounded-md hover:bg-gray-100">Dashboard</a>
                                    <a href="{{ route('member.bookings') }}"
                                        class="px-3 py-2 rounded-md hover:bg-gray-100">My Bookings</a>
                                    <a href="{{ route('member.membership') }}"
                                        class="px-3 py-2 rounded-md hover:bg-gray-100">Membership</a>
                                    <a href="{{ route('member.wallet') }}"
                                        class="px-3 py-2 rounded-md hover:bg-gray-100">Wallet</a>
                                    <a href="{{ route('member.payments') }}"
                                        class="px-3 py-2 rounded-md hover:bg-gray-100">Payments</a>
                                    <a href="{{ route('member.tickets') }}"
                                        class="px-3 py-2 rounded-md hover:bg-gray-100">Tickets</a>
                                    <a href="{{ route('member.feedback') }}"
                                        class="px-3 py-2 rounded-md hover:bg-gray-100">Feedback</a>
                                @endif

                                {{-- FRONT DESK --}}
                                @if (auth()->user()->hasRole('front-desk'))
                                    <a href="{{ route('frontdesk.dashboard') }}"
                                        class="px-3 py-2 rounded-md hover:bg-gray-100">Front Desk</a>
                                    <a href="{{ route('frontdesk.verify-ticket') }}"
                                        class="px-3 py-2 rounded-md hover:bg-gray-100">Verify Ticket</a>
                                    <a href="{{ route('frontdesk.payments') }}"
                                        class="px-3 py-2 rounded-md hover:bg-gray-100">Record Payment</a>
                                    <a href="{{ route('frontdesk.members') }}"
                                        class="px-3 py-2 rounded-md hover:bg-gray-100">Members</a>
                                    <a href="{{ route('frontdesk.bookings') }}"
                                        class="px-3 py-2 rounded-md hover:bg-gray-100">Bookings</a>
                                @endif

                                {{-- TRAINER --}}
                                @if (auth()->user()->hasRole('trainer'))
                                    <a href="{{ route('trainer.dashboard') }}"
                                        class="px-3 py-2 rounded-md hover:bg-gray-100">My Dashboard</a>
                                    <a href="{{ route('trainer.schedule') }}"
                                        class="px-3 py-2 rounded-md hover:bg-gray-100">Schedule</a>
                                    <a href="{{ route('trainer.clients') }}"
                                        class="px-3 py-2 rounded-md hover:bg-gray-100">My Clients</a>
                                    <a href="{{ route('trainer.earnings') }}"
                                        class="px-3 py-2 rounded-md hover:bg-gray-100">Earnings</a>
                                @endif

                                {{-- ADMIN / MANAGER / SUPER-ADMIN --}}
                                @if (auth()->user()->hasAnyRole(['super-admin', 'admin', 'manager']))
                                    {{-- Dashboard --}}
                                    <a href="{{ route('admin.dashboard') }}"
                                        class="px-3 py-2 rounded-md hover:bg-gray-100">Dashboard</a>

                                    {{-- People Dropdown --}}
                                    <div class="relative" x-data="{ open: false }" @click.outside="open = false">
                                        <button @click="open = !open"
                                            class="px-3 py-2 rounded-md hover:bg-gray-100 flex items-center gap-1">
                                            People
                                            <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M19 9l-7 7-7-7" />
                                            </svg>
                                        </button>
                                        <div x-show="open" x-cloak x-transition:enter="transition ease-out duration-100"
                                            x-transition:enter-start="opacity-0 scale-95"
                                            x-transition:enter-end="opacity-100 scale-100"
                                            class="absolute left-0 mt-2 w-48 rounded-lg bg-white border border-gray-200 shadow-lg py-1 z-50">
                                            <a href="{{ route('admin.users.index') }}"
                                                class="block px-4 py-2 text-sm hover:bg-gray-50">Users</a>
                                            <a href="{{ route('admin.members') }}"
                                                class="block px-4 py-2 text-sm hover:bg-gray-50">Members</a>
                                            <a href="{{ route('admin.trainers.index') }}"
                                                class="block px-4 py-2 text-sm hover:bg-gray-50">Trainers</a>
                                        </div>
                                    </div>

                                    {{-- Fitness Dropdown --}}
                                    <div class="relative" x-data="{ open: false }" @click.outside="open = false">
                                        <button @click="open = !open"
                                            class="px-3 py-2 rounded-md hover:bg-gray-100 flex items-center gap-1">
                                            Fitness
                                            <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M19 9l-7 7-7-7" />
                                            </svg>
                                        </button>
                                        <div x-show="open" x-cloak x-transition:enter="transition ease-out duration-100"
                                            x-transition:enter-start="opacity-0 scale-95"
                                            x-transition:enter-end="opacity-100 scale-100"
                                            class="absolute left-0 mt-2 w-56 rounded-lg bg-white border border-gray-200 shadow-lg py-1 z-50">
                                            <a href="{{ route('admin.plans.index') }}"
                                                class="block px-4 py-2 text-sm hover:bg-gray-50">Plans</a>
                                            <a href="{{ route('admin.classes.index') }}"
                                                class="block px-4 py-2 text-sm hover:bg-gray-50">Classes</a>
                                            <a href="{{ route('admin.session-categories.index') }}"
                                                class="block px-4 py-2 text-sm hover:bg-gray-50">Session Categories</a>
                                            <a href="{{ route('admin.session-types.index') }}"
                                                class="block px-4 py-2 text-sm hover:bg-gray-50">Session Types</a>
                                            <a href="{{ route('admin.session-slots.index') }}"
                                                class="block px-4 py-2 text-sm hover:bg-gray-50">Session Slots</a>
                                            <a href="{{ route('admin.equipment.index') }}"
                                                class="block px-4 py-2 text-sm hover:bg-gray-50">Equipment</a>
                                        </div>
                                    </div>

                                    {{-- Money Dropdown --}}
                                    <div class="relative" x-data="{ open: false }" @click.outside="open = false">
                                        <button @click="open = !open"
                                            class="px-3 py-2 rounded-md hover:bg-gray-100 flex items-center gap-1">
                                            Money
                                            <svg class="h-3 w-3" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M19 9l-7 7-7-7" />
                                            </svg>
                                        </button>
                                        <div x-show="open" x-cloak x-transition:enter="transition ease-out duration-100"
                                            x-transition:enter-start="opacity-0 scale-95"
                                            x-transition:enter-end="opacity-100 scale-100"
                                            class="absolute left-0 mt-2 w-48 rounded-lg bg-white border border-gray-200 shadow-lg py-1 z-50">
                                            <a href="{{ route('admin.bookings.index') }}"
                                                class="block px-4 py-2 text-sm hover:bg-gray-50">Bookings</a>
                                            <a href="{{ route('admin.payments') }}"
                                                class="block px-4 py-2 text-sm hover:bg-gray-50">Payments</a>
                                            <a href="{{ route('admin.reports.revenue') }}"
                                                class="block px-4 py-2 text-sm hover:bg-gray-50">Reports</a>
                                        </div>
                                    </div>

                                    {{-- System Dropdown --}}
                                    <div class="relative" x-data="{ open: false }" @click.outside="open = false">
                                        <button @click="open = !open"
                                            class="px-3 py-2 rounded-md hover:bg-gray-100 flex items-center gap-1">
                                            System
                                            <svg class="h-3 w-3" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M19 9l-7 7-7-7" />
                                            </svg>
                                        </button>
                                        <div x-show="open" x-cloak x-transition:enter="transition ease-out duration-100"
                                            x-transition:enter-start="opacity-0 scale-95"
                                            x-transition:enter-end="opacity-100 scale-100"
                                            class="absolute left-0 mt-2 w-56 rounded-lg bg-white border border-gray-200 shadow-lg py-1 z-50">
                                            <a href="{{ route('admin.announcements.index') }}"
                                                class="block px-4 py-2 text-sm hover:bg-gray-50">Announcements</a>
                                            <a href="{{ route('admin.feedback') }}"
                                                class="block px-4 py-2 text-sm hover:bg-gray-50">Feedback</a>
                                            <a href="{{ route('admin.settings') }}"
                                                class="block px-4 py-2 text-sm hover:bg-gray-50">Settings</a>
                                            @if (auth()->user()->hasRole('super-admin'))
                                                <a href="{{ route('admin.roles.index') }}"
                                                    class="block px-4 py-2 text-sm hover:bg-gray-50">Roles &amp;
                                                    Permissions</a>
                                            @endif
                                            <a href="{{ route('admin.activity') }}"
                                                class="block px-4 py-2 text-sm hover:bg-gray-50">Activity Log</a>
                                        </div>
                                    </div>
                                @endif
                            @endauth
                        </div>
                    </div>

                    {{-- RIGHT: Wallet + Avatar + Hamburger --}}
                    <div class="flex items-center gap-3">
                        @auth
                            {{-- Wallet balance (member only) --}}
                            @if (auth()->user()->hasRole('member') && auth()->user()->wallet)
                                <span class="hidden sm:inline text-sm font-medium text-gray-700">
                                    ₦{{ number_format(auth()->user()->wallet->balance, 2) }}
                                </span>
                            @endif

                            {{-- Avatar --}}
                            <a href="{{ auth()->user()->dashboardUrl() }}" class="flex items-center gap-2">
                                <img src="{{ auth()->user()->avatar_url }}" class="h-9 w-9 rounded-full" alt="">
                                <span class="hidden sm:block text-sm font-medium">{{ auth()->user()->first_name }}</span>
                            </a>

                            {{-- Logout (desktop) --}}
                            <form action="{{ route('logout') }}" method="POST" class="hidden md:block">
                                @csrf
                                <button class="text-sm text-gray-500 hover:text-gray-800">Logout</button>
                            </form>

                            {{-- Hamburger button (mobile) --}}
                            <button @click="mobileOpen = !mobileOpen"
                                class="md:hidden inline-flex items-center justify-center p-2 rounded-md text-gray-500 hover:text-gray-900 hover:bg-gray-100"
                                aria-label="Toggle menu">
                                <svg x-show="!mobileOpen" class="h-6 w-6" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M4 6h16M4 12h16M4 18h16" />
                                </svg>
                                <svg x-show="mobileOpen" x-cloak class="h-6 w-6" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        @else
                            <a href="{{ route('login') }}"
                                class="text-sm font-medium text-gray-700 hover:text-gray-900">Login</a>
                            <a href="{{ route('register') }}"
                                class="rounded-lg bg-brand-600 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-700">Sign
                                up</a>
                        @endauth
                    </div>
                </div>
            </div>

            {{-- ============ MOBILE MENU DROPDOWN ============ --}}
            @auth
                <div x-show="mobileOpen" x-cloak @click.outside="mobileOpen = false"
                    x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0 -translate-y-2" x-transition:enter-end="opacity-100 translate-y-0"
                    class="md:hidden border-t border-gray-200 bg-white shadow-lg max-h-[80vh] overflow-y-auto">
                    <div class="px-4 py-3 space-y-1">

                        {{-- MEMBER --}}
                        @if (auth()->user()->hasRole('member'))
                            <a href="{{ route('member.dashboard') }}"
                                class="block px-3 py-2 rounded-md text-sm hover:bg-gray-100">Dashboard</a>
                            <a href="{{ route('member.bookings') }}"
                                class="block px-3 py-2 rounded-md text-sm hover:bg-gray-100">My Bookings</a>
                            <a href="{{ route('member.membership') }}"
                                class="block px-3 py-2 rounded-md text-sm hover:bg-gray-100">Membership</a>
                            <a href="{{ route('member.wallet') }}"
                                class="block px-3 py-2 rounded-md text-sm hover:bg-gray-100">Wallet</a>
                            <a href="{{ route('member.payments') }}"
                                class="block px-3 py-2 rounded-md text-sm hover:bg-gray-100">Payments</a>
                            <a href="{{ route('member.tickets') }}"
                                class="block px-3 py-2 rounded-md text-sm hover:bg-gray-100">Tickets</a>
                            <a href="{{ route('member.feedback') }}"
                                class="block px-3 py-2 rounded-md text-sm hover:bg-gray-100">Feedback</a>
                        @endif

                        {{-- FRONT DESK --}}
                        @if (auth()->user()->hasRole('front-desk'))
                            <a href="{{ route('frontdesk.dashboard') }}"
                                class="block px-3 py-2 rounded-md text-sm hover:bg-gray-100">Front Desk</a>
                            <a href="{{ route('frontdesk.verify-ticket') }}"
                                class="block px-3 py-2 rounded-md text-sm hover:bg-gray-100">Verify Ticket</a>
                            <a href="{{ route('frontdesk.payments') }}"
                                class="block px-3 py-2 rounded-md text-sm hover:bg-gray-100">Record Payment</a>
                            <a href="{{ route('frontdesk.members') }}"
                                class="block px-3 py-2 rounded-md text-sm hover:bg-gray-100">Members</a>
                            <a href="{{ route('frontdesk.bookings') }}"
                                class="block px-3 py-2 rounded-md text-sm hover:bg-gray-100">Bookings</a>
                        @endif

                        {{-- TRAINER --}}
                        @if (auth()->user()->hasRole('trainer'))
                            <a href="{{ route('trainer.dashboard') }}"
                                class="block px-3 py-2 rounded-md text-sm hover:bg-gray-100">My Dashboard</a>
                            <a href="{{ route('trainer.schedule') }}"
                                class="block px-3 py-2 rounded-md text-sm hover:bg-gray-100">Schedule</a>
                            <a href="{{ route('trainer.clients') }}"
                                class="block px-3 py-2 rounded-md text-sm hover:bg-gray-100">My Clients</a>
                            <a href="{{ route('trainer.earnings') }}"
                                class="block px-3 py-2 rounded-md text-sm hover:bg-gray-100">Earnings</a>
                        @endif

                        {{-- ADMIN / MANAGER / SUPER-ADMIN --}}
                        @if (auth()->user()->hasAnyRole(['super-admin', 'admin', 'manager']))
                            <a href="{{ route('admin.dashboard') }}"
                                class="block px-3 py-2 rounded-md text-sm hover:bg-gray-100 font-medium">Dashboard</a>

                            <div class="border-t border-gray-100 my-2"></div>
                            <div class="px-3 py-1 text-xs uppercase text-gray-400 font-semibold">People</div>
                            <a href="{{ route('admin.users.index') }}"
                                class="block px-3 py-2 rounded-md text-sm hover:bg-gray-100 pl-6">Users</a>
                            <a href="{{ route('admin.members') }}"
                                class="block px-3 py-2 rounded-md text-sm hover:bg-gray-100 pl-6">Members</a>
                            <a href="{{ route('admin.trainers.index') }}"
                                class="block px-3 py-2 rounded-md text-sm hover:bg-gray-100 pl-6">Trainers</a>

                            <div class="border-t border-gray-100 my-2"></div>
                            <div class="px-3 py-1 text-xs uppercase text-gray-400 font-semibold">Fitness</div>
                            <a href="{{ route('admin.plans.index') }}"
                                class="block px-3 py-2 rounded-md text-sm hover:bg-gray-100 pl-6">Plans</a>
                            <a href="{{ route('admin.classes.index') }}"
                                class="block px-3 py-2 rounded-md text-sm hover:bg-gray-100 pl-6">Classes</a>
                            <a href="{{ route('admin.session-categories.index') }}"
                                class="block px-3 py-2 rounded-md text-sm hover:bg-gray-100 pl-6">Session Categories</a>
                            <a href="{{ route('admin.session-types.index') }}"
                                class="block px-3 py-2 rounded-md text-sm hover:bg-gray-100 pl-6">Session Types</a>
                            <a href="{{ route('admin.session-slots.index') }}"
                                class="block px-3 py-2 rounded-md text-sm hover:bg-gray-100 pl-6">Session Slots</a>
                            <a href="{{ route('admin.equipment.index') }}"
                                class="block px-3 py-2 rounded-md text-sm hover:bg-gray-100 pl-6">Equipment</a>

                            <div class="border-t border-gray-100 my-2"></div>
                            <div class="px-3 py-1 text-xs uppercase text-gray-400 font-semibold">Money</div>
                            <a href="{{ route('admin.bookings.index') }}"
                                class="block px-3 py-2 rounded-md text-sm hover:bg-gray-100 pl-6">Bookings</a>
                            <a href="{{ route('admin.payments') }}"
                                class="block px-3 py-2 rounded-md text-sm hover:bg-gray-100 pl-6">Payments</a>
                            <a href="{{ route('admin.reports.revenue') }}"
                                class="block px-3 py-2 rounded-md text-sm hover:bg-gray-100 pl-6">Reports</a>

                            <div class="border-t border-gray-100 my-2"></div>
                            <div class="px-3 py-1 text-xs uppercase text-gray-400 font-semibold">System</div>
                            <a href="{{ route('admin.announcements.index') }}"
                                class="block px-3 py-2 rounded-md text-sm hover:bg-gray-100 pl-6">Announcements</a>
                            <a href="{{ route('admin.feedback') }}"
                                class="block px-3 py-2 rounded-md text-sm hover:bg-gray-100 pl-6">Feedback</a>
                            <a href="{{ route('admin.settings') }}"
                                class="block px-3 py-2 rounded-md text-sm hover:bg-gray-100 pl-6">Settings</a>
                            @if (auth()->user()->hasRole('super-admin'))
                                <a href="{{ route('admin.roles.index') }}"
                                    class="block px-3 py-2 rounded-md text-sm hover:bg-gray-100 pl-6">Roles &amp;
                                    Permissions</a>
                            @endif
                            <a href="{{ route('admin.activity') }}"
                                class="block px-3 py-2 rounded-md text-sm hover:bg-gray-100 pl-6">Activity Log</a>
                        @endif

                        {{-- Divider --}}
                        <div class="border-t border-gray-100 my-2"></div>

                        {{-- Wallet balance --}}
                        @if (auth()->user()->hasRole('member') && auth()->user()->wallet)
                            <div class="px-3 py-2 text-sm text-gray-500">
                                Wallet: <span
                                    class="font-semibold text-gray-800">₦{{ number_format(auth()->user()->wallet->balance, 2) }}</span>
                            </div>
                        @endif

                        {{-- Logout --}}
                        <form action="{{ route('logout') }}" method="POST">
                            @csrf
                            <button
                                class="block w-full text-left px-3 py-2 rounded-md text-sm text-red-600 hover:bg-red-50 font-medium">
                                Logout
                            </button>
                        </form>
                    </div>
                </div>
            @endauth
        </nav>

        {{-- ============ MAIN CONTENT ============ --}}
        <main>
            @yield('content')
        </main>
    </div>

    {{-- Global SweetAlert handler for flash messages --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {

            // ============================================
            // Flash messages
            // ============================================
            @if (session('success'))
                Swal.fire({
                    icon: 'success',
                    title: 'Success!',
                    text: @json(session('success')),
                    confirmButtonColor: '#4f46e5',
                    timer: 3000,
                    timerProgressBar: true,
                });
            @endif

            @if (session('error'))
                Swal.fire({
                    icon: 'error',
                    title: 'Oops...',
                    text: @json(session('error')),
                    confirmButtonColor: '#4f46e5',
                });
            @endif

            @if (session('warning'))
                Swal.fire({
                    icon: 'warning',
                    title: 'Warning',
                    text: @json(session('warning')),
                    confirmButtonColor: '#4f46e5',
                });
            @endif

            @if (session('info'))
                Swal.fire({
                    icon: 'info',
                    title: 'Info',
                    text: @json(session('info')),
                    confirmButtonColor: '#4f46e5',
                });
            @endif

            @if ($errors->any())
                Swal.fire({
                    icon: 'error',
                    title: 'Validation Error',
                    html: `<ul style="text-align:left;margin:0;padding-left:20px;">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>`,
                    confirmButtonColor: '#4f46e5',
                });
            @endif
        });

        // ============================================
        // Confirm helper — with native fallback if Swal missing
        // ============================================
        function confirmSubmit(event, message, confirmText = 'Yes, proceed', cancelText = 'Cancel') {
            if (event) event.preventDefault();
            const form = event?.target?.closest('form');
            if (!form) return false;

            if (typeof Swal === 'undefined') {
                if (window.confirm(message)) {
                    form.submit();
                }
                return false;
            }

            Swal.fire({
                title: 'Are you sure?',
                text: message,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc2626',
                cancelButtonColor: '#6b7280',
                confirmButtonText: confirmText,
                cancelButtonText: cancelText,
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
            return false;
        }

        // Alias — for backwards compatibility
        function confirmAction(event, message, confirmText) {
            return confirmSubmit(event, message, confirmText);
        }
    </script>

    @stack('scripts')
</body>

</html>
