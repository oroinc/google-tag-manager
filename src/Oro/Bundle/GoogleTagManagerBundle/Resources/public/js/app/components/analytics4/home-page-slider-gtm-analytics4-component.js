define(function(require) {
    const BaseComponent = require('orogoogletagmanager/js/app/components/base-embedded-list-gtm-component');
    const mediator = require('oroui/js/mediator');
    const _ = require('underscore');

    /**
     * Listens to oro:embedded-list:* events and invokes promo "select_promotion, "view_promotion" GTM GA4 events
     */
    const HomePageSliderGtmAnalytics4Component = BaseComponent.extend({
        /**
         * @property {Object}
         */
        options: _.extend({}, BaseComponent.prototype.options, {
            nameSelector: '[data-role="slide-content"] h2'
        }),

        /**
         * @inheritdoc
         */
        constructor: function HomePageSliderGtmAnalytics4Component(options) {
            HomePageSliderGtmAnalytics4Component.__super__.constructor.call(this, options);
        },

        /**
         * @inheritdoc
         */
        _invokeEventView: function(viewData) {
            mediator.trigger('gtm:event:analytics4:view_promotion', viewData);
        },

        /**
         * @inheritdoc
         */
        _getModel: function($item) {
            return {
                name: $item.find(this.options.nameSelector).text().trim()
            };
        },

        /**
         * @inheritdoc
         */
        _getViewData: function(model, index) {
            return {
                creative_name: this._getBlockName(),
                item_name: model['name'],
                index: index
            };
        },

        /**
         * @inheritdoc
         */
        _invokeEventClick: function(clicksData, destinationUrl) {
            mediator.trigger('gtm:event:analytics4:select_promotion', clicksData, destinationUrl);
        },

        /**
         * @inheritdoc
         */
        _getClickData: function(model, index) {
            return this._getViewData(model, index);
        }
    });

    return HomePageSliderGtmAnalytics4Component;
});
