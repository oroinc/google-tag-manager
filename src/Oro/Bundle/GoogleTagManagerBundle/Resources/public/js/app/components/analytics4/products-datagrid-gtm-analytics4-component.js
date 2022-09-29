define(function(require) {
    'use strict';

    const mediator = require('oroui/js/mediator');
    const BaseComponent = require('oroui/js/app/components/base/component');
    const $ = require('jquery');
    const _ = require('underscore');
    const productDetailsGtmGa4Helper = require('orogoogletagmanager/js/app/product-details-gtm-analytics4-helper');
    const localeSettings = require('orolocale/js/locale-settings');

    /**
     * Handles clicks on products datagrid to invoke GTM "select_item" event.
     */
    const ProductsDatagridGtmAnalytics4Component = BaseComponent.extend({
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
            batchSize: 30,
            listName: ''
        }),

        get _gtmReady() {
            return mediator.execute({name: 'gtm:data-layer-manager:isReady', silent: true}) || false;
        },

        /**
         * @property {jQuery.Element}
         */
        $datagridEl: null,

        /**
         * @inheritdoc
         */
        constructor: function ProductsDatagridGtmAnalytics4Component(options) {
            ProductsDatagridGtmAnalytics4Component.__super__.constructor.call(this, options);
        },

        /**
         * @inheritdoc
         */
        initialize(options) {
            ProductsDatagridGtmAnalytics4Component.__super__.initialize.call(this, options);

            if (!this.productsDatagridComponent) {
                throw new Error('Sibling component `productsDatagridComponent` is required.');
            }

            this.options = _.defaults(options || {}, this.options);

            this.$datagridEl = this.productsDatagridComponent.$el;

            mediator.once('page:afterChange', this._onView.bind(this));
        },

        /**
         * @inheritdoc
         */
        delegateListeners() {
            ProductsDatagridGtmAnalytics4Component.__super__.delegateListeners.call(this);

            // Both click and mouseup needed to be able to track both left and middle buttons clicks.
            this.$datagridEl.on(`click.${this.cid} mouseup.${this.cid}`,
                this.options.productSelector + ' a', this._onClick.bind(this));

            this.listenTo(this.productsDatagridComponent.grid, 'content:update', this._onView.bind(this));
        },

        /**
         * @private
         */
        _onView() {
            const productsDetails = [];
            const listName = this._getListName();

            this.$datagridEl.find(this.options.productSelector).each((function(i, product) {
                const details = this._getProductDetails(product);
                if (details) {
                    productsDetails.push(_.extend(details, {item_list_name: listName}));
                }
            }).bind(this));

            _.each(this._chunk(productsDetails, this.options.batchSize), function(productsDetailsChunk) {
                mediator.trigger(
                    'gtm:event:analytics4:view_item_list',
                    productsDetailsChunk,
                    localeSettings.getCurrency()
                );
            });
        },

        /**
         * @param {jQuery.Event} event
         * @private
         */
        _onClick(event) {
            if (!event || event.isDefaultPrevented()) {
                return;
            }

            if (event.type === 'mouseup' && event.which !== 2) {
                // Skips when mouseup triggered for the left mouse button, as it will be fired by click afterwards.
                return;
            }

            // Skips links without new url ("javascript:void(null)", "#" and equal)
            const link = event.currentTarget;
            if (link.protocol !== window.location.protocol ||
                (link.pathname === window.location.pathname && link.search === window.location.search)
            ) {
                return;
            }

            const product = $(event.currentTarget).parents(this.options.productSelector)[0];
            const productDetails = this._getClickData(product);
            if (!productDetails) {
                return;
            }

            let destinationUrl = link.href;
            if (event.which === 2 || event.altKey || event.shiftKey || event.metaKey) {
                destinationUrl = null;
            } else if (this._gtmReady) {
                // Prevent going by the link destination URL. We will get there in GTM eventCallback.
                event.preventDefault();
            }

            mediator.trigger('gtm:event:analytics4:select_item', [productDetails], destinationUrl, this._getListName());
        },

        /**
         * @param {HTMLElement} product
         * @returns {Object|undefined}
         * @private
         */
        _getProductDetails(product) {
            const details = productDetailsGtmGa4Helper.getDetails(product);
            if (!details) {
                return undefined;
            }

            return _.extend({}, details, {
                index: this._getIndex(product),
                view_mode: this.productsDatagridComponent.themeOptions.currentRowView || 'default'
            });
        },

        /**
         * @param {HTMLElement} product
         * @returns {Object|undefined}
         * @private
         */
        _getClickData(product) {
            const details = this._getProductDetails(product);
            if (!details) {
                return undefined;
            }

            return _.extend({}, details, {
                currency: localeSettings.getCurrency()
            });
        },

        /**
         * @param {HTMLElement} product
         * @returns {Number}
         * @private
         */
        _getIndex(product) {
            return $(this.$datagridEl).find(this.options.productSelector).index(product);
        },

        /**
         * @returns {String} List name
         * @private
         */
        _getListName() {
            return this.options.listName;
        },

        /**
         * Chunks an array into multiple arrays, each containing size or fewer items.
         *
         * @param {Array} array
         * @param {Number} size
         * @returns {Array}
         * @private
         */
        _chunk(array, size) {
            return array.reduce(function(res, item, index) {
                if (index % size === 0) {
                    res.push([]);
                }
                res[res.length - 1].push(item);

                return res;
            }, []);
        },

        /**
         * @inheritdoc
         */
        dispose() {
            if (this.disposed) {
                return;
            }

            this.$datagridEl.off(`.${this.cid}`);

            ProductsDatagridGtmAnalytics4Component.__super__.dispose.call(this);
        }
    });

    return ProductsDatagridGtmAnalytics4Component;
});
