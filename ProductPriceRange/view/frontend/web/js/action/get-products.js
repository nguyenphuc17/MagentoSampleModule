/*
 * Copyright (c) 2023.
 * Fred Nguyen
 * email: fred.nguyen.17@gmail.com
 */

define(
    [
        'ko',
        'jquery',
        'mage/storage'
    ],
    function (
        ko,
        $,
        storage
    ) {
        'use strict';
        return function (requestData, productsList, notFoundTxt) {
            return storage.post(
                'rest/V1/product-by-price-range',
                JSON.stringify(requestData),
                false
            ).done(
                function (response) {
                    if (response.length > 0) {
                        productsList(response);
                        notFoundTxt('');
                    }
                    else
                    {
                        productsList([]);
                        notFoundTxt('There is no product within the inputted price range');
                    }
                }
            ).fail(
                function () {
                    notFoundTxt('There are no product within the inputted price range');
                }
            );
        };
    }
);
