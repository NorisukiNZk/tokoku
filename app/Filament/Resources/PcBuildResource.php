<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PcBuildResource\Pages;
use App\Models\PcBuild;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class PcBuildResource extends Resource
{
    protected static ?string $model = PcBuild::class;
    protected static ?string $navigationIcon = 'heroicon-o-cpu-chip';
    protected static ?string $navigationGroup = 'Katalog & Rakit PC';
    protected static ?string $navigationLabel = 'Simulasi Rakitan Tersimpan';
    protected static ?string $modelLabel = 'Rakitan PC';
    protected static ?int $navigationSort = 3;

    public static function getNavigationBadge(): ?string
    {
        $count = PcBuild::query()->count();

        return $count > 0 ? (string) $count : null;
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('share_code')
                ->label('Kode Bagikan')
                ->disabled(),
            Forms\Components\TextInput::make('build_name')
                ->label('Nama Rakitan')
                ->required(),
            Forms\Components\TextInput::make('customer_name')
                ->label('Nama Pemesan'),
            Forms\Components\TextInput::make('estimated_wattage')
                ->label('Estimasi Beban Daya (W)')
                ->numeric(),
            Forms\Components\TextInput::make('grand_total')
                ->label('Total Estimasi (Rp)')
                ->numeric()
                ->prefix('Rp'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('share_code')
                    ->label('Kode')
                    ->searchable()
                    ->copyable(),
                Tables\Columns\TextColumn::make('build_name')
                    ->label('Nama Rakitan')
                    ->searchable(),
                Tables\Columns\TextColumn::make('estimated_wattage')
                    ->label('Daya / Saran PSU')
                    ->formatStateUsing(fn (PcBuild $record) => "{$record->estimated_wattage}W (PSU {$record->recommended_psu_wattage}W)"),
                Tables\Columns\IconColumn::make('compatibility_passed')
                    ->label('Kompatibel')
                    ->boolean(),
                Tables\Columns\TextColumn::make('grand_total')
                    ->label('Total Biaya')
                    ->money('IDR', locale: 'id'),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y H:i'),
            ])
            ->actions([
                Tables\Actions\Action::make('open_builder')
                    ->label('Buka di Builder')
                    ->icon('heroicon-o-arrow-top-right-on-square')
                    ->url(fn (PcBuild $record) => url('/rakit-pc?kode=' . $record->share_code), shouldOpenInNewTab: true),
                Tables\Actions\DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManagePcBuilds::route('/'),
        ];
    }
}
