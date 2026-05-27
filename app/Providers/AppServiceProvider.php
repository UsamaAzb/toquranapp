<?php

namespace App\Providers;

use App\Models\ParentModel;
use App\Observers\ParentObserver;
use App\Services\BreadcrumbService;
use App\View\Composers\ParentMenuComposer;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        ParentModel::observe(ParentObserver::class);

        View::composer('layouts.sections.menu.verticalMenu', ParentMenuComposer::class);

        View::composer('layouts.sections.navbar.navbar-partial', function ($view): void {
            $data = $view->getData();

            if (isset($data['breadcrumb_links'])) {
                return;
            }

            $breadcrumbLinks = app(BreadcrumbService::class)->resolve();

            if ($breadcrumbLinks !== []) {
                $view->with('breadcrumb_links', $breadcrumbLinks);
            }
        });

        Vite::useStyleTagAttributes(function (?string $src, string $url, ?array $chunk, ?array $manifest) {
            if ($src !== null) {
                return [
                    'class' => preg_match("/(resources\/assets\/vendor\/scss\/(rtl\/)?core)-?.*/i", $src) ? 'template-customizer-core-css' : (preg_match("/(resources\/assets\/vendor\/scss\/(rtl\/)?theme)-?.*/i", $src) ? 'template-customizer-theme-css' : ''),
                ];
            }

            return [];
        });
    }
}
