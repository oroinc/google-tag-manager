define(function(require) {
    'use strict';

    var PurchaseGtmComponent;
    var BaseComponent = require('oroui/js/app/components/base/component');
    var mediator = require('oroui/js/mediator');
    var _ = require('underscore');

    PurchaseGtmComponent = BaseComponent.extend({
        /**
         * @property {Object}
         */
        options: {
            data: []
        },

        /**
         * @property {Object}
         */
        listen: {
            'gtm:data-layer-manager:ready mediator': '_onReady'
        },

        /**
         * @inheritDoc
         */
        constructor: function PurchaseGtmComponent() {
            PurchaseGtmComponent.__super__.constructor.apply(this, arguments);
        },

        /**
         * @param {Object} options
         */
        initialize: function(options) {
            this.options = _.defaults(options || {}, this.options);
        },

        _onReady: function() {
            _.each(this.options.data, function(data) {
                mediator.trigger('gtm:event:push', data);
            });
        }
    });

    return PurchaseGtmComponent;
});
