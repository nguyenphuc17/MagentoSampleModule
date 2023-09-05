<?php
/*
 * Copyright (c) 2023.
 * Fred Nguyen
 * email: fred.nguyen.17@gmail.com
 */

namespace Frednguyen\ProductPriceRange\Api;


/**
 * @api
 * @since 100.0.2
 */
interface PriceRangeProductManagementInterface
{
    /**
     * @param int $min_price
     * @param int $max_price
     * @param string $order_by
     * @return \Frednguyen\ProductPriceRange\Api\Data\PriceRangeProductInterface[]
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getProducts(int $min_price, int $max_price, string $order_by = 'asc'): array;

}
