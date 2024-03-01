<?php

declare(strict_types=1);

namespace FKS\Providers;

use Illuminate\Support\ServiceProvider;

class PdfGeneratorServiceProvider extends ServiceProvider
{
    public const MODULE_CONFIG_KEY = 'pdf-generator';

    public function register(): void
    {
        $configPath = __DIR__ . '/../../config/pdf-generator.php';
        $this->mergeConfigFrom($configPath, self::MODULE_CONFIG_KEY);
    }
}
