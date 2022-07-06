define(function(require) {
    const BaseComponent = require('orogoogletagmanager/js/app/components/base-embedded-list-gtm-component');
    const mediator = require('oroui/js/mediator');
    const _ = require('underscore');

    /**
     * Listens to oro:embedded-list:* events and invokes promo click, promo impression GTM events.
     *
     * @deprecated Will be removed in oro/google-tag-manager-bundle:5.1.0.
     */
    const HomePageSliderGtmComponent = BaseComponent.extend({
        /**
         * @property {Object}
         */
        options: _.extend({}, BaseComponent.prototype.options, {
            nameSelector: '[data-role="slide-content"] h2'
        }),

        /**
         * @inheritdoc
         */
        constructor: function HomePageSliderGtmComponent(options) {
            HomePageSliderGtmComponent.__super__.constructor.call(this, options);
        },

        /**
         * @inheritdoc
         */
        _invokeEventView: function(viewData) {
            mediator.trigger('gtm:event:promotionImpressions', viewData);
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
        _getViewData: function(model, position) {
            return {
                name: model['name'],
                creative: this._getBlockName(),
                position: position
            };
        },

        /**
         * @inheritdoc
         */
        _invokeEventClick: function(clicksData, destinationUrl) {
            mediator.trigger('gtm:event:promotionClick', clicksData, destinationUrl);
        },

        /**
         * @inheritdoc
         */
        _getClickData: function(model, position) {
            return this._getViewData(model, position);
        }
    });

    return HomePageSliderGtmComponent;
});
