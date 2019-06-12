define(function(require) {
    'use strict';

    var ProductGtmView;
    var BaseView = require('oroui/js/app/views/base/view');
    var _ = require('underscore');
    var mediator = require('oroui/js/mediator');
    var localeSettings = require('orolocale/js/locale-settings');

    ProductGtmView = BaseView.extend({
        /**
         * @property {Object}
         */
        options: {
            data: {}
        },

        /**
         * @inheritDoc
         */
        constructor: function ProductGtmView() {
            ProductGtmView.__super__.constructor.apply(this, arguments);
        },

        /**
         * @inheritDoc
         */
        initialize: function(options) {
            ProductGtmView.__super__.initialize.apply(this, arguments);

            this.options = _.defaults(options || {}, this.options);

            mediator.trigger('gtm:event:productDetail', [this.options.data], localeSettings.getCurrency());
        }
    });

    return ProductGtmView;
});
