<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\AdvancedSalesRule\Model\Plugin;

/**
 * @magentoDbIsolation enabled
 * @magentoAppIsolation enabled
 */
class RuleTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     * @magentoDataFixture Magento/SalesRule/_files/rules_categories.php
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    public function testFilterRuleSaveSimpleGroup()
    {
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

        /** @var \Magento\SalesRule\Model\Rule $rule */
        $rule = $objectManager->get(\Magento\Framework\Registry::class)
            ->registry('_fixture/Magento_SalesRule_Multiple_Categories');

        /** @var \Magento\AdvancedSalesRule\Model\ResourceModel\Rule\Condition\Filter $filter */
        $filter = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\AdvancedSalesRule\Model\ResourceModel\Rule\Condition\Filter::class
        );

        $connection = $filter->getConnection();
        $filtersSelect = $connection->select()->where('rule_id = ?', $rule->getRuleId())->from($filter->getMainTable());
        $items = $filtersSelect->query()->fetchAll();

        // verify rule_filter_id exists and remove rule_filter_id from $items
        foreach ($items as $index => $item) {
            $this->assertArrayHasKey('rule_filter_id', $items[$index]);
            unset($items[$index]['rule_filter_id']);
        }

        $expected = [
            [
                'rule_id' => $rule->getRuleId(),
                'group_id' => '1',
                'weight' => '0.5',
                'filter_text' => 'product:category:2',
                'filter_text_generator_class' =>
                    \Magento\AdvancedSalesRule\Model\Rule\Condition\FilterTextGenerator\Product\Category::class,
                'filter_text_generator_arguments' => '[]',
                'is_coupon' => '0',
            ],
            [
                'rule_id' => $rule->getRuleId(),
                'group_id' => '1',
                'weight' => '0.5',
                'filter_text' => 'product:category:3',
                'filter_text_generator_class' =>
                    \Magento\AdvancedSalesRule\Model\Rule\Condition\FilterTextGenerator\Product\Category::class,
                'filter_text_generator_arguments' => '[]',
                'is_coupon' => '0',
            ],
        ];
        //$rule->getConditions()->loadArray()
        $this->assertEquals($expected, $items);
    }

    /**
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     * @magentoDataFixture Magento/SalesRule/_files/rules_group_all_categories.php
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    public function testFilterRuleSaveMultipleGroupsAll()
    {
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

        /** @var \Magento\SalesRule\Model\Rule $rule */
        $rule = $objectManager->get(\Magento\Framework\Registry::class)
            ->registry('_fixture/Magento_SalesRule_Group_Multiple_Categories');

        /** @var \Magento\AdvancedSalesRule\Model\ResourceModel\Rule\Condition\Filter $filter */
        $filter = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\AdvancedSalesRule\Model\ResourceModel\Rule\Condition\Filter::class
        );

        $connection = $filter->getConnection();
        $filtersSelect = $connection->select()->where('rule_id = ?', $rule->getRuleId())->from($filter->getMainTable());
        $items = $filtersSelect->query()->fetchAll();

        // verify rule_filter_id exists and remove rule_filter_id from $items
        foreach ($items as $index => $item) {
            $this->assertArrayHasKey('rule_filter_id', $items[$index]);
            unset($items[$index]['rule_filter_id']);
        }

        $expected = [
            [
                'rule_id' => $rule->getRuleId(),
                'group_id' => '1',
                'weight' => '0.333333',
                'filter_text' => 'product:category:2',
                'filter_text_generator_class' =>
                    \Magento\AdvancedSalesRule\Model\Rule\Condition\FilterTextGenerator\Product\Category::class,
                'filter_text_generator_arguments' => '[]',
                'is_coupon' => '0',
            ],
            [
                'rule_id' => $rule->getRuleId(),
                'group_id' => '1',
                'weight' => '0.333333',
                'filter_text' => 'product:category:3',
                'filter_text_generator_class' =>
                    \Magento\AdvancedSalesRule\Model\Rule\Condition\FilterTextGenerator\Product\Category::class,
                'filter_text_generator_arguments' => '[]',
                'is_coupon' => '0',
            ],
            [
                'rule_id' => $rule->getRuleId(),
                'group_id' => '1',
                'weight' => '0.333333',
                'filter_text' => 'product:category:4',
                'filter_text_generator_class' =>
                    \Magento\AdvancedSalesRule\Model\Rule\Condition\FilterTextGenerator\Product\Category::class,
                'filter_text_generator_arguments' => '[]',
                'is_coupon' => '0',
            ],
        ];

        $this->assertEquals($expected, $items);
    }

    /**
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     * @magentoDataFixture Magento/SalesRule/_files/rules_group_any_categories.php
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    public function testFilterRuleSaveMultipleGroupsAny()
    {
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

        /** @var \Magento\SalesRule\Model\Rule $rule */
        $rule = $objectManager->get(\Magento\Framework\Registry::class)
            ->registry('_fixture/Magento_SalesRule_Group_Multiple_Categories');

        /** @var \Magento\AdvancedSalesRule\Model\ResourceModel\Rule\Condition\Filter $filter */
        $filter = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\AdvancedSalesRule\Model\ResourceModel\Rule\Condition\Filter::class
        );

        $connection = $filter->getConnection();
        $filtersSelect = $connection->select()->where('rule_id = ?', $rule->getRuleId())->from($filter->getMainTable());
        $items = $filtersSelect->query()->fetchAll();

        // verify rule_filter_id exists and remove rule_filter_id from $items
        foreach ($items as $index => $item) {
            $this->assertArrayHasKey('rule_filter_id', $items[$index]);
            unset($items[$index]['rule_filter_id']);
        }

        $expected = [
            [
                'rule_id' => $rule->getRuleId(),
                'group_id' => '1',
                'weight' => '0.333333',
                'filter_text' => 'product:category:2',
                'filter_text_generator_class' =>
                    \Magento\AdvancedSalesRule\Model\Rule\Condition\FilterTextGenerator\Product\Category::class,
                'filter_text_generator_arguments' => '[]',
                'is_coupon' => '0',
            ],
            [
                'rule_id' => $rule->getRuleId(),
                'group_id' => '1',
                'weight' => '0.333333',
                'filter_text' => 'product:category:3',
                'filter_text_generator_class' =>
                    \Magento\AdvancedSalesRule\Model\Rule\Condition\FilterTextGenerator\Product\Category::class,
                'filter_text_generator_arguments' => '[]',
                'is_coupon' => '0',
            ],
            [
                'rule_id' => $rule->getRuleId(),
                'group_id' => '1',
                'weight' => '0.333333',
                'filter_text' => 'product:category:4',
                'filter_text_generator_class' =>
                    \Magento\AdvancedSalesRule\Model\Rule\Condition\FilterTextGenerator\Product\Category::class,
                'filter_text_generator_arguments' => '[]',
                'is_coupon' => '0',
            ],
            [
                'rule_id' => $rule->getRuleId(),
                'group_id' => '2',
                'weight' => '0.5',
                'filter_text' => 'product:category:3',
                'filter_text_generator_class' =>
                    \Magento\AdvancedSalesRule\Model\Rule\Condition\FilterTextGenerator\Product\Category::class,
                'filter_text_generator_arguments' => '[]',
                'is_coupon' => '0',
            ],
            [
                'rule_id' => $rule->getRuleId(),
                'group_id' => '2',
                'weight' => '0.5',
                'filter_text' => 'product:category:4',
                'filter_text_generator_class' =>
                    \Magento\AdvancedSalesRule\Model\Rule\Condition\FilterTextGenerator\Product\Category::class,
                'filter_text_generator_arguments' => '[]',
                'is_coupon' => '0',
            ],
        ];

        $this->assertEquals($expected, $items);
    }

    /**
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     * @magentoDataFixture Magento/SalesRule/_files/rules_group_all_categories_price_attr_set.php
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    public function testFilterRuleSaveMultipleGroupsAllSupportedUnsupported()
    {
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

        /** @var \Magento\SalesRule\Model\Rule $rule */
        $rule = $objectManager->get(\Magento\Framework\Registry::class)
            ->registry('_fixture/Magento_SalesRule_Group_Multiple_Categories');

        /** @var \Magento\AdvancedSalesRule\Model\ResourceModel\Rule\Condition\Filter $filter */
        $filter = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\AdvancedSalesRule\Model\ResourceModel\Rule\Condition\Filter::class
        );

        $connection = $filter->getConnection();
        $filtersSelect = $connection->select()->where('rule_id = ?', $rule->getRuleId())->from($filter->getMainTable());
        $items = $filtersSelect->query()->fetchAll();

        // verify rule_filter_id exists and remove rule_filter_id from $items
        foreach ($items as $index => $item) {
            $this->assertArrayHasKey('rule_filter_id', $items[$index]);
            unset($items[$index]['rule_filter_id']);
        }

        $expected = [
            [
                'rule_id' => $rule->getRuleId(),
                'group_id' => '1',
                'weight' => '0.333333',
                'filter_text' => 'product:category:2',
                'filter_text_generator_class' =>
                    \Magento\AdvancedSalesRule\Model\Rule\Condition\FilterTextGenerator\Product\Category::class,
                'filter_text_generator_arguments' => '[]',
                'is_coupon' => '0',
            ],
            [
                'rule_id' => $rule->getRuleId(),
                'group_id' => '1',
                'weight' => '0.333333',
                'filter_text' => 'product:attribute:attribute_set_id:4',
                'filter_text_generator_class' =>
                    \Magento\AdvancedSalesRule\Model\Rule\Condition\FilterTextGenerator\Product\Attribute::class,
                'filter_text_generator_arguments' => '{"attribute":"attribute_set_id"}',
                'is_coupon' => '0',
            ],
            [
                'rule_id' => $rule->getRuleId(),
                'group_id' => '1',
                'weight' => '0.333333',
                'filter_text' => 'product:category:3',
                'filter_text_generator_class' =>
                    \Magento\AdvancedSalesRule\Model\Rule\Condition\FilterTextGenerator\Product\Category::class,
                'filter_text_generator_arguments' => '[]',
                'is_coupon' => '0',
            ],
        ];

        $this->assertEquals($expected, $items);
    }

    /**
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     * @magentoDataFixture Magento/SalesRule/_files/rules_group_any_categories_price_attr_set.php
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    public function testFilterRuleSaveMultipleGroupsAnySupportedUnsupported()
    {
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

        /** @var \Magento\SalesRule\Model\Rule $rule */
        $rule = $objectManager->get(\Magento\Framework\Registry::class)
            ->registry('_fixture/Magento_SalesRule_Group_Multiple_Categories');

        /** @var \Magento\AdvancedSalesRule\Model\ResourceModel\Rule\Condition\Filter $filter */
        $filter = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\AdvancedSalesRule\Model\ResourceModel\Rule\Condition\Filter::class
        );

        $connection = $filter->getConnection();
        $filtersSelect = $connection->select()->where('rule_id = ?', $rule->getRuleId())->from($filter->getMainTable());
        $items = $filtersSelect->query()->fetchAll();

        // verify rule_filter_id exists and remove rule_filter_id from $items
        foreach ($items as $index => $item) {
            $this->assertArrayHasKey('rule_filter_id', $items[$index]);
            unset($items[$index]['rule_filter_id']);
        }

        $expected = [
            [
                'rule_id' => $rule->getRuleId(),
                'group_id' => '1',
                'weight' => '0.5',
                'filter_text' => 'product:category:2',
                'filter_text_generator_class' =>
                    \Magento\AdvancedSalesRule\Model\Rule\Condition\FilterTextGenerator\Product\Category::class,
                'filter_text_generator_arguments' => '[]',
                'is_coupon' => '0',
            ],
            [
                'rule_id' => $rule->getRuleId(),
                'group_id' => '1',
                'weight' => '0.5',
                'filter_text' => 'product:attribute:attribute_set_id:4',
                'filter_text_generator_class' =>
                    \Magento\AdvancedSalesRule\Model\Rule\Condition\FilterTextGenerator\Product\Attribute::class,
                'filter_text_generator_arguments' => '{"attribute":"attribute_set_id"}',
                'is_coupon' => '0',
            ],
            [
                'rule_id' => $rule->getRuleId(),
                'group_id' => '2',
                'weight' => '1',
                'filter_text' => 'product:category:3',
                'filter_text_generator_class' =>
                    \Magento\AdvancedSalesRule\Model\Rule\Condition\FilterTextGenerator\Product\Category::class,
                'filter_text_generator_arguments' => '[]',
                'is_coupon' => '0',
            ],
        ];

        $this->assertEquals($expected, $items);
    }

    /**
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     * @magentoDataFixture Magento/SalesRule/_files/rules_group_any_categories_price_attr_set_any.php
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    public function testFilterRuleSaveMultipleGroupsAnySupportedUnsupportedAny()
    {
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

        /** @var \Magento\SalesRule\Model\Rule $rule */
        $rule = $objectManager->get(\Magento\Framework\Registry::class)
            ->registry('_fixture/Magento_SalesRule_Group_Multiple_Categories');

        /** @var \Magento\AdvancedSalesRule\Model\ResourceModel\Rule\Condition\Filter $filter */
        $filter = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\AdvancedSalesRule\Model\ResourceModel\Rule\Condition\Filter::class
        );

        $connection = $filter->getConnection();
        $filtersSelect = $connection->select()->where('rule_id = ?', $rule->getRuleId())->from($filter->getMainTable());
        $items = $filtersSelect->query()->fetchAll();

        // verify rule_filter_id exists and remove rule_filter_id from $items
        foreach ($items as $index => $item) {
            $this->assertArrayHasKey('rule_filter_id', $items[$index]);
            unset($items[$index]['rule_filter_id']);
        }

        $expected = [
            [
                'rule_id' => $rule->getRuleId(),
                'group_id' => '1',
                'weight' => '1',
                'filter_text' => 'product:category:2',
                'filter_text_generator_class' =>
                    \Magento\AdvancedSalesRule\Model\Rule\Condition\FilterTextGenerator\Product\Category::class,
                'filter_text_generator_arguments' => '[]',
                'is_coupon' => '0',
            ],
            [
                'rule_id' => $rule->getRuleId(),
                'group_id' => '2',
                'weight' => '1',
                'filter_text' => 'product:attribute:sku:80',
                'filter_text_generator_class' =>
                    \Magento\AdvancedSalesRule\Model\Rule\Condition\FilterTextGenerator\Product\Attribute::class,
                'filter_text_generator_arguments' => '{"attribute":"sku"}',
                'is_coupon' => '0',
            ],
            [
                'rule_id' => $rule->getRuleId(),
                'group_id' => '3',
                'weight' => '1',
                'filter_text' => 'product:attribute:attribute_set_id:4',
                'filter_text_generator_class' =>
                    \Magento\AdvancedSalesRule\Model\Rule\Condition\FilterTextGenerator\Product\Attribute::class,
                'filter_text_generator_arguments' => '{"attribute":"attribute_set_id"}',
                'is_coupon' => '0',
            ],
            [
                'rule_id' => $rule->getRuleId(),
                'group_id' => '4',
                'weight' => '1',
                'filter_text' => 'product:category:3',
                'filter_text_generator_class' =>
                    \Magento\AdvancedSalesRule\Model\Rule\Condition\FilterTextGenerator\Product\Category::class,
                'filter_text_generator_arguments' => '[]',
                'is_coupon' => '0',
            ],
        ];

        $this->assertEquals($expected, $items);
    }

    /**
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     * @magentoDataFixture Magento/SalesRule/_files/rules_group_categories_price_sku_attr_set_any.php
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    public function testFilterRuleSaveMultipleGroupsSupportedUnsupportedAny()
    {
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

        /** @var \Magento\SalesRule\Model\Rule $rule */
        $rule = $objectManager->get(\Magento\Framework\Registry::class)
            ->registry('_fixture/Magento_SalesRule_Group_Multiple_Categories');

        /** @var \Magento\AdvancedSalesRule\Model\ResourceModel\Rule\Condition\Filter $filter */
        $filter = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\AdvancedSalesRule\Model\ResourceModel\Rule\Condition\Filter::class
        );

        $connection = $filter->getConnection();
        $filtersSelect = $connection->select()->where('rule_id = ?', $rule->getRuleId())->from($filter->getMainTable());
        $items = $filtersSelect->query()->fetchAll();

        // verify rule_filter_id exists and remove rule_filter_id from $items
        foreach ($items as $index => $item) {
            $this->assertArrayHasKey('rule_filter_id', $items[$index]);
            unset($items[$index]['rule_filter_id']);
        }

        $expected = [
            [
                'rule_id' => $rule->getRuleId(),
                'group_id' => '1',
                'weight' => '1',
                'filter_text' => 'true',
                'filter_text_generator_class' => null,
                'filter_text_generator_arguments' => null,
                'is_coupon' => '0'
            ]
        ];

        $this->assertEquals($expected, $items);
    }

    /**
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     * @magentoDataFixture Magento/SalesRule/_files/rules_group_not_categories_sku_attr.php
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    public function testFilterRuleSaveMultipleNotGroupsSupportedUnsupported()
    {
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

        /** @var \Magento\SalesRule\Model\Rule $rule */
        $rule = $objectManager->get(\Magento\Framework\Registry::class)
            ->registry('_fixture/Magento_SalesRule_Group_Multiple_Categories');

        /** @var \Magento\AdvancedSalesRule\Model\ResourceModel\Rule\Condition\Filter $filter */
        $filter = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\AdvancedSalesRule\Model\ResourceModel\Rule\Condition\Filter::class
        );

        $connection = $filter->getConnection();
        $filtersSelect = $connection->select()->where('rule_id = ?', $rule->getRuleId())->from($filter->getMainTable());
        $items = $filtersSelect->query()->fetchAll();

        // verify rule_filter_id exists and remove rule_filter_id from $items
        foreach ($items as $index => $item) {
            $this->assertArrayHasKey('rule_filter_id', $items[$index]);
            unset($items[$index]['rule_filter_id']);
        }

        $expected = [
            [
                'rule_id' => $rule->getRuleId(),
                'group_id' => '1',
                'weight' => '1',
                'filter_text' => 'true',
                'filter_text_generator_class' => null,
                'filter_text_generator_arguments' => null,
                'is_coupon' => '0'
            ]
        ];

        $this->assertEquals($expected, $items);
    }

    /**
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     * @magentoDataFixture Magento/SalesRule/_files/rules_group_any_categories_price_address.php
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    public function testFilterRuleSaveMultipleNotGroupsAnyAllSupported()
    {
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

        /** @var \Magento\SalesRule\Model\Rule $rule */
        $rule = $objectManager->get(\Magento\Framework\Registry::class)
            ->registry('_fixture/Magento_SalesRule_Group_Multiple_Categories');

        /** @var \Magento\AdvancedSalesRule\Model\ResourceModel\Rule\Condition\Filter $filter */
        $filter = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\AdvancedSalesRule\Model\ResourceModel\Rule\Condition\Filter::class
        );

        $connection = $filter->getConnection();
        $filtersSelect = $connection->select()->where('rule_id = ?', $rule->getRuleId())->from($filter->getMainTable());
        $items = $filtersSelect->query()->fetchAll();

        // verify rule_filter_id exists and remove rule_filter_id from $items
        foreach ($items as $index => $item) {
            $this->assertArrayHasKey('rule_filter_id', $items[$index]);
            unset($items[$index]['rule_filter_id']);
        }

        $expected = [
            [
                'rule_id' => $rule->getRuleId(),
                'group_id' => '1',
                'weight' => '1',
                'filter_text' => 'product:category:2',
                'filter_text_generator_class' =>
                    \Magento\AdvancedSalesRule\Model\Rule\Condition\FilterTextGenerator\Product\Category::class,
                'filter_text_generator_arguments' => '[]',
                'is_coupon' => '0'
            ],
            [
                'rule_id' => $rule->getRuleId(),
                'group_id' => '2',
                'weight' => '1',
                'filter_text' => 'quote_address:payment_method:payflowpro',
                'filter_text_generator_class' =>
                    \Magento\AdvancedSalesRule\Model\Rule\Condition\FilterTextGenerator\Address\PaymentMethod::class,
                'filter_text_generator_arguments' => '{"attribute":"payment_method"}',
                'is_coupon' => '0'
            ],
            [
                'rule_id' => $rule->getRuleId(),
                'group_id' => '3',
                'weight' => '1',
                'filter_text' => 'quote_address:shipping_method:fedex_FEDEX_2_DAY',
                'filter_text_generator_class' =>
                    \Magento\AdvancedSalesRule\Model\Rule\Condition\FilterTextGenerator\Address\ShippingMethod::class,
                'filter_text_generator_arguments' => '{"attribute":"shipping_method"}',
                'is_coupon' => '0'
            ],
            [
                'rule_id' => $rule->getRuleId(),
                'group_id' => '4',
                'weight' => '1',
                'filter_text' => 'quote_address:postcode:78000',
                'filter_text_generator_class' =>
                    \Magento\AdvancedSalesRule\Model\Rule\Condition\FilterTextGenerator\Address\Postcode::class,
                'filter_text_generator_arguments' => '{"attribute":"postcode"}',
                'is_coupon' => '0'
            ],
            [
                'rule_id' => $rule->getRuleId(),
                'group_id' => '5',
                'weight' => '1',
                'filter_text' => 'quote_address:region:HD',
                'filter_text_generator_class' =>
                    \Magento\AdvancedSalesRule\Model\Rule\Condition\FilterTextGenerator\Address\Region::class,
                'filter_text_generator_arguments' => '{"attribute":"region"}',
                'is_coupon' => '0'
            ],
            [
                'rule_id' => $rule->getRuleId(),
                'group_id' => '6',
                'weight' => '1',
                'filter_text' => 'quote_address:region_id:56',
                'filter_text_generator_class' =>
                    \Magento\AdvancedSalesRule\Model\Rule\Condition\FilterTextGenerator\Address\RegionId::class,
                'filter_text_generator_arguments' => '{"attribute":"region_id"}',
                'is_coupon' => '0'
            ],
            [
                'rule_id' => $rule->getRuleId(),
                'group_id' => '7',
                'weight' => '1',
                'filter_text' => 'quote_address:country_id:US',
                'filter_text_generator_class' =>
                    \Magento\AdvancedSalesRule\Model\Rule\Condition\FilterTextGenerator\Address\CountryId::class,
                'filter_text_generator_arguments' => '{"attribute":"country_id"}',
                'is_coupon' => '0'
            ]
        ];

        $this->assertEquals($expected, $items);
    }
}
