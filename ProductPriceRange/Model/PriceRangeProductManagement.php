<?php
/*
 * Copyright (c) 2023.
 * Fred Nguyen
 * email: fred.nguyen.17@gmail.com
 */

namespace Frednguyen\ProductPriceRange\Model;

use Frednguyen\ProductPriceRange\Api\Data\PriceRangeProductInterface;
use Frednguyen\ProductPriceRange\Api\PriceRangeProductManagementInterface;
use Frednguyen\ProductPriceRange\Api\Data\PriceRangeProductInterfaceFactory;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Framework\Api\SortOrder;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\InventorySalesApi\Api\Data\SalesChannelInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\InventorySalesApi\Api\StockResolverInterface;
use Magento\InventorySalesApi\Api\GetProductSalableQtyInterface;
use Magento\Catalog\Model\ResourceModel\Product\Collection as ProductCollection;
use Magento\Framework\Pricing\Helper\Data as PriceHelper;

class PriceRangeProductManagement implements PriceRangeProductManagementInterface
{
    const DEFAULT_NUMBER_OF_PRODUCT = 10;
    /**
     * @var PriceRangeProductInterfaceFactory
     */
    protected $priceRangeProductInterfaceFactory;

    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var StockResolverInterface
     */
    protected $stockResolver;

    /**
     * @var GetProductSalableQtyInterface
     */
    protected $getProductSalableQty;

    /** @var ProductCollection */
    protected $productCollection;

    /** @var PriceHelper */
    protected $priceHelper;

    /** @var Status */
    protected $productStatus;

    /** @var Visibility */
    protected $productVisibility;

    /**
     * @var int
     */
    private $_stockId;

    /**
     * @param PriceRangeProductInterfaceFactory $priceRangeProductInterfaceFactory
     * @param ProductRepositoryInterface $productRepository
     * @param StoreManagerInterface $storeManager
     * @param StockResolverInterface $stockResolver
     * @param GetProductSalableQtyInterface $getProductSalableQty
     * @param ProductCollection $productCollection
     * @param PriceHelper $priceHelper
     * @param Status $productStatus
     * @param Visibility $productVisibility
     */
    public function __construct(
        PriceRangeProductInterfaceFactory $priceRangeProductInterfaceFactory,
        ProductRepositoryInterface        $productRepository,
        StoreManagerInterface             $storeManager,
        StockResolverInterface            $stockResolver,
        GetProductSalableQtyInterface     $getProductSalableQty,
        ProductCollection                 $productCollection,
        PriceHelper                       $priceHelper,
        Status $productStatus,
        Visibility $productVisibility
    )
    {
        $this->priceRangeProductInterfaceFactory = $priceRangeProductInterfaceFactory;
        $this->productRepository = $productRepository;
        $this->storeManager = $storeManager;
        $this->stockResolver = $stockResolver;
        $this->getProductSalableQty = $getProductSalableQty;
        $this->productCollection = $productCollection;
        $this->priceHelper = $priceHelper;
        $this->productStatus = $productStatus;
        $this->productVisibility = $productVisibility;
    }

    /**
     * @inheritDoc
     */
    public function getProducts(int $min_price, int $max_price, string $order_by = 'asc'): array
    {
        if (!$this->_validateInput($min_price, $max_price, $order_by))
            throw new \Magento\Framework\Webapi\Exception (__("Bad data requested"), \Magento\Framework\Webapi\Exception::HTTP_BAD_REQUEST);

        $result = [];
        $products = $this->_getProductsCollection($min_price, $max_price, $order_by);

        $mediaUrl = $this->storeManager->getStore()->getUrl('pub/media/catalog') . 'product';
        foreach ($products as $product) {
            $result[] = $this->_buildResponseItem($product,$mediaUrl);
        }

        return $result;
    }

    /**
     * @param $product
     * @param $mediaUrl
     * @return PriceRangeProductInterface
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    private function _buildResponseItem($product, $mediaUrl){
        $responseItem = $this->priceRangeProductInterfaceFactory->create();
        $url = $this->productRepository->get($product->getSku())->getProductUrl();
        $responseItem->setProductName($product->getName())
            ->setProductSku($product->getSku())
            ->setPrice($this->priceHelper->currency($product->getData('final_price'), true, false))
            ->setProductUrl($url)
            ->setProductThumbnail($mediaUrl . $product->getThumbnail())
            ->setQty($this->_getSalableQty($product));
        return $responseItem;
    }

    /**
     * @param int $min_price
     * @param int $max_price
     * @param string $order_by
     * @return bool
     */
    private function _validateInput(int $min_price, int $max_price, string $order_by): bool
    {
        if ($min_price < 0
            || $max_price < 0
            || ($min_price >= $max_price)
            || $max_price > $min_price * 5
            || !in_array(strtoupper($order_by), [SortOrder::SORT_ASC, SortOrder::SORT_DESC])
        ) {
            return false;
        }
        return true;
    }

    /**
     * @param \Magento\Catalog\Model\Product $product
     * @return int|null
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    private function _getSalableQty(\Magento\Catalog\Model\Product $product)
    {
        if (!$this->_stockId) {
            $websiteCode = $this->storeManager->getWebsite()->getCode();
            $this->_stockId = $this->stockResolver->execute(SalesChannelInterface::TYPE_WEBSITE, $websiteCode)->getStockId();
        }
        try {
            if($product->getTypeID() == 'configurable'){
                // For configurable product, sum total qty of child products
                $qty = 0;
                $productTypeInstance = $product->getTypeInstance();
                $childProducts = $productTypeInstance->getUsedProducts($product);
                foreach ($childProducts as $simple) {
                    $qty += (int)$this->getProductSalableQty->execute($simple->getSku(), $this->_stockId);
                }
            }else{
                $qty = (int)$this->getProductSalableQty->execute($product->getSku(), $this->_stockId);
            }
        } catch (\Exception $e) {
            // some product types (virtual, grouped, bundle product...) don't have salable qty
            $qty = null;
        }
        return $qty;
    }

    /**
     * @param int $min_price
     * @param int $max_price
     * @param string $order_by
     * @return ProductCollection
     */
    private function _getProductsCollection(int $min_price, int $max_price, string $order_by)
    {
        // Use product collection to add filter for final_price
        $collection = $this->productCollection
            ->addAttributeToSelect('*')
            ->addFinalPrice()
            ->addAttributeToFilter('status', ['in' => $this->productStatus->getVisibleStatusIds()])
            ->setVisibility($this->productVisibility->getVisibleInSiteIds())
            ->setPageSize(self::DEFAULT_NUMBER_OF_PRODUCT);
        $collection->getSelect()
            ->where('price_index.final_price >= ?', $min_price)
            ->where('price_index.final_price < ?', $max_price)
            ->order(new \Zend_Db_Expr("price_index.final_price $order_by"));
        return $collection;
    }
}
