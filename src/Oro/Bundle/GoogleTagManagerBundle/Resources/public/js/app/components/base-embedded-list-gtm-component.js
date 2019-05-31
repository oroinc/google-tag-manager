define(function(require) {
    var BaseEmbeddedListGtmComponent;
    var BaseComponent = require('oroui/js/app/components/base/component');
    var $ = require('jquery');
    var _ = require('underscore');

    /**
     * Base component for listening to oro:embedded-list:* events and invoking corresponding GTM events.
     */
    BaseEmbeddedListGtmComponent = BaseComponent.extend({
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
         * @inheritDoc
         */
        constructor: function BaseEmbeddedListGtmComponent() {
            BaseEmbeddedListGtmComponent.__super__.constructor.apply(this, arguments);
        },

        /**
         * @inheritDoc
         */
        initialize: function(options) {
            BaseEmbeddedListGtmComponent.__super__.initialize.apply(this, arguments);

            if (!this.embeddedListComponent) {
                throw new Error('Sibling component `embeddedListComponent` is required.');
            }

            this.options = _.defaults(options || {}, this.options);
        },

        /**
         * @inheritDoc
         */
        delegateListeners: function() {
            this.embeddedListComponent.on('oro:embedded-list:shown', this._onImpression.bind(this));
            this.embeddedListComponent.on('oro:embedded-list:clicked', this._onClick.bind(this));

            BaseEmbeddedListGtmComponent.__super__.delegateListeners.apply(this, arguments);
        },

        /**
         * @param {jQuery} $shownItems
         * @private
         */
        _onImpression: function($shownItems) {
            var impressionsData = [];

            $shownItems.each((function(i, item) {
                var $item = $(item);
                var model = this._getModel($item);
                if (model) {
                    impressionsData.push(this._getImpressionData(model, this._getPosition($item)));
                }
            }).bind(this));

            if (impressionsData.length) {
                this._invokeEventImpression(impressionsData);
            }
        },

        /**
         * Implement this method to invoke gtm:event:push event for impression.
         *
         * @param {Array} impressionsData
         * @private
         */
        _invokeEventImpression: function(impressionsData) {
            throw new Error('This method should be implemented in descendant');
        },

        /**
         * Implement this method to get impression data of the viewed item.
         *
         * @param {Object} model Model of the viewed item
         * @param {Number} position Position in the list
         * @returns {Object}
         * @private
         */
        _getImpressionData: function(model, position) {
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

            var model = this._getModel($clickedItem);
            if (!model) {
                return;
            }

            var destinationUrl = event.currentTarget.href;
            if (event.which === 2 || event.altKey || event.shiftKey || event.metaKey) {
                destinationUrl = null;
            } else {
                // Prevent going by the link destination URL. We will get there in GTM eventCallback.
                event.preventDefault();
            }

            var position = this._getPosition($clickedItem);
            var clicksData = [this._getClickData(model, position)];

            this._invokeEventClick(clicksData, destinationUrl);
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
         * Implement this method to get impression data of the clicked item.
         *
         * @param {Object} model Model of the clicked item
         * @param {Number} position Position in the list
         * @returns {Object}
         * @private
         */
        _getClickData: function(model, position) {
            throw new Error('This method should be implemented in descendant');
        },

        /**
         * @inheritDoc
         */
        dispose: function() {
            if (this.disposed) {
                return;
            }

            this.embeddedListComponent.off('oro:embedded-list:shown', this._onImpression.bind(this));
            this.embeddedListComponent.off('oro:embedded-list:clicked', this._onClick.bind(this));

            BaseEmbeddedListGtmComponent.__super__.dispose.apply(this, arguments);
        }
    });

    return BaseEmbeddedListGtmComponent;
});
