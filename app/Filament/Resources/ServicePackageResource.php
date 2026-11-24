<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ServicePackageResource\Pages;
use App\Models\ServicePackage;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class ServicePackageResource extends Resource
{
    protected static ?string $model = ServicePackage::class;
    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';
    protected static ?string $navigationGroup = 'Bengkel & Servis PC';
    protected static ?string $navigationLabel = 'Daftar Paket Servis';
    protected static ?string $modelLabel = 'Paket Servis';
    protected static ?int $navigationSort = 12;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('name')
                ->label('Nama Layanan Servis')
                ->required()
                ->live(onBlur: true)
                ->afterStateUpdated(fn (Set $set, ?string $state) => $set('slug', Str::slug((string) $state))),
            Forms\Components\TextInput::make('slug')
                ->required()
                ->unique(ignoreRecord: true),
            Forms\Components\TextInput::make('category_label')
                ->label('Kelompok Layanan')
                ->placeholder('Perawatan Suhu / Perbaikan Hardware')
                ->required(),
            Forms\Components\TextInput::make('estimated_duration')
                ->label('Estimasi Pengerjaan')
                ->placeholder('2 - 4 Jam')
                ->required(),
            Forms\Components\TextInput::make('base_price')
                ->label('Tarif Jasa Dasar (Rp)')
                ->numeric()
                ->prefix('Rp')
                ->required(),
            Forms\Components\TextInput::make('sort_order')
                ->label('Urutan')
                ->numeric()
                ->default(0),
            Forms\Components\Textarea::make('description')
                ->label('Rincian Pengerjaan')
                ->required()
                ->columnSpanFull(),
            Forms\Components\Toggle::make('is_active')
                ->label('Tampilkan di Form Booking Pelanggan')
                ->default(true),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('sort_order')
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Paket Layanan')
                    ->searchable(),
                Tables\Columns\TextColumn::make('category_label')
                    ->label('Kelompok')
                    ->badge(),
                Tables\Columns\TextColumn::make('estimated_duration')
                    ->label('Durasi'),
                Tables\Columns\TextColumn::make('base_price')
                    ->label('Tarif Dasar')
                    ->money('IDR', locale: 'id'),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Aktif')
                    ->boolean(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageServicePackages::route('/'),
        ];
    }
}
