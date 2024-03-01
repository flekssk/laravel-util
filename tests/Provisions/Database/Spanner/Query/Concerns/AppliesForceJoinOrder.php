<?php

namespace Tests\Provisions\Database\Spanner\Query\Concerns;

trait AppliesForceJoinOrder
{
    /**
     * @var ?bool
     */
    public $forceJoinOrder;

    /**
     * @param ?bool $forceJoinOrder
     * @return $this
     */
    public function forceJoinOrder(?bool $forceJoinOrder)
    {
        $this->forceJoinOrder = $forceJoinOrder;

        return $this;
    }
}
