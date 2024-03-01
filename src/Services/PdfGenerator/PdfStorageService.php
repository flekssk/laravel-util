<?php

declare(strict_types=1);

namespace FKS\Services\PdfGenerator;

use Illuminate\Support\Facades\Storage;
use FKS\Providers\PdfGeneratorServiceProvider;
use Symfony\Component\HttpFoundation\StreamedResponse;
use function FKS\Services\config;

class PdfStorageService extends Storage
{
    /** @var string */
    private string $storageName;

    public function __construct()
    {
        $this->storageName = \config(PdfGeneratorServiceProvider::MODULE_CONFIG_KEY . '.storage');
    }

    /**
     * @param string $path
     * @param string|null $name
     * @return StreamedResponse
     */
    public function download(string $path, ?string $name): StreamedResponse
    {
        return Storage::disk($this->storageName)
            ->download($path, $name);
    }

    /**
     * @param string $path
     * @param string|null $content
     * @return void
     */
    public function put(string $path, ?string $content): void
    {
        Storage::disk($this->storageName)
            ->put($path, $content);
    }

    /**
     * @param string $path
     * @return string|null
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    public function get(string $path): ?string
    {
       return Storage::disk($this->storageName)
            ->get($path);
    }

    public function exists(string $path): bool
    {
        return Storage::disk($this->storageName)
            ->exists($path);
    }
}
