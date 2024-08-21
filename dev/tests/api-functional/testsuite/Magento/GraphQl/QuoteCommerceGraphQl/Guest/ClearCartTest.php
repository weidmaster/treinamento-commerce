<?php
/************************************************************************
 *
 * Copyright 2023 Adobe
 * All Rights Reserved.
 *
 * NOTICE: All information contained herein is, and remains
 * the property of Adobe and its suppliers, if any. The intellectual
 * and technical concepts contained herein are proprietary to Adobe
 * and its suppliers and are protected by all applicable intellectual
 * property laws, including trade secret and copyright laws.
 * Dissemination of this information or reproduction of this material
 * is strictly forbidden unless prior written permission is obtained
 * from Adobe.
 * ************************************************************************
 */
declare(strict_types=1);

namespace Magento\GraphQl\QuoteCommerceGraphQl\Guest;

use Magento\Catalog\Test\Fixture\Product as ProductFixture;
use Magento\Customer\Test\Fixture\Customer;
use Magento\Quote\Test\Fixture\AddProductToCart as AddProductToCartFixture;
use Magento\Quote\Test\Fixture\CustomerCart;
use Magento\Quote\Test\Fixture\GuestCart as GuestCartFixture;
use Magento\Quote\Test\Fixture\QuoteIdMask as QuoteIdMaskFixture;
use Magento\Quote\Test\Fixture\MakeCartInactive as MakeCartInactiveFixture;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorage;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\GraphQlAbstract;

/**
 * Test coverage for clear guest cart
 */
class ClearCartTest extends GraphQlAbstract
{
    /**
     * @var DataFixtureStorage
     */
    private $fixtures;

    protected function setUp(): void
    {
        $objectManager = Bootstrap::getObjectManager();
        $this->fixtures = $objectManager->get(DataFixtureStorageManager::class)->getStorage();
    }

    /**
     * Test clear cart items
     *
     * @return void
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    #[
        DataFixture(ProductFixture::class, as: 'p1'),
        DataFixture(ProductFixture::class, as: 'p2'),
        DataFixture(GuestCartFixture::class, as: 'cart'),
        DataFixture(QuoteIdMaskFixture::class, ['cart_id' => '$cart.id$'], as: 'mask'),
        DataFixture(AddProductToCartFixture::class, ['cart_id' => '$cart.id$', 'product_id' => '$p1.id$', 'qty' => 2]),
        DataFixture(AddProductToCartFixture::class, ['cart_id' => '$cart.id$', 'product_id' => '$p2.id$', 'qty' => 2]),
    ]
    public function testClearCart(): void
    {
        $maskedQuoteId = $this->fixtures->get('mask')->getMaskedId();
        $query = $this->getQuery($maskedQuoteId);
        $response = $this->graphQlMutation($query);
        $this->assertArrayHasKey('clearCart', $response);
        $this->assertEmpty($response['clearCart']['cart']['items']);
        $this->assertEquals(null, $response['clearCart']['errors']);
    }

    /**
     * Test exception if masked cart id is missing
     *
     * @return void
     * @throws \Exception
     */
    public function testClearCartWithoutId(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Required parameter "uid" is missing.');
        $maskedQuoteId = '';
        $query = $this->getQuery($maskedQuoteId);
        $this->graphQlMutation($query);
    }

    /**
     * Test clear cart items for wrong cart id
     *
     * @return void
     * @throws \Exception
     */
    public function testClearCartWithWrongCartId(): void
    {
        $maskedQuoteId = "abc12345abc";
        $query = $this->getQuery($maskedQuoteId);
        $response = $this->graphQlMutation($query);
        $this->assertEquals("NOT_FOUND", $response['clearCart']['errors'][0]['type']);
        $this->assertEquals(null, $response['clearCart']['cart']);
    }

    /**
     * Test clear cart for unathorised cart id
     *
     * @return void
     * @throws \Exception
     */
    #[
        DataFixture(
            Customer::class,
            [
                'email' => 'customer@example.com',
                'password' => 'password'
            ],
            'customer'
        ),
        DataFixture(CustomerCart::class, ['customer_id' => '$customer.id$'], as: 'cart'),
        DataFixture(QuoteIdMaskFixture::class, ['cart_id' => '$cart.id$'], as: 'mask'),
    ]
    public function testClearCartWithUnathorisedCartId(): void
    {
        $maskedQuoteId = $this->fixtures->get('mask')->getMaskedId();
        $query = $this->getQuery($maskedQuoteId);
        $response = $this->graphQlMutation($query);
        $this->assertEquals("UNAUTHORISED", $response['clearCart']['errors'][0]['type']);
        $this->assertEquals(null, $response['clearCart']['cart']);
    }

    /**
     * Test clear cart items for inactive cart id
     *
     * @return void
     * @throws \Exception
     */
    #[
        DataFixture(GuestCartFixture::class, as: 'cart'),
        DataFixture(QuoteIdMaskFixture::class, ['cart_id' => '$cart.id$'], as: 'mask'),
        DataFixture(MakeCartInactiveFixture::class, ['cart_id' => '$cart.id$'], as: 'inactiveCart'),
    ]
    public function testClearCartWithInactiveCartId()
    {
        $maskedQuoteId = $this->fixtures->get('mask')->getMaskedId();
        $query = $this->getQuery($maskedQuoteId);
        $response = $this->graphQlMutation($query);
        $this->assertEquals("INACTIVE", $response['clearCart']['errors'][0]['type']);
        $this->assertEquals(null, $response['clearCart']['cart']);
    }

    /**
     * Returns GraphQl mutation string
     *
     * @param string $cartId
     *
     * @return string
     */
    private function getQuery(
        string $cartId
    ): string {
        return <<<MUTATION
mutation {
  clearCart(
    input:{
    uid: "{$cartId}"
    }
  ) {
    cart {
      id
      items {
        id
        product {
          sku
          stock_status
        }
        quantity
      }
    }
    errors {
    type
    message
    }
  }
}
MUTATION;
    }
}
