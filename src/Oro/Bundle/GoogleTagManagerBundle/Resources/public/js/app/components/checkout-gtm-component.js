define(function(require) {
    'use strict';

    var BaseComponent = require('oroui/js/app/components/base/component');
    var mediator = require('oroui/js/mediator');

    return BaseComponent.extend({
        /**
         * @property {Object}
         */
        options: {
            data: []
        },

        /**
         * @param {Object} options
         */
        initialize: function (options) {
            this.options = _.defaults(options || {}, this.options);

            mediator.trigger('gtm:event:push', this.options.data);
        }
    });
});
