import BaseComponent from 'oroui/js/app/components/base/component';
import mediator from 'oroui/js/mediator';
import _ from 'underscore';

const DataLayerPushComponent = BaseComponent.extend({
    /**
     * @property {Object}
     */
    options: {
        data: [],
        clear: true
    },

    constructor: function DataLayerPushComponent(options) {
        DataLayerPushComponent.__super__.constructor.call(this, options);
    },

    /**
     * @param {Object} options
     */
    initialize: function(options) {
        this.options = _.defaults(options || {}, this.options);

        _.each(this.options.data, (function(data) {
            mediator.trigger('gtm:event:push', data, this.options.clear);
        }).bind(this));
    }
});

export default DataLayerPushComponent;
