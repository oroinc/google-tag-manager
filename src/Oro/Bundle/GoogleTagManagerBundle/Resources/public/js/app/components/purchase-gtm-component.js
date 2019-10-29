define(function(require) {
    'use strict';

    const BaseComponent = require('oroui/js/app/components/base/component');
    const mediator = require('oroui/js/mediator');
    const _ = require('underscore');

    const PurchaseGtmComponent = BaseComponent.extend({
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
        constructor: function PurchaseGtmComponent(options) {
            PurchaseGtmComponent.__super__.constructor.call(this, options);
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
