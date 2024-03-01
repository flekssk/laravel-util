<?php

declare(strict_types=1);

namespace FKS\Exceptions;

final class FKSRepositoryException extends \Exception
{
    public static function invalidRepositoryEntityClass(string $repositoryClass, string $className): self
    {
        return new FKSRepositoryException("Invalid entity class $className defined in $repositoryClass");
    }

    public static function invalidWhereExpression(string $type): self
    {
        return new FKSRepositoryException("Invalid where expression, expected array or WhereCondition, got $type");
    }
}
