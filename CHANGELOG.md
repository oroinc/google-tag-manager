The upgrade instructions are available at [Oro documentation website](https://doc.oroinc.com/master/backend/setup/upgrade-to-new-version/).

The current file describes significant changes in the code that may affect the upgrade of your customizations.

## Changes in the Google Tag Manager package versions

- [5.1.0](#510-2023-03-31)
- [4.1.0](#410-2020-01-31)


## UNRELEASED

### Changed

#### GoogleTagManagerBundle
* Changed the GTM data layer payload of `add_to_cart`, `remove_from_cart`, `view_item`, `begin_checkout`, `add_shipping_info`, `add_payment_info` events:
  * Added `value` element
  * Removed `currency` element when `value` is not present - affects only chunked payloads (when an original payload is divided into chunks by 30 items each)

## 5.1.0 (2023-03-31)

### Added

#### GoogleTagManagerBundle
* Added `\Oro\Bundle\CheckoutBundle\Action\DefaultPaymentMethodSetter` (`oro_checkout.action.default_payment_method_setter`)
  for setting default payment method for Checkout if it is not already set to comply with similar behavior for shipping method.
  Updated the corresponding checkout workflows:
  * b2b_flow_checkout
  * b2b_flow_checkout_single_page
  * b2b_flow_alternative_checkout
* Added `\Oro\Bundle\CheckoutBundle\Event\CheckoutSourceEntityClearEvent` dispatching in `clear_checkout_source_entity` action.
* Added `\Oro\Bundle\GoogleTagManagerBundle\DataLayer\DataLayerManager::append` and 
  `\Oro\Bundle\GoogleTagManagerBundle\DataLayer\DataLayerManager::prepend` methods.
* Added `\Oro\Bundle\GoogleTagManagerBundle\Provider\DataCollectionStateProviderInterface` and 
  `\Oro\Bundle\GoogleTagManagerBundle\Provider\DataCollectionStateProvider` that collects data collection states from
  the inner providers tagged by `oro_google_tag_manager.data_collection_state_provider`.
* Added `oro_google_tag_manager_data_collection_state` layout data provider for checking if a certain data collection 
  type is enabled.
* Added events listeners, providers, layout blocks and layout updates enabling data collection for Google Analytics 4.
* Added search index field `gtm_analytics4_product_detail` for storing product model needed for
  Google Analytics 4. 
* Added a separate JS component `orogoogletagmanager/js/app/components/analytics4/data-layer-manager-analytics4-component`
  for handling Google Analytics 4 events via `mediator`.
* Added `oro_google_tag_manager_analytics4_product_detail` TWIG function to provide data for product model needed for
  Google Analytics 4.

### Changed

#### GoogleTagManagerBundle
* Changed the order of payment method views in the array returning from 
  `\Oro\Bundle\PaymentBundle\Method\View\CompositePaymentMethodViewProvider::getPaymentMethodViews` - now 
  the order of payment method views is the same as in the `$identifiers` input argument.
* JS module `orogoogletagmanager/js/app/components/analytics4/home-page-slider-gtm-analytics4-component` to `orogoogletagmanager/js/app/components/analytics4/image-slider-gtm-analytics4-component`.

### Removed

#### GoogleTagManagerBundle
* Removed `\Oro\Bundle\GoogleTagManagerBundle\DataLayer\DataLayerManager::add`, use 
  `\Oro\Bundle\GoogleTagManagerBundle\DataLayer\DataLayerManager::append` instead.
* System config `oro_google_tag_manager.enabled_data_collection_types` is removed from the System Configuration UI
  because the only one available option left - `google_analytics4`.
* Removed event listeners and providers because of the dropped support of Universal Analytics:
  * `\Oro\Bundle\GoogleTagManagerBundle\EventListener\CheckoutEventListener` (`oro_google_tag_manager.event_listener.checkout`)
  * `\Oro\Bundle\GoogleTagManagerBundle\EventListener\ProductDatagridProductDetailListener` (`oro_google_tag_manager.event_listener.frontend_product_datagrid_product_detail`)
  * `\Oro\Bundle\GoogleTagManagerBundle\EventListener\ProductListProductDetailListener` (`oro_google_tag_manager.event_listener.product_list.product_detail`)
  * `\Oro\Bundle\GoogleTagManagerBundle\EventListener\ShoppingListLineItemEventListener` (`oro_google_tag_manager.event_listener.shopping_list_line_item`)
  * `\Oro\Bundle\GoogleTagManagerBundle\EventListener\RequestProductItemEventListener` (`oro_google_tag_manager.event_listener.request_product_item`)
  * `\Oro\Bundle\GoogleTagManagerBundle\EventListener\WebsiteSearchIndexerListener` (`oro_google_tag_manager.event_listener.website_search_index`)
  * `\Oro\Bundle\GoogleTagManagerBundle\Provider\Checkout\CheckoutDetailProvider` (`oro_google_tag_manager.provider.checkout_detail`) 
    and `\Oro\Bundle\GoogleTagManagerBundle\Layout\DataProvider\CheckoutDataProvider` (`oro_google_tag_manager.layout.data_provider.checkout_data`)
  * `\Oro\Bundle\GoogleTagManagerBundle\Provider\CheckoutStepProvider` (`oro_google_tag_manager.provider.checkout_step`)
  * `\Oro\Bundle\GoogleTagManagerBundle\Provider\Checkout\PurchaseDetailProvider` (`oro_google_tag_manager.provider.purchase_detail`)
  * `\Oro\Bundle\GoogleTagManagerBundle\Provider\ProductDetailProvider` (`oro_google_tag_manager.provider.product_detail`)
* Removed JS components and views related to Universal Analytics:
  * `orogoogletagmanager/js/app/components/checkout-gtm-component`
  * `orogoogletagmanager/js/app/components/home-page-slider-gtm-component`
  * `orogoogletagmanager/js/app/components/products-datagrid-gtm-component`
  * `orogoogletagmanager/js/app/components/products-embedded-list-gtm-component`
  * `orogoogletagmanager/js/app/components/purchase-gtm-component`
  * `orogoogletagmanager/js/app/components/shopping-list-gtm-component`
  * `orogoogletagmanager/js/app/product-details-gtm-helper`
  * `orogoogletagmanager/js/app/views/product-gtm-view`
* Removed `mediator` event handlers related to Universal Analytics in `orogoogletagmanager/js/app/components/data-layer-manager-component`
  JS component.
* Removed TWIG templates, layout blocks and layout updates related to Universal Analytics.
* Removed `oro_google_tag_manager_product_detail` TWIG function related to Universal Analytics.
* Removed `product_detail` search index field of Product entity, make use of the new `gtm_analytics4_product_detail` instead.
* Removed `gtm:data-layer-manager:ready` mediator event, use mediator handler instead: `mediator.execute({name: 'gtm:data-layer-manager:isReady', silent: true}) || false`.

## 4.1.0 (2020-01-31)

### Removed
* `*.class` parameters for all entities were removed from the dependency injection container.
The entity class names should be used directly, e.g. `'Oro\Bundle\EmailBundle\Entity\Email'`
instead of `'%oro_email.email.entity.class%'` (in service definitions, datagrid config files, placeholders, etc.), and
`\Oro\Bundle\EmailBundle\Entity\Email::class` instead of `$container->getParameter('oro_email.email.entity.class')`
(in PHP code).
