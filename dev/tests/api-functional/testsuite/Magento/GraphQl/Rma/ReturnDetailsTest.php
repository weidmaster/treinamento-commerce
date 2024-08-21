<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\GraphQl\Rma;

use Magento\Catalog\Test\Fixture\Product as ProductFixture;
use Magento\Checkout\Test\Fixture\PlaceOrder as PlaceOrderFixture;
use Magento\Checkout\Test\Fixture\SetBillingAddress as SetBillingAddressFixture;
use Magento\Checkout\Test\Fixture\SetDeliveryMethod as SetDeliveryMethodFixture;
use Magento\Checkout\Test\Fixture\SetGuestEmail as SetGuestEmailFixture;
use Magento\Checkout\Test\Fixture\SetPaymentMethod as SetPaymentMethodFixture;
use Magento\Checkout\Test\Fixture\SetShippingAddress as SetShippingAddressFixture;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Test\Fixture\Customer as CustomerFixture;
use Magento\Eav\Api\Data\AttributeOptionInterface;
use Magento\Eav\Model\Entity\Attribute\Backend\ArrayBackend;
use Magento\Eav\Model\Entity\Attribute\Source\Table;
use Magento\Eav\Test\Fixture\AttributeOption as AttributeOptionFixture;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\Uid;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\GraphQl\GetCustomerAuthenticationHeader;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Test\Fixture\AddProductToCart as AddProductToCartFixture;
use Magento\Quote\Test\Fixture\CustomerCart as CustomerCartFixture;
use Magento\Rma\Api\Data\CommentInterface;
use Magento\Rma\Api\Data\RmaInterface;
use Magento\Rma\Api\Data\TrackInterface;
use Magento\Rma\Api\RmaAttributesManagementInterface;
use Magento\Rma\Api\RmaRepositoryInterface;
use Magento\Rma\Model\Rma;
use Magento\Rma\Model\Rma\Source\Status;
use Magento\Rma\Test\Fixture\Rma as RmaFixture;
use Magento\Rma\Test\Fixture\RmaItem as RmaItemFixture;
use Magento\Rma\Test\Fixture\RmaItemAttribute as RmaItemAttributeFixture;
use Magento\RmaGraphQl\Helper\Data;
use Magento\Sales\Test\Fixture\Invoice as InvoiceFixture;
use Magento\Sales\Test\Fixture\Shipment as ShipmentFixture;
use Magento\Store\Model\StoreManagerInterface;
use Magento\TestFramework\Fixture\Config;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\GraphQlAbstract;

/**
 * Test for return details
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ReturnDetailsTest extends GraphQlAbstract
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var Uid
     */
    private $idEncoder;

    /**
     * @var GetCustomerAuthenticationHeader
     */
    private $getCustomerAuthenticationHeader;

    /**
     * @var RmaRepositoryInterface
     */
    private $rmaRepository;

    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var Data
     */
    private $helper;

    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * Setup
     */
    public function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->getCustomerAuthenticationHeader = $this->objectManager->get(GetCustomerAuthenticationHeader::class);
        $this->rmaRepository = $this->objectManager->get(RmaRepositoryInterface::class);
        $this->idEncoder = $this->objectManager->get(Uid::class);
        $this->customerRepository = $this->objectManager->get(CustomerRepositoryInterface::class);
        $this->searchCriteriaBuilder = $this->objectManager->get(SearchCriteriaBuilder::class);
        $this->storeManager = $this->objectManager->get(StoreManagerInterface::class);
        $this->helper = $this->objectManager->get(Data::class);
        $this->serializer = $this->objectManager->get(SerializerInterface::class);
    }

    /**
     * Test return details with RMA address
     *
     * @magentoConfigFixture sales/magento_rma/enabled 1
     * @magentoConfigFixture sales/magento_rma/use_store_address 0
     * @magentoConfigFixture sales/magento_rma/store_name test
     * @magentoConfigFixture sales/magento_rma/address street
     * @magentoConfigFixture sales/magento_rma/address1 1
     * @magentoConfigFixture sales/magento_rma/region_id wrong region
     * @magentoConfigFixture sales/magento_rma/city Montgomery
     * @magentoConfigFixture sales/magento_rma/zip 12345
     * @magentoConfigFixture sales/magento_rma/country_id US
     * @magentoApiDataFixture Magento/Rma/_files/rma_with_customer.php
     */
    public function testReturnDetailsWithRmaAddress()
    {
        $customerEmail = 'customer_uk_address@test.com';
        $rma = $this->getCustomerReturn($customerEmail);
        $uid = $this->idEncoder->encode((string)$rma->getEntityId());

        $query = <<<QUERY
{
  customer {
    return(uid: "{$uid}") {
      number
      order {
        number
      }
      created_at
      customer{firstname lastname email}
      status
      comments {
        uid
        text
        created_at
        author_name
      }
      items {
        uid
        order_item {
          product_sku
          product_name
        }
        custom_attributes {
          uid
          label
          value
        }
        request_quantity
        quantity
        status
      }
      shipping {
        tracking {
          uid
          carrier {
            uid
            label
          }
          tracking_number
        }
        address {
          contact_name
          street
          city
          region {
            code
          }
          country {
            full_name_english
          }
          postcode
          telephone
        }
      }
    }
  }
}
QUERY;

        $response = $this->graphQlQuery(
            $query,
            [],
            '',
            $this->getCustomerAuthenticationHeader->execute($customerEmail, 'password')
        );

        $customer = $this->customerRepository->get($customerEmail);
        $comment = current($rma->getComments());
        $track = current($rma->getTracks());

        self::assertEquals($rma->getIncrementId(), $response['customer']['return']['number']);
        self::assertEquals('100000555', $response['customer']['return']['order']['number']);
        self::assertEquals($rma->getDateRequested(), $response['customer']['return']['created_at']);
        self::assertEquals(
            $customer->getFirstname(),
            $response['customer']['return']['customer']['firstname']
        );
        self::assertEquals(
            $customer->getLastname(),
            $response['customer']['return']['customer']['lastname']
        );
        self::assertEquals(
            $customer->getEmail(),
            $response['customer']['return']['customer']['email']
        );
        self::assertEqualsIgnoringCase(Status::STATE_APPROVED, $response['customer']['return']['status']);

        $this->assertComments($response, $comment);
        $this->assertItems($response);
        $this->assertTracking($response, $track);
        $this->assertAddress($response, false);
    }

    /**
     * Test return details with store address
     *
     * @magentoConfigFixture sales/magento_rma/enabled 1
     * @magentoConfigFixture sales/magento_rma/use_store_address 1
     * @magentoConfigFixture shipping/origin/name test
     * @magentoConfigFixture shipping/origin/street_line1 street
     * @magentoConfigFixture shipping/origin/street_line2 1
     * @magentoConfigFixture shipping/origin/city Montgomery
     * @magentoConfigFixture shipping/origin/region_id 1
     * @magentoConfigFixture shipping/origin/postcode 12345
     * @magentoConfigFixture shipping/origin/country_id US
     * @magentoApiDataFixture Magento/Rma/_files/rma_with_customer.php
     */
    public function testReturnDetailsWithStoreAddress()
    {
        $customerEmail = 'customer_uk_address@test.com';
        $rma = $this->getCustomerReturn($customerEmail);
        $uid = $this->idEncoder->encode((string)$rma->getEntityId());

        $query = <<<QUERY
{
  customer {
    return(uid: "{$uid}") {
      number
      order {
        number
      }
      created_at
      customer{firstname lastname email}
      status
      comments {
        uid
        text
        created_at
        author_name
      }
      items {
        uid
        order_item {
          product_sku
          product_name
        }
        custom_attributes {
          uid
          label
          value
        }
        request_quantity
        quantity
        status
      }
      shipping {
        tracking {
          uid
          carrier {
            uid
            label
          }
          tracking_number
        }
        address {
          contact_name
          street
          city
          region {
            code
          }
          country {
            full_name_english
          }
          postcode
          telephone
        }
      }
    }
  }
}
QUERY;

        $response = $this->graphQlQuery(
            $query,
            [],
            '',
            $this->getCustomerAuthenticationHeader->execute($customerEmail, 'password')
        );

        $customer = $this->customerRepository->get($customerEmail);
        $comment = current($rma->getComments());
        $track = current($rma->getTracks());

        self::assertEquals($rma->getIncrementId(), $response['customer']['return']['number']);
        self::assertEquals('100000555', $response['customer']['return']['order']['number']);
        self::assertEquals($rma->getDateRequested(), $response['customer']['return']['created_at']);
        self::assertEquals(
            $customer->getFirstname(),
            $response['customer']['return']['customer']['firstname']
        );
        self::assertEquals(
            $customer->getLastname(),
            $response['customer']['return']['customer']['lastname']
        );
        self::assertEquals(
            $customer->getEmail(),
            $response['customer']['return']['customer']['email']
        );
        self::assertEqualsIgnoringCase(Status::STATE_APPROVED, $response['customer']['return']['status']);

        $this->assertComments($response, $comment);
        $this->assertItems($response);
        $this->assertTracking($response, $track);
        $this->assertAddress($response, true);
    }

    /**
     * Test return details query with unauthorized customer
     *
     * @magentoConfigFixture default_store sales/magento_rma/enabled 1
     */
    public function testUnauthorized()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('The current customer isn\'t authorized.');

        $query = <<<QUERY
{
  customer {
    return(uid: "23as452gsa") {
      number
      order {
        number
      }
      created_at
      customer{firstname lastname email}
      status
      comments {
        uid
        text
        created_at
        author_name
      }
      items {
        uid
        order_item {
          product_sku
          product_name
        }
        custom_attributes {
          uid
          label
          value
        }
        request_quantity
        quantity
        status
      }
      shipping {
        tracking {
          uid
          carrier {
            label
          }
          tracking_number
        }
        address {
          contact_name
          street
          city
          region {
            code
          }
          country {
            full_name_english
          }
          postcode
          telephone
        }
      }
    }
  }
}
QUERY;

        $this->graphQlQuery($query);
    }

    /**
     * Test return details query with unauthorized customer
     *
     * @magentoApiDataFixture Magento/Rma/_files/rma_with_customer.php
     * @magentoConfigFixture default_store sales/magento_rma/enabled 0
     */
    public function testRmaDetailsWithDisabledRma()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('RMA is disabled.');

        $query = <<<QUERY
{
  customer {
    return(uid: "23as452gsa") {
      number
      order {
        number
      }
      created_at
      customer{firstname lastname email}
      status
      comments {
        uid
        text
        created_at
        author_name
      }
      items {
        uid
        order_item {
          product_sku
          product_name
        }
        custom_attributes {
          uid
          label
          value
        }
        request_quantity
        quantity
        status
      }
      shipping {
        tracking {
          uid
          carrier {
            label
          }
          tracking_number
        }
        address {
          contact_name
          street
          city
          region {
            code
          }
          country {
            full_name_english
          }
          postcode
          telephone
        }
      }
    }
  }
}
QUERY;

        $this->graphQlQuery(
            $query,
            [],
            '',
            $this->getCustomerAuthenticationHeader->execute('customer_uk_address@test.com', 'password')
        );
    }

    /**
     * Test return details query with not configured address
     *
     * @magentoConfigFixture default_store sales/magento_rma/enabled 1
     * @magentoApiDataFixture Magento/Rma/_files/rma_with_customer.php
     */
    public function testRmaDetailsWithoutConfiguredAddress()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Address for returns is not configured in admin.');

        $customerEmail = 'customer_uk_address@test.com';
        $rma = $this->getCustomerReturn($customerEmail);
        $uid = $this->idEncoder->encode((string)$rma->getEntityId());

        $query = <<<QUERY
{
  customer {
    return(uid: "{$uid}") {
      number
      order {
        number
      }
      created_at
      customer{firstname lastname email}
      status
      comments {
        uid
        text
        created_at
        author_name
      }
      items {
        uid
        order_item {
          product_sku
          product_name
        }
        custom_attributes {
          uid
          label
          value
        }
        request_quantity
        quantity
        status
      }
      shipping {
        tracking {
          uid
          carrier {
            uid
            label
          }
          tracking_number
        }
        address {
          contact_name
          street
          city
          region {
            code
          }
          country {
            full_name_english
          }
          postcode
          telephone
        }
      }
    }
  }
}
QUERY;

        $this->graphQlQuery(
            $query,
            [],
            '',
            $this->getCustomerAuthenticationHeader->execute($customerEmail, 'password')
        );
    }

    /**
     * Test return details query with not existing uid
     *
     * @magentoConfigFixture default_store sales/magento_rma/enabled 1
     * @magentoApiDataFixture Magento/Rma/_files/rma_with_customer.php
     */
    public function testWithNotExistingUid()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('You selected the wrong RMA.');

        $customerEmail = 'customer_uk_address@test.com';
        $rma = $this->getCustomerReturn($customerEmail);
        $rmaId = $rma->getEntityId() + 10;
        $uid = $this->idEncoder->encode((string)$rmaId);

        $query = <<<QUERY
{
  customer {
    return(uid: "{$uid}") {
      number
      order {
        number
      }
      created_at
      customer{firstname lastname email}
      status
      comments {
        uid
        text
        created_at
        author_name
      }
      items {
        uid
        order_item {
          product_sku
          product_name
        }
        custom_attributes {
          uid
          label
          value
        }
        request_quantity
        quantity
        status
      }
      shipping {
        tracking {
          uid
          carrier {
            uid
            label
          }
          tracking_number
        }
        address {
          contact_name
          street
          city
          region {
            code
          }
          country {
            full_name_english
          }
          postcode
          telephone
        }
      }
    }
  }
}
QUERY;

        $this->graphQlQuery(
            $query,
            [],
            '',
            $this->getCustomerAuthenticationHeader->execute($customerEmail, 'password')
        );
    }

    /**
     * Test return details query with not encoded uid
     *
     * @magentoConfigFixture default_store sales/magento_rma/enabled 1
     * @magentoApiDataFixture Magento/Rma/_files/rma_with_customer.php
     */
    public function testWithNotEncodedUid()
    {
        $customerEmail = 'customer_uk_address@test.com';
        $rma = $this->getCustomerReturn($customerEmail);
        $rmaId = $rma->getEntityId();

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage("Value of uid \"{$rmaId}\" is incorrect.");

        $query = <<<QUERY
{
  customer {
    return(uid: "{$rmaId}") {
      number
      order {
        number
      }
      created_at
      customer{firstname lastname email}
      status
      comments {
        uid
        text
        created_at
        author_name
      }
      items {
        uid
        order_item {
          product_sku
          product_name
        }
        custom_attributes {
          uid
          label
          value
        }
        request_quantity
        quantity
        status
      }
      shipping {
        tracking {
          uid
          carrier {
            uid
            label
          }
          tracking_number
        }
        address {
          contact_name
          street
          city
          region {
            code
          }
          country {
            full_name_english
          }
          postcode
          telephone
        }
      }
    }
  }
}
QUERY;

        $this->graphQlQuery(
            $query,
            [],
            '',
            $this->getCustomerAuthenticationHeader->execute($customerEmail, 'password')
        );
    }

    /**
     * Test return items with custom attributes
     */
    #[
        Config('sales/magento_rma/enabled', 1),
        Config('sales/magento_rma/use_store_address', 0),
        Config('sales/magento_rma/store_name', 'test'),
        Config('sales/magento_rma/address', 'street'),
        Config('sales/magento_rma/address1', 1),
        Config('sales/magento_rma/region_id', 'wrong region'),
        Config('sales/magento_rma/city', 'Montgomery'),
        Config('sales/magento_rma/zip', '12345'),
        Config('sales/magento_rma/country_id', 'US'),
        DataFixture(
            RmaItemAttributeFixture::class,
            [
                'attribute_code' => 'shoe_size',
            ],
            'varchar_attribute'
        ),
        DataFixture(
            RmaItemAttributeFixture::class,
            [
                'frontend_input' => 'multiselect',
                'source_model' => Table::class,
                'backend_model' => ArrayBackend::class,
                'attribute_code' => 'shoe_color',
            ],
            'multiselect_attribute'
        ),
        DataFixture(
            AttributeOptionFixture::class,
            [
                'entity_type' => RmaAttributesManagementInterface::ATTRIBUTE_SET_ID,
                'attribute_code' => '$multiselect_attribute.attribute_code$',
                'label' => 'red',
                'sort_order' => 20
            ],
            'multiselect_attribute_option_1'
        ),
        DataFixture(
            AttributeOptionFixture::class,
            [
                'entity_type' => RmaAttributesManagementInterface::ATTRIBUTE_SET_ID,
                'attribute_code' => '$multiselect_attribute.attribute_code$',
                'sort_order' => 10,
                'label' => 'white',
                'is_default' => true
            ],
            'multiselect_attribute_option_2'
        ),
        DataFixture(ProductFixture::class, as: 'product'),
        DataFixture(CustomerFixture::class, as: 'customer'),
        DataFixture(CustomerCartFixture::class, ['customer_id' => '$customer.id$'], 'cart'),
        DataFixture(AddProductToCartFixture::class, ['cart_id' => '$cart.id$', 'product_id' => '$product.id$']),
        DataFixture(SetBillingAddressFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(SetShippingAddressFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(SetGuestEmailFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(SetDeliveryMethodFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(SetPaymentMethodFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(PlaceOrderFixture::class, ['cart_id' => '$cart.id$'], 'order'),
        DataFixture(InvoiceFixture::class, ['order_id' => '$order.id$'], 'invoice'),
        DataFixture(
            ShipmentFixture::class,
            [
            'order_id' => '$order.id$',
            'items' => [['product_id' => '$product.id$']]
            ]
        ),
        DataFixture(RmaFixture::class, ['order_id' => '$order.id$'], 'rma'),
        DataFixture(
            RmaItemFixture::class,
            [
                'sku' => '$product.sku$',
                'name' => '$product.name$',
                'rma_entity_id' => '$rma.id$',
                'shoe_size' => '42',
                'id' => '$rma.id$'
            ],
            'rma_item'
        )
    ]
    public function testReturnCustomAttributesV2()
    {
        /** @var RmaInterface $rma */
        $rma = DataFixtureStorageManager::getStorage()->get('rma');
        /** @var CartInterface $cart */
        $cart = DataFixtureStorageManager::getStorage()->get('cart');
        /** @var AttributeOptionInterface $attributeOption2 */
        $attributeOption2 = DataFixtureStorageManager::getStorage()->get('multiselect_attribute_option_2');

        $uid = $this->idEncoder->encode((string)$rma->getEntityId());

        $query = <<<QUERY
{
  customer {
    return(uid: "{$uid}") {
      number
      items {
        custom_attributesV2 {
          code
          ... on AttributeValue {
            value
          }
          ... on AttributeSelectedOptions {
            selected_options {
              label
              value
            }
          }
        }
        request_quantity
        quantity
        status
      }
    }
  }
}
QUERY;

        $expected = [
            'customer' => [
                'return' => [
                    'number' => '1',
                    'items' => [
                        [
                            'custom_attributesV2' => [
                                [
                                    'code' => 'shoe_size',
                                    'value' => 42
                                ],
                                [
                                    'code' => 'shoe_color',
                                    'selected_options' => [
                                        [
                                            'label' => $attributeOption2->getLabel(),
                                            'value' => $attributeOption2->getValue(),
                                        ]
                                    ]
                                ]
                            ],
                            'request_quantity' => 1,
                            'quantity' => 1,
                            'status' => 'AUTHORIZED',
                        ]
                    ]
                ]
            ]
        ];

        $response = $this->graphQlQuery(
            $query,
            [],
            '',
            $this->getCustomerAuthenticationHeader->execute($cart->getCustomer()->getEmail(), 'password')
        );
        $this->assertEquals($expected, $response);
    }

    /**
     * Get customer return
     *
     * @param string $customerEmail
     * @return RmaInterface
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    private function getCustomerReturn(string $customerEmail): RmaInterface
    {
        $customer = $this->customerRepository->get($customerEmail);
        $this->searchCriteriaBuilder->addFilter(Rma::CUSTOMER_ID, $customer->getId());
        $searchResults = $this->rmaRepository->getList($this->searchCriteriaBuilder->create());

        return $searchResults->getFirstItem();
    }

    /**
     * Assert tracking
     *
     * @param array $response
     * @param TrackInterface $track
     * @throws NoSuchEntityException
     */
    private function assertTracking(array $response, TrackInterface $track): void
    {
        $storeId = (int)$this->storeManager->getStore()->getId();

        self::assertEquals(
            $this->idEncoder->encode((string)$track->getEntityId()),
            $response['customer']['return']['shipping']['tracking'][0]['uid']
        );
        self::assertEquals(
            'CarrierTitle',
            $response['customer']['return']['shipping']['tracking'][0]['carrier']['label']
        );
        self::assertEquals(
            $this->helper->encodeCarrierId('custom', $storeId),
            $response['customer']['return']['shipping']['tracking'][0]['carrier']['uid']
        );
        self::assertEquals(
            'TrackNumber',
            $response['customer']['return']['shipping']['tracking'][0]['tracking_number']
        );
    }

    /**
     * Assert Rma items
     *
     * @param array $response
     * @throws GraphQlInputException
     */
    private function assertItems(array $response): void
    {
        $expectedAttributes = [
            [
                'value' => $this->serializer->serialize("Exchange"),
                'label' => 'Resolution',
            ],
            [
                'value' => $this->serializer->serialize("Opened"),
                'label' => 'Item Condition',
            ],
            [
                'value' => $this->serializer->serialize(null),
                'label' => 'Reason to Return',
            ],
            [
                'value' => $this->serializer->serialize("don't like it"),
                'label' => 'Other',
            ],
        ];

        foreach ($response['customer']['return']['items'][0]['custom_attributes'] as $key => $attribute) {
            $this->assertAttribute($expectedAttributes[$key], $attribute);
        }

        self::assertNotEmpty($response['customer']['return']['items']);
        self::assertNotEmpty($response['customer']['return']['items'][0]['custom_attributes']);
        self::assertEquals(1, $response['customer']['return']['items'][0]['request_quantity']);
        self::assertEqualsIgnoringCase(
            Status::STATE_AUTHORIZED,
            $response['customer']['return']['items'][0]['status']
        );
    }

    /**
     * @param array $expected
     * @param array $actual
     * @throws GraphQlInputException
     */
    private function assertAttribute(array $expected, array $actual): void
    {
        self::assertIsNumeric($this->idEncoder->decode($actual['uid']));
        self::assertEquals($expected['value'], $actual['value']);
        self::assertEquals($expected['label'], $actual['label']);
    }

    /**
     * Assert RMA comments
     *
     * @param array $response
     * @param CommentInterface $comment
     */
    public function assertComments(array $response, CommentInterface $comment): void
    {
        self::assertEquals(
            $this->idEncoder->encode((string)$comment->getEntityId()),
            $response['customer']['return']['comments'][0]['uid']
        );
        self::assertEquals('Test comment', $response['customer']['return']['comments'][0]['text']);
        self::assertEquals($comment->getCreatedAt(), $response['customer']['return']['comments'][0]['created_at']);
        self::assertEquals('Customer Service', $response['customer']['return']['comments'][0]['author_name']);
    }

    /**
     * Assert address
     *
     * @param array $response
     * @param bool $useStoreAddress
     */
    private function assertAddress(array  $response, bool $useStoreAddress): void
    {
        if ($useStoreAddress) {
            self::assertNull($response['customer']['return']['shipping']['address']['contact_name']);
            self::assertEquals('AL', $response['customer']['return']['shipping']['address']['region']['code']);
        } else {
            self::assertEquals('test', $response['customer']['return']['shipping']['address']['contact_name']);
            self::assertNull($response['customer']['return']['shipping']['address']['region']['code']);
        }
        self::assertNull($response['customer']['return']['shipping']['address']['telephone']);

        self::assertEqualsCanonicalizing(
            ['street', '1'],
            $response['customer']['return']['shipping']['address']['street']
        );
        self::assertEquals('Montgomery', $response['customer']['return']['shipping']['address']['city']);
        self::assertEquals(
            'United States',
            $response['customer']['return']['shipping']['address']['country']['full_name_english']
        );
        self::assertEquals('12345', $response['customer']['return']['shipping']['address']['postcode']);
    }
}
