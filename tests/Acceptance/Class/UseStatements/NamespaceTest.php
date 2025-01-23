<?php

namespace Tests\Acceptance\Class\UseStatements;

use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Zerotoprod\DataModelAdapterSwagger\Swagger;
use Zerotoprod\DataModelGenerator\Engine;
use Zerotoprod\DataModelGenerator\Models\Config;
use Zerotoprod\DataModelGenerator\Models\ModelConfig;

class NamespaceTest extends TestCase
{
    #[Test] public function generate(): void
    {
        Engine::generate(
            Swagger::adapt(file_get_contents(__DIR__.'/schema.json')),
            Config::from([
                Config::model => [
                    ModelConfig::directory => self::$test_dir,
                    ModelConfig::use_statements => ['use \\Zerotoprod\\DataModel\\DataModel;']
                ]
            ])
        );

        self::assertStringEqualsFile(
            expectedFile: self::$test_dir.'/User.php',
            actualString: <<<PHP
                <?php
                class User
                {
                use \Zerotoprod\DataModel\DataModel;
                }
                PHP
        );
    }
}