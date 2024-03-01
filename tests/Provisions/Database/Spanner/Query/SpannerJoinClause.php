<?php

namespace Tests\Provisions\Database\Spanner\Query;

use Tests\Provisions\Database\Spanner\Query\Concerns\AppliesAsAlias;
use Tests\Provisions\Database\Spanner\Query\Concerns\AppliesBatchMode;
use Tests\Provisions\Database\Spanner\Query\Concerns\AppliesForceJoinOrder;
use Tests\Provisions\Database\Spanner\Query\Concerns\AppliesGroupByScanOptimization;
use Tests\Provisions\Database\Spanner\Query\Concerns\AppliesHashJoinBuildSide;
use Tests\Provisions\Database\Spanner\Query\Concerns\AppliesJoinMethod;
use Colopl\Spanner\Query\Concerns\AppliesForceIndex;
use Illuminate\Database\Query\JoinClause;

/**
 * Support api-data helpers
 */
class SpannerJoinClause extends JoinClause
{
    use AppliesAsAlias;
    use AppliesBatchMode;
    use AppliesGroupByScanOptimization;
    use AppliesForceJoinOrder;
    use AppliesHashJoinBuildSide;
    use AppliesJoinMethod;

    public const JOIN_METHOD_HASH_JOIN = 'HASH_JOIN';
    public const JOIN_METHOD_APPLY_JOIN = 'APPLY_JOIN';

    public const HASH_JOIN_BUILD_SIDE_LEFT = 'BUILD_LEFT';
    public const HASH_JOIN_BUILD_SIDE_RIGHT = 'BUILD_RIGHT';
}
