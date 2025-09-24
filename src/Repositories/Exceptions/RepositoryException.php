<?php

declare(strict_types=1);

namespace FKS\Repositories\Exceptions;

use Exception;

final class RepositoryException extends Exception
{
    public static function invalidRepositoryEntityClass(string $repositoryClass, string $className): self
    {
        return new RepositoryException("Invalid entity class $className defined in $repositoryClass");
    }

    public static function invalidWhereExpression(string $type): self
    {
        return new RepositoryException("Invalid where expression, expected array or WhereCondition, got $type");
    }
}
