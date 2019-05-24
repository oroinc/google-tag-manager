define(function(require) {
    var ProductsDatagridGtmComponent;
    var mediator = require('oroui/js/mediator');
    var BaseComponent = require('oroui/js/app/components/base/component');
    var _ = require('underscore');
    var ProductDetailsGtmHelper = require('orogoogletagmanager/js/app/product-details-gtm-helper');

    /**
     * Handles clicks on products datagrid to invoke GTM productClick events.
     */
    ProductsDatagridGtmComponent = BaseComponent.extend({
        relatedSiblingComponents: {
            // The option must be overridden in 'data-page-component-options' with the name of the related instance
            // of products datagrid component.
            productsDatagridComponent: 'frontend-product-search-grid'
        },

        /**
         * @property {Object}
         */
        options: _.extend({}, BaseComponent.prototype.options, {
            productSelector: '.grid-row.product-item',
            modelAwareSelector: '.gtm-product-model-exposed',
            listName: ''
        }),

        /**
         * @property {jQuery.Element}
         */
        $datagridEl: null,

        /**
         * @property {ProductDetailsGtmHelper}
         */
        productDetailsHelper: null,

        /**
         * @inheritDoc
         */
        constructor: function ProductsDatagridGtmComponent() {
            ProductsDatagridGtmComponent.__super__.constructor.apply(this, arguments);
        },

        /**
         * @inheritDoc
         */
        initialize: function(options) {
            ProductsDatagridGtmComponent.__super__.initialize.apply(this, arguments);

            if (!this.productsDatagridComponent) {
                throw new Error('Sibling component `productsDatagridComponent` is required.');
            }

            this.options = _.defaults(options || {}, this.options);

            this.$datagridEl = this.productsDatagridComponent.$el;

            this.productDetailsHelper = new ProductDetailsGtmHelper(this.options.modelAwareSelector);
        },

        /**
         * @inheritDoc
         */
        delegateListeners: function() {
            ProductsDatagridGtmComponent.__super__.delegateListeners.apply(this, arguments);

            // Both click and mouseup needed to be able to track both left and middle buttons clicks.
            this.$datagridEl.on('click mouseup', this.options.productSelector + ' a', this._onClick.bind(this));
        },

        /**
         * @param {jQuery.Event} event
         * @private
         */
        _onClick: function(event) {
            if (!event || event.isDefaultPrevented()) {
                return;
            }

            if (event.type === 'mouseup' && event.which !== 2) {
                // Skips when mouseup triggered for the left mouse button, as it will be fired by click afterwards.
                return;
            }

            // Skips links without new url ("javascript:void(null)", "#" and equal)
            var link = event.currentTarget;
            if (link.protocol !== window.location.protocol
                || (
                    link.pathname === window.location.pathname
                    && link.search === window.location.search
                )) {
                return;
            }

            var product = $(event.currentTarget).parents(this.options.productSelector)[0];
            var productDetails = this._getProductDetails(product);
            if (!productDetails) {
                return;
            }

            var destinationUrl = link.href;
            if (event.which === 2 || event.altKey || event.shiftKey || event.metaKey) {
                destinationUrl = null;
            } else {
                // Prevent going by the link destination URL. We will get there in GTM eventCallback.
                event.preventDefault();
            }

            mediator.trigger('gtm:event:productClick', [productDetails], destinationUrl, this._getListName());
        },

        /**
         * @param {HTMLElement} product
         * @returns {Object|undefined}
         * @private
         */
        _getProductDetails: function(product) {
            var details = this.productDetailsHelper.getDetails(product);
            if (!details) {
                return undefined;
            }

            return _.extend({}, details, {
                position: this._getPosition(product),
                viewMode: this.productsDatagridComponent.themeOptions.currentRowView || 'default'
            });
        },
        
        /**
         * @param {HTMLElement} product
         * @returns {Number}
         * @private
         */
        _getPosition: function(product) {
            return $(this.$datagridEl).find(this.options.productSelector).index(product);
        },

        /**
         * @returns {String} List name
         * @private
         */
        _getListName: function() {
            return this.options.listName;
        },

        /**
         * @inheritDoc
         */
        dispose: function() {
            if (this.disposed) {
                return;
            }

            this.$datagridEl.off('click mouseup', this.options.productSelector, this._onClick.bind(this));

            ProductsDatagridGtmComponent.__super__.dispose.apply(this, arguments);
        }
    });

    return ProductsDatagridGtmComponent;
});
