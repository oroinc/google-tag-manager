import BaseView from 'oroui/js/app/views/base/view';
import _ from 'underscore';
import mediator from 'oroui/js/mediator';
import localeSettings from 'orolocale/js/locale-settings';

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

        mediator.trigger('gtm:event:analytics4:view_item', [this.options.data], localeSettings.getCurrency());
    }
});

export default ProductGtmAnalytics4View;
