define(function(require) {
    var $ = require('jquery');
    var _ = require('underscore');

    /**
     * Provides product details ready for GTM events related to products.
     */
    var productsDetailsGtmHelper = {
        /**
         * @property {String}
         */
        modelAwareSelector: '[data-gtm-model]',

        /**
         * @param {HTMLElement|jQuery.Element} element HTML or jQuery element representing a product
         * @returns {Object|undefined}
         */
        getModel: function(element) {
            var model = $(element).find(productsDetailsGtmHelper.modelAwareSelector).data('gtmModel');
            if (typeof model !== 'undefined' && typeof model.id !== 'undefined' && typeof model.name !== 'undefined') {
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
                ['id', 'name', 'category', 'brand', 'price'],
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
            var model = this.getModel(element);
            if (!model) {
                return undefined;
            }

            return this.getDetailsFromModel(model);
        }
    };

    return productsDetailsGtmHelper;
});
