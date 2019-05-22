define(function(require) {
    var HomePageSliderGtmComponent;
    var BaseComponent = require('orogoogletagmanager/js/app/components/base-embedded-list-gtm-component');
    var mediator = require('oroui/js/mediator');
    var _ = require('underscore');

    /**
     * Listens to oro:embedded-list:* events and invokes promo click, promo impression GTM events,
     */
    HomePageSliderGtmComponent = BaseComponent.extend({
        /**
         * @property {Object}
         */
        options: _.extend({}, BaseComponent.prototype.options, {
            nameSelector: '.promo-slider__title'
        }),

        /**
         * @inheritDoc
         */
        constructor: function HomePageSliderGtmComponent() {
            HomePageSliderGtmComponent.__super__.constructor.apply(this, arguments);
        },

        /**
         * @inheritDoc
         */
        _invokeEventImpression: function(impressionsData) {
            mediator.trigger(
                'gtm:event:push',
                {
                    'event': 'promotionImpression',
                    'ecommerce': {
                        'promoView': {
                            'promotions': impressionsData
                        }
                    }
                }
            );
        },

        /**
         * @inheritDoc
         */
        _getModel: function($item) {
            return {
                name: $item.find(this.options.nameSelector).text().trim()
            };
        },

        /**
         * @inheritDoc
         */
        _getImpressionData: function(model, index) {
            return {
                name: model['name'],
                creative: this._getBlockName(),
                position: index
            };
        },

        /**
         * @inheritDoc
         */
        _invokeEventClick: function(clicksData, destinationUrl) {
            mediator.trigger('gtm:event:push', {
                'event': 'promotionClick',
                'ecommerce': {
                    'promoClick': {
                        'promotions': clicksData
                    }
                },
                'eventCallback': function() {
                    document.location = destinationUrl;
                }
            });
        },

        /**
         * @inheritDoc
         */
        _getClickData: function(model, index) {
            return this._getImpressionData(model, index);
        }
    });

    return HomePageSliderGtmComponent;
});
