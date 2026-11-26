@php
    $store = \App\Models\StoreSetting::current();
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Terlalu Banyak Permintaan (429) - {{ $store->store_name }}</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('icons/icon.svg') }}">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700|plus-jakarta-sans:700,800" rel="stylesheet" />
    <script>
        try {
            if (localStorage.getItem('tokoku_theme') === 'dark') {
                document.documentElement.classList.add('dark');
            }
        } catch (e) {}
    </script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen flex flex-col items-center justify-center p-4 antialiased">
    <main class="surface-ink rounded-2xl max-w-xl w-full p-6 sm:p-8 space-y-5">
        <div class="flex items-center justify-between gap-3">
            <span class="px-3 py-1 rounded-md bg-[#FB923C] text-[#0F172A] font-display font-extrabold text-xs tracking-wide">
                KODE 429
            </span>
            <span class="text-xs ink-muted font-semibold">{{ $store->store_name }}</span>
        </div>

        <div class="space-y-2">
            <h1 class="font-display text-2xl sm:text-3xl font-extrabold tracking-tight">
                Permintaan Terlalu Cepat
            </h1>
            <p class="text-sm ink-muted leading-relaxed">
                Sistem kami mendeteksi terlalu banyak percobaan beruntun dari koneksi Anda. Silakan tunggu sekitar satu menit sebelum mencoba kembali.
            </p>
        </div>

        <div class="flex flex-wrap gap-2.5 pt-2">
            <a href="{{ url('/') }}" class="btn-ink-accent min-h-11 px-4 rounded-lg text-sm inline-flex items-center justify-center">
                Kembali ke Beranda Toko
            </a>
        </div>
    </main>
</body>
</html>
