define(function(require) {
    var ProductsEmbeddedListGtmComponent;
    var BaseComponent = require('orogoogletagmanager/js/app/components/base-embedded-list-gtm-component');
    var mediator = require('oroui/js/mediator');
    var _ = require('underscore');
    var localeSettings = require('orolocale/js/locale-settings');

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
         * @inheritDoc
         */
        constructor: function ProductsEmbeddedListGtmComponent() {
            ProductsEmbeddedListGtmComponent.__super__.constructor.apply(this, arguments);
        },

        /**
         * @inheritDoc
         */
        _invokeEventImpression: function(impressionsData) {
            mediator.trigger(
                'gtm:event:push',
                {
                    'ecommerce': {
                        'currencyCode': localeSettings.getCurrency(),
                        'impressions': impressionsData
                    }
                }
            );
        },

        /**
         * @inheritDoc
         */
        _getModel: function($item) {
            var model = $item.find(this.options.modelAwareSelector).triggerHandler('gtm:model:get');
            if (typeof model !== 'undefined' && typeof model.id !== 'undefined' && typeof model.name !== 'undefined') {
                return model;
            }

            return undefined;
        },

        /**
         * @inheritDoc
         */
        _getImpressionData: function(model, index) {
            return _.extend({}, this._getProductDetailFromModel(model), {
                list: this._getBlockName(),
                position: index
            });
        },

        /**
         * @inheritDoc
         */
        _invokeEventClick: function(clicksData, destinationUrl) {
            mediator.trigger('gtm:event:push', {
                'event': 'productClick',
                'ecommerce': {
                    'click': {
                        'actionField': {
                            'list': this._getBlockName()
                        },
                        'products': clicksData
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
            return _.extend({}, this._getProductDetailFromModel(model), {
                position: index
            });
        },

        /**
         * @param {Object} model Model of the viewed item
         * @returns {Object} Prepared model data for event
         * @private
         */
        _getProductDetailFromModel: function (model) {
            return _.reduce(
                ['id', 'name', 'category', 'brand', 'price'],
                function (acc, attr) {
                    if (typeof model[attr] !== "undefined") {
                        acc[attr] = model[attr];
                    }

                    return acc;
                },
                {}
            );
        }
    });

    return ProductsEmbeddedListGtmComponent;
});
