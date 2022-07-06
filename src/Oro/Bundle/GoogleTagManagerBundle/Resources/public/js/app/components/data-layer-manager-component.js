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
            'gtm:event:push mediator': '_onPush'
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
         * @param {Boolean} [clear] Clear ecommerce object before pushing data. True by default.
         * @private
         */
        _onPush: function(data, clear = true) {
            if (clear) {
                this.getDataLayer().push({ecommerce: null});
            }

            this.getDataLayer().push(data);
        }
    });

    return DataLayerManagerComponent;
});
