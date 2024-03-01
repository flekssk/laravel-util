<?php

declare(strict_types=1);

namespace Tests\Provisions\Models;

use Colopl\Spanner\Eloquent\Model;
use FKS\Services\Metadata\Models\Traits\HasMetadata;

class ModelHasMetadata extends Model
{
    use HasMetadata;

    protected $table = 'test';
    protected $primaryKey = 'test_id';
}