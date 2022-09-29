import BaseComponent from 'oroui/js/app/components/base/component';
import mediator from 'oroui/js/mediator';

mediator.setHandler('gtm:data-layer-manager:isReady', () => false);

const DataLayerManagerComponent = BaseComponent.extend({
    /**
     * @property {Object}
     */
    options: Object.assign({}, BaseComponent.prototype.options, {
        dataLayerName: ''
    }),

    /**
     * @property {Array}
     */
    _dataLayer: null,

    /**
     * @property {Object}
     */
    listen: {
        'gtm:event:push mediator': '_onPush'
    },

    /**
     * @inheritdoc
     */
    constructor: function DataLayerManagerComponent(options) {
        DataLayerManagerComponent.__super__.constructor.call(this, options);
    },

    /**
     * @param {Object} options
     */
    initialize(options) {
        DataLayerManagerComponent.__super__.initialize.call(this, options);
        this.options = Object.assign({}, this.options, options || {});
        this._dataLayer = window[this.options.dataLayerName] || [];

        this._deferredInit();
        const onGTMLoaded = () => {
            mediator.setHandler('gtm:data-layer-manager:isReady', () => true);
            this._resolveDeferredInit();
        };
        const gtmScript = document.querySelector('script[data-gtm-integration]');

        if (
            // there's no gtm.js script, in case the integration is stubbed for a test
            !gtmScript ||
            // gtm.js already loaded
            gtmScript.loadDone ||
            // gtm.js is failed to load, resolve deferredInit to unblock UI (there's already system error in console)
            gtmScript.loadError
        ) {
            onGTMLoaded();
        } else {
            window.addEventListener('gtm:loaded', onGTMLoaded, {once: true});
            // gtm.js is failed to load, resolve deferredInit to unblock UI (there's already system error in console)
            window.addEventListener('gtm:error', onGTMLoaded, {once: true});
        }
    },

    /**
     * @param {Object} data
     * @param {boolean=} clear Clear ecommerce object before pushing data. True by default.
     * @private
     */
    _onPush(data, clear = true) {
        if (clear) {
            this._dataLayer.push({ecommerce: null});
        }

        this._dataLayer.push(data);
    }
});

export default DataLayerManagerComponent;
