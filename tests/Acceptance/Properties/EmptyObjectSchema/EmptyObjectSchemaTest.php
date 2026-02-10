<?php

namespace Tests\Acceptance\Properties\EmptyObjectSchema;

use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Zerotoprod\DataModelAdapterSwagger\Swagger;
use Zerotoprod\DataModelGenerator\Engine;
use Zerotoprod\DataModelGenerator\Models\Config;
use Zerotoprod\DataModelGenerator\Models\ConstantConfig;
use Zerotoprod\DataModelGenerator\Models\ModelConfig;
use Zerotoprod\DataModelGenerator\Models\PropertyConfig;

class EmptyObjectSchemaTest extends TestCase
{
    #[Test] public function empty_object_schema_resolves_to_array_type(): void
    {
        Engine::generate(
            Swagger::adapt(json_decode(file_get_contents(__DIR__.'/schema.json'), true)),
            Config::from([
                Config::model => [
                    ModelConfig::directory => self::$test_dir,
                    ModelConfig::namespace => 'App\\DataModels',
                    ModelConfig::constants => [
                        ConstantConfig::comments => true
                    ],
                    ModelConfig::properties => [
                        PropertyConfig::readonly => true,
                        PropertyConfig::comments => true,
                    ]
                ]
            ])
        );

        self::assertStringEqualsFile(
            expectedFile: self::$test_dir.'/User.php',
            actualString: <<<PHP
                <?php
                namespace App\DataModels;
                class User
                {
                /** @see \$name */
                public const name = 'name';
                /** @see \$event */
                public const event = 'event';
                public readonly string \$name;
                /** Events that are bound to applications. */
                public readonly array \$event;
                }
                PHP
        );
    }
}
