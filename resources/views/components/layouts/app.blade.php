@php
    $store = \App\Models\StoreSetting::current();
@endphp
<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? $store->store_name . ' - Jual Beli Part PC, Rakit & Servis' }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700" rel="stylesheet" />
    <script>
        (function () {
            try {
                var saved = localStorage.getItem('tokoku_theme');
                if (saved === 'dark') {
                    document.documentElement.classList.add('dark');
                } else if (saved === 'light') {
                    document.documentElement.classList.remove('dark');
                }
            } catch (e) {}
        })();
    </script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="min-h-screen flex flex-col antialiased">
    <a href="#konten-utama" class="sr-only focus:not-sr-only focus:fixed focus:top-3 focus:left-3 focus:z-50 btn-accent px-4 py-2.5 rounded-lg text-sm">
        Lewati ke konten utama
    </a>

    <header
        x-data="{
            mobileOpen: false,
            isDark: document.documentElement.classList.contains('dark'),
            toggleTheme() {
                this.isDark = !this.isDark;
                document.documentElement.classList.toggle('dark', this.isDark);
                try { localStorage.setItem('tokoku_theme', this.isDark ? 'dark' : 'light'); } catch (e) {}
            }
        }"
        @keydown.escape.window="mobileOpen = false"
        class="surface-card border-x-0 border-t-0 sticky top-0 z-40"
    >
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between gap-4 h-16 sm:h-[68px]">
                <a href="{{ route('home') }}" class="flex items-center gap-3 min-h-11 pr-2">
                    @if($store->logo_path)
                        <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($store->logo_path) }}" alt="{{ $store->store_name }}" class="h-9 w-auto rounded-lg">
                    @else
                        <span class="w-10 h-10 rounded-xl btn-accent inline-flex items-center justify-center text-base font-bold shrink-0" aria-hidden="true">
                            {{ strtoupper(substr($store->store_name, 0, 1)) }}
                        </span>
                    @endif
                    <div>
                        <span class="block font-bold text-lg leading-tight tracking-tight">{{ $store->store_name }}</span>
                        <span class="hidden sm:block text-xs text-muted-ink leading-tight">Marketplace PC, Rakit &amp; Servis</span>
                    </div>
                </a>

                <nav aria-label="Menu Utama" class="hidden md:flex items-center gap-1.5">
                    <a
                        href="{{ route('home') }}"
                        class="min-h-11 inline-flex items-center gap-2 px-4 rounded-lg text-sm transition-colors {{ request()->routeIs('home') || request()->routeIs('product.show') ? 'btn-ghost-active' : 'text-muted-ink hover:text-[var(--text-primary)] hover:bg-[var(--bg-subtle)] font-medium' }}"
                    >
                        <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 9.5L12 4l9 5.5v9a1.5 1.5 0 01-1.5 1.5h-15A1.5 1.5 0 013 18.5v-9z" />
                        </svg>
                        <span>Belanja Barang</span>
                    </a>
                    <a
                        href="{{ route('pc-builder') }}"
                        class="min-h-11 inline-flex items-center gap-2 px-4 rounded-lg text-sm transition-colors {{ request()->routeIs('pc-builder') ? 'btn-ghost-active' : 'text-muted-ink hover:text-[var(--text-primary)] hover:bg-[var(--bg-subtle)] font-medium' }}"
                    >
                        <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z" />
                        </svg>
                        <span>Rakit PC</span>
                    </a>
                    <a
                        href="{{ route('service.index') }}"
                        class="min-h-11 inline-flex items-center gap-2 px-4 rounded-lg text-sm transition-colors {{ request()->routeIs('service.index') ? 'btn-ghost-active' : 'text-muted-ink hover:text-[var(--text-primary)] hover:bg-[var(--bg-subtle)] font-medium' }}"
                    >
                        <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M11.42 15.17L17.25 21A2.652 2.652 0 0021 17.25l-5.877-5.877M11.42 15.17l2.496-3.03c.317-.384.74-.626 1.208-.766M11.42 15.17l-4.655 5.653a2.548 2.548 0 11-3.586-3.586l6.837-5.63m5.108-.233c.55-.164 1.163-.188 1.743-.14a4.5 4.5 0 004.486-6.336l-3.276 3.277a3.004 3.004 0 01-2.25-2.25l3.276-3.276a4.5 4.5 0 00-6.336 4.486c.091 1.076-.071 2.264-.904 2.95l-.102.085" />
                        </svg>
                        <span>Servis &amp; Cek Resi</span>
                    </a>
                </nav>

                <div class="flex items-center gap-2">
                    <button
                        type="button"
                        @click="toggleTheme()"
                        :aria-label="isDark ? 'Ganti ke tampilan terang' : 'Ganti ke tampilan gelap'"
                        class="btn-secondary min-h-11 px-3.5 rounded-lg text-xs sm:text-sm inline-flex items-center gap-2 cursor-pointer"
                    >
                        <svg x-show="!isDark" class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 12.79A9 9 0 1111.21 3a7 7 0 009.79 9.79z" />
                        </svg>
                        <svg x-show="isDark" x-cloak class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v2m0 14v2m9-9h-2M5 12H3m15.364-6.364l-1.414 1.414M7.05 16.95l-1.414 1.414m12.728 0l-1.414-1.414M7.05 7.05L5.636 5.636M12 8a4 4 0 100 8 4 4 0 000-8z" />
                        </svg>
                        <span x-text="isDark ? 'Terang' : 'Gelap'">Gelap</span>
                    </button>

                    <button
                        type="button"
                        @click="mobileOpen = !mobileOpen"
                        :aria-expanded="mobileOpen.toString()"
                        aria-controls="menu-seluler"
                        class="md:hidden btn-secondary min-h-11 px-3.5 rounded-lg text-sm inline-flex items-center gap-2 cursor-pointer"
                    >
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <path x-show="!mobileOpen" stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
                            <path x-show="mobileOpen" x-cloak stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                        <span>Menu</span>
                    </button>
                </div>
            </div>

            <nav
                id="menu-seluler"
                x-show="mobileOpen"
                x-cloak
                aria-label="Navigasi Seluler"
                class="md:hidden border-t border-[var(--border-default)] py-3 space-y-1.5"
            >
                <a href="{{ route('home') }}" class="flex items-center min-h-11 px-3.5 rounded-lg text-sm {{ request()->routeIs('home') ? 'btn-ghost-active' : 'font-medium hover:bg-[var(--bg-subtle)]' }}">
                    Belanja Barang Baru &amp; Second
                </a>
                <a href="{{ route('pc-builder') }}" class="flex items-center min-h-11 px-3.5 rounded-lg text-sm {{ request()->routeIs('pc-builder') ? 'btn-ghost-active' : 'font-medium hover:bg-[var(--bg-subtle)]' }}">
                    Simulasi Rakit PC
                </a>
                <a href="{{ route('service.index') }}" class="flex items-center min-h-11 px-3.5 rounded-lg text-sm {{ request()->routeIs('service.index') ? 'btn-ghost-active' : 'font-medium hover:bg-[var(--bg-subtle)]' }}">
                    Servis PC &amp; Cek Resi
                </a>
                <div class="pt-2 mt-2 border-t border-[var(--border-default)] flex flex-wrap gap-2">
                    <a href="{{ url('/admin') }}" class="btn-secondary min-h-11 px-3.5 rounded-lg text-xs inline-flex items-center">Masuk Admin</a>
                    <a href="{{ url('/teknisi') }}" class="btn-secondary min-h-11 px-3.5 rounded-lg text-xs inline-flex items-center">Masuk Teknisi</a>
                </div>
            </nav>
        </div>
    </header>

    <main id="konten-utama" class="flex-1">
        {{ $slot }}
    </main>

    <footer class="surface-card border-x-0 border-b-0 mt-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 grid grid-cols-1 md:grid-cols-12 gap-8">
            <div class="md:col-span-6 space-y-3">
                <p class="font-bold text-lg">{{ $store->store_name }}</p>
                <p class="text-sm text-muted-ink leading-relaxed max-w-lg">
                    Toko komponen komputer baru &amp; second bergaransi toko, dilengkapi layanan perawatan suhu, perbaikan perangkat keras, serta jasa perakitan PC rapi sesuai kebutuhan kerja maupun hiburan Anda.
                </p>
                @if($store->is_placeholder_data)
                    <p class="text-xs text-muted-ink pt-1">
                        Catatan pemilik: Informasi alamat &amp; nomor WhatsApp masih berupa data awal.
                        <a href="{{ url('/admin/manage-store-settings') }}" class="underline font-semibold text-[var(--text-primary)]">Atur profil toko di Panel Admin</a>.
                    </p>
                @endif
            </div>

            <div class="md:col-span-3 space-y-2 text-sm">
                <p class="font-semibold">Informasi Toko</p>
                <p class="text-muted-ink">{{ $store->workshop_address }}</p>
                <p class="text-muted-ink">{{ $store->cod_areas }}</p>
                <p class="text-muted-ink">{{ $store->operating_hours }}</p>
            </div>

            <div class="md:col-span-3 space-y-2 text-sm">
                <p class="font-semibold">Akses Cepat</p>
                <div class="flex flex-col items-start gap-1">
                    <a href="{{ route('home') }}" class="text-muted-ink hover:text-[var(--text-primary)] py-1">Katalog Barang</a>
                    <a href="{{ route('pc-builder') }}" class="text-muted-ink hover:text-[var(--text-primary)] py-1">Simulasi Rakit PC</a>
                    <a href="{{ route('service.index') }}" class="text-muted-ink hover:text-[var(--text-primary)] py-1">Booking &amp; Cek Servis</a>
                    <div class="pt-2 flex flex-wrap gap-2">
                        <a href="{{ url('/admin') }}" class="btn-secondary min-h-11 px-3 rounded-lg text-xs inline-flex items-center">Admin</a>
                        <a href="{{ url('/teknisi') }}" class="btn-secondary min-h-11 px-3 rounded-lg text-xs inline-flex items-center">Teknisi</a>
                    </div>
                </div>
            </div>
        </div>
    </footer>

    @livewireScripts
</body>
</html>
