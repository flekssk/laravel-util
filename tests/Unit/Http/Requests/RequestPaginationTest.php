<?php

namespace Tests\Unit\Http\Requests;

use Illuminate\Foundation\Testing\TestCase;
use Illuminate\Support\Facades\Validator;
use FKS\Contracts\FKSPaginatorInterface;
use FKS\ValueObjects\SearchConditions\LimitOffsetPaginator;
use FKS\ValueObjects\SearchConditions\PerPagePaginator;
use Tests\CreatesApplication;
use Tests\Provisions\Database\Spanner\SpannerConnectionFaker;
use Tests\Provisions\Http\Request\dataRequestPaginationIncorrectValues;
use Tests\Provisions\Http\Request\TestPaginatorImplementationRequest;
use Tests\Provisions\Http\Request\TestSearchRequest;
use Tests\Provisions\Repository\Provision\TestRepository;
use Webmozart\Assert\Assert;

class RequestPaginationTest extends TestCase
{
    use CreatesApplication;

    public function testDefaultPerPagePagination(): void
    {
        // Arrange
        SpannerConnectionFaker::fake();

        $request = new TestPaginatorImplementationRequest();
        $data = [
            'per_page' => 7,
            'page' => 8,
        ];

        $expectedSql = 'select * from `test_models` limit 7 offset 49';
        $request->query->replace($data);

        $repository = new TestRepository();
        $builder = $repository->search($request->getSearchConditions(), returnBuilder: true);

        // Act
        $paginatorResolvedClass = app(FKSPaginatorInterface::class);
        $paginatorResolvedClass->setupFromRequest($request);
        $paginatorResolvedClass->applyPagination($builder);

        // Assert
        self::assertEquals($expectedSql, $builder->toSql());
    }

    public function testPerPagePaginationIncorrectValuesFails(): void
    {
        // Arrange
        $request = new TestPaginatorImplementationRequest();

        foreach (dataRequestPaginationIncorrectValues::getPerPagePaginationIncorrectValues() as $values) {
            // Act
            $validator = Validator::make($values['data'], $request->rules());
            // Assert
            self::assertEquals($values['expected'], $validator->messages()->first());
        }
    }

    public function testPerPagePaginationOverlimitFails(): void
    {
        // Arrange
        $request = new TestPaginatorImplementationRequest();

        // Act
        $validator = Validator::make(['per_page' => $request::getPerPageMax() + 1], $request->rules());
        // Assert
        self::assertEquals('The per page field must not be greater than 200.', $validator->messages()->first());
    }

    public function testOverriddenLimitOffsetPagination(): void
    {
        // Arrange
        SpannerConnectionFaker::fake();
        config()->set('FKS-search.paginator', LimitOffsetPaginator::class);

        $this->app->bind(FKSPaginatorInterface::class, config('FKS-search.paginator', PerPagePaginator::class));

        $request = new TestPaginatorImplementationRequest();
        $data = [
            'limit' => 7,
            'offset' => 49,

        ];

        $expectedSql = 'select * from `test_models` limit 7 offset 49';
        $request->query->replace($data);

        $repository = new TestRepository();
        $builder = $repository->search($request->getSearchConditions(), returnBuilder: true);

        // Act
        $paginatorResolvedClass = app(FKSPaginatorInterface::class);
        $paginatorResolvedClass->setupFromRequest($request);
        $paginatorResolvedClass->applyPagination($builder);

        // Assert
        self::assertEquals($expectedSql, $builder->toSql());

    }

    public function testLimitOffsetPaginationIncorrectValuesFails(): void
    {
        // Arrange
        app()->instance(FKSPaginatorInterface::class, new LimitOffsetPaginator());
        $request = new TestPaginatorImplementationRequest();

        foreach (dataRequestPaginationIncorrectValues::getLimitOffsetPaginationIncorrectValues() as $values) {
            // Act
            $validator = Validator::make($values['data'], $request->rules());
            // Assert
            self::assertEquals($values['expected'], $validator->messages()->first());
        }
    }

    public function testLimitOffsetPaginationOverlimitFails(): void
    {
        // Arrange
        app()->instance(FKSPaginatorInterface::class, new LimitOffsetPaginator());
        $request = new TestPaginatorImplementationRequest();

        // Act
        $validator = Validator::make(['limit' => $request::getPerPageMax() + 1], $request->rules());
        // Assert
        self::assertEquals('The limit field must not be greater than 200.', $validator->messages()->first());
    }
}
