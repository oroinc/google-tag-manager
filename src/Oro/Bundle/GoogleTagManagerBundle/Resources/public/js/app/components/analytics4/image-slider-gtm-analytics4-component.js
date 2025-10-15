import BaseEmbeddedListGtmComponent from 'orogoogletagmanager/js/app/components/base-embedded-list-gtm-component';
import mediator from 'oroui/js/mediator';
import _ from 'underscore';

/**
 * Listens to oro:embedded-list:* events and invokes promo "select_promotion, "view_promotion" GTM GA4 events
 */
const ImageSliderGtmAnalytics4Component = BaseEmbeddedListGtmComponent.extend({
    /**
     * @property {Object}
     */
    options: _.extend({}, BaseEmbeddedListGtmComponent.prototype.options, {
        nameSelector: '[data-role="slide-item-link"] img'
    }),

    /**
     * @inheritdoc
     */
    constructor: function ImageSliderGtmAnalytics4Component(options) {
        ImageSliderGtmAnalytics4Component.__super__.constructor.call(this, options);
    },

    /**
     * @inheritdoc
     */
    _invokeEventView(viewData) {
        mediator.trigger('gtm:event:analytics4:view_promotion', viewData);
    },

    /**
     * @inheritdoc
     */
    _getModel($item) {
        return {
            name: $item.find(this.options.nameSelector).attr('data-item-name')
        };
    },

    /**
     * @inheritdoc
     */
    _getViewData(model, index) {
        return {
            creative_name: this._getBlockName(),
            item_name: model['name'],
            index: index
        };
    },

    /**
     * @inheritdoc
     */
    _invokeEventClick(clicksData, destinationUrl) {
        mediator.trigger('gtm:event:analytics4:select_promotion', clicksData, destinationUrl);
    },

    /**
     * @inheritdoc
     */
    _getClickData(model, index) {
        return this._getViewData(model, index);
    }
});

export default ImageSliderGtmAnalytics4Component;
