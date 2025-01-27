<?php

namespace Zerotoprod\DataModelAdapterSwagger;

use Zerotoprod\DataModelAdapterSwagger\Resolvers\PropertyTypeResolver;
use Zerotoprod\DataModelGenerator\Models\BackedEnumType;
use Zerotoprod\DataModelGenerator\Models\Components;
use Zerotoprod\DataModelGenerator\Models\Constant;
use Zerotoprod\DataModelGenerator\Models\Enum;
use Zerotoprod\DataModelGenerator\Models\EnumCase;
use Zerotoprod\DataModelGenerator\Models\Model;
use Zerotoprod\DataModelGenerator\Models\Property;
use Zerotoprod\DataModelSwagger\Schema;
use Zerotoprod\DataModelSwagger\Swagger as SwaggerModel;
use Zerotoprod\Psr4Classname\Classname;

class Swagger
{
    public static function adapt(array $swagger_schema): Components
    {
        $Swagger = SwaggerModel::from($swagger_schema);
        $Models = [];
        $Enums = [];

        foreach ($Swagger->definitions as $name => $Schema) {
            if ($Schema->type === 'string' && !$Schema->enum) {
                continue;
            }
            if ($Schema->type === 'array' && $Schema->items->ref) {
                continue;
            }
            if ($Schema->type === 'array' && $Schema->items->type === 'string') {
                continue;
            }
            if ($Schema->type === 'string' && $Schema->enum) {
                $Enums[$name] = [
                    Enum::comment => $Schema->description ? "/** $Schema->description */" : null,
                    Enum::filename => Classname::generate($name, 'Enum.php'),
                    Enum::backed_type => BackedEnumType::string,
                    Enum::cases => array_map(
                        static fn($value) => [
                            EnumCase::name => $value,
                            EnumCase::value => "'$value'"
                        ],
                        $Schema->enum
                    ),
                ];
                continue;
            }
            $constants = [];
            $Properties = $Schema->type === 'array'
                ? $Schema->items->properties
                : $Schema->properties;

            foreach ($Properties as $property_name => $Property) {
                $comment = $Property->description
                    ?
                    <<<PHP
                        /**
                         * $Property->description
                         *
                         * @see $$property_name
                         */
                        PHP
                    : <<<PHP
                        /** @see $$property_name */
                        PHP;

                $constants[$property_name] = [
                    Constant::comment => $comment,
                    Constant::value => "'$property_name'",
                    Constant::type => 'string'
                ];
            }
            $inline_object = $Schema->type === 'array' && $Schema->items?->type === 'object';
            if ($inline_object) {
                $name .= 'Item';
            }
            $Models[$name] = [
                Model::comment => $Schema->description ?
                    <<<PHP
                    /** 
                     * $Schema->description 
                     */
                    PHP
                    : null,
                Model::filename => Classname::generate($name, '.php'),
                Model::constants => $constants,
                Model::properties => array_combine(
                    array_keys($Properties),
                    array_map(
                        static function (string $property_name, Schema $PropertySchema) use ($Swagger) {
                            $propertyData = [
                                Property::attributes => [],
                                Property::comment => null,
                                Property::types => null,
                            ];

                            $comment = null;
                            $attributes = null;
                            if ($PropertySchema->ref && $Swagger->definitions[basename($PropertySchema->ref)]->enum) {
                                $types = [basename($PropertySchema->ref).'Enum'];
                            } elseif ($PropertySchema->ref && $Swagger->definitions[basename($PropertySchema->ref)]->type === 'array' && $Swagger->definitions[basename($PropertySchema->ref)]->items->type === 'object') {
                                $types = ['array'];
                            } elseif ($PropertySchema->ref && $Swagger->definitions[basename($PropertySchema->ref)]->type === 'array') {
                                $types = [basename($PropertySchema->ref)];
                            } elseif ($PropertySchema->ref && $Swagger->definitions[basename($PropertySchema->ref)]->type !== 'object' && $Swagger->definitions[basename($PropertySchema->ref)]) {
                                $types = PropertyTypeResolver::resolve($Swagger->definitions[basename($PropertySchema->ref)]);
                            } else {
                                $types = PropertyTypeResolver::resolve($PropertySchema);
                            }

                            if ($PropertySchema->ref && $Swagger->definitions[basename($PropertySchema->ref)]->description) {
                                $comment = "/** {$Swagger->definitions[basename($PropertySchema->ref)]->description} */";
                            }

                            if (!$propertyData[Property::comment] && isset($PropertySchema->description)) {
                                $comment = "/** $PropertySchema->description */";
                            }

                            if (
                                ($PropertySchema->items?->ref && $PropertySchema->type === 'array')
                                || ($PropertySchema->ref && basename($PropertySchema->ref) && isset($Swagger->definitions[basename($PropertySchema->ref)]) && $Swagger->definitions[basename($PropertySchema->ref)]->type === 'array' && $Swagger->definitions[basename($PropertySchema->ref)]->items->type !== 'string')
                            ) {
                                $class = null;
                                if ($PropertySchema->ref && isset($Swagger->definitions[basename($PropertySchema->ref)]) && $Swagger->definitions[basename($PropertySchema->ref)]->type === 'array' && $Swagger->definitions[basename($PropertySchema->ref)]->items->ref) {
                                    $types = ['array'];
                                    $class = Classname::generate(basename($Swagger->definitions[basename($PropertySchema->ref)]->items->ref));
                                } else {
                                    if ($PropertySchema->items?->ref) {
                                        $class = Classname::generate(basename($PropertySchema->items?->ref));
                                    }
                                }
                                if (isset($Swagger->definitions[$class]) && $Swagger->definitions[$class]->enum) {
                                    $class .= 'Enum';
                                }
                                if ($PropertySchema->ref && isset($Swagger->definitions[basename($PropertySchema->ref)]) && $Swagger->definitions[basename($PropertySchema->ref)]->type === 'array' && $Swagger->definitions[basename($PropertySchema->ref)]->items?->type === 'object') {
                                    $class = Classname::generate(basename($PropertySchema->ref)).'Item';
                                }
                                $attributes = ["#[\\Zerotoprod\\DataModel\\Describe(['cast' => [\\Zerotoprod\\DataModelHelper\\DataModelHelper::class, 'mapOf'], 'type' => $class::class])]"];

                                $doc_block_parts = [];
                                if ($PropertySchema->description) {
                                    $doc_block_parts[] = $PropertySchema->description;
                                }
                                $doc_block_parts[] = "@var array<int|string, $class>";

                                $comment = "/** \n * ".implode("\n * ", $doc_block_parts)."\n */";
                            }

                            if ($PropertySchema->ref && isset($Swagger->definitions[basename($PropertySchema->ref)]) && $Swagger->definitions[basename($PropertySchema->ref)]->type === 'array' && $Swagger->definitions[basename($PropertySchema->ref)]->items->type === 'string') {
                                $types = PropertyTypeResolver::resolve($Swagger->definitions[basename($PropertySchema->ref)]->items);
                            }

                            if (isset($PropertySchema->ref)) {
                                $a = $Swagger->definitions[basename($PropertySchema->ref)];
                                if (isset($a->items->ref)) {
                                    $b = $Swagger->definitions[basename($a->items->ref)];
                                    if ($b->type !== 'object') {
                                        $types = ['array'];
                                        $type = implode('|',  PropertyTypeResolver::resolve($b));
                                        $comment = <<<PHP
                                        /**
                                         * $b->description
                                         * @var array<$type>
                                         */
                                        PHP;
                                        $attributes = null;
                                    }
                                }
                            }

                            $propertyData[Property::comment] = $comment;
                            $propertyData[Property::types] = $types;
                            $propertyData[Property::attributes] = $attributes;

                            return $propertyData;
                        },
                        array_keys($Properties),
                        $Properties
                    )
                ),
            ];
        }

        return Components::from([
            Components::Models => $Models,
            Components::Enums => $Enums
        ]);
    }
}