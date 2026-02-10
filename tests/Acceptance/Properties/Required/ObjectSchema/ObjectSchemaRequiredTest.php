<?php

namespace Tests\Acceptance\Properties\Required\ObjectSchema;

use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Zerotoprod\DataModelAdapterSwagger\Swagger;

class ObjectSchemaRequiredTest extends TestCase
{
    #[Test] public function required_property_is_marked_required(): void
    {
        $Components = Swagger::adapt(json_decode(file_get_contents(__DIR__.'/schema.json'), true));

        self::assertTrue($Components->Models['User']->properties['name']->required);
    }

    #[Test] public function non_required_property_is_not_marked_required(): void
    {
        $Components = Swagger::adapt(json_decode(file_get_contents(__DIR__.'/schema.json'), true));

        self::assertFalse($Components->Models['User']->properties['age']->required);
    }
}
