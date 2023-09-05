<?php
/*
 * Copyright (c) 2023.
 * Fred Nguyen
 * email: fred.nguyen.17@gmail.com
 */

namespace Frednguyen\ProductPriceRange\Api\Data;

interface PriceRangeProductInterface
{

    const PRODUCT_NAME = 'product_name';
    const PRODUCT_SKU = 'product_sku';
    const PRODUCT_URL = 'product_url';
    const PRODUCT_THUMBNAIL = 'product_thumbnail';
    const PRODUCT_QTY = 'qty';
    const PRODUCT_PRICE = 'price';


    /**
     * @return string
     */
    public function getProductName();

    /**
     *
     * @param string $value
     * @return $this
     */
    public function setProductName($value);

    /**
     * @return string
     */
    public function getProductUrl();

    /**
     *
     * @param string $value
     * @return $this
     */
    public function setProductUrl($value);

    /**
     * @return string
     */
    public function getProductSku();

    /**
     *
     * @param string $value
     * @return $this
     */
    public function setProductSku($value);

    /**
     * @return int
     */
    public function getQty();

    /**
     *
     * @param int $value
     * @return $this
     */
    public function setQty($value);

    /**
     * @return string
     */
    public function getPrice();

    /**
     *
     * @param string $value
     * @return $this
     */
    public function setPrice($value);

    /**
     * @return string
     */
    public function getProductThumbnail();

    /**
     *
     * @param string $value
     * @return $this
     */
    public function setProductThumbnail($value);

}
