<?php

declare(strict_types=1);

namespace FKS\Abilities\Console\Commands;

use Illuminate\Console\Command;
use FKS\Abilities\AbilityService;

class GenerateAbilitiesFacadeCommand extends Command
{
    protected $signature = 'abilities:generate-facade';

    public function handle(AbilityService $abilityService): void
    {
        $abilityService->generateFacade();
    }
}
