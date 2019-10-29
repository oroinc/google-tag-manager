define(function(require) {
    'use strict';

    const BaseComponent = require('oroui/js/app/components/base/component');
    const mediator = require('oroui/js/mediator');
    const _ = require('underscore');

    const ShoppingListGtmComponent = BaseComponent.extend({
        /**
         * @property {Object}
         */
        options: {
            data: []
        },

        constructor: function ShoppingListGtmComponent(options) {
            ShoppingListGtmComponent.__super__.constructor.call(this, options);
        },

        /**
         * @param {Object} options
         */
        initialize: function(options) {
            this.options = _.defaults(options || {}, this.options);

            _.each(this.options.data, function(data) {
                mediator.trigger('gtm:event:push', data);
            });
        }
    });

    return ShoppingListGtmComponent;
});
