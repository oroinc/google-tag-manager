define(function(require) {
    'use strict';

    const BaseView = require('oroui/js/app/views/base/view');
    const _ = require('underscore');
    const mediator = require('oroui/js/mediator');
    const localeSettings = require('orolocale/js/locale-settings');

    const ProductGtmAnalytics4View = BaseView.extend({
        /**
         * @property {Object}
         */
        options: {
            data: {}
        },

        /**
         * @inheritdoc
         */
        constructor: function ProductGtmAnalytics4View(options) {
            ProductGtmAnalytics4View.__super__.constructor.call(this, options);
        },

        /**
         * @inheritdoc
         */
        initialize: function(options) {
            ProductGtmAnalytics4View.__super__.initialize.call(this, options);

            this.options = _.defaults(options || {}, this.options);

            const viewData = this.options.data;
            if (viewData.price !== undefined) {
                viewData.price = parseFloat(viewData.price);
            }

            mediator.trigger('gtm:event:analytics4:view_item', [this.options.data], localeSettings.getCurrency());
        }
    });

    return ProductGtmAnalytics4View;
});
