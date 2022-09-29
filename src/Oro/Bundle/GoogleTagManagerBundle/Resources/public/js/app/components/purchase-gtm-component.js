define(function(require) {
    'use strict';

    const BaseComponent = require('oroui/js/app/components/base/component');
    const mediator = require('oroui/js/mediator');
    const _ = require('underscore');

    /**
     * @deprecated Will be removed in oro/google-tag-manager-bundle:5.1.0.
     */
    const PurchaseGtmComponent = BaseComponent.extend({
        /**
         * @property {Object}
         */
        options: {
            data: []
        },

        /**
         * @property {boolean}
         */
        get _gtmReady() {
            return mediator.execute({name: 'gtm:data-layer-manager:isReady', silent: true}) || false;
        },

        /**
         * @inheritdoc
         */
        constructor: function PurchaseGtmComponent(options) {
            PurchaseGtmComponent.__super__.constructor.call(this, options);
        },

        /**
         * @param {Object} options
         */
        initialize: function(options) {
            this.options = _.defaults(options || {}, this.options);
            if (this._gtmReady) {
                this._onReady();
            } else {
                this.listenToOnce(mediator, 'gtm:data-layer-manager:ready', this._onReady);
            }
        },

        _onReady: function() {
            _.each(this.options.data, function(data) {
                mediator.trigger('gtm:event:push', data);
            });
        }
    });

    return PurchaseGtmComponent;
});
