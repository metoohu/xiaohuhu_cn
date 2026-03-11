<?php

namespace App\Providers;

use App\Models\Category;
use Illuminate\Support\Facades\View;
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
        View::composer('front.layouts.master', function ($view) {
            $view->with('navCategories', Category::where('status', 1)
                ->whereNull('parent_id')
                ->whereNotNull('slug')
                ->orderBy('sort')
                ->with(['children' => fn ($q) => $q->where('status', 1)->whereNotNull('slug')->orderBy('sort')])
                ->get());
            if (request()->routeIs('front.categories.show') && $cat = request()->route('category')) {
                $view->with('currentCategory', $cat);
            }
        });
    }
}
