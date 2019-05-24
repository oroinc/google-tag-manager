define(function(require) {
    var BaseComponent = require('oroui/js/app/components/base/component');
    var mediator = require('oroui/js/mediator');

    return BaseComponent.extend({
        /**
         * @property {Object}
         */
        _dataLayer: null,

        /**
         * @property {Object}
         */
        listen: {
            'gtm:event:push mediator': '_onPush',
            'gtm:event:promotionClick mediator': '_onPromotionClick',
            'gtm:event:promotionImpressions mediator': '_onPromotionImpressions',
            'gtm:event:productClick mediator': '_onProductClick',
            'gtm:event:productImpressions mediator': '_onProductImpressions'
        },

        /**
         * @param {Object} options
         */
        initialize: function(options) {
            this._dataLayer = window[options.dataLayerName];

            mediator.trigger('gtm:data-layer-manager:ready');
        },

        getDataLayer: function() {
            return this._dataLayer;
        },

        /**
         * @param {Object} data
         * @private
         */
        _onPush: function(data) {
            this.getDataLayer().push(data);
        },

        /**
         * @param {Object} clicksData
         * @param {String} destinationUrl
         * @private
         */
        _onPromotionClick: function(clicksData, destinationUrl) {
            this._onPush({
                'event': 'promotionClick',
                'ecommerce': {
                    'promoClick': {
                        'promotions': clicksData
                    }
                },
                'eventCallback': function() {
                    document.location = destinationUrl;
                }
            });
        },

        /**
         * @param {Object} impressionsData
         * @private
         */
        _onPromotionImpressions: function(impressionsData) {
            this._onPush({
                'event': 'promotionImpression',
                'ecommerce': {
                    'promoView': {
                        'promotions': impressionsData
                    }
                }
            });
        },

        /**
         * @param {Object} clicksData
         * @param {String} destinationUrl
         * @param {String} [listName]
         * @private
         */
        _onProductClick: function(clicksData, destinationUrl, listName) {
            var data = {
                'event': 'productClick',
                'ecommerce': {
                    'click': {
                        'products': clicksData
                    }
                },
                'eventCallback': function() {
                    if (destinationUrl) {
                        document.location = destinationUrl;
                    }
                }
            };

            if (listName) {
                data['ecommerce']['click']['actionField'] = {list: listName};
            }

            this._onPush(data);
        },

        /**
         * @param {Object} impressionsData
         * @param {String} [currencyCode]
         * @private
         */
        _onProductImpressions: function(impressionsData, currencyCode) {
            var data = {
                'event': 'productImpression',
                'ecommerce': {
                    'currencyCode': currencyCode,
                    'impressions': impressionsData
                }
            };

            if (currencyCode) {
                data['ecommerce']['currencyCode'] = currencyCode;
            }

            this._onPush(data);
        }
    });
});
