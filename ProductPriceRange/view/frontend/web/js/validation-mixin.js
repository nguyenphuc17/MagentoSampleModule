/*
 * Copyright (c) 2023.
 * Fred Nguyen
 * email: fred.nguyen.17@gmail.com
 */

define(['jquery'], function($) {
    'use strict';
    return function(targetWidget) {
        $.validator.addMethod(
            'validate-highest-price',
            function (value, element, params) {
                if ($.isNumeric($(params).val()) && $.isNumeric(value)) {
                    let lowVal = $(params).val();
                    let highVal = parseFloat($(params).val())*5;
                    return ((parseFloat(value) > lowVal) && (parseFloat(value) <= highVal));
                }

                return true;
            },
            'Please enter value greater than \"Low Range\" and no more than 5x higher than the entered \"Low Range\".'
        )
        return targetWidget;
    }
});
