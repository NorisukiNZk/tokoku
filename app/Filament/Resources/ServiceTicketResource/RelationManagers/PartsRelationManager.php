<?php

namespace App\Filament\Resources\ServiceTicketResource\RelationManagers;

use App\Models\Product;
use App\Models\ServiceTicketPart;
use Closure;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
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
                ->options(function (?ServiceTicketPart $record) {
                    $currentProductId = $record?->product_id;

                    return Product::query()
                        ->where(function ($q) use ($currentProductId) {
                            $q->where('stock', '>', 0);
                            if ($currentProductId) {
                                $q->orWhere('id', $currentProductId);
                            }
                        })
                        ->orderBy('name')
                        ->get()
                        ->mapWithKeys(fn (Product $p) => [
                            $p->id => "{$p->name} (Sisa stok: {$p->stock})",
                        ]);
                })
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
                ->rules([
                    fn (Get $get, ?ServiceTicketPart $record): Closure => function (string $attribute, mixed $value, Closure $fail) use ($get, $record) {
                        $selectedProductId = $get('product_id');
                        if (! $selectedProductId) {
                            return;
                        }
                        $product = Product::query()->find($selectedProductId);
                        if (! $product) {
                            return;
                        }
                        $alreadyHeld = ($record && (int) $record->product_id === (int) $selectedProductId)
                            ? (int) $record->stock_deducted_qty
                            : 0;
                        $maxAvailable = (int) $product->stock + $alreadyHeld;
                        if ((int) $value > $maxAvailable) {
                            $fail("Stok {$product->name} hanya tersedia {$maxAvailable} unit.");
                        }
                    },
                ])
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
