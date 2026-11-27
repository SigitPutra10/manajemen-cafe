@props(['title' => 'Panel Admin'])

<!DOCTYPE html>
<html lang="id">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>{{ $title }} - {{ $cafeName }}</title>
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=plus-jakarta-sans:400,500,600,700&display=swap" rel="stylesheet" />
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-screen bg-[#F7F5F0] text-[#1C1917] antialiased">
        <div
            x-data="{
                sidebarOpen: false,
                openSidebar() {
                    this.sidebarOpen = true;
                    this.$nextTick(() => {
                        this.$refs.closeSidebarBtn?.focus();
                    });
                },
                closeSidebar() {
                    this.sidebarOpen = false;
                    this.$nextTick(() => {
                        this.$refs.openSidebarBtn?.focus();
                    });
                }
            }"
            @keydown.escape.window="if (sidebarOpen) closeSidebar()"
            class="flex min-h-screen"
        >
            {{-- Mobile Backdrop (solid dim, no glassmorphism) --}}
            <div
                x-show="sidebarOpen"
                x-transition.opacity.duration.150ms
                @click="closeSidebar()"
                class="fixed inset-0 z-30 bg-[#1C1917]/40 lg:hidden"
                style="display: none;"
                aria-hidden="true"
            ></div>

            {{-- Sidebar Navigation --}}
            <aside
                :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
                class="fixed inset-y-0 left-0 z-40 flex w-64 shrink-0 flex-col border-r border-[#E2DDD5] bg-white transition-transform duration-150 ease-out motion-reduce:transition-none lg:static lg:translate-x-0"
                aria-label="Navigasi Admin"
            >
                {{-- Brand / Cafe Name from settings --}}
                <div class="flex min-h-[64px] items-center justify-between border-b border-[#E2DDD5] px-4 py-3">
                    <div class="min-w-0">
                        <p class="text-[11px] font-semibold uppercase tracking-wider text-[#57534E]">Panel Admin</p>
                        <p class="truncate text-base font-bold text-[#1C1917]" title="{{ $cafeName }}">
                            {{ $cafeName }}
                        </p>
                    </div>
                    <button
                        type="button"
                        x-ref="closeSidebarBtn"
                        @click="closeSidebar()"
                        class="inline-flex min-h-[44px] min-w-[44px] items-center justify-center rounded-md border border-[#E2DDD5] bg-white text-[#1C1917] transition-colors hover:bg-[#EFECE6] focus:outline-none focus:ring-2 focus:ring-[#9A3412] focus:ring-offset-2 lg:hidden"
                        aria-label="Tutup menu navigasi"
                    >
                        <svg class="h-5 w-5" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.75" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 5l10 10M15 5L5 15" />
                        </svg>
                    </button>
                </div>

                {{-- Navigation Groups --}}
                <nav class="flex-1 overflow-y-auto px-3 py-4" aria-label="Menu Utama">
                    <div class="space-y-1">
                        <p class="px-3 pb-1 text-[11px] font-semibold uppercase tracking-wider text-[#57534E]">
                            Manajemen
                        </p>

                        {{-- Dashboard --}}
                        <a
                            href="{{ route('admin.dashboard') }}"
                            @class([
                                'flex min-h-[44px] items-center gap-3 rounded-md px-3 py-2 text-sm transition-colors focus:outline-none focus:ring-2 focus:ring-[#9A3412] focus:ring-offset-1',
                                'border border-[#E2DDD5] bg-[#EFECE6] font-semibold text-[#1C1917]' => request()->routeIs('admin.dashboard'),
                                'font-medium text-[#57534E] hover:bg-[#EFECE6] hover:text-[#1C1917]' => ! request()->routeIs('admin.dashboard'),
                            ])
                            @if(request()->routeIs('admin.dashboard')) aria-current="page" @endif
                        >
                            <svg class="h-5 w-5 shrink-0" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true">
                                <rect x="3" y="3" width="6" height="6" rx="1" />
                                <rect x="11" y="3" width="6" height="6" rx="1" />
                                <rect x="3" y="11" width="6" height="6" rx="1" />
                                <rect x="11" y="11" width="6" height="6" rx="1" />
                            </svg>
                            <span class="truncate">Dashboard</span>
                        </a>

                        {{-- Kategori --}}
                        <a
                            href="{{ route('admin.categories.index') }}"
                            @class([
                                'flex min-h-[44px] items-center gap-3 rounded-md px-3 py-2 text-sm transition-colors focus:outline-none focus:ring-2 focus:ring-[#9A3412] focus:ring-offset-1',
                                'border border-[#E2DDD5] bg-[#EFECE6] font-semibold text-[#1C1917]' => request()->routeIs('admin.categories.*'),
                                'font-medium text-[#57534E] hover:bg-[#EFECE6] hover:text-[#1C1917]' => ! request()->routeIs('admin.categories.*'),
                            ])
                            @if(request()->routeIs('admin.categories.*')) aria-current="page" @endif
                        >
                            <svg class="h-5 w-5 shrink-0" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 5.5A1.5 1.5 0 014.5 4h3.379a1.5 1.5 0 011.06.44l1.122 1.12A1.5 1.5 0 0011.121 6H15.5A1.5 1.5 0 0117 7.5v7A1.5 1.5 0 0115.5 16h-11A1.5 1.5 0 013 14.5v-9z" />
                            </svg>
                            <span class="truncate">Kategori</span>
                        </a>

                        {{-- Menu/Produk --}}
                        <a
                            href="{{ route('admin.products.index') }}"
                            @class([
                                'flex min-h-[44px] items-center gap-3 rounded-md px-3 py-2 text-sm transition-colors focus:outline-none focus:ring-2 focus:ring-[#9A3412] focus:ring-offset-1',
                                'border border-[#E2DDD5] bg-[#EFECE6] font-semibold text-[#1C1917]' => request()->routeIs('admin.products.*'),
                                'font-medium text-[#57534E] hover:bg-[#EFECE6] hover:text-[#1C1917]' => ! request()->routeIs('admin.products.*'),
                            ])
                            @if(request()->routeIs('admin.products.*')) aria-current="page" @endif
                        >
                            <svg class="h-5 w-5 shrink-0" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h12M4 10h12M4 14h8" />
                            </svg>
                            <span class="truncate">Menu/Produk</span>
                        </a>

                        <a
                            href="{{ route('admin.reports.index') }}"
                            @class([
                                'flex min-h-[44px] items-center gap-3 rounded-md px-3 py-2 text-sm transition-colors focus:outline-none focus:ring-2 focus:ring-[#9A3412] focus:ring-offset-1',
                                'border border-[#E2DDD5] bg-[#EFECE6] font-semibold text-[#1C1917]' => request()->routeIs('admin.reports.*'),
                                'font-medium text-[#57534E] hover:bg-[#EFECE6] hover:text-[#1C1917]' => ! request()->routeIs('admin.reports.*'),
                            ])
                            @if(request()->routeIs('admin.reports.*')) aria-current="page" @endif
                        >
                            <svg class="h-5 w-5 shrink-0" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4 15V9m4 6V5m4 10v-4m4 4V7" />
                            </svg>
                            <span class="truncate">Laporan</span>
                        </a>
                    </div>

                    <div class="mt-6 space-y-1">
                        <p class="px-3 pb-1 text-[11px] font-semibold uppercase tracking-wider text-[#57534E]">
                            Operasional
                        </p>

                        {{-- POS Kasir --}}
                        <a
                            href="{{ route('pos.index') }}"
                            @class([
                                'flex min-h-[44px] items-center gap-3 rounded-md px-3 py-2 text-sm transition-colors focus:outline-none focus:ring-2 focus:ring-[#9A3412] focus:ring-offset-1',
                                'border border-[#E2DDD5] bg-[#EFECE6] font-semibold text-[#1C1917]' => request()->routeIs('pos.*'),
                                'font-medium text-[#57534E] hover:bg-[#EFECE6] hover:text-[#1C1917]' => ! request()->routeIs('pos.*'),
                            ])
                            @if(request()->routeIs('pos.*')) aria-current="page" @endif
                        >
                            <svg class="h-5 w-5 shrink-0" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true">
                                <rect x="3" y="4" width="14" height="10" rx="1.5" />
                                <path stroke-linecap="round" d="M7 17h6M10 14v3" />
                            </svg>
                            <span class="truncate">POS Kasir</span>
                        </a>

                        <a
                            href="{{ route('orders.queue') }}"
                            @class([
                                'flex min-h-[44px] items-center gap-3 rounded-md px-3 py-2 text-sm transition-colors focus:outline-none focus:ring-2 focus:ring-[#9A3412] focus:ring-offset-1',
                                'border border-[#E2DDD5] bg-[#EFECE6] font-semibold text-[#1C1917]' => request()->routeIs('orders.queue'),
                                'font-medium text-[#57534E] hover:bg-[#EFECE6] hover:text-[#1C1917]' => ! request()->routeIs('orders.queue'),
                            ])
                            @if(request()->routeIs('orders.queue')) aria-current="page" @endif
                        >
                            <svg class="h-5 w-5 shrink-0" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 7h8v5a4 4 0 01-8 0V7zm8 1h1.5a2 2 0 010 4H13" />
                            </svg>
                            <span class="truncate">Antrean Barista</span>
                        </a>

                        {{-- Pengaturan Kafe --}}
                        <a
                            href="{{ route('admin.settings.edit') }}"
                            @class([
                                'flex min-h-[44px] items-center gap-3 rounded-md px-3 py-2 text-sm transition-colors focus:outline-none focus:ring-2 focus:ring-[#9A3412] focus:ring-offset-1',
                                'border border-[#E2DDD5] bg-[#EFECE6] font-semibold text-[#1C1917]' => request()->routeIs('admin.settings.*'),
                                'font-medium text-[#57534E] hover:bg-[#EFECE6] hover:text-[#1C1917]' => ! request()->routeIs('admin.settings.*'),
                            ])
                            @if(request()->routeIs('admin.settings.*')) aria-current="page" @endif
                        >
                            <svg class="h-5 w-5 shrink-0" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true">
                                <circle cx="10" cy="10" r="2.5" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M10 3.5v1.5m0 10v1.5M3.5 10H5m10 0h1.5M5.4 5.4l1.1 1.1m7 7l1.1 1.1M14.6 5.4l-1.1 1.1m-7 7l-1.1 1.1" />
                            </svg>
                            <span class="truncate">Pengaturan Kafe</span>
                        </a>
                    </div>
                </nav>

                {{-- User Info & Logout --}}
                <div class="border-t border-[#E2DDD5] p-3">
                    <div class="mb-2 px-2">
                        <p class="truncate text-sm font-semibold text-[#1C1917]">{{ auth()->user()->name }}</p>
                        <p class="truncate text-xs text-[#57534E]">{{ auth()->user()->email }}</p>
                    </div>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button
                            type="submit"
                            class="flex min-h-[44px] w-full items-center justify-center rounded-md border border-[#E2DDD5] bg-white px-3 py-2 text-sm font-semibold text-[#1C1917] transition-colors hover:bg-[#EFECE6] focus:outline-none focus:ring-2 focus:ring-[#9A3412] focus:ring-offset-2"
                        >
                            Keluar
                        </button>
                    </form>
                </div>
            </aside>

            {{-- Main Content Area --}}
            <div class="flex min-w-0 flex-1 flex-col">
                {{-- Top Header Bar --}}
                <header class="sticky top-0 z-20 flex min-h-[64px] items-center justify-between gap-4 border-b border-[#E2DDD5] bg-white px-4 py-3 sm:px-6">
                    <div class="flex min-w-0 items-center gap-3">
                        <button
                            type="button"
                            x-ref="openSidebarBtn"
                            @click="openSidebar()"
                            :aria-expanded="sidebarOpen.toString()"
                            class="inline-flex min-h-[44px] items-center gap-2 rounded-md border border-[#E2DDD5] bg-white px-3 py-2 text-sm font-semibold text-[#1C1917] transition-colors hover:bg-[#EFECE6] focus:outline-none focus:ring-2 focus:ring-[#9A3412] focus:ring-offset-2 lg:hidden"
                        >
                            <svg class="h-5 w-5 shrink-0" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.75" aria-hidden="true">
                                <path stroke-linecap="round" d="M3 5h14M3 10h14M3 15h14" />
                            </svg>
                            <span>Menu</span>
                        </button>
                        <h1 class="truncate text-lg font-bold text-[#1C1917] sm:text-xl">
                            {{ $title }}
                        </h1>
                    </div>

                    @isset($actions)
                        <div class="flex shrink-0 items-center gap-2">
                            {{ $actions }}
                        </div>
                    @endisset
                </header>

                {{-- Page Content --}}
                <main class="flex-1 px-4 py-6 sm:px-6 lg:px-8">
                    <div class="mx-auto max-w-6xl">
                        {{ $slot }}
                    </div>
                </main>
            </div>
        </div>
    </body>
</html>
