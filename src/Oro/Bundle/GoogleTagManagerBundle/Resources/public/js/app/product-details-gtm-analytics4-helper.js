define(function(require) {
    const $ = require('jquery');
    const _ = require('underscore');

    /**
     * Provides product details ready for GTM Google Analytics 4 events related to products.
     */
    const productsDetailsGtmAnalytics4Helper = {
        /**
         * @property {String}
         */
        modelAwareSelector: '[data-gtm-analytics4-model]',

        /**
         * @param {HTMLElement|jQuery.Element} element HTML or jQuery element representing a product
         * @returns {Object|undefined}
         */
        getModel: function(element) {
            const model = $(element).find(productsDetailsGtmAnalytics4Helper.modelAwareSelector)
                .data('gtmAnalytics4Model');
            if (typeof model !== 'undefined' &&
                typeof model.item_id !== 'undefined' &&
                typeof model.item_name !== 'undefined'
            ) {
                return model;
            }

            return undefined;
        },

        /**
         * @param {Object} model Model of a product
         * @returns {Object} GTM product details
         */
        getDetailsFromModel: function(model) {
            return _.reduce(
                [
                    'item_id',
                    'item_name',
                    'item_category',
                    'item_category2',
                    'item_category3',
                    'item_category4',
                    'item_category5',
                    'item_brand',
                    'price'
                ],
                function(acc, attr) {
                    if (typeof model[attr] !== 'undefined') {
                        acc[attr] = model[attr];
                    }

                    return acc;
                },
                {}
            );
        },

        /**
         * @param {HTMLElement} element HTML element representing a product
         * @returns {Object|undefined} GTM product details
         */
        getDetails: function(element) {
            const model = this.getModel(element);
            if (!model) {
                return undefined;
            }

            return this.getDetailsFromModel(model);
        }
    };

    return productsDetailsGtmAnalytics4Helper;
});
