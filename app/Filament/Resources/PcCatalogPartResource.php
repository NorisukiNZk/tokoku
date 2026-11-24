<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PcCatalogPartResource\Pages;
use App\Models\Category;
use App\Models\PcCatalogPart;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class PcCatalogPartResource extends Resource
{
    protected static ?string $model = PcCatalogPart::class;
    protected static ?string $navigationIcon = 'heroicon-o-circle-stack';
    protected static ?string $navigationGroup = 'Katalog & Rakit PC';
    protected static ?string $navigationLabel = 'Database Part API (Rakit PC)';
    protected static ?string $modelLabel = 'Part Referensi API';
    protected static ?int $navigationSort = 4;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('slot')
                ->label('Slot Komponen')
                ->options(Category::COMPONENT_SLOTS)
                ->required(),
            Forms\Components\TextInput::make('brand')
                ->label('Merek'),
            Forms\Components\TextInput::make('name')
                ->label('Nama Komponen')
                ->required()
                ->columnSpanFull(),
            Forms\Components\TextInput::make('price_usd')
                ->label('Harga Referensi (USD)')
                ->numeric()
                ->prefix('$')
                ->required(),
            Forms\Components\TextInput::make('estimated_price_idr')
                ->label('Estimasi Harga Rupiah (Rp)')
                ->numeric()
                ->prefix('Rp')
                ->required(),
            Forms\Components\Select::make('price_tier')
                ->label('Kelas Harga')
                ->options(PcCatalogPart::PRICE_TIERS)
                ->required(),
            Forms\Components\TextInput::make('spec_summary')
                ->label('Ringkasan Spesifikasi')
                ->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('estimated_price_idr', 'asc')
            ->columns([
                Tables\Columns\TextColumn::make('slot')
                    ->label('Kategori')
                    ->badge()
                    ->formatStateUsing(fn (string $state) => Category::COMPONENT_SLOTS[$state] ?? strtoupper($state)),
                Tables\Columns\TextColumn::make('name')
                    ->label('Nama Komponen')
                    ->description(fn (PcCatalogPart $record) => $record->spec_summary)
                    ->searchable(['name', 'brand', 'spec_summary'])
                    ->wrap(),
                Tables\Columns\TextColumn::make('price_tier')
                    ->label('Kelas')
                    ->badge()
                    ->formatStateUsing(fn (string $state) => PcCatalogPart::PRICE_TIERS[$state] ?? $state)
                    ->color(fn (string $state) => match ($state) {
                        'budget' => 'success',
                        'mid' => 'info',
                        'high' => 'warning',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('price_usd')
                    ->label('USD')
                    ->money('USD')
                    ->sortable(),
                Tables\Columns\TextColumn::make('estimated_price_idr')
                    ->label('Estimasi Rupiah')
                    ->money('IDR', locale: 'id')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('slot')
                    ->label('Kategori Slot')
                    ->options([
                        'cpu' => 'Prosesor (CPU)',
                        'motherboard' => 'Motherboard',
                        'ram' => 'Memori (RAM)',
                        'gpu' => 'Kartu Grafis (VGA)',
                        'storage' => 'Penyimpanan (SSD/HDD)',
                        'cooler' => 'Pendingin CPU',
                        'case' => 'Casing PC',
                        'psu' => 'Power Supply (PSU)',
                    ]),
                Tables\Filters\SelectFilter::make('price_tier')
                    ->label('Kelas Harga')
                    ->options(PcCatalogPart::PRICE_TIERS),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManagePcCatalogParts::route('/'),
        ];
    }
}
