<?php

declare(strict_types=1);

namespace Tests\Unit\Casts;

use Colopl\Spanner\Eloquent\Model;
use PHPUnit\Framework\TestCase;
use FKS\Casts\JsonObjectCast;
use Tests\Provisions\Database\Spanner\Query\SpannerQueryGrammar;

class JsonObjectCastTest extends TestCase
{
    public function testCast(): void
    {
        $testModel = new Model();
        $cast = new JsonObjectCast();
        $value = ['key1' => 'test', 'key2' => '\\/s/s\\as'];

        $encodeResult = $cast->set($testModel, 'test_json', $value, []);
        $this->assertEquals('JSON \'{"key1":"test","key2":"\\\\\\\\/s/s\\\\\\\\as"}\'',  $encodeResult['test_json']->getValue(app(SpannerQueryGrammar::class)));

        $decodeResult = $cast->get($testModel, 'test_json', $encodeResult['test_json']->getValue(app(SpannerQueryGrammar::class)), []);
        $this->assertEquals($value, $decodeResult);

        $decodeResult = $cast->get($testModel, 'test_json', $encodeResult['test_json'], []);
        $this->assertEquals($value, $decodeResult);
    }
}