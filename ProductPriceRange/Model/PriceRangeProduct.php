<?php
/*
 * Copyright (c) 2023.
 * Fred Nguyen
 * email: fred.nguyen.17@gmail.com
 */

namespace Frednguyen\ProductPriceRange\Model;
use Frednguyen\ProductPriceRange\Api\Data\PriceRangeProductInterface;
use Magento\Framework\Model\AbstractModel;

class PriceRangeProduct extends AbstractModel
    implements PriceRangeProductInterface
{
    /**
     * @inheritDoc
     */
    public function getProductName()
    {
        return $this->getData(self::PRODUCT_NAME);
    }

    /**
     * @inheritDoc
     */
    public function setProductName($value)
    {
        return $this->setData(self::PRODUCT_NAME, $value);
    }

    /**
     * @inheritDoc
     */
    public function getProductUrl()
    {
        return $this->getData(self::PRODUCT_URL);
    }

    /**
     * @inheritDoc
     */
    public function setProductUrl($value)
    {
        return $this->setData(self::PRODUCT_URL, $value);
    }

    /**
     * @inheritDoc
     */
    public function getProductThumbnail()
    {
        return $this->getData(self::PRODUCT_THUMBNAIL);
    }

    /**
     * @inheritDoc
     */
    public function setProductThumbnail($value)
    {
        return $this->setData(self::PRODUCT_THUMBNAIL, $value);
    }

    /**
     * @inheritDoc
     */
    public function getProductSku()
    {
        return $this->getData(self::PRODUCT_SKU);
    }

    /**
     * @inheritDoc
     */
    public function setProductSku($value)
    {
        return $this->setData(self::PRODUCT_SKU, $value);
    }

    /**
     * @inheritDoc
     */
    public function getQty()
    {
        return $this->getData(self::PRODUCT_QTY);
    }

    /**
     * @inheritDoc
     */
    public function setQty($value)
    {
        return $this->setData(self::PRODUCT_QTY, $value);
    }

    /**
     * @inheritDoc
     */
    public function getPrice()
    {
        return $this->getData(self::PRODUCT_PRICE);
    }

    /**
     * @inheritDoc
     */
    public function setPrice($value)
    {
        return $this->setData(self::PRODUCT_PRICE, $value);
    }
}
