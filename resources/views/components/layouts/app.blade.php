@php
    $store = \App\Models\StoreSetting::current();
    $logoUrl = $store->logo_path ? '/storage/' . ltrim($store->logo_path, '/') : null;
    $resolvedTitle = $title ?? ($store->store_name . ' - Jual Beli Part PC, Rakit & Servis');
    $resolvedDescription = $metaDescription ?? ($store->tagline ?: 'Toko komponen PC baru & second bergaransi toko, simulasi rakit PC interaktif, serta layanan servis komputer transparan.');
    $resolvedCanonical = $canonicalUrl ?? url()->current();
    $resolvedMetaType = $metaType ?? 'website';
    $resolvedMetaImage = !empty($metaImage)
        ? (str_starts_with($metaImage, 'http') ? $metaImage : url($metaImage))
        : ($logoUrl ? url($logoUrl) : asset('icons/icon-512.png'));
@endphp
<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <title>{{ $resolvedTitle }}</title>
    <meta name="description" content="{{ $resolvedDescription }}">
    <link rel="canonical" href="{{ $resolvedCanonical }}">
    <meta property="og:locale" content="id_ID">
    <meta property="og:site_name" content="{{ $store->store_name }}">
    <meta property="og:type" content="{{ $resolvedMetaType }}">
    <meta property="og:title" content="{{ $resolvedTitle }}">
    <meta property="og:description" content="{{ $resolvedDescription }}">
    <meta property="og:url" content="{{ $resolvedCanonical }}">
    <meta property="og:image" content="{{ $resolvedMetaImage }}">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $resolvedTitle }}">
    <meta name="twitter:description" content="{{ $resolvedDescription }}">
    <meta name="twitter:image" content="{{ $resolvedMetaImage }}">
    <link rel="manifest" href="{{ asset('manifest.webmanifest') }}">
    <link rel="icon" type="image/svg+xml" href="{{ asset('icons/icon.svg') }}">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('icons/apple-touch-icon.png') }}">
    <meta name="theme-color" content="#C2410C" media="(prefers-color-scheme: light)">
    <meta name="theme-color" content="#0B0F19" media="(prefers-color-scheme: dark)">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="{{ $store->store_name }}">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700|plus-jakarta-sans:700,800" rel="stylesheet" />
    <script>
        (function () {
            function applyStoredTheme() {
                try {
                    var saved = localStorage.getItem('tokoku_theme');
                    document.documentElement.classList.toggle('dark', saved === 'dark');
                } catch (e) {}
            }

            applyStoredTheme();
            document.addEventListener('livewire:navigated', applyStoredTheme);

            window.__tokokuInstallEvent = null;
            window.addEventListener('beforeinstallprompt', function (event) {
                event.preventDefault();
                window.__tokokuInstallEvent = event;
                window.dispatchEvent(new CustomEvent('tokoku-install-ready'));
            });

            if ('serviceWorker' in navigator) {
                window.addEventListener('load', function () {
                    navigator.serviceWorker.register('/sw.js').catch(function () {});
                });
            }

            document.addEventListener('alpine:init', function () {
                if (Alpine.store('tokoku')) return;

                var initialSaved = [];
                var initialCompare = [];
                try {
                    initialSaved = JSON.parse(localStorage.getItem('tokoku_saved_items') || '[]');
                    if (!Array.isArray(initialSaved)) initialSaved = [];
                    initialCompare = JSON.parse(localStorage.getItem('tokoku_compare_items') || '[]');
                    if (!Array.isArray(initialCompare)) initialCompare = [];
                } catch (e) {
                    initialSaved = [];
                    initialCompare = [];
                }

                Alpine.store('tokoku', {
                    isDark: document.documentElement.classList.contains('dark'),
                    canNativeInstall: Boolean(window.__tokokuInstallEvent),
                    isStandalone: window.matchMedia('(display-mode: standalone)').matches || window.navigator.standalone === true,
                    savedItems: initialSaved,
                    compareItems: initialCompare,
                    savedDrawerOpen: false,
                    compareModalOpen: false,
                    mobileSheetOpen: false,
                    installGuideOpen: false,
                    toasts: [],
                    nextToastId: 1,

                    syncTheme() {
                        this.isDark = document.documentElement.classList.contains('dark');
                    },

                    toggleTheme() {
                        this.isDark = !this.isDark;
                        document.documentElement.classList.toggle('dark', this.isDark);
                        try { localStorage.setItem('tokoku_theme', this.isDark ? 'dark' : 'light'); } catch (e) {}
                        this.notify(this.isDark ? 'Mode gelap aktif' : 'Mode terang aktif');
                    },

                    persistSaved() {
                        try { localStorage.setItem('tokoku_saved_items', JSON.stringify(this.savedItems)); } catch (e) {}
                    },

                    persistCompare() {
                        try { localStorage.setItem('tokoku_compare_items', JSON.stringify(this.compareItems)); } catch (e) {}
                    },

                    isSaved(id) {
                        return this.savedItems.some(function (item) { return item.id === id; });
                    },

                    toggleSaved(item) {
                        if (this.isSaved(item.id)) {
                            this.savedItems = this.savedItems.filter(function (x) { return x.id !== item.id; });
                            this.persistSaved();
                            this.notify('Dihapus dari daftar barang tersimpan');
                        } else {
                            this.savedItems = [item].concat(this.savedItems);
                            this.persistSaved();
                            this.notify('Barang disimpan ke daftar favorit');
                        }
                    },

                    removeSaved(id) {
                        this.savedItems = this.savedItems.filter(function (x) { return x.id !== id; });
                        this.persistSaved();
                        this.notify('Barang dihapus dari daftar tersimpan');
                    },

                    clearSaved() {
                        this.savedItems = [];
                        this.persistSaved();
                        this.notify('Semua barang tersimpan dikosongkan');
                    },

                    isCompared(id) {
                        return this.compareItems.some(function (item) { return item.id === id; });
                    },

                    toggleCompare(item) {
                        if (this.isCompared(item.id)) {
                            this.compareItems = this.compareItems.filter(function (x) { return x.id !== item.id; });
                            this.persistCompare();
                            this.notify('Dihapus dari perbandingan');
                            return;
                        }
                        if (this.compareItems.length >= 3) {
                            this.notify('Maksimal 3 barang dapat dibandingkan sekaligus');
                            return;
                        }
                        this.compareItems = this.compareItems.concat([item]);
                        this.persistCompare();
                        this.notify('Ditambahkan ke perbandingan (' + this.compareItems.length + '/3)');
                    },

                    removeCompare(id) {
                        this.compareItems = this.compareItems.filter(function (x) { return x.id !== id; });
                        this.persistCompare();
                        if (this.compareItems.length === 0) {
                            this.compareModalOpen = false;
                        }
                    },

                    clearCompare() {
                        this.compareItems = [];
                        this.compareModalOpen = false;
                        this.persistCompare();
                        this.notify('Daftar perbandingan dikosongkan');
                    },

                    lowestComparePrice() {
                        if (this.compareItems.length < 2) return null;
                        return Math.min.apply(null, this.compareItems.map(function (x) { return Number(x.priceNum || 0); }));
                    },

                    notify(message) {
                        var id = this.nextToastId++;
                        this.toasts.push({ id: id, message: message });
                        var self = this;
                        setTimeout(function () {
                            self.toasts = self.toasts.filter(function (t) { return t.id !== id; });
                        }, 2600);
                    },

                    copyText(text, successMsg) {
                        var msg = successMsg || 'Berhasil disalin ke papan klip';
                        var self = this;
                        if (navigator.clipboard && navigator.clipboard.writeText) {
                            navigator.clipboard.writeText(text).then(function () {
                                self.notify(msg);
                            }).catch(function () {
                                self.fallbackCopy(text, msg);
                            });
                            return;
                        }
                        this.fallbackCopy(text, msg);
                    },

                    fallbackCopy(text, msg) {
                        try {
                            var ta = document.createElement('textarea');
                            ta.value = text;
                            ta.setAttribute('readonly', '');
                            ta.style.position = 'fixed';
                            ta.style.opacity = '0';
                            document.body.appendChild(ta);
                            ta.select();
                            document.execCommand('copy');
                            document.body.removeChild(ta);
                            this.notify(msg);
                        } catch (e) {
                            this.notify('Salin manual: ' + text);
                        }
                    },

                    async shareLink(title, text, url) {
                        if (navigator.share) {
                            try {
                                await navigator.share({ title: title, text: text, url: url });
                                return;
                            } catch (e) {
                                if (e && e.name === 'AbortError') return;
                            }
                        }
                        this.copyText(url, 'Tautan halaman disalin, siap dibagikan');
                    },

                    async triggerInstall() {
                        if (window.__tokokuInstallEvent) {
                            window.__tokokuInstallEvent.prompt();
                            var outcome = await window.__tokokuInstallEvent.userChoice;
                            if (outcome && outcome.outcome === 'accepted') {
                                this.isStandalone = true;
                                this.notify('Tokoku terpasang di perangkat Anda');
                            }
                            window.__tokokuInstallEvent = null;
                            this.canNativeInstall = false;
                            return;
                        }
                        this.mobileSheetOpen = false;
                        this.installGuideOpen = true;
                    },

                    closeOverlays() {
                        this.savedDrawerOpen = false;
                        this.compareModalOpen = false;
                        this.mobileSheetOpen = false;
                        this.installGuideOpen = false;
                    }
                });
            });

            document.addEventListener('livewire:navigated', function () {
                if (window.Alpine && Alpine.store('tokoku')) {
                    Alpine.store('tokoku').syncTheme();
                    Alpine.store('tokoku').closeOverlays();
                }
            });
        })();
    </script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body
    x-data
    @tokoku-install-ready.window="$store.tokoku.canNativeInstall = true"
    @keydown.escape.window="$store.tokoku.closeOverlays()"
    class="min-h-screen flex flex-col antialiased"
>
    <a href="#konten-utama" class="sr-only focus:not-sr-only focus:fixed focus:top-3 focus:left-3 focus:z-50 btn-accent px-4 py-2.5 rounded-lg text-sm">
        Lewati ke konten utama
    </a>

    <header class="surface-card border-x-0 border-t-0 sticky top-0 z-40">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between gap-2 sm:gap-3 h-14 sm:h-[68px]">
                <a href="{{ route('home') }}" wire:navigate class="flex items-center gap-2.5 sm:gap-3 min-h-11 pr-2">
                    @if($logoUrl)
                        <img src="{{ $logoUrl }}" alt="{{ $store->store_name }}" class="h-8 sm:h-9 w-auto rounded-lg">
                    @else
                        <span class="w-9 h-9 sm:w-10 sm:h-10 rounded-xl bg-[#0F172A] text-[#F8FAFC] border border-[#EA580C] inline-flex items-center justify-center font-display text-sm sm:text-base font-extrabold shrink-0" aria-hidden="true">
                            {{ strtoupper(substr($store->store_name, 0, 1)) }}
                        </span>
                    @endif
                    <div>
                        <span class="block font-display font-extrabold text-base sm:text-lg leading-tight tracking-tight">{{ $store->store_name }}</span>
                        <span class="hidden sm:block text-xs text-muted-ink leading-tight">Hardware Store, Rakit PC &amp; Bengkel</span>
                    </div>
                </a>

                <nav aria-label="Menu Utama" class="hidden md:flex items-center gap-1.5">
                    <a
                        href="{{ route('home') }}"
                        wire:navigate
                        @if(request()->routeIs('home') || request()->routeIs('product.show')) aria-current="page" @endif
                        class="min-h-11 inline-flex items-center gap-2 px-4 rounded-lg text-sm transition-colors {{ request()->routeIs('home') || request()->routeIs('product.show') ? 'btn-ghost-active' : 'text-muted-ink hover:text-[var(--text-primary)] hover:bg-[var(--bg-subtle)] font-medium' }}"
                    >
                        <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 9.5L12 4l9 5.5v9a1.5 1.5 0 01-1.5 1.5h-15A1.5 1.5 0 013 18.5v-9z" />
                        </svg>
                        <span>Belanja Barang</span>
                    </a>
                    <a
                        href="{{ route('pc-builder') }}"
                        wire:navigate
                        @if(request()->routeIs('pc-builder')) aria-current="page" @endif
                        class="min-h-11 inline-flex items-center gap-2 px-4 rounded-lg text-sm transition-colors {{ request()->routeIs('pc-builder') ? 'btn-ghost-active' : 'text-muted-ink hover:text-[var(--text-primary)] hover:bg-[var(--bg-subtle)] font-medium' }}"
                    >
                        <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z" />
                        </svg>
                        <span>Rakit PC</span>
                    </a>
                    <a
                        href="{{ route('service.index') }}"
                        wire:navigate
                        @if(request()->routeIs('service.index')) aria-current="page" @endif
                        class="min-h-11 inline-flex items-center gap-2 px-4 rounded-lg text-sm transition-colors {{ request()->routeIs('service.index') ? 'btn-ghost-active' : 'text-muted-ink hover:text-[var(--text-primary)] hover:bg-[var(--bg-subtle)] font-medium' }}"
                    >
                        <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M11.42 15.17L17.25 21A2.652 2.652 0 0021 17.25l-5.877-5.877M11.42 15.17l2.496-3.03c.317-.384.74-.626 1.208-.766M11.42 15.17l-4.655 5.653a2.548 2.548 0 11-3.586-3.586l6.837-5.63m5.108-.233c.55-.164 1.163-.188 1.743-.14a4.5 4.5 0 004.486-6.336l-3.276 3.277a3.004 3.004 0 01-2.25-2.25l3.276-3.276a4.5 4.5 0 00-6.336 4.486c.091 1.076-.071 2.264-.904 2.95l-.102.085" />
                        </svg>
                        <span>Servis &amp; Cek Resi</span>
                    </a>
                </nav>

                <div class="flex items-center gap-1.5 sm:gap-2">
                    <button
                        type="button"
                        @click="$store.tokoku.savedDrawerOpen = true"
                        aria-label="Buka daftar barang tersimpan"
                        class="btn-secondary min-h-11 px-3 rounded-lg text-xs sm:text-sm inline-flex items-center gap-1.5 cursor-pointer"
                    >
                        <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z" />
                        </svg>
                        <span class="hidden lg:inline">Tersimpan</span>
                        <span
                            x-show="$store.tokoku.savedItems.length > 0"
                            x-cloak
                            x-text="$store.tokoku.savedItems.length"
                            class="badge-accent-soft px-1.5 py-0.5 rounded text-[11px] font-bold tabular-nums leading-none"
                        ></span>
                    </button>

                    <button
                        type="button"
                        x-show="!$store.tokoku.isStandalone"
                        @click="$store.tokoku.triggerInstall()"
                        class="hidden sm:inline-flex btn-ghost-active min-h-11 px-3.5 rounded-lg text-xs sm:text-sm items-center gap-1.5 cursor-pointer"
                    >
                        <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 16V4m0 12l-4-4m4 4l4-4M4 20h16" />
                        </svg>
                        <span>Pasang di HP</span>
                    </button>

                    <button
                        type="button"
                        @click="$store.tokoku.toggleTheme()"
                        :aria-label="$store.tokoku.isDark ? 'Ganti ke tampilan terang' : 'Ganti ke tampilan gelap'"
                        class="btn-secondary min-h-11 px-3 rounded-lg text-xs sm:text-sm inline-flex items-center gap-1.5 cursor-pointer"
                    >
                        <svg x-show="!$store.tokoku.isDark" class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 12.79A9 9 0 1111.21 3a7 7 0 009.79 9.79z" />
                        </svg>
                        <svg x-show="$store.tokoku.isDark" x-cloak class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v2m0 14v2m9-9h-2M5 12H3m15.364-6.364l-1.414 1.414M7.05 16.95l-1.414 1.414m12.728 0l-1.414-1.414M7.05 7.05L5.636 5.636M12 8a4 4 0 100 8 4 4 0 000-8z" />
                        </svg>
                        <span class="hidden sm:inline" x-text="$store.tokoku.isDark ? 'Terang' : 'Gelap'">Gelap</span>
                    </button>
                </div>
            </div>
        </div>
    </header>

    <main id="konten-utama" class="flex-1">
        {{ $slot }}
    </main>

    <footer class="surface-ink border-x-0 border-b-0 mt-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 grid grid-cols-1 md:grid-cols-12 gap-8">
            <div class="md:col-span-6 space-y-3">
                <div class="flex items-center gap-2.5">
                    <span class="w-8 h-8 rounded-lg bg-[#EA580C] text-white font-display font-extrabold text-sm inline-flex items-center justify-center" aria-hidden="true">
                        {{ strtoupper(substr($store->store_name, 0, 1)) }}
                    </span>
                    <p class="font-display font-bold text-lg">{{ $store->store_name }}</p>
                </div>
                <p class="text-sm ink-muted leading-relaxed max-w-lg">
                    Toko komponen komputer baru &amp; second bergaransi toko, dilengkapi layanan perawatan suhu, perbaikan perangkat keras, serta jasa perakitan PC rapi sesuai kebutuhan kerja maupun hiburan Anda.
                </p>
                @if($store->is_placeholder_data)
                    <p class="text-xs ink-muted pt-1">
                        Catatan pemilik: Alamat &amp; nomor WhatsApp masih berupa data awal.
                        <a href="{{ url('/admin/manage-store-settings') }}" class="underline font-semibold text-[#FDBA74]">Atur profil toko di Panel Admin</a>.
                    </p>
                @endif
            </div>

            <div class="md:col-span-3 space-y-2 text-sm">
                <p class="font-display font-bold">Workshop &amp; Jam Buka</p>
                <p class="ink-muted">{{ $store->workshop_address }}</p>
                <p class="ink-muted">{{ $store->cod_areas }}</p>
                <p class="ink-muted">{{ $store->operating_hours }}</p>
            </div>

            <div class="md:col-span-3 space-y-2 text-sm">
                <p class="font-display font-bold">Akses Cepat</p>
                <div class="flex flex-col items-start gap-1">
                    <a href="{{ route('home') }}" wire:navigate class="ink-muted hover:text-white py-1">Katalog Barang</a>
                    <a href="{{ route('pc-builder') }}" wire:navigate class="ink-muted hover:text-white py-1">Simulasi Rakit PC</a>
                    <a href="{{ route('service.index') }}" wire:navigate class="ink-muted hover:text-white py-1">Booking &amp; Cek Servis</a>
                    <div class="pt-2 flex flex-wrap gap-2">
                        <a href="{{ url('/admin') }}" class="btn-ink-outline min-h-11 px-3.5 rounded-lg text-xs inline-flex items-center">Admin</a>
                        <a href="{{ url('/teknisi') }}" class="btn-ink-outline min-h-11 px-3.5 rounded-lg text-xs inline-flex items-center">Teknisi</a>
                    </div>
                </div>
            </div>
        </div>
    </footer>

    @if(request()->routeIs('home') || request()->routeIs('product.show'))
    <div
        x-show="$store.tokoku.compareItems.length > 0 && !$store.tokoku.compareModalOpen"
        x-cloak
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 translate-y-4"
        x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 translate-y-0"
        x-transition:leave-end="opacity-0 translate-y-4"
        class="compare-float-bar fixed inset-x-0 md:inset-x-auto md:left-1/2 md:-translate-x-1/2 bottom-[calc(3.85rem+env(safe-area-inset-bottom,0px))] md:bottom-5 md:rounded-2xl z-40 px-3.5 sm:px-4 py-2.5 sm:py-3 md:min-w-[30rem] no-print"
    >
        <div class="flex items-center justify-between gap-2.5 sm:gap-3">
            <div class="flex items-center gap-2 min-w-0">
                <span class="px-2 py-1 rounded-md bg-[#FB923C] text-[#0F172A] font-display font-extrabold text-xs tabular-nums shrink-0" x-text="$store.tokoku.compareItems.length + '/3'"></span>
                <div class="min-w-0">
                    <p class="font-semibold text-xs sm:text-sm truncate">Bandingkan Barang</p>
                    <p class="text-[11px] ink-muted truncate" x-text="$store.tokoku.compareItems.map(i => i.name).join(' vs ')"></p>
                </div>
            </div>
            <div class="flex items-center gap-1.5 sm:gap-2 shrink-0">
                <button type="button" @click="$store.tokoku.compareModalOpen = true" class="btn-ink-accent min-h-11 px-3 sm:px-3.5 rounded-lg text-xs sm:text-sm cursor-pointer">
                    Lihat
                </button>
                <button type="button" @click="$store.tokoku.clearCompare()" class="btn-ink-outline min-h-11 px-2.5 sm:px-3 rounded-lg text-xs cursor-pointer">
                    Reset
                </button>
            </div>
        </div>
    </div>
    @endif

    <div
        x-show="$store.tokoku.compareModalOpen"
        x-cloak
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        role="dialog"
        aria-modal="true"
        aria-labelledby="judul-bandingkan-barang"
        class="fixed inset-0 z-50 flex items-end sm:items-center justify-center p-0 sm:p-5 bg-slate-950/75 no-print"
        @click.self="$store.tokoku.compareModalOpen = false"
    >
        <div class="surface-card rounded-t-2xl sm:rounded-2xl w-full max-w-5xl max-h-[90dvh] flex flex-col overflow-hidden">
            <div class="p-4 sm:p-5 border-b border-[var(--border-default)] flex items-center justify-between gap-3">
                <div>
                    <h2 id="judul-bandingkan-barang" class="font-display font-bold text-lg">Perbandingan Barang Berdampingan</h2>
                    <p class="text-xs text-muted-ink">Lihat selisih harga, garansi, kondisi fisik, dan spesifikasi untuk menentukan pilihan paling pas.</p>
                </div>
                <div class="flex items-center gap-2">
                    <button type="button" @click="$store.tokoku.clearCompare()" class="btn-secondary min-h-11 px-3.5 rounded-lg text-xs sm:text-sm cursor-pointer">
                        Kosongkan
                    </button>
                    <button type="button" @click="$store.tokoku.compareModalOpen = false" class="btn-accent min-h-11 px-4 rounded-lg text-xs sm:text-sm cursor-pointer">
                        Tutup (Esc)
                    </button>
                </div>
            </div>

            <div class="p-4 sm:p-5 overflow-auto flex-1">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <template x-for="item in $store.tokoku.compareItems" :key="item.id">
                        <div class="surface-subtle rounded-xl p-4 flex flex-col justify-between gap-4">
                            <div class="space-y-3">
                                <div class="relative aspect-[4/3] rounded-lg overflow-hidden bg-[#0F172A]">
                                    <img :src="item.image" alt="" x-show="item.image" class="w-full h-full object-cover">
                                    <button
                                        type="button"
                                        @click="$store.tokoku.removeCompare(item.id)"
                                        class="btn-secondary min-h-9 px-2.5 rounded-lg text-xs absolute top-2 right-2 cursor-pointer"
                                    >
                                        Lepas
                                    </button>
                                </div>

                                <div>
                                    <span
                                        x-show="$store.tokoku.lowestComparePrice() !== null && Number(item.priceNum) === $store.tokoku.lowestComparePrice()"
                                        class="badge-ok inline-block px-2 py-0.5 rounded-md text-[11px] font-semibold mb-1.5"
                                    >
                                        Harga Paling Hemat
                                    </span>
                                    <h3 class="font-display font-bold text-base leading-snug" x-text="item.name"></h3>
                                    <p class="font-display font-extrabold text-xl tabular-nums mt-1" x-text="item.price"></p>
                                </div>

                                <dl class="space-y-2 text-xs border-t border-[var(--border-default)] pt-3">
                                    <div class="flex justify-between gap-2">
                                        <dt class="text-muted-ink">Kondisi</dt>
                                        <dd class="font-semibold text-right" x-text="item.condition"></dd>
                                    </div>
                                    <div class="flex justify-between gap-2">
                                        <dt class="text-muted-ink">Negosiasi</dt>
                                        <dd class="font-semibold text-right" x-text="item.negotiable ? 'Bisa Nego' : 'Harga Nett'"></dd>
                                    </div>
                                    <div class="flex justify-between gap-2">
                                        <dt class="text-muted-ink">Garansi</dt>
                                        <dd class="font-semibold text-right" x-text="item.warranty || 'Cek di toko'"></dd>
                                    </div>
                                    <div class="pt-1 border-t border-[var(--border-default)]">
                                        <dt class="text-muted-ink mb-1">Spesifikasi Utama</dt>
                                        <dd class="font-medium" x-text="item.specs || 'Lihat rincian lengkap pada halaman detail'"></dd>
                                    </div>
                                </dl>
                            </div>

                            <div class="grid grid-cols-2 gap-2 pt-2">
                                <a :href="item.url" wire:navigate class="btn-secondary min-h-11 px-3 rounded-lg text-xs inline-flex items-center justify-center">
                                    Detail
                                </a>
                                <a :href="item.waUrl" target="_blank" rel="noopener noreferrer" class="btn-accent min-h-11 px-3 rounded-lg text-xs inline-flex items-center justify-center">
                                    Chat WA
                                </a>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </div>
    </div>

    <nav aria-label="Navigasi Bawah Seluler" class="bottom-nav-bar md:hidden fixed bottom-0 inset-x-0 z-40">
        <div class="grid grid-cols-4 gap-1 px-2 py-1.5">
            <a
                href="{{ route('home') }}"
                wire:navigate
                @if(request()->routeIs('home') || request()->routeIs('product.show')) aria-current="page" @endif
                class="min-h-12 rounded-lg flex flex-col items-center justify-center gap-0.5 text-[11px] {{ request()->routeIs('home') || request()->routeIs('product.show') ? 'btn-ghost-active' : 'text-muted-ink font-medium' }}"
            >
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 9.5L12 4l9 5.5v9a1.5 1.5 0 01-1.5 1.5h-15A1.5 1.5 0 013 18.5v-9z" />
                </svg>
                <span>Belanja</span>
            </a>

            <a
                href="{{ route('pc-builder') }}"
                wire:navigate
                @if(request()->routeIs('pc-builder')) aria-current="page" @endif
                class="min-h-12 rounded-lg flex flex-col items-center justify-center gap-0.5 text-[11px] {{ request()->routeIs('pc-builder') ? 'btn-ghost-active' : 'text-muted-ink font-medium' }}"
            >
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z" />
                </svg>
                <span>Rakit PC</span>
            </a>

            <a
                href="{{ route('service.index') }}"
                wire:navigate
                @if(request()->routeIs('service.index')) aria-current="page" @endif
                class="min-h-12 rounded-lg flex flex-col items-center justify-center gap-0.5 text-[11px] {{ request()->routeIs('service.index') ? 'btn-ghost-active' : 'text-muted-ink font-medium' }}"
            >
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M11.42 15.17L17.25 21A2.652 2.652 0 0021 17.25l-5.877-5.877M11.42 15.17l2.496-3.03c.317-.384.74-.626 1.208-.766M11.42 15.17l-4.655 5.653a2.548 2.548 0 11-3.586-3.586l6.837-5.63m5.108-.233c.55-.164 1.163-.188 1.743-.14a4.5 4.5 0 004.486-6.336l-3.276 3.277a3.004 3.004 0 01-2.25-2.25l3.276-3.276a4.5 4.5 0 00-6.336 4.486c.091 1.076-.071 2.264-.904 2.95l-.102.085" />
                </svg>
                <span>Servis</span>
            </a>

            <button
                type="button"
                @click="$store.tokoku.mobileSheetOpen = !$store.tokoku.mobileSheetOpen"
                :aria-expanded="$store.tokoku.mobileSheetOpen.toString()"
                aria-controls="sheet-menu-seluler"
                class="min-h-12 rounded-lg flex flex-col items-center justify-center gap-0.5 text-[11px] cursor-pointer"
                :class="$store.tokoku.mobileSheetOpen ? 'btn-ghost-active' : 'text-muted-ink font-medium'"
            >
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
                </svg>
                <span>Menu</span>
            </button>
        </div>
    </nav>

    <div
        x-show="$store.tokoku.savedDrawerOpen"
        x-cloak
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        role="dialog"
        aria-modal="true"
        aria-labelledby="judul-barang-tersimpan"
        class="fixed inset-0 z-50 flex justify-end bg-slate-950/60 no-print"
        @click.self="$store.tokoku.savedDrawerOpen = false"
    >
        <div
            x-show="$store.tokoku.savedDrawerOpen"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="translate-x-full"
            x-transition:enter-end="translate-x-0"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="translate-x-0"
            x-transition:leave-end="translate-x-full"
            class="surface-card w-full max-w-md h-[100dvh] flex flex-col overflow-hidden"
        >
            <div class="p-4 sm:p-5 border-b border-[var(--border-default)] flex items-center justify-between gap-3">
                <div>
                    <h2 id="judul-barang-tersimpan" class="font-bold text-base sm:text-lg">Barang Tersimpan</h2>
                    <p class="text-xs text-muted-ink">Simpan unit incaran Anda di perangkat ini untuk dibandingkan sebelum chat WA.</p>
                </div>
                <button type="button" @click="$store.tokoku.savedDrawerOpen = false" class="btn-secondary min-h-11 px-3.5 rounded-lg text-sm cursor-pointer">
                    Tutup
                </button>
            </div>

            <div class="p-4 overflow-y-auto flex-1 space-y-3">
                <template x-if="$store.tokoku.savedItems.length === 0">
                    <div class="surface-subtle rounded-xl p-6 text-center space-y-2">
                        <p class="font-semibold text-sm">Belum ada barang yang disimpan</p>
                        <p class="text-xs text-muted-ink">Ketuk ikon penanda (Simpan) pada kartu produk di etalase untuk menyimpannya ke daftar ini.</p>
                    </div>
                </template>

                <template x-for="item in $store.tokoku.savedItems" :key="item.id">
                    <div class="surface-subtle rounded-xl p-3.5 flex items-center justify-between gap-3">
                        <div class="flex items-center gap-3 min-w-0">
                            <img :src="item.image" alt="" x-show="item.image" class="w-14 h-12 rounded-lg object-cover shrink-0 border border-[var(--border-default)]">
                            <div class="min-w-0">
                                <a :href="item.url" wire:navigate class="font-semibold text-sm line-clamp-1 hover:underline" x-text="item.name"></a>
                                <p class="font-bold text-sm tabular-nums" x-text="item.price"></p>
                                <p class="text-xs text-muted-ink" x-text="item.condition"></p>
                            </div>
                        </div>
                        <button
                            type="button"
                            @click="$store.tokoku.removeSaved(item.id)"
                            class="btn-secondary min-h-11 px-3 rounded-lg text-xs cursor-pointer shrink-0"
                        >
                            Hapus
                        </button>
                    </div>
                </template>
            </div>

            <div x-show="$store.tokoku.savedItems.length > 0" class="p-4 border-t border-[var(--border-default)] flex justify-between gap-2">
                <button type="button" @click="$store.tokoku.clearSaved()" class="btn-secondary min-h-11 px-4 rounded-lg text-xs sm:text-sm cursor-pointer">
                    Kosongkan Semua
                </button>
                <button type="button" @click="$store.tokoku.savedDrawerOpen = false" class="btn-accent min-h-11 px-5 rounded-lg text-xs sm:text-sm cursor-pointer">
                    Lanjut Belanja
                </button>
            </div>
        </div>
    </div>

    <div
        id="sheet-menu-seluler"
        x-show="$store.tokoku.mobileSheetOpen"
        x-cloak
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        role="dialog"
        aria-modal="true"
        aria-labelledby="judul-sheet-seluler"
        class="md:hidden fixed inset-0 z-50 flex items-end bg-slate-950/60 no-print"
        @click.self="$store.tokoku.mobileSheetOpen = false"
    >
        <div
            x-show="$store.tokoku.mobileSheetOpen"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="translate-y-full"
            x-transition:enter-end="translate-y-0"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="translate-y-0"
            x-transition:leave-end="translate-y-full"
            class="surface-card w-full rounded-t-2xl p-5 space-y-4"
            style="padding-bottom: calc(1.5rem + env(safe-area-inset-bottom, 0px));"
        >
            <div class="flex items-center justify-between gap-2">
                <h2 id="judul-sheet-seluler" class="font-bold text-base">Menu &amp; Akses Cepat</h2>
                <button type="button" @click="$store.tokoku.mobileSheetOpen = false" class="btn-secondary min-h-11 px-3.5 rounded-lg text-sm cursor-pointer">
                    Tutup
                </button>
            </div>

            <div class="grid grid-cols-1 gap-2.5">
                <button
                    type="button"
                    x-show="!$store.tokoku.isStandalone"
                    @click="$store.tokoku.triggerInstall()"
                    class="btn-accent w-full min-h-11 px-4 rounded-lg text-sm cursor-pointer"
                >
                    Pasang Aplikasi ke Layar Utama HP
                </button>

                <button
                    type="button"
                    @click="$store.tokoku.mobileSheetOpen = false; $store.tokoku.savedDrawerOpen = true"
                    class="btn-secondary w-full min-h-11 px-4 rounded-lg text-sm cursor-pointer flex items-center justify-between"
                >
                    <span>Lihat Barang Tersimpan</span>
                    <span class="badge-accent-soft px-2 py-0.5 rounded text-xs font-bold tabular-nums" x-text="$store.tokoku.savedItems.length"></span>
                </button>

                <button
                    type="button"
                    @click="$store.tokoku.toggleTheme()"
                    class="btn-secondary w-full min-h-11 px-4 rounded-lg text-sm cursor-pointer"
                    x-text="$store.tokoku.isDark ? 'Ganti ke Mode Terang' : 'Ganti ke Mode Gelap'"
                >
                    Ganti Tema
                </button>

                <div class="grid grid-cols-2 gap-2.5 pt-1">
                    <a href="{{ url('/admin') }}" class="btn-secondary min-h-11 px-3 rounded-lg text-sm inline-flex items-center justify-center">
                        Panel Admin
                    </a>
                    <a href="{{ url('/teknisi') }}" class="btn-secondary min-h-11 px-3 rounded-lg text-sm inline-flex items-center justify-center">
                        Panel Teknisi
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div
        x-show="$store.tokoku.installGuideOpen"
        x-cloak
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        role="dialog"
        aria-modal="true"
        aria-labelledby="judul-panduan-pwa"
        class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/60 no-print"
        @click.self="$store.tokoku.installGuideOpen = false"
    >
        <div
            x-show="$store.tokoku.installGuideOpen"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95"
            class="surface-card w-full max-w-md rounded-2xl p-5 sm:p-6 space-y-4"
        >
            <div class="flex items-start justify-between gap-3">
                <div>
                    <h2 id="judul-panduan-pwa" class="font-bold text-lg">Pasang {{ $store->store_name }} di HP Anda</h2>
                    <p class="text-xs sm:text-sm text-muted-ink mt-0.5">
                        Akses cepat layar penuh seperti aplikasi native tanpa mengunduh file besar.
                    </p>
                </div>
                <button type="button" @click="$store.tokoku.installGuideOpen = false" class="btn-secondary min-h-11 px-3 rounded-lg text-sm cursor-pointer">
                    Tutup
                </button>
            </div>

            <div class="space-y-3 text-sm">
                <div class="surface-subtle rounded-xl p-3.5 space-y-1">
                    <p class="font-semibold">Di Android (Google Chrome / Edge)</p>
                    <p class="text-xs sm:text-sm text-muted-ink">
                        Ketuk ikon titik tiga di pojok kanan atas browser, lalu pilih <strong>Tambahkan ke layar utama</strong> atau <strong>Instal aplikasi</strong>.
                    </p>
                </div>

                <div class="surface-subtle rounded-xl p-3.5 space-y-1">
                    <p class="font-semibold">Di iPhone / iPad (Safari)</p>
                    <p class="text-xs sm:text-sm text-muted-ink">
                        Ketuk tombol <strong>Bagikan (Share)</strong> di bagian bawah layar Safari, lalu geser ke bawah dan pilih <strong>Tambahkan ke Layar Utama (Add to Home Screen)</strong>.
                    </p>
                </div>
            </div>

            <button type="button" @click="$store.tokoku.installGuideOpen = false" class="btn-accent w-full min-h-11 rounded-lg text-sm cursor-pointer">
                Mengerti
            </button>
        </div>
    </div>

    <div
        aria-live="polite"
        aria-atomic="true"
        class="fixed right-4 bottom-24 md:bottom-6 z-50 flex flex-col items-end gap-2 pointer-events-none no-print"
    >
        <template x-for="toast in $store.tokoku.toasts" :key="toast.id">
            <div
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 translate-y-2"
                x-transition:enter-end="opacity-100 translate-y-0"
                x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="opacity-100 translate-y-0"
                x-transition:leave-end="opacity-0 translate-y-2"
                class="toast-item px-4 py-2.5 rounded-xl text-xs sm:text-sm font-semibold pointer-events-auto max-w-xs"
                x-text="toast.message"
            ></div>
        </template>
    </div>

    @livewireScripts
</body>
</html>
