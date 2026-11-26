<div x-data="{ showNewPw: false }" style="display: flex; flex-direction: column; gap: 1.25rem;">
    <div>
        <h1 class="tk-auth-heading">{{ $this->getHeading() }}</h1>
        <p class="tk-auth-desc">
            Buat kata sandi baru minimal 8 karakter untuk mengamankan akun operasional Anda.
        </p>
    </div>

    <form wire:submit="resetPassword" class="tk-auth-form">
        <div>
            <label for="newpw-email" class="tk-label">Alamat Email Akun</label>
            <div class="tk-auth-input-wrap">
                <svg class="tk-auth-input-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                </svg>
                <input
                    id="newpw-email"
                    type="email"
                    value="{{ $this->email }}"
                    disabled
                    class="tk-auth-input"
                    style="opacity: 0.8;"
                >
            </div>
        </div>

        <div>
            <label for="newpw-password" class="tk-label">Kata Sandi Baru</label>
            <div class="tk-auth-input-wrap">
                <svg class="tk-auth-input-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                </svg>
                <input
                    id="newpw-password"
                    :type="showNewPw ? 'text' : 'password'"
                    wire:model="password"
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
                >
                    <span x-text="showNewPw ? 'Tutup' : 'Lihat'">Lihat</span>
                </button>
            </div>
            @error('password')
                <p class="tk-auth-error" role="alert">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="newpw-confirm" class="tk-label">Ulangi Kata Sandi Baru</label>
            <div class="tk-auth-input-wrap">
                <svg class="tk-auth-input-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <input
                    id="newpw-confirm"
                    :type="showNewPw ? 'text' : 'password'"
                    wire:model="passwordConfirmation"
                    placeholder="Ketik ulang kata sandi baru"
                    autocomplete="new-password"
                    required
                    class="tk-auth-input"
                >
            </div>
            @error('passwordConfirmation')
                <p class="tk-auth-error" role="alert">{{ $message }}</p>
            @enderror
        </div>

        <button
            type="submit"
            wire:loading.attr="disabled"
            wire:target="resetPassword"
            class="tk-auth-submit"
        >
            <span wire:loading.remove wire:target="resetPassword">Simpan Kata Sandi Baru</span>
            <span wire:loading wire:target="resetPassword">Menyimpan Kata Sandi...</span>
        </button>

        <div style="text-align: center; padding-top: 0.25rem;">
            <a href="{{ $loginUrl }}" class="tk-auth-link">
                &larr; Kembali ke halaman login
            </a>
        </div>
    </form>
</div>
