<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductResource\Pages;
use App\Models\Product;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;
    protected static ?string $navigationIcon = 'heroicon-o-Archive-box';
    protected static ?string $navigationGroup = 'Katalog & Rakit PC';
    protected static ?string $navigationLabel = 'Barang & Komponen PC';
    protected static ?string $modelLabel = 'Barang / Komponen';
    protected static ?int $navigationSort = 1;

    public static function getNavigationIcon(): string
    {
        return 'heroicon-o-archive-box';
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Informasi Utama & Kondisi Barang')
                ->columns(2)
                ->schema([
                    Forms\Components\Select::make('category_id')
                        ->label('Kategori')
                        ->relationship('category', 'name')
                        ->required()
                        ->preload()
                        ->searchable(),
                    Forms\Components\TextInput::make('sku')
                        ->label('Kode SKU / Label Unit')
                        ->placeholder('VGA-RX6600-01')
                        ->required()
                        ->unique(ignoreRecord: true),
                    Forms\Components\TextInput::make('name')
                        ->label('Judul Barang')
                        ->required()
                        ->columnSpanFull()
                        ->live(onBlur: true)
                        ->afterStateUpdated(function (Get $get, Set $set, ?string $state) {
                            if (! filled($get('slug')) && filled($state)) {
                                $set('slug', Str::slug($state . '-' . ($get('sku') ?: Str::random(4))));
                            }
                        }),
                    Forms\Components\TextInput::make('slug')
                        ->label('Slug URL')
                        ->required()
                        ->unique(ignoreRecord: true),
                    Forms\Components\TextInput::make('brand')
                        ->label('Merek / Brand')
                        ->placeholder('AMD / MSI / ASUS / Lenovo'),
                    Forms\Components\Select::make('condition')
                        ->label('Kondisi Fisik & Fungsi')
                        ->options(Product::CONDITIONS)
                        ->required()
                        ->default('new'),
                    Forms\Components\TextInput::make('warranty_info')
                        ->label('Keterangan Garansi')
                        ->placeholder('Garansi Toko 14 Hari / Resmi 3 Tahun'),
                    Forms\Components\Textarea::make('condition_notes')
                        ->label('Catatan Kelengkapan atau Minus Fisik')
                        ->placeholder('Tulis jujur kelengkapan dus, segel baut, suhu uji, atau titik minus bila ada.')
                        ->rows(2)
                        ->columnSpanFull(),
                    Forms\Components\Textarea::make('description')
                        ->label('Deskripsi Lengkap')
                        ->rows(3)
                        ->columnSpanFull(),
                ]),

            Forms\Components\Section::make('Harga, Stok, & Penayangan')
                ->columns(3)
                ->schema([
                    Forms\Components\TextInput::make('price')
                        ->label('Harga Jual (Rp)')
                        ->numeric()
                        ->prefix('Rp')
                        ->required(),
                    Forms\Components\TextInput::make('stock')
                        ->label('Jumlah Stok')
                        ->numeric()
                        ->default(1)
                        ->required(),
                    Forms\Components\Select::make('availability_status')
                        ->label('Status Lapak')
                        ->options(Product::AVAILABILITY_STATUSES)
                        ->default('available')
                        ->required(),
                    Forms\Components\TextInput::make('location_area')
                        ->label('Lokasi Barang / Titik COD')
                        ->default('Workshop Tokoku')
                        ->required(),
                    Forms\Components\Toggle::make('is_negotiable')
                        ->label('Harga Bisa Nego')
                        ->default(false),
                    Forms\Components\Toggle::make('cod_available')
                        ->label('Bisa COD / Cek di Toko')
                        ->default(true),
                    Forms\Components\Toggle::make('show_in_marketplace')
                        ->label('Tampilkan di Etalase Marketplace')
                        ->default(true),
                    Forms\Components\Toggle::make('show_in_pc_builder')
                        ->label('Masukkan ke Simulasi Rakit PC')
                        ->live()
                        ->default(false),
                ]),

            Forms\Components\Section::make('Foto Produk')
                ->columns(2)
                ->schema([
                    Forms\Components\FileUpload::make('primary_image')
                        ->label('Foto Utama')
                        ->image()
                        ->disk('public')
                        ->directory('products'),
                    Forms\Components\FileUpload::make('gallery_images')
                        ->label('Foto Sudut Tambahan (Opsional)')
                        ->image()
                        ->multiple()
                        ->disk('public')
                        ->directory('products'),
                ]),

            Forms\Components\Section::make('Spesifikasi Teknis untuk Validasi Rakit PC')
                ->description('Isi bagian yang relevan bila barang ini diaktifkan pada Simulasi Rakit PC.')
                ->columns(3)
                ->collapsed(fn (Get $get): bool => ! (bool) $get('show_in_pc_builder'))
                ->schema([
                    Forms\Components\Select::make('compatibility_specs.socket')
                        ->label('Soket CPU / Motherboard')
                        ->options([
                            'AM4' => 'AMD AM4',
                            'AM5' => 'AMD AM5',
                            'LGA1700' => 'Intel LGA1700',
                            'LGA1200' => 'Intel LGA1200',
                            'LGA1151' => 'Intel LGA1151',
                            'LGA1150' => 'Intel LGA1150',
                        ]),
                    Forms\Components\Select::make('compatibility_specs.ram_type')
                        ->label('Generasi RAM (Mobo / RAM)')
                        ->options([
                            'DDR3' => 'DDR3',
                            'DDR4' => 'DDR4',
                            'DDR5' => 'DDR5',
                        ]),
                    Forms\Components\TextInput::make('compatibility_specs.ram_slots')
                        ->label('Jumlah Slot RAM (Khusus Mobo)')
                        ->numeric(),
                    Forms\Components\TextInput::make('compatibility_specs.modules_count')
                        ->label('Jumlah Keping Kit (Khusus RAM)')
                        ->numeric(),
                    Forms\Components\TextInput::make('compatibility_specs.capacity_gb')
                        ->label('Kapasitas (GB, RAM/SSD)')
                        ->numeric(),
                    Forms\Components\Select::make('compatibility_specs.form_factor')
                        ->label('Ukuran Motherboard')
                        ->options([
                            'ATX' => 'ATX',
                            'MATX' => 'Micro-ATX (mATX)',
                            'ITX' => 'Mini-ITX',
                        ]),
                    Forms\Components\CheckboxList::make('compatibility_specs.supported_form_factors')
                        ->label('Dukungan Mobo pada Casing')
                        ->options([
                            'ATX' => 'ATX',
                            'MATX' => 'Micro-ATX',
                            'ITX' => 'Mini-ITX',
                        ]),
                    Forms\Components\CheckboxList::make('compatibility_specs.supported_sockets')
                        ->label('Dukungan Soket pada Cooler')
                        ->options([
                            'AM4' => 'AM4',
                            'AM5' => 'AM5',
                            'LGA1700' => 'LGA1700',
                            'LGA1200' => 'LGA1200',
                        ]),
                    Forms\Components\Toggle::make('compatibility_specs.has_igpu')
                        ->label('CPU Memiliki iGPU Bawaan')
                        ->default(true),
                    Forms\Components\TextInput::make('compatibility_specs.gpu_length_mm')
                        ->label('Panjang Fisik VGA (mm)')
                        ->numeric(),
                    Forms\Components\TextInput::make('compatibility_specs.max_gpu_mm')
                        ->label('Batas Panjang VGA di Casing (mm)')
                        ->numeric(),
                    Forms\Components\TextInput::make('compatibility_specs.tdp_watt')
                        ->label('Konsumsi Daya / TDP (Watt)')
                        ->numeric(),
                    Forms\Components\TextInput::make('compatibility_specs.psu_watt')
                        ->label('Kapasitas Murni PSU (Watt)')
                        ->numeric(),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('updated_at', 'desc')
            ->columns([
                Tables\Columns\ImageColumn::make('primary_image')
                    ->label('Foto')
                    ->disk('public')
                    ->square(),
                Tables\Columns\TextColumn::make('name')
                    ->label('Barang & SKU')
                    ->description(fn (Product $record): string => $record->sku . ' | ' . ($record->category?->name ?? '-'))
                    ->searchable(['name', 'sku', 'brand'])
                    ->wrap(),
                Tables\Columns\TextColumn::make('condition')
                    ->label('Kondisi')
                    ->badge()
                    ->formatStateUsing(fn (string $state) => Product::CONDITIONS[$state] ?? $state)
                    ->color(fn (string $state) => match ($state) {
                        'new' => 'success',
                        'like_new' => 'info',
                        'good_used' => 'warning',
                        'minus_parts' => 'danger',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('price')
                    ->label('Harga')
                    ->money('IDR', locale: 'id')
                    ->description(fn (Product $record): string => $record->is_negotiable ? 'Bisa Nego' : 'Nett')
                    ->sortable(),
                Tables\Columns\TextColumn::make('stock')
                    ->label('Stok & Status')
                    ->formatStateUsing(fn (Product $record): string => $record->stock . ' unit (' . $record->availabilityLabel() . ')'),
                Tables\Columns\IconColumn::make('show_in_pc_builder')
                    ->label('Rakit PC')
                    ->boolean(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('category_id')
                    ->label('Kategori')
                    ->relationship('category', 'name'),
                Tables\Filters\SelectFilter::make('condition')
                    ->label('Kondisi')
                    ->options(Product::CONDITIONS),
                Tables\Filters\SelectFilter::make('availability_status')
                    ->label('Status Lapak')
                    ->options(Product::AVAILABILITY_STATUSES),
            ])
            ->actions([
                Tables\Actions\Action::make('toggle_sold')
                    ->label(fn (Product $record) => $record->availability_status === 'sold' ? 'Tayangkan Lagi' : 'Tandai Terjual')
                    ->icon('heroicon-o-check-badge')
                    ->color(fn (Product $record) => $record->availability_status === 'sold' ? 'info' : 'success')
                    ->action(function (Product $record) {
                        if ($record->availability_status === 'sold') {
                            $record->update(['availability_status' => 'available', 'stock' => max(1, $record->stock)]);
                        } else {
                            $record->update(['availability_status' => 'sold', 'stock' => 0]);
                        }
                    }),
                Tables\Actions\EditAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'edit' => Pages\EditProduct::route('/{record}/edit'),
        ];
    }
}
