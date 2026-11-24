<?php

namespace App\Filament\Resources\ServiceTicketResource\RelationManagers;

use App\Models\Product;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Set;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class PartsRelationManager extends RelationManager
{
    protected static string $relationship = 'parts';
    protected static ?string $title = 'Pemakaian Sparepart / Komponen Pengganti';

    public function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('product_id')
                ->label('Ambil dari Stok Katalog (Opsional)')
                ->options(fn () => Product::query()->where('stock', '>', 0)->pluck('name', 'id'))
                ->searchable()
                ->live()
                ->afterStateUpdated(function (?string $state, Set $set) {
                    if ($state) {
                        $product = Product::query()->find($state);
                        if ($product) {
                            $set('part_name', $product->name);
                            $set('unit_price', $product->price);
                        }
                    }
                }),
            Forms\Components\TextInput::make('part_name')
                ->label('Nama Sparepart / Bahan')
                ->required(),
            Forms\Components\TextInput::make('qty')
                ->label('Jumlah')
                ->numeric()
                ->default(1)
                ->minValue(1)
                ->required(),
            Forms\Components\TextInput::make('unit_price')
                ->label('Harga Satuan (Rp)')
                ->numeric()
                ->prefix('Rp')
                ->required(),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('part_name')->label('Sparepart'),
                Tables\Columns\TextColumn::make('qty')->label('Qty'),
                Tables\Columns\TextColumn::make('unit_price')->label('Harga Satuan')->money('IDR', locale: 'id'),
                Tables\Columns\TextColumn::make('subtotal')->label('Subtotal')->money('IDR', locale: 'id'),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()->label('Tambah Sparepart'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ]);
    }
}
