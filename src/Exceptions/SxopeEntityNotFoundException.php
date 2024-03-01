<?php

declare(strict_types=1);

namespace FKS\Exceptions;

use Google\Cloud\Spanner\Bytes;
use FKS\Contracts\FKSNotFoundExceptionInterface;
use FKS\ValueObjects\Id;

class FKSEntityNotFoundException extends \Exception implements FKSNotFoundExceptionInterface
{
    public function __construct(string $type, $id, $propertyToFind = 'id')
    {
        if ($id instanceof Bytes) {
            $id = Id::create($id)->getUuid();
        } elseif ($id instanceof \Stringable) {
            $id = (string) $id;
        }

        parent::__construct("Entity with $propertyToFind $id not found", 404);
    }
}
