define(function(require) {
    const BaseComponent = require('orogoogletagmanager/js/app/components/base-embedded-list-gtm-component');
    const mediator = require('oroui/js/mediator');
    const _ = require('underscore');
    const localeSettings = require('orolocale/js/locale-settings');
    const productDetailsGtmGa4Helper = require('orogoogletagmanager/js/app/product-details-gtm-analytics4-helper');

    /**
     * Listens to oro:embedded-list:* events and invokes "select_item" and "view_item_list" GTM events.
     */
    const ProductsEmbeddedListGtmAnalytics4Component = BaseComponent.extend({
        /**
         * @inheritdoc
         */
        constructor: function ProductsEmbeddedListGtmAnalytics4Component(options) {
            ProductsEmbeddedListGtmAnalytics4Component.__super__.constructor.call(this, options);
        },

        /**
         * @inheritdoc
         */
        initialize: function(options) {
            ProductsEmbeddedListGtmAnalytics4Component.__super__.initialize.call(this, options);
        },

        /**
         * @inheritdoc
         */
        _invokeEventView: function(viewData) {
            mediator.trigger('gtm:event:analytics4:view_item_list', viewData, localeSettings.getCurrency());
        },

        /**
         * @inheritdoc
         */
        _getModel: function($item) {
            return productDetailsGtmGa4Helper.getModel($item);
        },

        /**
         * @inheritdoc
         */
        _getViewData: function(model, index) {
            return _.extend({}, productDetailsGtmGa4Helper.getDetailsFromModel(model), {
                item_list_name: this._getBlockName(),
                index: index
            });
        },

        /**
         * @inheritdoc
         */
        _invokeEventClick: function(clicksData, destinationUrl) {
            mediator.trigger('gtm:event:analytics4:select_item', clicksData, destinationUrl, this._getBlockName());
        },

        /**
         * @inheritdoc
         */
        _getClickData: function(model, index) {
            return _.extend({}, productDetailsGtmGa4Helper.getDetailsFromModel(model), {
                index: index,
                currency: localeSettings.getCurrency()
            });
        }
    });

    return ProductsEmbeddedListGtmAnalytics4Component;
});
