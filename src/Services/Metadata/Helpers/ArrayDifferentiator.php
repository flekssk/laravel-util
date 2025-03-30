<?php

namespace FKS\Services\Metadata\Helpers;

trait ArrayDifferentiator
{
    protected function areDifferent(string $oldValue, string $newValue): bool
    {
        $oldValueArray = $this->convertToArray($oldValue);
        $newValueArray = $this->convertToArray($newValue);

        if (!empty($oldValueArray) && !empty($newValueArray)) {
            if($this->isMultidimensional($oldValueArray)) {
                return $this->areMultidimensionalArraysDifferent($oldValueArray, $newValueArray);
            }

            if ($this->isAssociative($oldValueArray)) {
                return $this->areAssociativeArraysDifferent($oldValueArray, $newValueArray);
            }

            return $this->areSequentialArraysDifferent($oldValueArray, $newValueArray);
        }

        return $oldValue !== $newValue;
    }

    protected function areMultidimensionalArraysDifferent(array $oldValue, array $newValue): bool
    {
        if(count($oldValue, COUNT_RECURSIVE) !== count($newValue, COUNT_RECURSIVE)) {
            return true;
        }

        $oldValue = $this->sortMultidimensionalArray($oldValue);
        $newValue = $this->sortMultidimensionalArray($newValue);

        return json_encode($oldValue) !== json_encode($newValue);
    }

    protected function sortMultidimensionalArray(array $array): array
    {
        usort($array, function($a, $b) {
            return json_encode($a) <=> json_encode($b);
        });

        foreach ($array as $key => &$value) {
            ksort($value);
        }

        return $array;
    }

    protected function sortArray(array $array): array
    {
        if($this->isMultidimensional($array)) {
            return $this->sortMultidimensionalArray($array);
        }

        if(!$this->isAssociative($array)) {
            return $this->sortSequentialArray($array);
        }

        sort($array);
        return $array;
    }

    protected function sortSequentialArray(array $array): array
    {
        usort($array, function($a, $b) {
            return $a <=> $b;
        });

        return $array;
    }

    protected function areAssociativeArraysDifferent(array $oldValue, array $newValue): bool
    {
        foreach ($oldValue as $key => $value) {

            if ($value !== $newValue[$key]) {
                return true;
            }
        }
        return false;
    }

    protected function areSequentialArraysDifferent(array $oldValue, array $newValue): bool
    {
        $oldValue = $this->sortSequentialArray($oldValue);
        $newValue = $this->sortSequentialArray($newValue);

        return json_encode($oldValue) !== json_encode($newValue);
    }

    protected function isAssociative(array $value): bool
    {
        return array_keys($value) !== range(0, count($value) - 1);
    }

    protected function isMultidimensional(array $value): bool
    {
        return count($value) !== count($value, COUNT_RECURSIVE);
    }

    protected function convertToArray(string $value): array
    {
        $jsonParsed = json_decode($value, true);
        if(json_last_error() === JSON_ERROR_NONE) {
            if(!is_array($jsonParsed)) {
                return [];
            }
            return $jsonParsed;
        }
        $commaSeparatedValues = explode(',', $value);
        if (count($commaSeparatedValues) > 1) {
            return $commaSeparatedValues;
        }

        return [];
    }

}
