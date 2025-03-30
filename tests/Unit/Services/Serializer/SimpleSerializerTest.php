<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Serializer;

use PHPUnit\Framework\TestCase;
use FKS\Services\Serializer\Elements\ArrayItem;
use FKS\Services\Serializer\SimpleSerializer;
use Tests\Provisions\Serializer\TestObject;
use Tests\Provisions\Serializer\TestValueObject;
use Tests\Provisions\Serializer\TestValueObjectCollection;

class SimpleSerializerTest extends TestCase
{
    public function testDeserializeFromJsonWithCorrectInputData(): void
    {
        $data = [
            [
                'string_value' => 'string',
                'int_value' => 1,
                'boolean_value' => true,
                'value_object_value' => [
                    'value' => 'value',
                ],
                'value_objects_value' => [
                    [
                        'value' => 'value',
                    ]
                ],
                'nullable_value' => null
            ]
        ];

        $result = (new SimpleSerializer())->deserializeFromJson(
            json_encode($data, JSON_THROW_ON_ERROR),
            TestValueObjectCollection::class,
            [
                ArrayItem::create(
                    TestObject::class,
                    [
                        'valueObjectValue' => TestValueObject::class,
                        'valueObjectsValue' => [TestValueObject::class],
                    ]
                )
            ]
        );

        $this->assertEquals($data[0]['string_value'], $result->items[0]->stringValue);
        $this->assertEquals($data[0]['int_value'], $result->items[0]->intValue);
        $this->assertEquals($data[0]['boolean_value'], $result->items[0]->booleanValue);
        $this->assertEquals($data[0]['value_object_value']['value'], $result->items[0]->valueObjectValue->value);
        $this->assertEquals($data[0]['value_objects_value'][0]['value'], $result->items[0]->valueObjectsValue[0]->value);
        $this->assertEquals($data[0]['nullable_value'], $result->items[0]->nullableValue);
    }

    /**
     * @dataProvider incorrectDataProvider
     */
    public function testDeserializeFromJsonWithIncorrectInputData(
        array $data,
        string $expectedException,
        ?string $expectedExceptionMessage
    ): void {
        $this->expectException($expectedException);
        $this->expectExceptionMessage($expectedExceptionMessage);

        $propertiesMapping = [
            ArrayItem::create(
                TestObject::class,
                [
                    'valueObjectValue' => TestValueObject::class,
                    'valueObjectsValue' => [TestValueObject::class],
                ]
            )
        ];

        $this->expectException(\Error::class);
        if ($expectedExceptionMessage !== null) {
            $this->expectExceptionMessage($expectedExceptionMessage);
        }

        (new SimpleSerializer())->deserializeFromJson(
            json_encode($data, JSON_THROW_ON_ERROR),
            TestValueObjectCollection::class,
            $propertiesMapping
        );
    }

    public function incorrectDataProvider(): array
    {
        return [
            [
                [
                    [
                        'string_value' => 'string',
                        'int_values' => 1,
                        'boolean_value' => true,
                        'value_object_value' => [
                            'value' => 'value',
                        ],
                        'value_objects_value' => [
                            [
                                'value' => 'value',
                            ]
                        ],
                        'nullable_value' => null
                    ]
                ],
                \Error::class,
                'Unknown named parameter $intValues'
            ],
            [
                [
                    [
                        'string_value' => 'string',
                        'int_values' => 1,
                        'boolean_value' => true,
                        'value_object_value' => [
                            'value' => 'value',
                        ],
                        'value_objects_value' => [
                            'value' => 'value',
                        ],
                        'nullable_value' => null
                    ]
                ],
                \Error::class,
                'Only arrays and Traversables can be unpacked'
            ],
            [
                [
                    [
                        'string_value' => 1,
                        'int_value' => 1,
                        'boolean_value' => true,
                        'value_object_value' => [
                            'value' => 'value',
                        ],
                        'value_objects_value' => [
                            [
                                'value' => 'value',
                            ]
                        ],
                        'nullable_value' => null
                    ]
                ],
                \Error::class,
                null
            ]
        ];
    }
}
