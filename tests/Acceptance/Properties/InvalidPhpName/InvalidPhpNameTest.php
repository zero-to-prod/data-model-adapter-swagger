<?php

namespace Tests\Acceptance\Properties\InvalidPhpName;

use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Zerotoprod\DataModelAdapterSwagger\Swagger;
use Zerotoprod\DataModelGenerator\Engine;
use Zerotoprod\DataModelGenerator\Models\Config;
use Zerotoprod\DataModelGenerator\Models\ConstantConfig;
use Zerotoprod\DataModelGenerator\Models\ModelConfig;
use Zerotoprod\DataModelGenerator\Models\PropertyConfig;

class InvalidPhpNameTest extends TestCase
{
    #[Test] public function sanitizes_invalid_php_property_names(): void
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
                        PropertyConfig::readonly => true
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
                /** @see \$kid */
                public const kid = 'kid';
                /** @see \$x5t_S256 */
                public const x5t_S256 = 'x5t#S256';
                public readonly string \$kid;
                #[\Zerotoprod\DataModel\Describe(['from' => self::x5t_S256])]
                public readonly string \$x5t_S256;
                }
                PHP
        );
    }
}
