define(function(require) {
    'use strict';

    var ProductModelExposeComponent;
    var BaseComponent = require('oroui/js/app/components/base/component');

    /**
     * Allows to fetch GTM model from DOM element.
     */
    ProductModelExposeComponent = BaseComponent.extend({
        /**
         * @property {Object|null}
         */
        model: null,

        /**
         * @inheritDoc
         */
        constructor: function ProductModelExposeComponent() {
            ProductModelExposeComponent.__super__.constructor.apply(this, arguments);
        },

        /**
         * @inheritDoc
         */
        initialize: function(options) {
            ProductModelExposeComponent.__super__.initialize.apply(this, arguments);

            this.options = _.defaults(options || {}, this.options);
            this.$el = this.options._sourceElement;

            this.model = this.options.data;
        },

        /**
         * @inheritDoc
         */
        delegateListeners: function() {
            ProductModelExposeComponent.__super__.delegateListeners.apply(this, arguments);

            this.$el.on('gtm:model:get', this.getModel.bind(this));
        },

        /**
         * @returns {Object}
         */
        getModel: function() {
            return this.model;
        },

        /**
         * @inheritDoc
         */
        dispose: function() {
            if (this.disposed) {
                return;
            }

            this.$el.off('gtm:model:get', this.getModel.bind(this));

            ProductModelExposeComponent.__super__.dispose.apply(this, arguments);
        }
    });

    return ProductModelExposeComponent;
});
