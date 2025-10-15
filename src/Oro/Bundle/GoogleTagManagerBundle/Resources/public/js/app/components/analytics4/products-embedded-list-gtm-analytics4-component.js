import BaseEmbeddedListGtmComponent from 'orogoogletagmanager/js/app/components/base-embedded-list-gtm-component';
import mediator from 'oroui/js/mediator';
import _ from 'underscore';
import localeSettings from 'orolocale/js/locale-settings';
import productDetailsGtmGa4Helper from 'orogoogletagmanager/js/app/product-details-gtm-analytics4-helper';

/**
 * Listens to oro:embedded-list:* events and invokes "select_item" and "view_item_list" GTM events.
 */
const ProductsEmbeddedListGtmAnalytics4Component = BaseEmbeddedListGtmComponent.extend({
    /**
     * @inheritdoc
     */
    constructor: function ProductsEmbeddedListGtmAnalytics4Component(options) {
        ProductsEmbeddedListGtmAnalytics4Component.__super__.constructor.call(this, options);
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

export default ProductsEmbeddedListGtmAnalytics4Component;
