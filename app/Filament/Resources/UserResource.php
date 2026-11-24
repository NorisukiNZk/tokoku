<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Hash;

class UserResource extends Resource
{
    protected static ?string $model = User::class;
    protected static ?string $navigationIcon = 'heroicon-o-users';
    protected static ?string $navigationGroup = 'Pengaturan & Akses';
    protected static ?string $navigationLabel = 'Akun Staf & Teknisi';
    protected static ?string $modelLabel = 'Pengguna Staf';
    protected static ?int $navigationSort = 91;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('name')
                ->label('Nama Lengkap')
                ->required(),
            Forms\Components\TextInput::make('email')
                ->label('Alamat Email Login')
                ->email()
                ->required()
                ->unique(ignoreRecord: true),
            Forms\Components\TextInput::make('phone')
                ->label('Nomor HP / WA')
                ->maxLength(30),
            Forms\Components\Select::make('role')
                ->label('Peran Akses')
                ->options([
                    'admin' => 'Admin Toko (Akses /admin & /teknisi)',
                    'technician' => 'Teknisi Servis (Hanya akses /teknisi)',
                ])
                ->required()
                ->default('technician'),
            Forms\Components\TextInput::make('password')
                ->label('Kata Sandi')
                ->password()
                ->dehydrateStateUsing(fn (?string $state) => filled($state) ? Hash::make($state) : null)
                ->dehydrated(fn (?string $state) => filled($state))
                ->required(fn (string $operation): bool => $operation === 'create'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nama')
                    ->searchable(),
                Tables\Columns\TextColumn::make('email')
                    ->label('Email Login')
                    ->searchable(),
                Tables\Columns\TextColumn::make('role')
                    ->label('Peran')
                    ->badge()
                    ->formatStateUsing(fn (string $state) => $state === 'admin' ? 'Admin' : 'Teknisi')
                    ->color(fn (string $state) => $state === 'admin' ? 'primary' : 'info'),
                Tables\Columns\TextColumn::make('phone')
                    ->label('Telepon'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageUsers::route('/'),
        ];
    }
}
