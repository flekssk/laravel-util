<?php

declare(strict_types=1);

namespace FKS\Search;

use Illuminate\Support\Facades\Validator;
use Illuminate\Support\ServiceProvider;
use Ramsey\Uuid\Uuid;
use FKS\Search\Contracts\PaginatorInterface;
use FKS\Search\Contracts\SearchQueryBuilderFactoryInterface;
use FKS\Search\Enums\SearchDriversEnum;
use FKS\Search\Factories\SearchQueryBuilderFactory;
use FKS\Search\Helpers\SearchComponentConfigHelper;
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

    public function boot(): void
    {
        $this->publishes([
            __DIR__ . '/../../config/search.php' => config_path('search.php'),
        ], 'FKS-search-config');
    }
}
