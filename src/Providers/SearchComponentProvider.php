<?php

declare(strict_types=1);

namespace FKS\Providers;

use FKS\Contracts\PaginatorInterface;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\ServiceProvider;
use Ramsey\Uuid\Uuid;
use FKS\Contracts\SearchQueryBuilderFactoryInterface;
use FKS\Enums\SearchComponent\SearchDriversEnum;
use FKS\Factories\SearchQueryBuilderFactory;
use FKS\Helpers\SearchComponent\SearchComponentConfigHelper;
use Throwable;

class SearchComponentProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(SearchQueryBuilderFactoryInterface::class, function () {
            return tap(new SearchQueryBuilderFactory(), function (SearchQueryBuilderFactory $factory) {
                $factory->setSearchDriver(SearchDriversEnum::MYSQL);
            });
        });

        $this->app->bind(PaginatorInterface::class, SearchComponentConfigHelper::getPaginatorClass());
    }

    public function boot()
    {
        Validator::extend('uuid_or_hex', function ($attribute, $value) {
            try {
                return (bool) Uuid::fromString($value)->getBytes();
            } catch (Throwable) {
                return false;
            }
        });
        $this->publishes([
            __DIR__ . '/../../config/FKS-search.php' => config_path('FKS-search.php'),
        ], 'FKS-search-config');
    }
}
