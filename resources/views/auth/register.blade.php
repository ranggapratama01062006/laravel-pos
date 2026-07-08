<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <title>Daftar Kasir Laravel</title>
    <link rel="stylesheet" href="{{ asset('css/login.css') }}" />
</head>
<body>
    <div class="login-shell">
        <section class="login-card">
            <div class="login-aside">
                <div class="login-brand">
                    <span>Kasir</span>
                    <strong>Laravel</strong>
                </div>
                <h1>Buat Akun Baru</h1>
                <p>Daftar untuk mulai menggunakan aplikasi POS dengan fitur lengkap dan modern.</p>
            </div>
            <form class="login-form" method="POST" action="{{ route('register.submit') }}">
                @csrf
                <div class="field-group">
                    <label for="name">Nama Lengkap</label>
                    <input id="name" name="name" type="text" value="{{ old('name') }}" required autocomplete="name" autofocus />
                    @error('name')
                        <p class="field-error">{{ $message }}</p>
                    @enderror
                </div>
                <div class="field-group">
                    <label for="email">Email</label>
                    <input id="email" name="email" type="email" value="{{ old('email') }}" required autocomplete="email" />
                    @error('email')
                        <p class="field-error">{{ $message }}</p>
                    @enderror
                </div>
                <div class="field-group">
                    <label for="password">Kata Sandi</label>
                    <input id="password" name="password" type="password" required autocomplete="new-password" />
                    @error('password')
                        <p class="field-error">{{ $message }}</p>
                    @enderror
                </div>
                <div class="field-group">
                    <label for="password_confirmation">Konfirmasi Kata Sandi</label>
                    <input id="password_confirmation" name="password_confirmation" type="password" required autocomplete="new-password" />
                </div>
                <button type="submit" class="btn-submit">Daftar</button>
                @if ($errors->any())
                    <div class="notice">{{ $errors->first() }}</div>
                @endif
                <p class="auth-switch">Sudah punya akun? <a href="{{ route('login') }}">Masuk sekarang</a></p>
            </form>
        </section>
    </div>
</body>
</html>
