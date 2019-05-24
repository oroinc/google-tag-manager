define(function(require) {
    var ProductsEmbeddedListGtmComponent;
    var BaseComponent = require('orogoogletagmanager/js/app/components/base-embedded-list-gtm-component');
    var mediator = require('oroui/js/mediator');
    var _ = require('underscore');
    var localeSettings = require('orolocale/js/locale-settings');
    var ProductDetailsGtmHelper = require('orogoogletagmanager/js/app/product-details-gtm-helper');

    /**
     * Listens to oro:embedded-list:* events and invokes product click, product impression GTM events,
     */
    ProductsEmbeddedListGtmComponent = BaseComponent.extend({
        /**
         * @property {Object}
         */
        options: _.extend({}, BaseComponent.prototype.options, {
            modelAwareSelector: '.gtm-product-model-exposed'
        }),

        /**
         * @property {ProductDetailsGtmHelper}
         */
        productDetailsHelper: null,

        /**
         * @inheritDoc
         */
        constructor: function ProductsEmbeddedListGtmComponent() {
            ProductsEmbeddedListGtmComponent.__super__.constructor.apply(this, arguments);
        },

        /**
         * @inheritDoc
         */
        initialize: function(options) {
            ProductsEmbeddedListGtmComponent.__super__.initialize.apply(this, arguments);

            this.productDetailsHelper = new ProductDetailsGtmHelper(this.options.modelAwareSelector);
        },

        /**
         * @inheritDoc
         */
        _invokeEventImpression: function(impressionsData) {
            mediator.trigger('gtm:event:productImpressions', impressionsData, localeSettings.getCurrency());
        },

        /**
         * @inheritDoc
         */
        _getModel: function($item) {
            return this.productDetailsHelper.getModel($item);
        },

        /**
         * @inheritDoc
         */
        _getImpressionData: function(model, position) {
            return _.extend({}, this.productDetailsHelper.getDetailsFromModel(model), {
                list: this._getBlockName(),
                position: position
            });
        },

        /**
         * @inheritDoc
         */
        _invokeEventClick: function(clicksData, destinationUrl) {
            mediator.trigger('gtm:event:productClick', clicksData, destinationUrl, this._getBlockName());
        },

        /**
         * @inheritDoc
         */
        _getClickData: function(model, position) {
            return _.extend({}, this.productDetailsHelper.getDetailsFromModel(model), {
                position: position
            });
        }
    });

    return ProductsEmbeddedListGtmComponent;
});
