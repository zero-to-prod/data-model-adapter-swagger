<?php

namespace Tests\Acceptance\Properties\InvalidPhpName\MergeDescribe;

use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Zerotoprod\DataModelAdapterSwagger\Swagger;
use Zerotoprod\DataModelGenerator\Engine;
use Zerotoprod\DataModelGenerator\Models\Config;
use Zerotoprod\DataModelGenerator\Models\ConstantConfig;
use Zerotoprod\DataModelGenerator\Models\ModelConfig;
use Zerotoprod\DataModelGenerator\Models\PropertyConfig;

class InvalidPhpNameMergeDescribeTest extends TestCase
{
    #[Test] public function merges_from_into_existing_describe_attribute(): void
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

        $expected = <<<'PHP'
            <?php
            namespace App\DataModels;
            class JsonWebKey
            {
            /** @see $x5c_chain */
            public const x5c_chain = 'x5c#chain';
            PHP;
        $expected .= "\n/** \n * @var array<int|string, Certificate>\n */\n";
        $expected .= <<<'PHP'
            #[\Zerotoprod\DataModel\Describe(['cast' => [\Zerotoprod\DataModelHelper\DataModelHelper::class, 'mapOf'], 'type' => Certificate::class, 'from' => self::x5c_chain])]
            public readonly array $x5c_chain;
            }
            PHP;

        self::assertStringEqualsFile(
            expectedFile: self::$test_dir.'/JsonWebKey.php',
            actualString: $expected
        );
    }
}
