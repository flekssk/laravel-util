<?php

namespace Tests\Unit\Http\Requests;

use PHPUnit\Framework\TestCase;
use FKS\Collections\SearchConditionsCollection;
use FKS\ValueObjects\SearchConditions\Conditions\DateRangeCondition;
use function PHPUnit\Framework\assertEquals;
use function PHPUnit\Framework\assertFalse;
use function PHPUnit\Framework\assertInstanceOf;
use function PHPUnit\Framework\assertTrue;

class SearchConditionsCollectionTest extends TestCase
{
    public function testGetFilterCollectionByParamName(): void
    {
        $searchConditionsCollection = new SearchConditionsCollection();
        $searchConditionsCollection->add(new DateRangeCondition('date', now()->subDays(rand(1, 100)), now(), 'timestamp'));
        $searchConditionsCollection->add(new DateRangeCondition('date', now()->subDays(rand(1, 100)), now(), 'timestamp'));
        $searchConditionsCollection->add(new DateRangeCondition('date1', now()->subDays(rand(1, 100)), now(), 'timestamp'));
        $filteredCollection = $searchConditionsCollection->getFilterCollectionByParamName('date');
        assertInstanceOf(SearchConditionsCollection::class, $filteredCollection);
        assertEquals(2, $filteredCollection->count());
    }

    public function testCheckFilterHasMethods(): void
    {
        $searchConditionsCollection = new SearchConditionsCollection();
        $searchConditionsCollection->add(new DateRangeCondition('date', now()->subDays(rand(1, 100)), now(), 'timestamp'));
        $searchConditionsCollection->add(new DateRangeCondition('date1', now()->subDays(rand(1, 100)), now(), 'timestamp'));
        $searchConditionsCollection->add(new DateRangeCondition('date2', now()->subDays(rand(1, 100)), now(), 'timestamp'));

        assertTrue($searchConditionsCollection->hasAny(['date4', 'date', 'date5']));
        assertTrue($searchConditionsCollection->hasAny(['date']));
        assertTrue($searchConditionsCollection->has(['date', 'date1', 'date2']));
        assertTrue($searchConditionsCollection->has('date'));

        assertFalse($searchConditionsCollection->hasAny(['date3', 'date4', 'date5']));
        assertFalse($searchConditionsCollection->has(['date', 'date1', 'date3']));
        assertFalse($searchConditionsCollection->has('date3'));
    }

    public function testGetAndRemoveFilterCollectionByParamName(): void
    {
        $searchConditionsCollection = new SearchConditionsCollection();
        $searchConditionsCollection->add(new DateRangeCondition('date', now()->subDays(rand(1, 100)), now(), 'timestamp'));
        $searchConditionsCollection->add(new DateRangeCondition('date', now()->subDays(rand(1, 100)), now(), 'timestamp'));
        $searchConditionsCollection->add(new DateRangeCondition('date1', now()->subDays(rand(1, 100)), now(), 'timestamp'));

        $resultConditionsCollection = $searchConditionsCollection->getFilterCollectionAndRemoveByParamName('date');
        assertInstanceOf(SearchConditionsCollection::class, $resultConditionsCollection);
        assertEquals(1, $searchConditionsCollection->count());
        assertEquals(2, $resultConditionsCollection->count());
    }

    public function testRemoveFilterCollectionByParamName(): void
    {
        $searchConditionsCollection = new SearchConditionsCollection();
        $searchConditionsCollection->add(new DateRangeCondition('date', now()->subDays(rand(1, 100)), now(), 'timestamp'));
        $searchConditionsCollection->add(new DateRangeCondition('date', now()->subDays(rand(1, 100)), now(), 'timestamp'));
        $searchConditionsCollection->add(new DateRangeCondition('date1', now()->subDays(rand(1, 100)), now(), 'timestamp'));

        $searchConditionsCollection->removeFiltersByParamName('date');
        assertInstanceOf(SearchConditionsCollection::class, $searchConditionsCollection);

        assertEquals(1, $searchConditionsCollection->count());
    }
}
