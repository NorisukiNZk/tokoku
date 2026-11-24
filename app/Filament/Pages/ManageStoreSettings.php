<?php

namespace App\Filament\Pages;

use App\Models\StoreSetting;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class ManageStoreSettings extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';
    protected static ?string $navigationGroup = 'Pengaturan & Akses';
    protected static ?string $navigationLabel = 'Identitas & Kontak Toko';
    protected static ?string $title = 'Identitas, Kontak WhatsApp, & Tarif Dasar Toko';
    protected static ?int $navigationSort = 90;

    protected static string $view = 'filament.pages.manage-store-settings';

    public ?array $data = [];

    public function mount(): void
    {
        $setting = StoreSetting::current();
        $this->form->fill($setting->toArray());
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Profil & Kontak Pemesanan')
                    ->description('Nomor WhatsApp dan alamat ini ditampilkan di halaman publik untuk tombol Nego, Booking Servis, serta Pesan Rakitan.')
                    ->columns(2)
                    ->schema([
                        TextInput::make('store_name')
                            ->label('Nama Toko')
                            ->required()
                            ->maxLength(120),
                        TextInput::make('whatsapp_number')
                            ->label('Nomor WhatsApp Admin (Diawali 62)')
                            ->placeholder('6281234567890')
                            ->required()
                            ->maxLength(30),
                        TextInput::make('tagline')
                            ->label('Subjudul Singkat Toko')
                            ->columnSpanFull()
                            ->maxLength(180),
                        TextInput::make('workshop_address')
                            ->label('Alamat Lengkap Workshop / Toko Fisik')
                            ->required()
                            ->columnSpanFull()
                            ->maxLength(255),
                        TextInput::make('cod_areas')
                            ->label('Ketentuan COD / Pengambilan')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('operating_hours')
                            ->label('Jam Operasional')
                            ->required()
                            ->maxLength(120),
                        TextInput::make('default_assembly_fee')
                            ->label('Tarif Dasar Jasa Rakit PC (Rp)')
                            ->numeric()
                            ->prefix('Rp')
                            ->required(),
                        TextInput::make('usd_to_idr_rate')
                            ->label('Kurs Konversi API Part PC (1 USD ke Rp)')
                            ->numeric()
                            ->prefix('Rp')
                            ->default(16500)
                            ->required(),
                        FileUpload::make('logo_path')
                            ->label('Logo Resmi Toko (Opsional, PNG/SVG)')
                            ->image()
                            ->directory('branding')
                            ->disk('public'),
                    ]),
            ])
            ->statePath('data');
    }

    protected function getFormActions(): array
    {
        return [
            Action::make('save')
                ->label('Simpan Pengaturan Toko')
                ->submit('save'),
        ];
    }

    public function save(): void
    {
        $state = $this->form->getState();
        $setting = StoreSetting::current();

        $isStillPlaceholder = ($state['whatsapp_number'] ?? '') === '6280000000000'
            || str_contains((string) ($state['workshop_address'] ?? ''), '[ALAMAT WORKSHOP');

        $setting->update(array_merge($state, [
            'is_placeholder_data' => $isStillPlaceholder,
        ]));

        Notification::make()
            ->title('Pengaturan toko berhasil diperbarui.')
            ->success()
            ->send();
    }
}
