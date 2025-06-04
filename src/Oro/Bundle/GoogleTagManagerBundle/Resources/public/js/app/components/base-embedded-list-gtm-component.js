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

        _gtmReady() {
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
        initialize(options) {
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
        delegateListeners() {
            this.listenTo(this.embeddedListComponent, {
                'oro:embedded-list:shown': this._onView,
                'oro:embedded-list:clicked': this._onClick
            });

            BaseEmbeddedListGtmComponent.__super__.delegateListeners.call(this);
        },
        /**
         * @param {jQuery} $shownItems
         * @protected
         */
        _onView($shownItems) {
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
         * @protected
         */
        _invokeEventView(viewData) {
            throw new Error('This method should be implemented in descendant');
        },

        /**
         * Implement this method to provide the data for the viewed item.
         *
         * @param {Object} model Model of the viewed item
         * @param {Number} index Position in the list
         * @returns {Object}
         * @protected
         */
        _getViewData(model, index) {
            throw new Error('This method should be implemented in descendant');
        },

        /**
         * Implement this method to get model from the viewed item.
         *
         * @param {jQuery.Element} $item
         * @returns {Object|undefined}
         * @protected
         */
        _getModel($item) {
            throw new Error('This method should be implemented in descendant');
        },

        /**
         * @param {jQuery.Element} $item
         * @returns {Number}
         * @protected
         */
        _getPosition($item) {
            return $(this.embeddedListComponent.$el).find(this.embeddedListComponent.options.itemSelector).index($item);
        },

        /**
         * @returns {String} Embedded block name
         * @protected
         */
        _getBlockName() {
            return this.options.blockName;
        },

        /**
         * @param {jQuery.Element} $clickedItem
         * @param {jQuery.Event} event
         * @protected
         */
        _onClick($clickedItem, event) {
            if (!event) {
                return;
            }

            const model = this._getModel($clickedItem);
            if (!model) {
                return;
            }

            const link = event.currentTarget;
            let destinationUrl = link.href;
            if (event.which === 2 ||
                event.altKey ||
                event.shiftKey ||
                event.metaKey ||
                link.target === '_blank' ||
                event.isDefaultPrevented()
            ) {
                destinationUrl = null;
            }

            const index = this._getPosition($clickedItem);
            const clicksData = [this._getClickData(model, index)];

            if (destinationUrl !== null && this._gtmReady()) {
                // Prevent going by the link destination URL. We will get there in GTM eventCallback.
                event.preventDefault();
            }

            this._invokeEventClick(clicksData, destinationUrl);
        },

        /**
         * Implement this method to invoke gtm:event:push event for click.
         *
         * @param {Array} clicksData Array of data of clicked items
         * @param {String} destinationUrl URL of the clicked link
         * @protected
         */
        _invokeEventClick(clicksData, destinationUrl) {
            throw new Error('This method should be implemented in descendant');
        },

        /**
         * Implement this method to provide the data for the clicked item.
         *
         * @param {Object} model Model of the clicked item
         * @param {Number} index Position in the list
         * @returns {Object}
         * @protected
         */
        _getClickData(model, index) {
            throw new Error('This method should be implemented in descendant');
        }
    });

    return BaseEmbeddedListGtmComponent;
});
