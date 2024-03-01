<?php

declare(strict_types=1);

namespace FKS\ValueObjects;

use Google\Cloud\Spanner\Bytes;
use Ramsey\Uuid\Uuid;
use FKS\Exceptions\FKSDomainException;

class Id extends Bytes
{
    public function getUuid(): string
    {
        return Uuid::fromBytes((string)$this->get())->toString();
    }

    public function getHex(): string
    {
        return preg_replace('/-/', '', $this->getUuid());
    }

    public function getHexadecimal(): string
    {
        $hex = $this->getHex();
        $result = "";

        for ($i = 0; $i < strlen($hex); $i++) {
            if ($i % 2 == 0) {
                $result .= "\x";
            }
            $result .= $hex[$i];
        }

        return 'b"' . $result . '"';
    }

    public static function createFromString(string $uuidOrHex): self
    {
        return new self(Uuid::fromString($uuidOrHex)->getBytes());
    }

    public static function createFromBytes(Bytes $bytes): self
    {
        return new self($bytes->get());
    }

    public static function create($value): self
    {
        if ($value instanceof self) {
            return $value;
        }

        if ($value instanceof Bytes) {
            return self::createFromBytes($value);
        }
        if (is_string($value)) {
            return self::createFromString($value);
        }

        throw new FKSDomainException(
            "Value not supported for Id creation. Passed: "
            . (is_object($value) ? $value::class : gettype($value))
            . " type instead Bytes or string"
        );
    }

    public static function batchCreate(array $data): array
    {
        return array_values(
            array_map(
                static fn ($id) => self::create($id),
                array_filter($data)
            )
        );
    }

    public static function mapArrayValueToHex(array $array, string $keyName = null): array
    {
        if ($keyName !== null) {
            return array_map(static fn ($element) => self::create($element[$keyName])->getHex(), $array);
        }

        return array_map(static fn ($element) => self::create($element)->getHex(), $array);
    }

    public static function mapArrayValueToUuid(array $array, string $keyName = null): array
    {
        if ($keyName !== null) {
            return array_map(static fn ($element) => self::create($element[$keyName])->getUuid(), $array);
        }

        return array_map(static fn ($element) => self::create($element)->getUuid(), $array);
    }

    public static function mapArrayValueToHexadecimal(array $array, string $keyName = null): array
    {
        if ($keyName !== null) {
            return array_map(static fn ($element) => self::create($element[$keyName])->getHexadecimal(), $array);
        }

        return array_map(static fn ($element) => self::create($element)->getHexadecimal(), $array);
    }

    public function equal(Bytes|Id $id): bool
    {
        if (get_class($id) === Bytes::class) {
            $id = self::create($id);
        }
        return $this->getUuid() === $id->getUuid();
    }

    public static function createNullable($value): ?self
    {
        return ($value === null || $value === "") ? null : self::create($value);
    }
}
