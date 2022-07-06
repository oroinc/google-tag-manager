define(function(require) {
    'use strict';

    const BaseComponent = require('oroui/js/app/components/base/component');
    const mediator = require('oroui/js/mediator');
    const $ = require('jquery');
    const _ = require('underscore');
    const localeSettings = require('orolocale/js/locale-settings');

    const DataLayerManagerComponent = BaseComponent.extend({
        /**
         * @property {Object}
         */
        options: _.extend({}, BaseComponent.prototype.options, {
            dataLayerName: '',
            // The number of milliseconds for which the calls to "eventCallback" are to be delayed to avoid duplicated
            // calls when multiple containers are present on a page.
            eventCallbackDebounceTimeout: 100
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

            // @deprecated Will be removed in oro/google-tag-manager-bundle:5.1.0.
            'gtm:event:promotionClick mediator': '_onPromotionClick',
            // @deprecated Will be removed in oro/google-tag-manager-bundle:5.1.0.
            'gtm:event:promotionImpressions mediator': '_onPromotionImpressions',
            // @deprecated Will be removed in oro/google-tag-manager-bundle:5.1.0.
            'gtm:event:productClick mediator': '_onProductClick',
            // @deprecated Will be removed in oro/google-tag-manager-bundle:5.1.0.
            'gtm:event:productDetail mediator': '_onProductDetail',
            // @deprecated Will be removed in oro/google-tag-manager-bundle:5.1.0.
            'gtm:event:productImpressions mediator': '_onProductImpressions'
        },

        /**
         * @inheritdoc
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
         * @inheritdoc
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
         * @param {Boolean} [clear] Clear ecommerce object before pushing data. False by default.
         * @private
         */
        _onPush: function(data, clear = false) {
            if (clear) {
                this.getDataLayer().push({ecommerce: null});
            }

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
                eventCallback: this._getClickLinkCallback(destinationUrl)
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
                    currencyCode: localeSettings.getCurrency(),
                    click: {
                        products: clicksData
                    }
                },
                eventCallback: this._getClickLinkCallback(destinationUrl)
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
        },

        /**
         * Returns callback for "eventCallback" that is called after the "productClick", "promotionClick" events
         * are triggered.
         *
         * @param {String} destinationUrl
         * @private
         */
        _getClickLinkCallback: function(destinationUrl) {
            return _.debounce(function() {
                if (destinationUrl) {
                    document.location = destinationUrl;
                }
            }, this.options.eventCallbackDebounceTimeout);
        }
    });

    return DataLayerManagerComponent;
});
