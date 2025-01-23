<?php

namespace Tests\Unit\VersionValidation\Correct;

use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Zerotoprod\DataModelAdapterSwagger\Swagger;
use Zerotoprod\DataModelGenerator\Models\Components;
use Zerotoprod\DataModelGenerator\Models\Config;

class AdaptTest extends TestCase
{
    #[Test] public function correct_version_validation(): void
    {
        self::assertTrue(
            is_a(
                object_or_class: Swagger::adapt(
                    file_get_contents(__DIR__.'/schema.json'),
                ),
                class: Components::class
            )
        );
    }
}