<?php

namespace App\Providers\Filament;

use App\Filament\Pages\Auth\TokokuLogin;
use App\Filament\Pages\Auth\TokokuRequestPasswordReset;
use App\Filament\Pages\Auth\TokokuResetPassword;
use App\Filament\Resources\ServiceTicketResource;
use App\Filament\Teknisi\Pages\WorkshopBoard;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Support\Enums\MaxWidth;
use Filament\View\PanelsRenderHook;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class TeknisiPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('teknisi')
            ->path('teknisi')
            ->login(TokokuLogin::class)
            ->passwordReset(TokokuRequestPasswordReset::class, TokokuResetPassword::class)
            ->spa()
            ->font('Inter')
            ->brandName('Meja Teknisi Tokoku')
            ->brandLogo(fn () => view('filament.components.brand-logo', ['role' => 'Bengkel Teknisi']))
            ->brandLogoHeight('2.4rem')
            ->topNavigation()
            ->maxContentWidth(MaxWidth::Full)
            ->globalSearchKeyBindings(['command+k', 'ctrl+k'])
            ->colors([
                'primary' => Color::Orange,
                'gray' => Color::Slate,
                'success' => Color::Emerald,
                'warning' => Color::Amber,
                'danger' => Color::Rose,
            ])
            ->renderHook(
                PanelsRenderHook::HEAD_END,
                fn () => view('filament.components.custom-panel-styles')
            )
            ->renderHook(
                PanelsRenderHook::USER_MENU_BEFORE,
                fn () => view('filament.components.topbar-quick-actions', ['panelId' => 'teknisi'])
            )
            ->resources([
                ServiceTicketResource::class,
            ])
            ->discoverResources(in: app_path('Filament/Teknisi/Resources'), for: 'App\\Filament\\Teknisi\\Resources')
            ->discoverPages(in: app_path('Filament/Teknisi/Pages'), for: 'App\\Filament\\Teknisi\\Pages')
            ->pages([
                WorkshopBoard::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
