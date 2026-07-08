<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    @php($brandName = config('app.name', 'Kasir Laravel'))
    <title>Masuk - {{ $brandName }}</title>
    <link rel="stylesheet" href="{{ asset('css/login.css') }}" />
</head>
<body>
    <div class="login-shell">
        <section class="login-card">
            <div class="login-aside">
                <div class="login-brand">
                    <span>POS</span>
                    <strong>{{ $brandName }}</strong>
                </div>
                <h1>Masuk ke Dashboard POS</h1>
                <p>Kelola transaksi, produk, dan pelanggan dengan tampilan yang bersih dan cepat untuk bisnis Anda.</p>
            </div>
            <form class="login-form" method="POST" action="{{ route('login.submit') }}">
                @csrf
                <div class="form-header">
                    <h2>Selamat datang</h2>
                    <p>Silakan masuk untuk melanjutkan.</p>
                </div>
                <div class="field-group">
                    <label for="email">Email</label>
                    <input id="email" name="email" type="email" value="{{ old('email') }}" required autocomplete="email" autofocus />
                    @error('email')
                        <p class="field-error">{{ $message }}</p>
                    @enderror
                </div>
                <div class="field-group">
                    <label for="password">Kata Sandi</label>
                    <input id="password" name="password" type="password" required autocomplete="current-password" />
                    @error('password')
                        <p class="field-error">{{ $message }}</p>
                    @enderror
                </div>
                <div class="field-row">
                    <label class="checkbox-field">
                        <input type="checkbox" name="remember" />
                        <span>Ingat saya</span>
                    </label>
                </div>
                <button type="submit" class="btn-submit">Masuk</button>
                @if ($errors->any())
                    <div class="notice">{{ $errors->first() }}</div>
                @endif
                <p class="auth-switch">Belum punya akun? <a href="{{ route('register') }}">Daftar sekarang</a></p>
            </form>
        </section>
    </div>
</body>
</html>
