<?php

declare(strict_types=1);

namespace Tests\Unit\Casts;

use Colopl\Spanner\Eloquent\Model;
use PHPUnit\Framework\TestCase;
use FKS\Services\Metadata\Casts\MetadataValueCast;

class MetadataValueCastTest extends TestCase
{
    public function testCast(): void
    {
        $testModel = new Model();
        $cast = new MetadataValueCast();

        $this->assertEquals(1, $cast->get($testModel, 'test', '1', []));
        $this->assertEquals('1', $cast->set($testModel, 'test', 1, []));

        $this->assertEquals('test', $cast->get($testModel, 'test', 'test', []));
        $this->assertEquals('test', $cast->set($testModel, 'test', 'test', []));

        $this->assertEquals(['test' => 'test'], $cast->get($testModel, 'test', json_encode(['test' => 'test']), []));
        $this->assertEquals(json_encode(['test' => 'test']), $cast->set($testModel, 'test', ['test' => 'test'], []));
    }
}