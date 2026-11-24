<?php

namespace App\Filament\Resources\ProductResource\Pages;

use App\Filament\Resources\ProductResource;
use App\Models\Product;
use Filament\Actions;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListProducts extends ListRecords
{
    protected static string $resource = ProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->label('Tambah Barang Baru / Second'),
        ];
    }

    public function getTabs(): array
    {
        return [
            'semua' => Tab::make('Semua')
                ->badge(Product::query()->count()),
            'ready' => Tab::make('Tersedia')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('availability_status', 'available')->where('stock', '>', 0))
                ->badge(Product::query()->where('availability_status', 'available')->where('stock', '>', 0)->count())
                ->badgeColor('success'),
            'menipis' => Tab::make('Stok Menipis')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('availability_status', 'available')->where('stock', '<=', 2))
                ->badge(Product::query()->where('availability_status', 'available')->where('stock', '<=', 2)->count())
                ->badgeColor('warning'),
            'baru' => Tab::make('Baru Segel')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('condition', 'new')),
            'second' => Tab::make('Unit Second')
                ->modifyQueryUsing(fn (Builder $query) => $query->whereIn('condition', ['like_new', 'good_used', 'minus_parts'])),
            'terjual' => Tab::make('Terjual')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('availability_status', 'sold')),
        ];
    }
}
