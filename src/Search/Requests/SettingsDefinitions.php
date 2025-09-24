<?php

declare(strict_types=1);

namespace FKS\Search\Requests;

use Illuminate\Contracts\Support\Arrayable;
use FKS\Search\ValueObjects\Settings\FilterPresetSetting;
use FKS\Search\ValueObjects\Settings\SearchSetting;

class SettingsDefinitions implements \Iterator, Arrayable
{
    /**
     * @var SearchSetting[]
     */
    private array $definitions = [];
    private int $position = 0;

    public function get(string $name): ?SearchSetting
    {
        return match ($name) {
            FilterPresetSetting::getName() => $this->getFilterPresetSetting(),
            default => null,
        };
    }

    public function validationRules(): array
    {
        $settingsRules = [];

        foreach ($this->definitions as $definition) {
            $settingsRules[] = $definition->validationRules('settings');
        }

        return [
            'settings' => 'array',
            ...$settingsRules
        ];
    }

    public function filterPreset(array|\BackedEnum $presets): SettingsDefinitions
    {
        $added = false;
        foreach ($this->definitions as $index => $definition) {
            if ($definition instanceof FilterPresetSetting) {
                $this->definitions[$index] = $definition->merge($presets);
                $added = true;
            }
        }

        if (!$added) {
            $this->definitions[] = new FilterPresetSetting($presets);
        }

        return $this;
    }

    public function getFilterPresetSetting(): ?FilterPresetSetting
    {
        $setting = null;

        foreach ($this->definitions as $index => $definition) {
            if ($definition instanceof FilterPresetSetting) {
                $setting = $definition->merge($presets);
            }
        }

        return $setting;
    }

    public function current(): mixed
    {
        return $this->definitions[$this->position];
    }

    public function next(): void
    {
        $this->position++;
    }

    public function key(): int
    {
        return $this->position;
    }

    public function valid(): bool
    {
        return isset($this->definitions[$this->position]);
    }

    public function rewind(): void
    {
        $this->position = 0;
    }

    public function toArray(): array
    {
        return $this->definitions;
    }
}
