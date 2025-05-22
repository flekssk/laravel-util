<?php

declare(strict_types=1);

namespace FKS\ClassPropertiesParser;

use Doctrine\Common\Annotations\PhpParser;
use DomainException;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use ReflectionClass;

class PropertiesParser
{
    public static function getProperties(string $classString): array
    {
        $reflection = new ReflectionClass($classString);
        $parser = new PhpParser();

        $properties = [];
        $useStatements = $parser->parseUseStatements($reflection);

        $constructorAnnotationProperties = [];
        $constructor = $reflection->getMethod('__construct');

        if ($constructor->getDocComment() !== false) {
            preg_match_all('/@([a-z]+?)\s+(.*?)\n/i', $constructor->getDocComment(), $constructorAnnotations);

            if (array_key_exists(0, $constructorAnnotations)) {
                for ($i = 0; $i < count($constructorAnnotations[0]); $i++) {
                    if ($constructorAnnotations[1][$i] === 'param') {
                        [$type, $propertyName] = explode(' ', $constructorAnnotations[2][$i]);
                        $propertyName = str_replace('$', '', $propertyName);

                        $propertyIsArray = false;
                        $isNullable = false;
                        if (Str::contains($type, '[]')) {
                            $propertyIsArray = true;
                            $type = str_replace('[]', '', $type);

                            $availableTypes = explode('|', $type);

                            foreach ($availableTypes as $index => $availableType) {
                                if ($availableType === 'null') {
                                    $isNullable = true;
                                    unset($availableTypes[$index]);
                                }
                            }

                            if (count($availableTypes) > 1) {
                                $types = implode(', ', $availableTypes);
                                throw new DomainException( "Serializer can`t chose type of $propertyName. Find more that one type $types");
                            }

                            $type = array_pop($availableTypes);
                        }

                        if (array_key_exists(strtolower($type), $useStatements)) {
                            $type = $useStatements[strtolower($type)];
                        } else {
                            $classParts = explode('\\', $classString);
                            array_pop($classParts);
                            $classParts[] = $type;
                            $type = implode('\\', $classParts);
                        }

                        if ($propertyIsArray) {
                            $constructorAnnotationProperties[$propertyName] = new ArrayClassProperty($propertyName, $type, $isNullable);
                        } else {
                            $constructorAnnotationProperties[$propertyName] = new ObjectClassProperty($propertyName, $type, $isNullable);
                        }
                    }
                }
            }
        }

        foreach ($reflection->getProperties() as $property) {
            $propertyIsObject = false;
            $propertyIsArray = false;
            foreach (explode('|', (string) $property->getType()) as $propertyType) {
                $type = $property->getType();
                if (in_array($property->getType(), ['mixed', 'array', '?array']) && $property->getDocComment() !== false) {
                    preg_match_all('/@([a-z]+?)\s+(.*?)\n/i', $property->getDocComment(), $annotations);

                    if (!isset($annotations[1]) or count($annotations[1]) == 0) {
                        continue;
                    }

                    if ($annotations[1][0] === 'var') {
                        $type = $annotations[2][0];
                        if (Str::contains($type, '[]')) {
                            $type = str_replace('[]', '', $propertyType);
                            $propertyIsArray = true;
                        }
                        if (Str::contains($type, '\\')) {
                            $propertyIsObject = true;
                        } elseif (array_key_exists(strtolower($type), $useStatements)) {
                            $propertyIsObject = true;
                            $type = $useStatements[strtolower($type)];
                        }
                    }
                }
            }
            if (isset($constructorAnnotationProperties[$property->name])) {
                $properties[] = $constructorAnnotationProperties[$property->name];
            } elseif ($propertyIsObject && $propertyIsArray) {
                $properties[] = new ArrayClassProperty($property->getName(),  is_object($type) ? $type->getName() : $type);
            } elseif ($propertyIsObject) {
                $properties[] = new ObjectClassProperty($property->getName(),  is_object($type) ? $type->getName() : $type);
            } elseif ($property->class === Collection::class) {
                $properties[] = new ScalarClassProperty($property->getName(), 'array', false);
            } else {
                $properties[] = new ScalarClassProperty(
                    $property->getName(),
                    is_object($type) ? $type->getName() : $type,
                    $property->getType()?->allowsNull() ?? false,
                );
            }
        }

        return $properties;
    }

    public static function getConstructorPropertiesNames(string $classString): array
    {
        $reflection = new ReflectionClass($classString);
        $constructor = $reflection->getMethod('__construct');

        return array_map(static fn ($param) => $param->name, $constructor->getParameters());
    }
}
