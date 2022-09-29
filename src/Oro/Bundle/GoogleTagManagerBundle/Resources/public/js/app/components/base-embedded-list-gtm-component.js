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
         * @property {boolean}
         */
        get _gtmReady() {
            return mediator.execute({name: 'gtm:data-layer-manager:isReady', silent: true}) || false;
        },

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
                const embeddedListComponentName = options.relatedSiblingComponents.__initial__.embeddedListComponent;
                throw new Error(`EmbeddedListComponent with name "${embeddedListComponentName}" is not found, ` +
                    'it is required for GTM integration.');
            }

            this.options = _.defaults(options || {}, this.options);
        },

        /**
         * @inheritdoc
         */
        delegateListeners: function() {
            this.listenTo(this.embeddedListComponent, {
                'oro:embedded-list:shown': this._onImpression,
                'oro:embedded-list:clicked': this._onClick
            });

            BaseEmbeddedListGtmComponent.__super__.delegateListeners.call(this);
        },

        _onGtmReady: function() {
            // @deprecated
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
                    if (typeof this._getImpressionData !== 'undefined') {
                        viewData.push(this._getImpressionData(model, this._getPosition($item)));
                    } else {
                        viewData.push(this._getViewData(model, this._getPosition($item)));
                    }
                }
            }).bind(this));

            if (viewData.length) {
                if (typeof this._invokeEventImpression !== 'undefined') {
                    this._invokeEventImpression(viewData);
                } else {
                    this._invokeEventView(viewData);
                }
            }
        },

        /**
         * @param {jQuery} $shownItems
         * @private
         *
         * @deprecated Use _onView instead.
         */
        _onImpression: function($shownItems) {
            this._onView($shownItems);
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
        }
    });

    return BaseEmbeddedListGtmComponent;
});
