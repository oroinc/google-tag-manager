define(function(require) {
    'use strict';

    var ShoppingListGtmComponent;
    var BaseComponent = require('oroui/js/app/components/base/component');
    var mediator = require('oroui/js/mediator');
    var _ = require('underscore');

    ShoppingListGtmComponent = BaseComponent.extend({
        /**
         * @property {Object}
         */
        options: {
            data: []
        },

        constructor: function ShoppingListGtmComponent() {
            ShoppingListGtmComponent.__super__.constructor.apply(this, arguments);
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
