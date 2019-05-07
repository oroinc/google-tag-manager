define(function (require) {
    var BaseComponent = require('oroui/js/app/components/base/component');
    var mediator = require('oroui/js/mediator');

    return BaseComponent.extend({
        /**
         * @property {Object}
         */
        _dataLayer: null,

        /**
         * @property {Object}
         */
        listen: {
            'gtm:event:push mediator': '_onPush'
        },

        /**
         * @param {Object} options
         */
        initialize: function (options) {
            this._dataLayer = window[options.dataLayerName];

            mediator.trigger('gtm:data-layer-manager:ready');
        },

        getDataLayer: function () {
            return this._dataLayer;
        },

        /**
         * @param {Object} data
         * @private
         */
        _onPush: function (data) {
            this.getDataLayer().push(data);
        }
    });
});
