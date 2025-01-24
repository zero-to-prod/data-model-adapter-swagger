<?php

namespace Tests\Acceptance\Spapi\UpdateShipmentStatusRequest;

use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Zerotoprod\DataModelAdapterSwagger\Swagger;
use Zerotoprod\DataModelGenerator\Engine;
use Zerotoprod\DataModelGenerator\Models\Config;
use Zerotoprod\DataModelGenerator\Models\ConstantConfig;
use Zerotoprod\DataModelGenerator\Models\ModelConfig;
use Zerotoprod\DataModelGenerator\Models\PropertyConfig;

class UpdateShipmentStatusRequestTest extends TestCase
{
    #[Test] public function generate(): void
    {
        Engine::generate(
            Swagger::adapt(json_decode(file_get_contents(__DIR__.'/schema.json'), true)),
            Config::from([
                Config::model => [
                    ModelConfig::directory => self::$test_dir,
                    ModelConfig::namespace => 'Tests\\generated',
                    ModelConfig::use_statements => ['use \\Zerotoprod\\DataModel\\DataModel;'],
                    ModelConfig::comments => true,
                    ModelConfig::properties => [
                        PropertyConfig::comments => true
                    ],
                    ModelConfig::constants => [
                        ConstantConfig::comments => true
                    ]
                ]
            ])
        );

        self::assertStringEqualsFile(
            expectedFile: self::$test_dir.'/UpdateShipmentStatusRequest.php',
            actualString: <<<PHP
                <?php
                namespace Tests\generated;
                class UpdateShipmentStatusRequest
                {
                use \Zerotoprod\DataModel\DataModel;
                /** @see \$marketplaceId */
                public const marketplaceId = 'marketplaceId';
                /** @see \$shipmentStatus */
                public const shipmentStatus = 'shipmentStatus';
                /** @see \$orderItems */
                public const orderItems = 'orderItems';
                /**
                 * description
                 *
                 * @see \$OtherAttributes
                 */
                public const OtherAttributes = 'OtherAttributes';
                /**
                 * description
                 *
                 * @see \$PaymentExecutionDetail
                 */
                public const PaymentExecutionDetail = 'PaymentExecutionDetail';
                /**
                 * A list of payment methods for the order.
                 *
                 * @see \$PaymentMethodDetails
                 */
                public const PaymentMethodDetails = 'PaymentMethodDetails';
                /**
                 * The list of transparency codes.
                 *
                 * @see \$transparencyCodes
                 */
                public const transparencyCodes = 'transparencyCodes';
                /** description */
                public string \$marketplaceId;
                /** description */
                public ShipmentStatusEnum \$shipmentStatus;
                /** 
                 * @var array<int|string, OrderItemsItem>
                 */
                #[\Zerotoprod\DataModel\Describe(['cast' => [\Zerotoprod\DataModelHelper\DataModelHelper::class, 'mapOf'], 'type' => OrderItemsItem::class])]
                public array \$orderItems;
                /** 
                 * description
                 * @var array<int|string, OtherDeliveryAttributesEnum>
                 */
                #[\Zerotoprod\DataModel\Describe(['cast' => [\Zerotoprod\DataModelHelper\DataModelHelper::class, 'mapOf'], 'type' => OtherDeliveryAttributesEnum::class])]
                public array \$OtherAttributes;
                /** 
                 * description
                 * @var array<int|string, PaymentExecutionDetailItem>
                 */
                #[\Zerotoprod\DataModel\Describe(['cast' => [\Zerotoprod\DataModelHelper\DataModelHelper::class, 'mapOf'], 'type' => PaymentExecutionDetailItem::class])]
                public array \$PaymentExecutionDetail;
                /** A list of payment methods for the order. */
                public string \$PaymentMethodDetails;
                /** 
                 * The list of transparency codes.
                 * @var array<int|string, TransparencyCode>
                 */
                #[\Zerotoprod\DataModel\Describe(['cast' => [\Zerotoprod\DataModelHelper\DataModelHelper::class, 'mapOf'], 'type' => TransparencyCode::class])]
                public array \$transparencyCodes;
                }
                PHP
        );
    }
}