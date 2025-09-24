<?php

declare(strict_types=1);

namespace FKS\Search\ValueObjects;

use Illuminate\Contracts\Database\Eloquent\Builder as BuilderContract;

class FiltersPreset
{
    public function __construct(
        private readonly string|\BackedEnum $name,
        private readonly \Closure $caller,
        private readonly ?\Closure $check = null,
    ) {}

    public function apply(SearchConditions $conditions, BuilderContract $builder)
    {
        if ($this->shouldBeApplyed($conditions, $builder)) {
            $this->caller($conditions, $builder);
        }
    }

    private function shouldBeApplyed(SearchConditions $conditions, BuilderContract $builder)
    {
        $presetSettings = $conditions->getSettingsDefinitions()->getFilterPresetSetting();

        if (
            ($presetSettings !== null && $presetSettings->hasPreset($this->name))
            || ($check !== null && $check($conditions, $builder))
        ) {
            return true;
        }

        return false;
    }
}
