<?php

namespace App\Filament\Resources\ServiceTicketResource\RelationManagers;

use App\Models\ServiceTicket;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class LogsRelationManager extends RelationManager
{
    protected static string $relationship = 'logs';
    protected static ?string $title = 'Riwayat Log Pengerjaan';

    public function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('status')
                ->label('Tahap Status')
                ->options(ServiceTicket::STATUSES)
                ->default(fn () => $this->getOwnerRecord()->status)
                ->required(),
            Forms\Components\Toggle::make('is_public')
                ->label('Tampilkan ke Pelanggan pada Cek Resi')
                ->default(true),
            Forms\Components\Textarea::make('note')
                ->label('Catatan Pengerjaan')
                ->required()
                ->columnSpanFull(),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Waktu')
                    ->dateTime('d M Y H:i'),
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (string $state) => ServiceTicket::STATUSES[$state] ?? $state),
                Tables\Columns\TextColumn::make('note')
                    ->label('Catatan')
                    ->wrap(),
                Tables\Columns\IconColumn::make('is_public')
                    ->label('Publik')
                    ->boolean(),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('Tambah Catatan Log')
                    ->mutateFormDataUsing(function (array $data): array {
                        $data['user_id'] = Auth::id();

                        return $data;
                    }),
            ])
            ->actions([
                Tables\Actions\DeleteAction::make(),
            ]);
    }
}
