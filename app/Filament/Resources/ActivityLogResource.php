<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ActivityLogResource\Pages;
use App\Models\ActivityLog;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class ActivityLogResource extends Resource
{
    protected static ?string $model = ActivityLog::class;

    protected static ?string $navigationGroup = 'Pengaturan & Akses';

    protected static ?int $navigationSort = 95;

    protected static ?string $navigationIcon = 'heroicon-o-shield-check';

    protected static ?string $navigationLabel = 'Log Audit Aktivitas';

    protected static ?string $modelLabel = 'Log Audit';

    protected static ?string $pluralModelLabel = 'Log Audit Aktivitas';

    protected static ?string $slug = 'activity-logs';

    public static function canViewAny(): bool
    {
        return auth()->user()?->role === 'admin';
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit(Model $record): bool
    {
        return false;
    }

    public static function canDelete(Model $record): bool
    {
        return false;
    }

    public static function canDeleteAny(): bool
    {
        return false;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('created_at')
                    ->label('Waktu')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
                TextColumn::make('actor_name')
                    ->label('Pelaku')
                    ->weight('bold')
                    ->searchable(),
                TextColumn::make('action')
                    ->label('Jenis Aksi')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => ActivityLog::ACTIONS[$state] ?? $state)
                    ->color(fn (string $state): string => match ($state) {
                        'product_updated' => 'info',
                        'product_deleted' => 'danger',
                        'ticket_updated' => 'warning',
                        'ticket_customer_approved' => 'success',
                        'settings_updated' => 'primary',
                        default => 'gray',
                    }),
                TextColumn::make('subject_label')
                    ->label('Objek')
                    ->fontFamily('mono')
                    ->weight('semibold')
                    ->searchable()
                    ->placeholder('-'),
                TextColumn::make('description')
                    ->label('Ringkasan Perubahan')
                    ->wrap()
                    ->searchable(),
                TextColumn::make('ip_address')
                    ->label('Alamat IP')
                    ->fontFamily('mono')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->placeholder('-'),
            ])
            ->filters([
                SelectFilter::make('action')
                    ->label('Saring Jenis Aksi')
                    ->options(ActivityLog::ACTIONS),
            ])
            ->actions([
                Action::make('view_diff')
                    ->label('Detail')
                    ->icon('heroicon-o-eye')
                    ->color('gray')
                    ->modalHeading(fn (ActivityLog $record): string => 'Detail Audit: ' . ($record->subject_label ?: $record->actionLabel()))
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Tutup')
                    ->modalWidth('2xl')
                    ->modalContent(fn (ActivityLog $record) => view('filament.modals.activity-log-detail', [
                        'record' => $record,
                    ])),
            ])
            ->bulkActions([])
            ->emptyStateHeading('Belum ada catatan aktivitas')
            ->emptyStateDescription('Perubahan stok, harga produk, status servis, persetujuan biaya pelanggan, dan pengaturan toko akan tercatat otomatis di sini.');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListActivityLogs::route('/'),
        ];
    }
}
