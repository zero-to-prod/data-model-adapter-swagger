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
                        static function (string $property_name, Schema $PropertySchema) use ($name, $inline_object, $Swagger) {
                            $propertyData = [
                                Property::attributes => [],
                                Property::comment => null,
                                Property::types => null,
                            ];

                            $comment = null;
                            if ($PropertySchema->ref && $Swagger->definitions[basename($PropertySchema->ref)]->enum) {
                                $types = [basename($PropertySchema->ref).'Enum'];
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

                            if (($PropertySchema->items?->ref && $PropertySchema->type === 'array') || $Swagger->definitions[basename($PropertySchema->ref)]->type === 'array') {
                                if ($Swagger->definitions[basename($PropertySchema->ref)]->type === 'array' && $Swagger->definitions[basename($PropertySchema->ref)]->items->ref) {
                                    $types = ['array'];
                                    $class = Classname::generate(basename($Swagger->definitions[basename($PropertySchema->ref)]->items->ref));
                                } else {
                                    $class = Classname::generate(basename($PropertySchema->items->ref));
                                }
                                if ($Swagger->definitions[$class]->enum) {
                                    $class .= 'Enum';
                                }
                                if ($Swagger->definitions[basename($PropertySchema->ref)]->type === 'array' && $Swagger->definitions[basename($PropertySchema->ref)]->items?->type === 'object') {
                                    $class = Classname::generate(basename($PropertySchema->ref)) . 'Item';
                                }
                                $propertyData[Property::attributes] = [
                                    "#[\\Zerotoprod\\DataModel\\Describe(['cast' => [\\Zerotoprod\\DataModelHelper\\DataModelHelper::class, 'mapOf'], 'type' => $class::class])]"
                                ];

                                $doc_block_parts = [];
                                if ($PropertySchema->description) {
                                    $doc_block_parts[] = $PropertySchema->description;
                                }
                                $doc_block_parts[] = "@var array<int|string, $class>";

                                $comment = "/** \n * ".implode("\n * ", $doc_block_parts)."\n */";
                            }

                            $propertyData[Property::comment] = $comment;
                            $propertyData[Property::types] = $types;

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