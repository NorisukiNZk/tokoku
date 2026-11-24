<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ServiceTicketResource\Pages;
use App\Filament\Resources\ServiceTicketResource\RelationManagers\LogsRelationManager;
use App\Filament\Resources\ServiceTicketResource\RelationManagers\PartsRelationManager;
use App\Models\ServicePackage;
use App\Models\ServiceTicket;
use App\Models\ServiceTicketLog;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Set;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class ServiceTicketResource extends Resource
{
    protected static ?string $model = ServiceTicket::class;
    protected static ?string $navigationIcon = 'heroicon-o-wrench-screwdriver';
    protected static ?string $navigationGroup = 'Bengkel & Servis PC';
    protected static ?string $navigationLabel = 'Tiket Servis & Perawatan';
    protected static ?string $modelLabel = 'Tiket Servis';
    protected static ?int $navigationSort = 10;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Data Pelanggan & Perangkat')
                ->columns(2)
                ->schema([
                    Forms\Components\TextInput::make('ticket_number')
                        ->label('Nomor Tiket Servis')
                        ->default(fn () => ServiceTicket::generateTicketNumber())
                        ->disabled(fn (string $operation) => $operation === 'edit')
                        ->dehydrated()
                        ->required(),
                    Forms\Components\Select::make('visit_type')
                        ->label('Metode Layanan')
                        ->options([
                            'walk_in' => 'Antar ke Workshop / Toko',
                            'home_pickup' => 'Jemput Unit / Kunjungan Lokasi',
                        ])
                        ->default('walk_in')
                        ->required(),
                    Forms\Components\TextInput::make('customer_name')
                        ->label('Nama Pelanggan')
                        ->required(),
                    Forms\Components\TextInput::make('customer_phone')
                        ->label('Nomor WhatsApp Pelanggan')
                        ->helperText('4 digit terakhir dipakai pelanggan untuk verifikasi lacak resi.')
                        ->required(),
                    Forms\Components\Select::make('device_type')
                        ->label('Jenis Perangkat')
                        ->options([
                            'PC Rakitan Desktop' => 'PC Rakitan Desktop',
                            'Laptop Gaming / Kerja' => 'Laptop Gaming / Kerja',
                            'PC Kantor / All-in-One' => 'PC Kantor / All-in-One',
                            'Komponen Lepasan (VGA/Mobo)' => 'Komponen Lepasan (VGA/Mobo)',
                        ])
                        ->required(),
                    Forms\Components\TextInput::make('device_brand_model')
                        ->label('Tipe / Spesifikasi Singkat Unit')
                        ->placeholder('Contoh: ASUS TUF A15 / Ryzen 5 + RTX 3060')
                        ->required(),
                    Forms\Components\Select::make('service_package_id')
                        ->label('Paket Layanan')
                        ->relationship('servicePackage', 'name')
                        ->preload()
                        ->searchable()
                        ->live()
                        ->afterStateUpdated(function (?string $state, Set $set) {
                            if ($state) {
                                $price = ServicePackage::query()->whereKey($state)->value('base_price');
                                if ($price !== null) {
                                    $set('service_fee', $price);
                                }
                            }
                        }),
                    Forms\Components\DatePicker::make('preferred_date')
                        ->label('Tanggal Kedatangan / Jadwal'),
                    Forms\Components\Textarea::make('complaint_summary')
                        ->label('Keluhan Awal Pelanggan')
                        ->required()
                        ->rows(2)
                        ->columnSpanFull(),
                    Forms\Components\Textarea::make('customer_address')
                        ->label('Alamat Jemput / Catatan Lokasi')
                        ->rows(2)
                        ->columnSpanFull(),
                ]),

            Forms\Components\Section::make('Diagnosa Teknisi, Pengukuran Suhu, & Biaya')
                ->columns(3)
                ->schema([
                    Forms\Components\Select::make('status')
                        ->label('Status Pengerjaan')
                        ->options(ServiceTicket::STATUSES)
                        ->default('booked')
                        ->required(),
                    Forms\Components\Select::make('technician_id')
                        ->label('Teknisi Penanggung Jawab')
                        ->relationship('technician', 'name')
                        ->preload()
                        ->searchable(),
                    Forms\Components\DatePicker::make('warranty_until')
                        ->label('Batas Garansi Servis'),
                    Forms\Components\TextInput::make('temp_before_c')
                        ->label('Suhu Awal Sebelum Servis (°C)')
                        ->numeric()
                        ->suffix('°C'),
                    Forms\Components\TextInput::make('temp_after_c')
                        ->label('Suhu Akhir Setelah QC (°C)')
                        ->numeric()
                        ->suffix('°C'),
                    Forms\Components\TextInput::make('service_fee')
                        ->label('Biaya Jasa Servis (Rp)')
                        ->numeric()
                        ->prefix('Rp')
                        ->default(0)
                        ->required(),
                    Forms\Components\Textarea::make('diagnosis_notes')
                        ->label('Hasil Pengecekan & Tindakan Teknisi')
                        ->rows(3)
                        ->columnSpanFull(),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('updated_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('ticket_number')
                    ->label('No. Tiket')
                    ->searchable()
                    ->copyable()
                    ->description(fn (ServiceTicket $record) => $record->visit_type === 'home_pickup' ? 'Jemput Unit' : 'Workshop'),
                Tables\Columns\TextColumn::make('device_brand_model')
                    ->label('Perangkat & Pelanggan')
                    ->description(fn (ServiceTicket $record) => $record->customer_name . ' (' . $record->customer_phone . ')')
                    ->searchable(['device_brand_model', 'customer_name', 'customer_phone'])
                    ->wrap(),
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (string $state) => ServiceTicket::STATUSES[$state] ?? $state)
                    ->color(fn (string $state) => match ($state) {
                        'booked', 'diagnosing' => 'info',
                        'waiting_approval' => 'danger',
                        'in_progress', 'testing_qc' => 'warning',
                        'ready', 'completed' => 'success',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('temp_before_c')
                    ->label('Suhu Uji')
                    ->formatStateUsing(function (ServiceTicket $record): string {
                        if ($record->temp_before_c && $record->temp_after_c) {
                            return "{$record->temp_before_c}°C -> {$record->temp_after_c}°C";
                        }
                        if ($record->temp_before_c) {
                            return "Awal {$record->temp_before_c}°C";
                        }

                        return '-';
                    }),
                Tables\Columns\TextColumn::make('technician.name')
                    ->label('Teknisi')
                    ->placeholder('Belum ditugaskan'),
                Tables\Columns\TextColumn::make('grand_total')
                    ->label('Total Biaya')
                    ->money('IDR', locale: 'id')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Status Pengerjaan')
                    ->options(ServiceTicket::STATUSES),
            ])
            ->actions([
                Tables\Actions\Action::make('quick_progress')
                    ->label('Update Progres')
                    ->icon('heroicon-o-arrow-path')
                    ->color('primary')
                    ->fillForm(fn (ServiceTicket $record) => [
                        'status' => $record->status,
                        'temp_after_c' => $record->temp_after_c,
                    ])
                    ->form([
                        Forms\Components\Select::make('status')
                            ->label('Status Baru')
                            ->options(ServiceTicket::STATUSES)
                            ->required(),
                        Forms\Components\TextInput::make('temp_after_c')
                            ->label('Suhu Setelah Pengerjaan (°C, Opsional)')
                            ->numeric(),
                        Forms\Components\Textarea::make('note')
                            ->label('Catatan Progres untuk Pelanggan')
                            ->placeholder('Contoh: Penggantian pasta selesai, sedang uji beban 30 menit.')
                            ->required(),
                    ])
                    ->action(function (ServiceTicket $record, array $data): void {
                        $updates = ['status' => $data['status']];
                        if (! empty($data['temp_after_c'])) {
                            $updates['temp_after_c'] = (int) $data['temp_after_c'];
                        }
                        if (! $record->technician_id && Auth::id()) {
                            $updates['technician_id'] = Auth::id();
                        }
                        $record->update($updates);

                        ServiceTicketLog::query()->create([
                            'service_ticket_id' => $record->id,
                            'user_id' => Auth::id(),
                            'status' => $data['status'],
                            'note' => $data['note'],
                            'is_public' => true,
                        ]);

                        Notification::make()
                            ->title('Status tiket ' . $record->ticket_number . ' diperbarui.')
                            ->success()
                            ->send();
                    }),
                Tables\Actions\EditAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            PartsRelationManager::class,
            LogsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListServiceTickets::route('/'),
            'create' => Pages\CreateServiceTicket::route('/create'),
            'edit' => Pages\EditServiceTicket::route('/{record}/edit'),
        ];
    }
}
