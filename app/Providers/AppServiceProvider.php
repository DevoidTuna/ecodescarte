<?php

namespace App\Providers;

use App\Domain\CollectionPoint\CollectionPointRepository;
use App\Infrastructure\Persistence\Eloquent\EloquentCollectionPointRepository;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Where the port meets the adapter. Swapping the storage engine means
        // editing this line; no use case changes.
        $this->app->bind(
            CollectionPointRepository::class,
            EloquentCollectionPointRepository::class,
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
