define(function(require) {
    'use strict';

    const BaseView = require('oroui/js/app/views/base/view');
    const _ = require('underscore');
    const mediator = require('oroui/js/mediator');
    const localeSettings = require('orolocale/js/locale-settings');

    /**
     * @deprecated Will be removed in oro/google-tag-manager-bundle:5.1.0.
     */
    const ProductGtmView = BaseView.extend({
        /**
         * @property {Object}
         */
        options: {
            data: {}
        },

        /**
         * @inheritdoc
         */
        constructor: function ProductGtmView(options) {
            ProductGtmView.__super__.constructor.call(this, options);
        },

        /**
         * @inheritdoc
         */
        initialize: function(options) {
            ProductGtmView.__super__.initialize.call(this, options);

            this.options = _.defaults(options || {}, this.options);

            mediator.trigger('gtm:event:productDetail', [this.options.data], localeSettings.getCurrency());
        }
    });

    return ProductGtmView;
});
