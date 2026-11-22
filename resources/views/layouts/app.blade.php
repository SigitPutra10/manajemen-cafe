<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') · CaféNusa</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
</head>
<body>
<div class="layout">

    <!-- ===== Sidebar (desktop) ===== -->
    <aside class="sidebar">
        <div class="sidebar__brand">
            <div class="sidebar__logo">☕</div>
            <div>
                <p class="sidebar__title">CaféNusa</p>
                <p class="sidebar__subtitle">Manajemen Kedai Kopi</p>
            </div>
        </div>

        <p class="sidebar__label">Menu Utama</p>
        <nav class="sidebar__nav">
            <a href="{{ route('dashboard') }}" class="sidebar__link {{ request()->routeIs('dashboard') ? 'is-active' : '' }}">
                <span>📊</span><span>Dashboard</span>
            </a>
            <a href="{{ route('pemesanan.index') }}" class="sidebar__link {{ request()->routeIs('pemesanan.*') ? 'is-active' : '' }}">
                <span>🧺</span><span>Pemesanan</span>
            </a>
            <a href="{{ route('menu.index') }}" class="sidebar__link {{ request()->routeIs('menu.*') ? 'is-active' : '' }}">
                <span>🍽️</span><span>Data Menu</span>
            </a>
            <a href="{{ route('kategori.index') }}" class="sidebar__link {{ request()->routeIs('kategori.*') ? 'is-active' : '' }}">
                <span>🏷️</span><span>Kategori Menu</span>
            </a>
            <a href="{{ route('transaksi.index') }}" class="sidebar__link {{ request()->routeIs('transaksi.*') ? 'is-active' : '' }}">
                <span>🧾</span><span>Data Transaksi</span>
            </a>
        </nav>

        <div class="sidebar__footer">
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit" class="btn btn-outline btn-sm" style="width:100%; color:#F8FAFC; border-color:rgba(255,255,255,.25);">
                    🚪 Logout ({{ Auth::user()->name }})
                </button>
            </form>
            <p style="margin-top:10px;">Laravel · Vanilla JS</p>
        </div>
    </aside>

    <div class="layout__main">
        <!-- ===== Navbar (mobile) ===== -->
        <header class="navbar-mobile">
            <a href="{{ route('dashboard') }}" style="font-weight:700;">☕ CaféNusa</a>
            <nav style="display:flex; gap:14px; flex-wrap:wrap;">
                <a href="{{ route('dashboard') }}">Dashboard</a>
                <a href="{{ route('pemesanan.index') }}">Pemesanan</a>
                <a href="{{ route('menu.index') }}">Menu</a>
                <a href="{{ route('kategori.index') }}">Kategori</a>
                <a href="{{ route('transaksi.index') }}">Transaksi</a>
                <form action="{{ route('logout') }}" method="POST" style="margin:0;">
                    @csrf
                    <button type="submit" style="background:none; border:none; color:#F8FAFC; font-size:13px; cursor:pointer; padding:0; font-family:inherit;">Logout</button>
                </form>
            </nav>
        </header>

        <main class="content">
            <!-- ===== Notifikasi ===== -->
            @if(session('success'))
                <div class="alert alert-success">
                    <span>✅ {{ session('success') }}</span>
                    <button type="button" class="alert__close" data-dismiss aria-label="Tutup">&times;</button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-error">
                    <span>⚠️ {{ session('error') }}</span>
                    <button type="button" class="alert__close" data-dismiss aria-label="Tutup">&times;</button>
                </div>
            @endif

            @if($errors->any())
                <div class="alert alert-error">
                    <div>
                        <strong>Periksa kembali data berikut:</strong>
                        <ul style="margin:6px 0 0; padding-left:18px;">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                    <button type="button" class="alert__close" data-dismiss aria-label="Tutup">&times;</button>
                </div>
            @endif

            @yield('content')
        </main>
    </div>
</div>

<script src="{{ asset('js/app.js') }}?v={{ substr(md5_file(public_path('js/app.js')) ?: '0', 0, 8) }}"></script>
@stack('scripts')
</body>
</html>
