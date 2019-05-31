define(function(require) {
    'use strict';

    var CheckoutGtmComponent;
    var BaseComponent = require('oroui/js/app/components/base/component');
    var mediator = require('oroui/js/mediator');
    var _ = require('underscore');

    CheckoutGtmComponent = BaseComponent.extend({
        /**
         * @property {Object}
         */
        options: {
            data: []
        },

        /**
         * @inheritDoc
         */
        constructor: function CheckoutGtmComponent() {
            CheckoutGtmComponent.__super__.constructor.apply(this, arguments);
        },

        /**
         * @param {Object} options
         */
        initialize: function(options) {
            this.options = _.defaults(options || {}, this.options);

            mediator.trigger('gtm:event:push', this.options.data);
        }
    });

    return CheckoutGtmComponent;
});
