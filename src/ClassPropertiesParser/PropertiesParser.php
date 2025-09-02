<?php

declare(strict_types=1);

namespace FKS\ClassPropertiesParser;

use Doctrine\Common\Annotations\PhpParser;
use PHPStan\PhpDocParser\ParserConfig;
use ReflectionClass;
use PHPStan\PhpDocParser\Lexer\Lexer;
use PHPStan\PhpDocParser\Parser\TypeParser;
use PHPStan\PhpDocParser\Parser\ConstExprParser;
use PHPStan\PhpDocParser\Parser\PhpDocParser;
use PHPStan\PhpDocParser\Parser\TokenIterator;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\ExtendsTagValueNode;
use PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode;
use PHPStan\PhpDocParser\Printer\Printer;
use DomainException;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class PropertiesParser
{
    public static function getProperties(string $classString): array
    {
        $reflection = new ReflectionClass($classString);
        $parser = new PhpParser();

        $properties = [];
        $useStatements = $parser->parseUseStatements($reflection);

        if (is_a($classString, Collection::class, true)) {
            $extends = PropertiesParser::parseExtendsGenerics($classString);
            $generic = null;
            if (isset($extends['generics'])) {
                $generic = count($extends['generics']) > 1 ? $extends['generics'][1] : $extends['generics'][0];
            }

            if (array_key_exists(strtolower($generic), $useStatements)) {
                $type = $useStatements[strtolower($generic)];
            }
            return [
                new ArrayClassProperty('items', $type ?? 'string', false),
            ];
        }

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
            } else {
                $properties[] = new ScalarClassProperty(
                    $property->getName(),
                    is_object($type) ? $type->getName() : $type,
                    $property->getType()->allowsNull(),
                );
            }
        }

        return $properties;
    }

    public static function parseExtendsGenerics(string $class): ?array
    {
        $ref = new ReflectionClass($class);
        $doc = $ref->getDocComment();
        if ($doc === false) {
            return null;
        }

        $parserConfig = new ParserConfig([]);
        $lexer = new Lexer($parserConfig);
        $constParser = new ConstExprParser($parserConfig);
        $phpDocParser = new PhpDocParser($parserConfig, new TypeParser($parserConfig, $constParser), new ConstExprParser($parserConfig));
        $tokens = new TokenIterator($lexer->tokenize($doc));
        $node = $phpDocParser->parse($tokens);
        $printer = new Printer();

        foreach ($node->children as $child) {
            if ($child instanceof PhpDocTagNode && $child->name === '@extends') {
                $value = $child->value;
                if (!$value instanceof ExtendsTagValueNode) {
                    continue;
                }

                $typeNode = $value->type;
                $baseType = $typeNode->type;
                $base = ltrim($baseType->name, '\\');

                $generics = [];
                foreach ($typeNode->genericTypes as $g) {
                    $generics[] = $g instanceof IdentifierTypeNode
                        ? ltrim($g->name, '\\')
                        : trim($printer->print($g));
                }

                return [
                    'base' => $base,
                    'generics' => $generics,
                ];
            }
        }

        return null;
    }
}
