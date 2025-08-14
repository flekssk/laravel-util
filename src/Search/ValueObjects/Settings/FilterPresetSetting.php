<?php

declare(strict_types=1);

namespace FKS\Search\ValueObjects\Settings;

use Illuminate\Validation\Rule;
use phpseclib3\Math\BigInteger\Engines\PHP\Reductions\Classic;

class FilterPresetSetting extends SearchSetting
{
    /**
     * @param array<string>|array<\BackedEnum>|class-string<\BackedEnum> $availablePresets
     * @param array<string>|array<\BackedEnum> $presets
     */
    public function __construct(private array|string $availablePresets, private array $presets = []) {}

    public static function getName(): string
    {
        return 'filter_preset';
    }

    public function validationRules(string $prefix = ''): array
    {
        $availablePresetsValidation = is_string($this->getAvailablePresets())
            ? Rule::enum($this->getAvailablePresets())
            : Rule::in($this->getAvailablePresets());

        return [
            "$prefix.filter_preset" => 'array',
            "$prefix.filter_preset.*" => [
                'string',
                $availablePresetsValidation
            ]
        ];
    }

    public function addPreset(string|\BackedEnum $preset): static
    {
        if (!in_array($preset, $this->presets)) {
            $this->presets[] = $preset;
        }

        return $this;
    }

    public function getPresets(): array
    {
        return [];
    }

    public function hasPreset(string|\BackedEnum $preset): bool
    {
        return in_array($preset, $this->presets, true);
    }

    public function merge(FilterPresetSetting $other): static
    {
        $this->presets = array_unique(array_merge($this->presets, $other->presets));

        return $this;
    }

    /**
     * @return array|class-string<\BackedEnum>
     */
    public function getAvailablePresets(): array|string
    {
        if (is_string($this->availablePresets) && !is_a($this->availablePresets, \BackedEnum::class, true)) {
            throw new \Exception('Invalid available presets');
        }

        return $this->availablePresets;
    }
}
