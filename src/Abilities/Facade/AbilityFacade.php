<?php

declare(strict_types=1);

namespace FKS\Abilities\Facade;

use Illuminate\Support\Facades\Facade;
use RuntimeException;

abstract class AbilityFacade extends Facade
{
    private static string $currentClass;

    public static function getAbilities(): array
    {
        return [];
    }

    public static function getFacadeAccessor()
    {
        return static::$currentClass;
    }

    public static function __callStatic($method, $args)
    {
        foreach (static::getAbilities() as $className => $abilities) {
            if (in_array($method, $abilities)) {
                static::$currentClass = $className;
                break;
            }
        }

        $instance = static::getFacadeRoot();

        if (!$instance) {
            throw new RuntimeException('A facade root has not been set.');
        }

        return $instance->$method(...$args);
    }

}
