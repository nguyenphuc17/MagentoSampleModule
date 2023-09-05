/*
 * Copyright (c) 2023.
 * Fred Nguyen
 * email: fred.nguyen.17@gmail.com
 */

define([
    'jquery',
    'uiComponent',
    'ko',
    'Frednguyen_ProductPriceRange/js/action/get-products'
    ], function ($, Component, ko, getProducts) {
        'use strict';
        return Component.extend({
            defaults: {
                template: 'Frednguyen_ProductPriceRange/product-list'
            },
            initialize: function () {
                this.products = ko.observableArray([]);
                this.maxPrice = ko.observable('');
                this.minPrice = ko.observable('');
                this.sortOrder = ko.observable('asc');
                this.notFoundTxt = ko.observable('');
                this._super();
            },
            getListProduct: function () {
                if ($('#price-range').valid()){
                    $('body').trigger('processStart');
                    let requestData = {
                        min_price:this.minPrice(),
                        max_price:this.maxPrice(),
                        order_by:this.sortOrder()
                    };
                    getProducts(requestData, this.products, this.notFoundTxt).always(
                        function () {
                            $('body').trigger('processStop');
                        }
                    );
                }
            },
            resetForm: function (){
                this.products([]);
                this.notFoundTxt('');
                this.maxPrice('');
                this.minPrice('');
                this.sortOrder('asc');
                $("div.mage-error").remove();
                $(".mage-error").removeClass("mage-error");
            }
        });
    }
);
