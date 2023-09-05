<?php
/*
 * Copyright (c) 2023.
 * Fred Nguyen
 * email: fred.nguyen.17@gmail.com
 */

namespace Frednguyen\ProductPriceRange\Test\Unit\Model;

use Frednguyen\ProductPriceRange\Api\Data\PriceRangeProductInterfaceFactory;
use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\TestCase;
use Frednguyen\ProductPriceRange\Model\PriceRangeProductManagement;
use PHPUnit\Framework\MockObject\MockObject;

class PriceRangeProductManagementTest extends TestCase
{
    private ?PriceRangeProductManagement $priceRangeProductManagement = null;
    /**
     * @var MockObject|Collection
     */
    protected $productCollection;

    /**
     * @var ObjectManager
     */
    protected $objectManager;
    /**
     * @var \Magento\Store\Model\StoreManagerInterface|MockObject
     */
    protected $storeManager;
    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface|MockObject
     */
    protected $productRepository;
    /**
     * @var PriceRangeProductInterfaceFactory|MockObject
     */
    protected $priceRangeProductInterfaceFactory;
    /**
     * @var \Frednguyen\ProductPriceRange\Api\Data\PriceRangeProductInterface|MockObject
     */
    protected $priceRangeInterface;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);

        $this->storeManager = $this->createMock(\Magento\Store\Model\StoreManagerInterface::class);
        $this->productRepository = $this->createMock(\Magento\Catalog\Api\ProductRepositoryInterface::class);
        $this->priceRangeProductInterfaceFactory = $this->createMock(PriceRangeProductInterfaceFactory::class);
        $this->priceRangeInterface = $this->createMock(\Frednguyen\ProductPriceRange\Api\Data\PriceRangeProductInterface::class);
        $this->priceRangeProductInterfaceFactory->expects($this->any())->method('create')->willReturn($this->priceRangeInterface);

        $this->productCollection = $this->objectManager->getCollectionMock(Collection::class, []);

        $methods = \array_merge(
            \get_class_methods(\Magento\Store\Api\Data\StoreInterface::class),
            ['getUrl']
        );
        $store = $this->getMockBuilder(\Magento\Store\Api\Data\StoreInterface::class)
            ->setMethods($methods)
            ->disableOriginalConstructor()
            ->getMock();
        $store->expects($this->any())
            ->method('getUrl')
            ->willReturn('test_media_url');

        $this->storeManager
            ->expects($this->any())
            ->method('getStore')
            ->willReturn($store);
    }

    /**
     * @return void
     */
    private function _initTestObject()
    {
        $this->priceRangeProductManagement = $this->objectManager->getObject(
            PriceRangeProductManagement::class,
            [
                'productCollection' => $this->productCollection,
                'productRepository' => $this->productRepository,
                'storeManager' => $this->storeManager,
                'priceRangeProductInterfaceFactory' => $this->priceRangeProductInterfaceFactory,
                '_stockId' => 1
            ]
        );
    }

    /**
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function testGetProductsThrowErrorByWrongPriceRangeInput()
    {
        $min_price = 100;
        $max_price = 50;
        $order_by = 'ASC';
        $this->_initTestObject();
        $this->expectExceptionCode(400);
        $this->expectException(\Magento\Framework\Webapi\Exception::class);
        $this->priceRangeProductManagement->getProducts($min_price,$max_price,$order_by);
    }

    /**
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function testGetProductsThrowErrorByWrongOrderBy()
    {
        $min_price = 10;
        $max_price = 20;
        $order_by = 'WrongVal';
        $this->_initTestObject();
        $this->expectExceptionCode(400);
        $this->expectException(\Magento\Framework\Webapi\Exception::class);
        $this->priceRangeProductManagement->getProducts($min_price,$max_price,$order_by);
    }

    /**
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function testGetProductsSuccess2Items()
    {
        $product1 = $this->createMock(\Magento\Catalog\Model\Product::class);
        $product1
            ->expects($this->any())
            ->method('getSku')
            ->willReturn('test_sku_1');

        $product2 = $this->createMock(\Magento\Catalog\Model\Product::class);
        $product2
            ->expects($this->any())
            ->method('getSku')
            ->willReturn('test_sku_2');

        $this->productCollection = $this->objectManager
            ->getCollectionMock(Collection::class, [$product1, $product2]);

        $this->_initTestObject();

        $this->productCollection
            ->expects($this->once())
            ->method('addAttributeToSelect')
            ->will($this->returnSelf());
        $this->productCollection
            ->expects($this->once())
            ->method('addFinalPrice')
            ->will($this->returnSelf());
        $this->productCollection
            ->expects($this->once())
            ->method('addAttributeToFilter')
            ->will($this->returnSelf());
        $this->productCollection
            ->expects($this->once())
            ->method('setVisibility')
            ->will($this->returnSelf());
        $this->productCollection
            ->expects($this->once())
            ->method('setPageSize')
            ->will($this->returnSelf());

        $select = $this->createMock(\Magento\Framework\DB\Select::class);
        $select->expects($this->exactly(2))
            ->method('where')
            ->will($this->returnSelf());
        $select->expects($this->once())
            ->method('order')
            ->will($this->returnSelf());
        $this->productCollection
            ->expects($this->once())
            ->method('getSelect')
            ->willReturn($select);

        $this->productRepository
            ->expects($this->exactly(2))
            ->method('get')
            ->willReturn($product1);

        $this->priceRangeInterface
            ->expects($this->exactly(2))
            ->method('setProductName')
            ->will($this->returnSelf());

        $this->priceRangeInterface
            ->expects($this->exactly(2))
            ->method('setProductSku')
            ->will($this->returnSelf());

        $this->priceRangeInterface
            ->expects($this->exactly(2))
            ->method('setPrice')
            ->will($this->returnSelf());

        $this->priceRangeInterface
            ->expects($this->exactly(2))
            ->method('setProductUrl')
            ->will($this->returnSelf());
        $this->priceRangeInterface
            ->expects($this->exactly(2))
            ->method('setProductThumbnail')
            ->will($this->returnSelf());
        $this->priceRangeInterface
            ->expects($this->exactly(2))
            ->method('setQty')
            ->will($this->returnSelf());

        $result = $this->priceRangeProductManagement->getProducts(10, 20, 'ASC');
        $this->assertIsArray($result);
        $this->assertCount(2, $result, "Expected to return 2 Items");
    }

    /**
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function testGetProductsReturnNoData()
    {
        $this->_initTestObject();
        $this->productCollection
            ->expects($this->once())
            ->method('addAttributeToSelect')
            ->will($this->returnSelf());
        $this->productCollection
            ->expects($this->once())
            ->method('addFinalPrice')
            ->will($this->returnSelf());
        $this->productCollection
            ->expects($this->once())
            ->method('addAttributeToFilter')
            ->will($this->returnSelf());
        $this->productCollection
            ->expects($this->once())
            ->method('setVisibility')
            ->will($this->returnSelf());
        $this->productCollection
            ->expects($this->once())
            ->method('setPageSize')
            ->will($this->returnSelf());

        $select = $this->createMock(\Magento\Framework\DB\Select::class);
        $select->expects($this->exactly(2))
            ->method('where')
            ->will($this->returnSelf());
        $select->expects($this->once())
            ->method('order')
            ->will($this->returnSelf());
        $this->productCollection
            ->expects($this->once())
            ->method('getSelect')
            ->willReturn($select);

        $result = $this->priceRangeProductManagement->getProducts(10, 20, 'DESC');
        $this->assertEquals([], $result);
    }

}
