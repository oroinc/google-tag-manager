define(function(require) {
    const BaseComponent = require('oroui/js/app/components/base/component');
    const mediator = require('oroui/js/mediator');
    const $ = require('jquery');
    const _ = require('underscore');

    /**
     * Base component for listening to oro:embedded-list:* events and invoking corresponding GTM events.
     */
    const BaseEmbeddedListGtmComponent = BaseComponent.extend({
        relatedSiblingComponents: {
            // The option must be overridden in 'data-page-component-options' with the name of the related instance
            // of embedded list component.
            embeddedListComponent: 'embedded-list-component'
        },

        /**
         * @property {Object}
         */
        options: _.extend({}, BaseComponent.prototype.options, {
            blockName: ''
        }),

        /**
         * @property {Boolean}
         */
        _gtmReady: false,

        /**
         * @inheritdoc
         */
        constructor: function BaseEmbeddedListGtmComponent(options) {
            BaseEmbeddedListGtmComponent.__super__.constructor.call(this, options);
        },

        /**
         * @inheritdoc
         */
        initialize: function(options) {
            BaseEmbeddedListGtmComponent.__super__.initialize.call(this, options);

            if (!this.embeddedListComponent) {
                throw new Error('Sibling component `embeddedListComponent` is required.');
            }

            this.options = _.defaults(options || {}, this.options);
        },

        /**
         * @inheritdoc
         */
        delegateListeners: function() {
            mediator.once('gtm:data-layer-manager:ready', this._onGtmReady, this);

            this.embeddedListComponent.on('oro:embedded-list:shown', this._onView.bind(this));
            this.embeddedListComponent.on('oro:embedded-list:clicked', this._onClick.bind(this));

            BaseEmbeddedListGtmComponent.__super__.delegateListeners.call(this);
        },

        _onGtmReady: function() {
            this._gtmReady = true;
        },

        /**
         * @param {jQuery} $shownItems
         * @private
         */
        _onView: function($shownItems) {
            const viewData = [];

            $shownItems.each((function(i, item) {
                const $item = $(item);
                const model = this._getModel($item);
                if (model) {
                    viewData.push(this._getViewData(model, this._getPosition($item)));
                }
            }).bind(this));

            if (viewData.length) {
                this._invokeEventView(viewData);
            }
        },

        /**
         * Implement this method to invoke gtm:event:push event for view item list.
         *
         * @param {Array} viewData
         * @private
         */
        _invokeEventView: function(viewData) {
            throw new Error('This method should be implemented in descendant');
        },

        /**
         * Implement this method to provide the data for the viewed item.
         *
         * @param {Object} model Model of the viewed item
         * @param {Number} index Position in the list
         * @returns {Object}
         * @private
         */
        _getViewData: function(model, index) {
            throw new Error('This method should be implemented in descendant');
        },

        /**
         * Implement this method to get model from the viewed item.
         *
         * @param {jQuery.Element} $item
         * @returns {Object|undefined}
         * @private
         */
        _getModel: function($item) {
            throw new Error('This method should be implemented in descendant');
        },

        /**
         * @param {jQuery.Element} $item
         * @returns {Number}
         * @private
         */
        _getPosition: function($item) {
            return $(this.embeddedListComponent.$el).find(this.embeddedListComponent.options.itemSelector).index($item);
        },

        /**
         * @returns {String} Embedded block name
         * @private
         */
        _getBlockName: function() {
            return this.options.blockName;
        },

        /**
         * @param {jQuery.Element} $clickedItem
         * @param {jQuery.Event} event
         * @private
         */
        _onClick: function($clickedItem, event) {
            if (!event || event.isDefaultPrevented()) {
                return;
            }

            const model = this._getModel($clickedItem);
            if (!model) {
                return;
            }

            const link = event.currentTarget;
            let destinationUrl = link.href;
            if (event.which === 2 || event.altKey || event.shiftKey || event.metaKey || link.target === '_blank') {
                destinationUrl = null;
            }

            const index = this._getPosition($clickedItem);
            const clicksData = [this._getClickData(model, index)];

            this._invokeEventClick(clicksData, destinationUrl);

            if (destinationUrl !== null && clicksData.eventCallback) {
                // Prevent going by the link destination URL. We will get there in GTM eventCallback.
                event.preventDefault();

                if (!this._gtmReady) {
                    // Calls data layer eventCallback manually because GTM is not initialized yet.
                    clicksData.eventCallback();
                }
            }
        },

        /**
         * Implement this method to invoke gtm:event:push event for click.
         *
         * @param {Array} clicksData Array of data of clicked items
         * @param {String} destinationUrl URL of the clicked link
         * @private
         */
        _invokeEventClick: function(clicksData, destinationUrl) {
            throw new Error('This method should be implemented in descendant');
        },

        /**
         * Implement this method to provide the data for the clicked item.
         *
         * @param {Object} model Model of the clicked item
         * @param {Number} index Position in the list
         * @returns {Object}
         * @private
         */
        _getClickData: function(model, index) {
            throw new Error('This method should be implemented in descendant');
        },

        /**
         * @inheritdoc
         */
        dispose: function() {
            if (this.disposed) {
                return;
            }

            mediator.off('gtm:data-layer-manager:ready', this._onGtmReady, this);

            this.embeddedListComponent.off('oro:embedded-list:shown', this._onView.bind(this));
            this.embeddedListComponent.off('oro:embedded-list:clicked', this._onClick.bind(this));

            BaseEmbeddedListGtmComponent.__super__.dispose.call(this);
        }
    });

    return BaseEmbeddedListGtmComponent;
});
