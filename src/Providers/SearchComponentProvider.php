<?php

declare(strict_types=1);

namespace FKS\Providers;

use Illuminate\Support\Facades\Validator;
use Illuminate\Support\ServiceProvider;
use Ramsey\Uuid\Uuid;
use FKS\Contracts\SearchQueryBuilderFactoryInterface;
use FKS\Contracts\FKSPaginatorInterface;
use FKS\Enums\SearchComponent\SearchDriversEnum;
use FKS\Factories\SearchQueryBuilderFactory;
use FKS\Helpers\SearchComponent\SearchComponentConfigHelper;
use FKS\ValueObjects\SearchConditions\PerPagePaginator;
use Throwable;

class SearchComponentProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(SearchQueryBuilderFactoryInterface::class, function () {
            return tap(new SearchQueryBuilderFactory(), function (SearchQueryBuilderFactory $factory) {
                $factory->setSearchDriver(SearchDriversEnum::SPANNER);
            });
        });

        $this->app->bind(FKSPaginatorInterface::class, SearchComponentConfigHelper::getPaginatorClass());
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
