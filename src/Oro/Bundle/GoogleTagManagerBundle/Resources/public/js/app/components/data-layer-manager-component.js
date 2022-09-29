define(function(require) {
    'use strict';

    const BaseComponent = require('oroui/js/app/components/base/component');
    const mediator = require('oroui/js/mediator');
    const _ = require('underscore');
    const localeSettings = require('orolocale/js/locale-settings');

    mediator.setHandler('gtm:data-layer-manager:isReady', () => false);

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
        _dataLayer: null,

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
        initialize(options) {
            DataLayerManagerComponent.__super__.initialize.call(this, options);
            this.options = _.defaults(options || {}, this.options);
            this._dataLayer = window[this.options.dataLayerName] || [];

            this._deferredInit();
            const onGTMLoaded = () => {
                mediator.trigger('gtm:data-layer-manager:ready');
                mediator.setHandler('gtm:data-layer-manager:isReady', () => true);
                this._resolveDeferredInit();
            };
            const gtmScript = document.querySelector('script[data-gtm-integration]');

            if (
                // there's no gtm.js script, in case the integration is stubbed for a test
                !gtmScript ||
                // gtm.js already loaded
                gtmScript.loadDone ||
                // gtm.js load is failed, resolve deferredInit to unblock UI (there's already system error in console)
                gtmScript.loadError
            ) {
                onGTMLoaded();
            } else {
                window.addEventListener('gtm:loaded', onGTMLoaded, {once: true});
                // gtm.js load is failed, resolve deferredInit to unblock UI (there's already system error in console)
                window.addEventListener('gtm:error', onGTMLoaded, {once: true});
            }
        },

        /**
         * @returns {Array}
         */
        getDataLayer() {
            return this._dataLayer;
        },

        /**
         * @param {Object} data
         * @param {boolean} [clear] Clear ecommerce object before pushing data. False by default.
         * @private
         */
        _onPush(data, clear = false) {
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
