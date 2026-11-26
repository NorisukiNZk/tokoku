<div x-data="{ showPin: false, showNewPw: false }" style="display: flex; flex-direction: column; gap: 1.2rem;">
    <div>
        <div style="display: inline-flex; align-items: center; gap: 0.45rem; margin-bottom: 0.45rem;">
            <span class="tk-badge {{ $verifiedUserId ? 'tk-badge-ok' : 'tk-badge-blue' }}">
                {{ $verifiedUserId ? 'Langkah 2 dari 2 · Buat Sandi Baru' : 'Langkah 1 dari 2 · Verifikasi PIN Staf' }}
            </span>
        </div>
        <h1 class="tk-auth-heading">{{ $this->getHeading() }}</h1>
        <p class="tk-auth-desc">
            @if($verifiedUserId)
                Identitas akun <strong>{{ $verifiedEmail }}</strong> telah terverifikasi. Silakan ketik kata sandi baru Anda di bawah ini.
            @else
                Cukup masukkan email staf dan 6 digit <strong>PIN Pemulihan</strong> Anda untuk langsung mengganti kata sandi di halaman ini tanpa perlu membuka email.
            @endif
        </p>
    </div>

    @if(!$verifiedUserId)
        @if($isLocalDemoAllowed)
            <div class="tk-auth-alert-info" style="display: flex; align-items: center; justify-content: space-between; gap: 0.75rem; flex-wrap: wrap;">
                <span>
                    <strong>Mode Lokal Aktif:</strong> Isi cepat email &amp; PIN demo ({{ $panelId === 'admin' ? '112233' : '445566' }}) untuk mencoba alur ini.
                </span>
                <button
                    type="button"
                    wire:click="fillLocalDemoRecovery"
                    class="tk-btn tk-btn-sm"
                >
                    Isi PIN Demo
                </button>
            </div>
        @endif

        <form wire:submit="verifyPinAndProceed" class="tk-auth-form">
            <div>
                <label for="reset-email" class="tk-label">Alamat Email Staf</label>
                <div class="tk-auth-input-wrap">
                    <svg class="tk-auth-input-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                    </svg>
                    <input
                        id="reset-email"
                        type="email"
                        wire:model="email"
                        placeholder="{{ $panelId === 'admin' ? 'admin@tokoku.local' : 'teknisi@tokoku.local' }}"
                        autocomplete="email"
                        autofocus
                        required
                        class="tk-auth-input"
                    >
                </div>
                @error('email')
                    <p class="tk-auth-error" role="alert">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="reset-pin" class="tk-label">PIN Pemulihan 6 Digit / Kode Pemulihan Toko</label>
                <div class="tk-auth-input-wrap">
                    <svg class="tk-auth-input-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
                    </svg>
                    <input
                        id="reset-pin"
                        :type="showPin ? 'text' : 'password'"
                        wire:model="recoveryPin"
                        placeholder="Masukkan 6 angka PIN pemulihan"
                        autocomplete="off"
                        required
                        class="tk-auth-input tk-auth-input-pw"
                    >
                    <button
                        type="button"
                        @click="showPin = !showPin"
                        class="tk-auth-pw-toggle"
                        :aria-label="showPin ? 'Sembunyikan PIN' : 'Lihat PIN'"
                    >
                        <span x-text="showPin ? 'Tutup' : 'Lihat'">Lihat</span>
                    </button>
                </div>
                @error('recoveryPin')
                    <p class="tk-auth-error" role="alert">{{ $message }}</p>
                @enderror
            </div>

            @include('filament.components.recaptcha-box', [
                'isGoogleRecaptcha' => $isGoogleRecaptcha,
                'recaptchaSiteKey' => $recaptchaSiteKey,
                'challengePrompt' => $challengePrompt,
            ])

            <button
                type="submit"
                wire:loading.attr="disabled"
                wire:target="verifyPinAndProceed"
                class="tk-auth-submit"
            >
                <span wire:loading.remove wire:target="verifyPinAndProceed">Verifikasi PIN &amp; Lanjut Ganti Sandi</span>
                <span wire:loading wire:target="verifyPinAndProceed">Memeriksa PIN...</span>
            </button>

            <div style="display: flex; align-items: center; justify-content: space-between; gap: 0.75rem; flex-wrap: wrap; padding-top: 0.2rem;">
                <a href="{{ $loginUrl }}" class="tk-auth-link">
                    &larr; Kembali ke halaman login
                </a>

                @if($panelId === 'teknisi')
                    <a href="{{ $adminHelpWaUrl }}" target="_blank" rel="noopener noreferrer" class="tk-auth-link">
                        Lupa PIN? Minta bantuan Admin via WA &rarr;
                    </a>
                @endif
            </div>
        </form>
    @else
        <form wire:submit="saveNewPassword" class="tk-auth-form">
            <div>
                <label for="verified-new-pw" class="tk-label">Kata Sandi Baru</label>
                <div class="tk-auth-input-wrap">
                    <svg class="tk-auth-input-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                    </svg>
                    <input
                        id="verified-new-pw"
                        :type="showNewPw ? 'text' : 'password'"
                        wire:model="newPassword"
                        placeholder="Minimal 8 karakter"
                        autocomplete="new-password"
                        autofocus
                        required
                        class="tk-auth-input tk-auth-input-pw"
                    >
                    <button
                        type="button"
                        @click="showNewPw = !showNewPw"
                        class="tk-auth-pw-toggle"
                        :aria-label="showNewPw ? 'Sembunyikan sandi baru' : 'Lihat sandi baru'"
                    >
                        <span x-text="showNewPw ? 'Tutup' : 'Lihat'">Lihat</span>
                    </button>
                </div>
                @error('newPassword')
                    <p class="tk-auth-error" role="alert">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="verified-confirm-pw" class="tk-label">Ulangi Kata Sandi Baru</label>
                <div class="tk-auth-input-wrap">
                    <svg class="tk-auth-input-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <input
                        id="verified-confirm-pw"
                        :type="showNewPw ? 'text' : 'password'"
                        wire:model="newPasswordConfirmation"
                        placeholder="Ketik ulang kata sandi baru"
                        autocomplete="new-password"
                        required
                        class="tk-auth-input"
                    >
                </div>
                @error('newPasswordConfirmation')
                    <p class="tk-auth-error" role="alert">{{ $message }}</p>
                @enderror
            </div>

            <button
                type="submit"
                wire:loading.attr="disabled"
                wire:target="saveNewPassword"
                class="tk-auth-submit"
            >
                <span wire:loading.remove wire:target="saveNewPassword">Simpan Kata Sandi Baru</span>
                <span wire:loading wire:target="saveNewPassword">Menyimpan Kata Sandi...</span>
            </button>

            <div style="text-align: center; padding-top: 0.2rem;">
                <button type="button" wire:click="cancelVerification" class="tk-auth-link" style="background: transparent; border: 0; cursor: pointer;">
                    &larr; Ganti email atau batalkan
                </button>
            </div>
        </form>
    @endif
</div>
