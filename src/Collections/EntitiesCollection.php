<?php

declare(strict_types=1);

namespace FKS\Collections;

use ArrayAccess;
use Illuminate\Support\Collection;
use IteratorAggregate;

/**
 * This collection is only for highlighting types which collection contains.
 *
 * Just use EntityClass to return hinting.
 * You can make @return EntitiesCollection<EntityClass> to make ide understanding which elements in collection.
 *
 * @template EntityClass as model class
 * @template-implements ArrayAccess<int, EntityClass>
 * @template-implements IteratorAggregate<int, EntityClass>
 *
 * @see https://psalm.dev/docs/annotating_code/templated_annotations/
 */
class EntitiesCollection extends Collection
{
    /**
     * @var array<mixed, EntityClass>
     */
    protected $items;

    /**
     * @return array<mixed, EntityClass>
     */
    public function all(): array
    {
        return parent::all();
    }

    /**
     * @param $key
     * @param $default
     * @return EntityClass|null
     */
    public function get($key, $default = null)
    {
        return parent::get($key, $default);
    }

    /**
     * @param callable|null $callback
     * @param null $default
     * @return EntityClass
     */
    public function first(callable $callback = null, $default = null)
    {
        return parent::first($callback, $default);
    }
}
