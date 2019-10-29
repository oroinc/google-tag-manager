define(function(require) {
    'use strict';

    const BaseComponent = require('oroui/js/app/components/base/component');
    const mediator = require('oroui/js/mediator');
    const $ = require('jquery');
    const _ = require('underscore');

    const DataLayerManagerComponent = BaseComponent.extend({
        /**
         * @property {Object}
         */
        options: _.extend({}, BaseComponent.prototype.options, {
            dataLayerName: ''
        }),

        /**
         * @property {Array}
         */
        _dataLayer: [],

        /**
         * @property {Object}
         */
        listen: {
            'gtm:event:push mediator': '_onPush',
            'gtm:event:promotionClick mediator': '_onPromotionClick',
            'gtm:event:promotionImpressions mediator': '_onPromotionImpressions',
            'gtm:event:productClick mediator': '_onProductClick',
            'gtm:event:productDetail mediator': '_onProductDetail',
            'gtm:event:productImpressions mediator': '_onProductImpressions'
        },

        /**
         * @inheritDoc
         */
        constructor: function DataLayerManagerComponent(options) {
            DataLayerManagerComponent.__super__.constructor.call(this, options);
        },

        /**
         * @param {Object} options
         */
        initialize: function(options) {
            DataLayerManagerComponent.__super__.initialize.call(this, options);

            this.options = _.defaults(options || {}, this.options);
        },

        /**
         * @inheritDoc
         */
        delegateListeners: function() {
            DataLayerManagerComponent.__super__.delegateListeners.call(this);

            const gtmLoaded = $.Deferred();
            const dataLayer = window[this.options.dataLayerName];
            if (dataLayer instanceof Array && dataLayer.push !== Array.prototype.push) {
                // Google Tag Manager has been already loaded.
                gtmLoaded.resolve();
            } else {
                window.addEventListener('gtm:loaded', function() {
                    gtmLoaded.resolve();
                }, {once: true});
            }

            const pageLoaded = $.Deferred();
            mediator.once('page:afterChange', function() {
                pageLoaded.resolve();
            });

            // Wait until GTM is loaded and page is fully ready.
            $.when([gtmLoaded, pageLoaded]).done((function() {
                // Copy dataLayer contents if we already have something queued to push to data layer.
                const dataLayerOld = this._dataLayer;

                this._dataLayer = window[this.options.dataLayerName];

                if (dataLayerOld.length) {
                    // Push queued content to data layer.
                    _.each(dataLayerOld, (function(item) {
                        this._onPush(item);
                    }).bind(this));
                }

                mediator.trigger('gtm:data-layer-manager:ready');
            }).bind(this));
        },

        /**
         * @returns {Array}
         */
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
                event: 'promotionClick',
                ecommerce: {
                    promoClick: {
                        promotions: clicksData
                    }
                },
                eventCallback: function() {
                    if (destinationUrl) {
                        document.location = destinationUrl;
                    }
                }
            });
        },

        /**
         * @param {Object} impressionsData
         * @private
         */
        _onPromotionImpressions: function(impressionsData) {
            this._onPush({
                event: 'promotionImpression',
                ecommerce: {
                    promoView: {
                        promotions: impressionsData
                    }
                }
            });
        },

        /**
         * @param {Object} detailData
         * @param {String} [currencyCode]
         * @param {String} [listName]
         * @private
         */
        _onProductDetail: function(detailData, currencyCode, listName) {
            const data = {
                event: 'productDetail',
                ecommerce: {
                    currencyCode: currencyCode,
                    detail: {
                        products: detailData
                    }
                }
            };

            if (listName) {
                data['ecommerce']['detail']['actionField'] = {list: listName};
            }

            this._onPush(data);
        },

        /**
         * @param {Object} clicksData
         * @param {String} destinationUrl
         * @param {String} [listName]
         * @private
         */
        _onProductClick: function(clicksData, destinationUrl, listName) {
            const data = {
                event: 'productClick',
                ecommerce: {
                    click: {
                        products: clicksData
                    }
                },
                eventCallback: function() {
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
            const data = {
                event: 'productImpression',
                ecommerce: {
                    currencyCode: currencyCode,
                    impressions: impressionsData
                }
            };

            if (currencyCode) {
                data['ecommerce']['currencyCode'] = currencyCode;
            }

            this._onPush(data);
        }
    });

    return DataLayerManagerComponent;
});
