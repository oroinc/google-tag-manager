define(function(require) {
    'use strict';

    var BaseComponent = require('oroui/js/app/components/base/component');
    var mediator = require('oroui/js/mediator');

    return BaseComponent.extend({
        /**
         * @property {Object}
         */
        listen: {
            'gtm:data-layer-manager:ready mediator': '_handle'
        },

        /**
         * @property {Object}
         */
        options: {
            step: null
        },

        /**
         * @param {Object} options
         */
        initialize: function (options) {
            this.options = _.defaults(options || {}, this.options);

            // mediator.trigger('gtm:event:push', {
            //     'event': 'checkout',
            //     'ecommerce': {
            //         'checkout': {
            //             'actionField': {'step': this.options.step},
            //             'products': [{
            //                 'name': 'T-Shirt',
            //                 'id': '12345',
            //                 'price': '15.25',
            //                 'brand': 'ACME',
            //                 'category': 'Apparel',
            //                 'variant': 'Gray',
            //                 'quantity': 1
            //             }]
            //         }
            //     }
            // });
        },

        _handle: function () {

        }
    });
});
