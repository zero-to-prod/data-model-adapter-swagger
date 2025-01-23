<?php

namespace Tests\Acceptance\Properties\String\Value;

use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Zerotoprod\DataModelAdapterSwagger\Swagger;
use Zerotoprod\DataModelGenerator\Engine;
use Zerotoprod\DataModelGenerator\Models\Config;
use Zerotoprod\DataModelGenerator\Models\ModelConfig;
use Zerotoprod\DataModelGenerator\Models\PropertyConfig;

class PropertyStringTest extends TestCase
{
    #[Test] public function generate(): void
    {
        Engine::generate(
            Swagger::adapt(file_get_contents(__DIR__.'/schema.json')),
            Config::from([
                Config::model => [
                    ModelConfig::directory => self::$test_dir,
                    ModelConfig::properties => [
                        PropertyConfig::types => [
                            'integer' => 'string'
                        ]
                    ]
                ]
            ])
        );

        self::assertStringEqualsFile(
            expectedFile: self::$test_dir.'/User.php',
            actualString: <<<PHP
                <?php
                class User
                {
                public string \$name;
                public string \$age;
                }
                PHP
        );
    }
}