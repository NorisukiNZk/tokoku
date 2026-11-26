@php
    $livewire ??= null;
    $panel = filament()->getCurrentPanel();
    $panelId = $panel?->getId() ?? 'admin';
    $isAdmin = $panelId === 'admin';
    $store = \App\Models\StoreSetting::current();
@endphp

<x-filament-panels::layout.base :livewire="$livewire">
    <div
        x-data="{
            isDark: document.documentElement.classList.contains('dark'),
            toggleTheme() {
                this.isDark = !this.isDark;
                document.documentElement.classList.toggle('dark', this.isDark);
                try {
                    localStorage.setItem('theme', this.isDark ? 'dark' : 'light');
                    localStorage.setItem('tokoku_theme', this.isDark ? 'dark' : 'light');
                } catch (e) {}
            }
        }"
        class="tk-auth-shell"
    >
        <div class="tk-auth-card">
            <aside aria-label="Informasi Ruang Kerja Staf" class="tk-auth-showcase">
                <div style="display: flex; flex-direction: column; gap: 1.25rem;">
                    <div style="display: flex; align-items: center; justify-content: space-between; gap: 0.75rem; flex-wrap: wrap;">
                        <div style="display: inline-flex; align-items: center; gap: 0.65rem;">
                            <span style="width: 2.25rem; height: 2.25rem; border-radius: 0.6rem; background: #0F172A; border: 1.5px solid #FB923C; color: #F8FAFC; font-family: 'Plus Jakarta Sans', sans-serif; font-weight: 800; display: inline-flex; align-items: center; justify-content: center;">
                                {{ strtoupper(substr($store->store_name, 0, 1)) }}
                            </span>
                            <span style="font-family: 'Plus Jakarta Sans', sans-serif; font-weight: 800; font-size: 1.05rem;">
                                {{ $store->store_name }}
                            </span>
                        </div>
                        <span class="tk-auth-badge">
                            {{ $isAdmin ? 'Ruang Admin Toko' : 'Meja Bengkel Teknisi' }}
                        </span>
                    </div>

                    <div>
                        <h2 class="tk-auth-showcase-title">
                            {{ $isAdmin ? 'Pusat Kendali Katalog, Rakitan, & Operasional' : 'Papan Antrean Servis, Diagnosa, & Uji Suhu QC' }}
                        </h2>
                        <p class="tk-auth-showcase-desc">
                            {{ $isAdmin
                                ? 'Kelola stok baru maupun second, pantau nilai persediaan komponen, atur tarif toko, serta awasi pergerakan tiket servis dari satu panel.'
                                : 'Ambil unit antrean masuk, catat diagnosa dan penurunan suhu perangkat, serta tambahkan pemakaian sparepart yang terhubung langsung ke stok toko.' }}
                        </p>
                    </div>

                    <div class="tk-auth-art" aria-hidden="true">
                        @if($isAdmin)
                            <svg viewBox="0 0 420 132" style="width: 100%; height: auto; display: block;">
                                <rect width="420" height="132" fill="#0B1220" />
                                <rect x="16" y="16" width="118" height="100" rx="8" fill="#1E293B" stroke="#334155" />
                                <text x="28" y="38" fill="#CBD5E1" font-family="Inter, sans-serif" font-size="9" font-weight="600">STOK ETALASE</text>
                                <rect x="28" y="48" width="64" height="10" rx="3" fill="#FB923C" />
                                <rect x="28" y="66" width="90" height="7" rx="3" fill="#475569" />
                                <rect x="28" y="80" width="76" height="7" rx="3" fill="#334155" />
                                <rect x="28" y="96" width="46" height="10" rx="4" fill="#34D399" />

                                <rect x="148" y="16" width="124" height="100" rx="8" fill="#1E293B" stroke="#FB923C" stroke-width="1.5" />
                                <text x="160" y="38" fill="#FDBA74" font-family="Inter, sans-serif" font-size="9" font-weight="700">KATALOG RAKIT PC</text>
                                <circle cx="182" cy="76" r="20" fill="#0F172A" stroke="#FB923C" stroke-width="2" />
                                <circle cx="182" cy="76" r="6" fill="#FB923C" />
                                <rect x="212" y="58" width="48" height="8" rx="3" fill="#F8FAFC" />
                                <rect x="212" y="72" width="38" height="7" rx="3" fill="#94A3B8" />
                                <rect x="212" y="86" width="44" height="10" rx="4" fill="#38BDF8" />

                                <rect x="286" y="16" width="118" height="100" rx="8" fill="#1E293B" stroke="#334155" />
                                <text x="298" y="38" fill="#CBD5E1" font-family="Inter, sans-serif" font-size="9" font-weight="600">MONITOR BENGKEL</text>
                                <rect x="298" y="50" width="92" height="14" rx="4" fill="#0F172A" stroke="#34D399" />
                                <rect x="298" y="70" width="92" height="14" rx="4" fill="#0F172A" stroke="#FB923C" />
                                <rect x="298" y="90" width="92" height="14" rx="4" fill="#0F172A" stroke="#38BDF8" />
                            </svg>
                        @else
                            <svg viewBox="0 0 420 132" style="width: 100%; height: auto; display: block;">
                                <rect width="420" height="132" fill="#0B1220" />
                                <rect x="16" y="18" width="88" height="96" rx="8" fill="#1E293B" stroke="#38BDF8" />
                                <text x="26" y="38" fill="#BAE6FD" font-family="Inter, sans-serif" font-size="8.5" font-weight="700">1. DIAGNOSA</text>
                                <rect x="26" y="48" width="68" height="22" rx="4" fill="#0F172A" />
                                <rect x="26" y="76" width="68" height="22" rx="4" fill="#0F172A" />

                                <rect x="116" y="18" width="88" height="96" rx="8" fill="#1E293B" stroke="#FBBF24" />
                                <text x="126" y="38" fill="#FDE68A" font-family="Inter, sans-serif" font-size="8.5" font-weight="700">2. KONFIRMASI</text>
                                <rect x="126" y="48" width="68" height="26" rx="4" fill="#2C190E" stroke="#FB923C" />

                                <rect x="216" y="18" width="88" height="96" rx="8" fill="#1E293B" stroke="#FB923C" stroke-width="1.5" />
                                <text x="226" y="38" fill="#FDBA74" font-family="Inter, sans-serif" font-size="8.5" font-weight="700">3. UJI SUHU QC</text>
                                <rect x="226" y="50" width="68" height="18" rx="4" fill="#450A0A" />
                                <text x="234" y="62" fill="#FECACA" font-family="monospace" font-size="9">91°C AWAL</text>
                                <rect x="226" y="74" width="68" height="18" rx="4" fill="#064E3B" />
                                <text x="234" y="86" fill="#A7F3D0" font-family="monospace" font-size="9">65°C LULUS</text>

                                <rect x="316" y="18" width="88" height="96" rx="8" fill="#1E293B" stroke="#34D399" />
                                <text x="326" y="38" fill="#A7F3D0" font-family="Inter, sans-serif" font-size="8.5" font-weight="700">4. SIAP AMBIL</text>
                                <rect x="326" y="48" width="68" height="26" rx="4" fill="#064E3B" stroke="#34D399" />
                            </svg>
                        @endif
                    </div>

                    <ul class="tk-auth-feature-list">
                        @if($isAdmin)
                            <li class="tk-auth-feature-item">
                                <span class="tk-auth-feature-dot" aria-hidden="true"></span>
                                <span>Kontrol stok cepat, status negosiasi, dan sinkronisasi sparepart bengkel.</span>
                            </li>
                            <li class="tk-auth-feature-item">
                                <span class="tk-auth-feature-dot" aria-hidden="true"></span>
                                <span>Akses penuh ke katalog referensi Rakit PC, akun teknisi, dan profil WhatsApp toko.</span>
                            </li>
                        @else
                            <li class="tk-auth-feature-item">
                                <span class="tk-auth-feature-dot" aria-hidden="true"></span>
                                <span>Papan Kanban 4 lajur interaktif untuk klaim tiket dan perbarui progres pelanggan.</span>
                            </li>
                            <li class="tk-auth-feature-item">
                                <span class="tk-auth-feature-dot" aria-hidden="true"></span>
                                <span>Pencatatan suhu awal/akhir dan pemakaian sparepart terintegrasi stok katalog.</span>
                            </li>
                        @endif
                    </ul>
                </div>

                <div style="display: flex; flex-wrap: wrap; gap: 0.55rem; padding-top: 0.5rem;">
                    <a href="{{ url('/') }}" class="tk-auth-ink-btn">
                        <span aria-hidden="true">&larr;</span>
                        <span>Ke Etalase Publik</span>
                    </a>
                    @if($isAdmin)
                        <a href="{{ url('/teknisi/login') }}" class="tk-auth-ink-btn">
                            <span>Buka Login Teknisi</span>
                            <span aria-hidden="true">&rarr;</span>
                        </a>
                    @else
                        <a href="{{ url('/admin/login') }}" class="tk-auth-ink-btn">
                            <span>Buka Login Admin</span>
                            <span aria-hidden="true">&rarr;</span>
                        </a>
                    @endif
                </div>
            </aside>

            <main class="tk-auth-form-pane">
                <div class="tk-auth-topbar">
                    <span class="tk-badge tk-badge-blue">
                        {{ $isAdmin ? 'Akses Khusus Administrator' : 'Akses Staf Teknisi & Admin' }}
                    </span>

                    <button
                        type="button"
                        @click="toggleTheme()"
                        class="tk-btn tk-btn-sm"
                        :aria-label="isDark ? 'Ganti ke mode terang' : 'Ganti ke mode gelap'"
                    >
                        <span x-text="isDark ? 'Mode Terang' : 'Mode Gelap'">Mode Gelap</span>
                    </button>
                </div>

                <div style="flex: 1; display: flex; flex-direction: column; justify-content: center;">
                    {{ $slot }}
                </div>

                <div style="border-top: 1px solid var(--tk-border); padding-top: 0.9rem; display: flex; align-items: center; justify-content: space-between; gap: 0.5rem; flex-wrap: wrap; font-size: 0.76rem; color: var(--tk-muted);">
                    <span>Dilindungi pembatas percobaan login &amp; verifikasi keamanan.</span>
                    <span>{{ $store->operating_hours }}</span>
                </div>
            </main>
        </div>
    </div>
</x-filament-panels::layout.base>
