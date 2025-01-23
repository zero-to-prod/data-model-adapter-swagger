<?php

namespace Zerotoprod\DataModelAdapterSwagger\Resolvers;

use Zerotoprod\DataModelSwagger\Schema;
use Zerotoprod\Psr4Classname\Classname;

class PropertyTypeResolver
{
    public static function resolve(Schema $Schema, ?string $enum = null): array
    {
        if ($enum) {
            $types = [$enum];
        } else {
            $types = array_filter(
                array_map(
                    static fn(Schema $Schema) => self::resolveType($Schema),
                    array_merge(
                        [$Schema],
                        $Schema->oneOf ?? [],
                        $Schema->anyOf ?? []
                    )
                )
            );
        }

        return array_unique($types);
    }

    private static function resolveType(Schema $Schema): ?string
    {
        if ($Schema->ref) {
            return Classname::generate(basename($Schema->ref));
        }

        return $Schema->type;
    }
}