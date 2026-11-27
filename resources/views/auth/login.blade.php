<x-guest-layout :title="$cafeName">
    <div class="mb-6 border-b border-[#E2DDD5] pb-5">
        <p class="text-xs font-semibold uppercase tracking-wide text-[#57534E]">Sistem Kasir &amp; Manajemen</p>
        <h1 class="mt-1 text-2xl font-bold tracking-tight text-[#1C1917]">{{ $cafeName }}</h1>
        <p class="mt-1 text-sm text-[#57534E]">Masuk menggunakan akun staf untuk mulai bertugas.</p>
    </div>

    @if (session('status'))
        <div class="mb-4 rounded-md border border-[#15803D]/20 bg-[#DCFCE7] px-3.5 py-2.5 text-sm font-medium text-[#15803D]" role="status">
            {{ session('status') }}
        </div>
    @endif

    <form method="POST" action="{{ route('login') }}" class="space-y-4">
        @csrf

        <div>
            <label for="email" class="block text-sm font-semibold text-[#1C1917]">Email</label>
            <input
                id="email"
                name="email"
                type="email"
                value="{{ old('email') }}"
                required
                autofocus
                autocomplete="username"
                placeholder="nama@cafe.test"
                class="mt-1.5 block min-h-[44px] w-full rounded-md border border-[#E2DDD5] bg-white px-3.5 py-2 text-sm text-[#1C1917] placeholder-[#57534E]/60 focus:border-[#9A3412] focus:outline-none focus:ring-2 focus:ring-[#9A3412]/25"
            />
            @if ($errors->has('email'))
                <p class="mt-1.5 rounded-md bg-[#FEE2E2] px-2.5 py-1.5 text-xs font-medium text-[#B91C1C]" role="alert">
                    {{ $errors->first('email') }}
                </p>
            @endif
        </div>

        <div>
            <label for="password" class="block text-sm font-semibold text-[#1C1917]">Kata Sandi</label>
            <input
                id="password"
                name="password"
                type="password"
                required
                autocomplete="current-password"
                placeholder="Masukkan kata sandi"
                class="mt-1.5 block min-h-[44px] w-full rounded-md border border-[#E2DDD5] bg-white px-3.5 py-2 text-sm text-[#1C1917] placeholder-[#57534E]/60 focus:border-[#9A3412] focus:outline-none focus:ring-2 focus:ring-[#9A3412]/25"
            />
            @if ($errors->has('password'))
                <p class="mt-1.5 rounded-md bg-[#FEE2E2] px-2.5 py-1.5 text-xs font-medium text-[#B91C1C]" role="alert">
                    {{ $errors->first('password') }}
                </p>
            @endif
        </div>

        <div class="flex items-center justify-between pt-1">
            <label for="remember_me" class="inline-flex min-h-[44px] items-center gap-2 text-sm text-[#57534E]">
                <input
                    id="remember_me"
                    name="remember"
                    type="checkbox"
                    class="h-4 w-4 rounded border-[#E2DDD5] text-[#9A3412] focus:ring-2 focus:ring-[#9A3412] focus:ring-offset-1"
                />
                <span>Ingat saya</span>
            </label>
        </div>

        <button
            type="submit"
            class="inline-flex min-h-[44px] w-full items-center justify-center rounded-md bg-[#9A3412] px-4 py-2.5 text-sm font-semibold text-white transition-colors hover:bg-[#7C2D12] focus:outline-none focus:ring-2 focus:ring-[#9A3412] focus:ring-offset-2"
        >
            Masuk
        </button>
    </form>

    <div class="mt-6 border-t border-[#E2DDD5] pt-5">
        <p class="text-xs font-semibold uppercase tracking-wide text-[#57534E]">Akses Cepat Pengujian</p>
        <div class="mt-3 grid grid-cols-1 gap-2.5 sm:grid-cols-2">
            <button
                type="button"
                id="fill-demo-admin"
                class="inline-flex min-h-[44px] w-full items-center justify-center rounded-md border border-[#E2DDD5] bg-[#F7F5F0] px-3 py-2 text-xs font-semibold text-[#1C1917] transition-colors hover:bg-[#EFECE6] focus:outline-none focus:ring-2 focus:ring-[#9A3412] focus:ring-offset-2"
            >
                Isi Akun Demo Admin
            </button>
            <button
                type="button"
                id="fill-demo-kasir"
                class="inline-flex min-h-[44px] w-full items-center justify-center rounded-md border border-[#E2DDD5] bg-[#F7F5F0] px-3 py-2 text-xs font-semibold text-[#1C1917] transition-colors hover:bg-[#EFECE6] focus:outline-none focus:ring-2 focus:ring-[#9A3412] focus:ring-offset-2"
            >
                Isi Akun Demo Kasir
            </button>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var emailInput = document.getElementById('email');
            var passwordInput = document.getElementById('password');
            var adminBtn = document.getElementById('fill-demo-admin');
            var kasirBtn = document.getElementById('fill-demo-kasir');

            function fillDemoCredentials(email, password) {
                if (!emailInput || !passwordInput) {
                    return;
                }
                emailInput.value = email;
                passwordInput.value = password;
                emailInput.dispatchEvent(new Event('input', { bubbles: true }));
                passwordInput.dispatchEvent(new Event('input', { bubbles: true }));
            }

            if (adminBtn) {
                adminBtn.addEventListener('click', function () {
                    fillDemoCredentials('admin@cafe.test', 'password');
                });
            }

            if (kasirBtn) {
                kasirBtn.addEventListener('click', function () {
                    fillDemoCredentials('kasir@cafe.test', 'password');
                });
            }
        });
    </script>
</x-guest-layout>
