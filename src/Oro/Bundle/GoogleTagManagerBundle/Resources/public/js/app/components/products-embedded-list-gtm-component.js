define(function(require) {
    var ProductsEmbeddedListGtmComponent;
    var BaseComponent = require('orogoogletagmanager/js/app/components/base-embedded-list-gtm-component');
    var mediator = require('oroui/js/mediator');
    var _ = require('underscore');
    var localeSettings = require('orolocale/js/locale-settings');
    var productDetailsGtmHelper = require('orogoogletagmanager/js/app/product-details-gtm-helper');

    /**
     * Listens to oro:embedded-list:* events and invokes product click, product impression GTM events,
     */
    ProductsEmbeddedListGtmComponent = BaseComponent.extend({
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
            return productDetailsGtmHelper.getModel($item);
        },

        /**
         * @inheritDoc
         */
        _getImpressionData: function(model, position) {
            return _.extend({}, productDetailsGtmHelper.getDetailsFromModel(model), {
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
            return _.extend({}, productDetailsGtmHelper.getDetailsFromModel(model), {
                position: position
            });
        }
    });

    return ProductsEmbeddedListGtmComponent;
});
