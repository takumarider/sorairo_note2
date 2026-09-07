<?php

namespace App\Providers\Filament;

use App\Filament\Widgets\StatsOverview;
use App\Filament\Widgets\SystemSettingsAlert;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\MenuItem;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\View\PanelsRenderHook;
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\HtmlString;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->brandName('Sorairo Note')
            ->login()
            ->colors([
                'primary' => Color::Amber,
                'gray' => Color::Slate,
                'info' => Color::Sky,
                'success' => Color::Emerald,
                'warning' => Color::Amber,
                'danger' => Color::Rose,
            ])
            ->viteTheme('resources/css/filament/admin/theme.css')
            ->renderHook(
                PanelsRenderHook::HEAD_END,
                fn () => new HtmlString(<<<'HTML'
                    <style>
                        .sr-inline-overlay {
                            position: fixed;
                            inset: 0;
                            background: rgb(15 23 42 / 0.45);
                            opacity: 0;
                            pointer-events: none;
                            transition: opacity 0.2s ease;
                            z-index: 70;
                        }

                        .sr-inline-overlay[data-open='true'] {
                            opacity: 1;
                            pointer-events: auto;
                        }

                        .sr-inline-slide-over {
                            position: fixed;
                            top: 0;
                            right: 0;
                            height: 100dvh;
                            width: min(92vw, 32rem);
                            background: #fff;
                            transform: translateX(100%);
                            transition: transform 0.25s ease;
                            box-shadow: -12px 0 28px rgb(2 6 23 / 0.2);
                            z-index: 80;
                        }

                        .sr-inline-slide-over[data-open='true'] {
                            transform: translateX(0);
                        }
                    </style>
                    HTML),
            )
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                Pages\Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([
                SystemSettingsAlert::class,
                StatsOverview::class,
                Widgets\AccountWidget::class,
                Widgets\FilamentInfoWidget::class,
            ])
            ->userMenuItems([
                'switch_to_user' => MenuItem::make()
                    ->label('ユーザー画面へ')
                    ->url(fn (): string => route('dashboard'))
                    ->icon('heroicon-o-arrow-left-circle'),
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
