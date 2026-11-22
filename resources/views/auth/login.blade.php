<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Login · CaféNusa</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
</head>
<body>
<div class="auth-wrap">
    <div class="auth-card">
        <div class="auth-brand">
            <div class="sidebar__logo">☕</div>
            <div>
                <p class="sidebar__title" style="color:var(--text-main);">CaféNusa</p>
                <p class="sidebar__subtitle" style="color:var(--text-muted);">Manajemen Kedai Kopi</p>
            </div>
        </div>

        @if(session('success'))
            <div class="alert alert-success">
                <span>{{ session('success') }}</span>
                <button type="button" class="alert__close" data-dismiss>&times;</button>
            </div>
        @endif

        @if($errors->any())
            <div class="alert alert-error">
                <span>{{ $errors->first() }}</span>
                <button type="button" class="alert__close" data-dismiss>&times;</button>
            </div>
        @endif

        <h2 style="font-size:20px; margin:0 0 4px;">Masuk ke akun Anda</h2>
        <p style="font-size:13px; color:var(--text-muted); margin:0 0 20px;">Silakan login untuk mengelola data kedai.</p>

        <form action="{{ route('login.post') }}" method="POST">
            @csrf

            <div class="field">
                <label class="label" for="email">Email <span class="req">*</span></label>
                <input class="input @error('email') is-error @enderror" type="email" id="email" name="email"
                       value="{{ old('email') }}" required autofocus placeholder="admin@cafenusa.test">
            </div>

            <div class="field">
                <label class="label" for="password">Password <span class="req">*</span></label>
                <input class="input @error('password') is-error @enderror" type="password" id="password" name="password"
                       required placeholder="••••••••">
            </div>

            <div class="field" style="display:flex; align-items:center; gap:8px;">
                <input type="checkbox" id="remember" name="remember" value="1" style="accent-color:var(--primary-accent);">
                <label for="remember" style="margin:0; font-weight:500;">Ingat saya</label>
            </div>

            <button type="submit" class="btn btn-primary" style="width:100%;">Login</button>
        </form>
    </div>
</div>

<script src="{{ asset('js/app.js') }}"></script>
</body>
</html>
