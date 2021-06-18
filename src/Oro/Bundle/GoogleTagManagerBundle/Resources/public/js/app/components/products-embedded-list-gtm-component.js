define(function(require) {
    const BaseComponent = require('orogoogletagmanager/js/app/components/base-embedded-list-gtm-component');
    const mediator = require('oroui/js/mediator');
    const _ = require('underscore');
    const localeSettings = require('orolocale/js/locale-settings');
    const productDetailsGtmHelper = require('orogoogletagmanager/js/app/product-details-gtm-helper');

    /**
     * Listens to oro:embedded-list:* events and invokes product click, product impression GTM events,
     */
    const ProductsEmbeddedListGtmComponent = BaseComponent.extend({
        /**
         * @inheritdoc
         */
        constructor: function ProductsEmbeddedListGtmComponent(options) {
            ProductsEmbeddedListGtmComponent.__super__.constructor.call(this, options);
        },

        /**
         * @inheritdoc
         */
        initialize: function(options) {
            ProductsEmbeddedListGtmComponent.__super__.initialize.call(this, options);
        },

        /**
         * @inheritdoc
         */
        _invokeEventImpression: function(impressionsData) {
            mediator.trigger('gtm:event:productImpressions', impressionsData, localeSettings.getCurrency());
        },

        /**
         * @inheritdoc
         */
        _getModel: function($item) {
            return productDetailsGtmHelper.getModel($item);
        },

        /**
         * @inheritdoc
         */
        _getImpressionData: function(model, position) {
            return _.extend({}, productDetailsGtmHelper.getDetailsFromModel(model), {
                list: this._getBlockName(),
                position: position
            });
        },

        /**
         * @inheritdoc
         */
        _invokeEventClick: function(clicksData, destinationUrl) {
            mediator.trigger('gtm:event:productClick', clicksData, destinationUrl, this._getBlockName());
        },

        /**
         * @inheritdoc
         */
        _getClickData: function(model, position) {
            return _.extend({}, productDetailsGtmHelper.getDetailsFromModel(model), {
                position: position
            });
        }
    });

    return ProductsEmbeddedListGtmComponent;
});
