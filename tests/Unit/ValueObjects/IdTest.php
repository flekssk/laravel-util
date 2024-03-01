<?php

namespace Tests\Unit\ValueObjects;

use Google\Cloud\Spanner\Bytes;
use Illuminate\Support\Str;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Exception\InvalidUuidStringException;
use Ramsey\Uuid\Uuid;
use FKS\Exceptions\FKSDomainException;
use FKS\ValueObjects\Id;

/**
 * @covers \FKS\ValueObjects\Id
 */
class IdTest extends TestCase
{
    public function testCheckRules()
    {
        $uuid = Str::uuid()->toString();
        $id = Id::createFromString($uuid);
        $this->assertEquals($uuid, $id->getUuid());
        $this->assertEquals(preg_replace('/-/', '', $uuid), $id->getHex());

        $uuid = Str::uuid()->toString();
        $id = Id::createFromBytes(new Bytes(Uuid::fromString($uuid)->getBytes()));
        $this->assertEquals($uuid, $id->getUuid());
        $this->assertEquals(preg_replace('/-/', '', $uuid), $id->getHex());
    }

    public function testCreate()
    {
        $uuid = Str::uuid()->toString();
        $this->assertEquals(Id::create($uuid)->getUuid(), $uuid);

        $uuid = Str::uuid()->toString();
        $hex = Id::create($uuid)->getHex();
        $this->assertEquals(Id::create($hex)->getUuid(), $uuid);

        $uuid = Str::uuid()->toString();
        $idInBytes = new Bytes(Uuid::fromString($uuid)->getBytes());
        $this->assertEquals(Id::create($idInBytes)->getUuid(), $uuid);
    }

    /**
     * @dataProvider invalidIdParamsDataProvider
     */
    public function testCreateException($exceptionCLass, $value, string $errorMessage): void
    {
        $this->expectException($exceptionCLass);
        $this->expectExceptionMessage($errorMessage);
        Id::create($value);
    }


    public function testEqual()
    {
        $uuid = Str::uuid()->toString();

        $id = Id::create($uuid);
        $id2 = Id::create($uuid);

        $this->assertTrue($id->equal($id2));

        $id = Id::create(Str::uuid()->toString());
        $id2 = Id::create(Str::uuid()->toString());

        $this->assertFalse($id->equal($id2));
    }

    /**
     * @dataProvider validIdCreateNullableParamsDataProvider
     */
    public function testValidIdCreateNullable($value, $result)
    {
        $id = Id::createNullable($value);
        $this->assertEquals($result, $id);
    }

    /**
     * @dataProvider invalidIdCreateNullableParamsDataProvider
     */
    public function testCreateNullableException($exceptionCLass, $value, string $errorMessage): void
    {
        $this->expectException($exceptionCLass);
        $this->expectExceptionMessage($errorMessage);
        Id::createNullable($value);
    }

    public function invalidIdParamsDataProvider(): array
    {
        return [
            [
                FKSDomainException::class,
                new \stdClass(),
                'Value not supported for Id creation. Passed: stdClass type instead Bytes or string'
            ],
            [
                FKSDomainException::class,
                null,
                'Value not supported for Id creation. Passed: NULL type instead Bytes or string'
            ],
            [
                FKSDomainException::class,
                1,
                'Value not supported for Id creation. Passed: integer type instead Bytes or string'
            ],
            [
                InvalidUuidStringException::class,
                '25b6c60b-e9d1-4e81-9a9c-555849ca00801',
                'Invalid UUID string: 25b6c60b-e9d1-4e81-9a9c-555849ca00801'
            ],
        ];
    }

    public function invalidIdCreateNullableParamsDataProvider(): array
    {
        return [
            [
                FKSDomainException::class,
                new \stdClass(),
                'Value not supported for Id creation. Passed: stdClass type instead Bytes or string'
            ],
            [
                FKSDomainException::class,
                1,
                'Value not supported for Id creation. Passed: integer type instead Bytes or string'
            ],
            [
                FKSDomainException::class,
                0,
                'Value not supported for Id creation. Passed: integer type instead Bytes or string'
            ],
            [
                InvalidUuidStringException::class,
                '25b6c60b-e9d1-4e81-9a9c-555849ca00801',
                'Invalid UUID string: 25b6c60b-e9d1-4e81-9a9c-555849ca00801'
            ],
        ];
    }

    public function validIdCreateNullableParamsDataProvider(): array
    {
        return [
            [
                null,
                null,
            ],
            [
                '',
                null,
            ],
        ];
    }
}
