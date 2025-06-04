define(function(require) {
    'use strict';

    const BaseComponent = require('oroui/js/app/components/base/component');
    const mediator = require('oroui/js/mediator');
    const _ = require('underscore');

    const DataLayerManagerAnalytics4Component = BaseComponent.extend({
        /**
         * @property {Object}
         */
        listen: {
            'gtm:event:analytics4:select_promotion mediator': '_onSelectPromotion',
            'gtm:event:analytics4:view_promotion mediator': '_onViewPromotion',
            'gtm:event:analytics4:select_item mediator': '_onSelectItem',
            'gtm:event:analytics4:view_item mediator': '_onViewItem',
            'gtm:event:analytics4:view_item_list mediator': '_onViewItemList'
        },

        /**
         * @inheritdoc
         */
        constructor: function DataLayerManagerAnalytics4Component(options) {
            DataLayerManagerAnalytics4Component.__super__.constructor.call(this, options);
        },

        /**
         * @param {Object} options
         */
        initialize: function(options) {
            DataLayerManagerAnalytics4Component.__super__.initialize.call(this, options);

            this.options = _.defaults(options || {}, this.options);
        },

        /**
         * @param {Object} clicksData
         * @param {String} destinationUrl
         * @param {Boolean} [clear] Clear ecommerce object before pushing data. True by default.
         * @private
         */
        _onSelectPromotion: function(clicksData, destinationUrl, clear = true) {
            mediator.trigger('gtm:event:push', {
                event: 'select_promotion',
                ecommerce: {
                    items: clicksData
                },
                eventCallback: this._getClickLinkCallback(destinationUrl)
            }, clear);
        },

        /**
         * @param {Object} viewPromotionData
         * @param {Boolean} [clear] Clear ecommerce object before pushing data. True by default.
         * @private
         */
        _onViewPromotion: function(viewPromotionData, clear = true) {
            mediator.trigger('gtm:event:push', {
                event: 'view_promotion',
                ecommerce: {
                    items: viewPromotionData
                }
            }, clear);
        },

        /**
         * @param {Object} viewItemData
         * @param {String} [currencyCode]
         * @param {String} [listName]
         * @param {Boolean} [clear] Clear ecommerce object before pushing data. True by default.
         * @private
         */
        _onViewItem: function(viewItemData, currencyCode, listName, clear = true) {
            let subtotalValue = 0.0;
            for (const {price} of [...viewItemData]) {
                if (price) {
                    subtotalValue += price;
                }
            }

            const data = {
                event: 'view_item',
                ecommerce: {
                    currency: currencyCode,
                    value: subtotalValue,
                    items: viewItemData
                }
            };

            if (listName) {
                data['ecommerce']['item_list_name'] = listName;
            }

            mediator.trigger('gtm:event:push', data, clear);
        },

        /**
         * @param {Object} selectItemData
         * @param {String} destinationUrl
         * @param {String} [listName]
         * @param {Boolean} [clear] Clear ecommerce object before pushing data. True by default.
         * @private
         */
        _onSelectItem: function(selectItemData, destinationUrl, listName, clear = true) {
            const data = {
                event: 'select_item',
                ecommerce: {
                    items: selectItemData
                },
                eventCallback: this._getClickLinkCallback(destinationUrl)
            };

            if (listName) {
                data['ecommerce']['item_list_name'] = listName;
            }

            mediator.trigger('gtm:event:push', data, clear);
        },

        /**
         * @param {Object} viewItemListData
         * @param {String} [currencyCode]
         * @param {Boolean} [clear]
         * @private
         */
        _onViewItemList: function(viewItemListData, currencyCode, clear = true) {
            const data = {
                event: 'view_item_list',
                ecommerce: {
                    items: viewItemListData
                }
            };

            if (currencyCode) {
                data['ecommerce']['currency'] = currencyCode;
            }

            mediator.trigger('gtm:event:push', data, clear);
        },

        /**
         * Returns callback for "eventCallback" that is called after the "select_item", "select_promotion" events
         * are triggered.
         *
         * @param {String} destinationUrl
         * @private
         */
        _getClickLinkCallback: function(destinationUrl) {
            if (destinationUrl) {
                document.location = destinationUrl;
            }

            return {};
        }
    });

    return DataLayerManagerAnalytics4Component;
});
