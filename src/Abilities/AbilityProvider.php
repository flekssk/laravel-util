<?php

declare(strict_types=1);

namespace FKS\Abilities;

use Illuminate\Support\ServiceProvider;
use FKS\Abilities\Console\Commands\GenerateAbilitiesFacadeCommand;
use FKS\Abilities\Contracts\AbilitiesResolverInterface;
use FKS\Abilities\ValueObjects\AbilitiesConfig;
use FKS\Serializer\SerializerFacade;
use Symfony\Component\Finder\Finder;

class AbilityProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton(
            AbilityService::class,
            function () {
                $finder = new Finder();
                $finder->files()->in(app_path())->name('*.php');

                $interfaceName = AbilitiesResolverInterface::class;

                foreach ($finder as $file) {
                    $content = file_get_contents($file->getRealPath());

                    // Извлекаем namespace и имя класса
                    preg_match('/namespace\s+([^;]+)/i', $content, $namespaceMatches);
                    preg_match('/class\s+(\w+)/i', $content, $classMatches);

                    if (isset($namespaceMatches[1]) && isset($classMatches[1])) {
                        $namespace = trim($namespaceMatches[1]);
                        $className = trim($classMatches[1]);
                        $fullClassName = $namespace . '\\' . $className;

                        if (class_exists($fullClassName)) {
                            if (in_array($interfaceName, class_implements($fullClassName), true)) {
                                $implementingClasses[] = $this->app->make($fullClassName);
                            }
                        }
                    }
                }

                return new AbilityService(
                    $implementingClasses,
                    SerializerFacade::deserializeFromArray(config('abilities', []), AbilitiesConfig::class),
                );
            },
        );
    }

    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                GenerateAbilitiesFacadeCommand::class,
            ]);
        }

        $this->mergeConfigFrom(
            __DIR__ . '/../../config/abilities.php', 'abilities'
        );
    }
}
