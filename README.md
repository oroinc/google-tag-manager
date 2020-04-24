OroGoogleTagManagerBundle
=========================

Table of content
-----------------
- [Overview](#overview)
- [Getting Started](#getting-started)
- [Add Server Side Events (How to's)](#add-server-side-events)
    - [Create Custom Collector](#create-custom-collector)
    - [Manual Add Event To DataLayerManager](#manual-add-event-to-DataLayerManager)
- [Add Client Side Events (How to's)](#add-client-side-events)
    - [Product Events](#product-events)
    - [Push GTM Message In JavaScript](#push-gtm-message-in-javascript)

## Overview
Adds integration with Google Tag Manager. Integration can be set on a website level.

## Getting Started
To create a new integration with Google Tag Manager in your Oro application:

1. Navigate to **System > Integrations > Manage Integrations** in the main menu.
2. Click **Create Integration** on the top right.
3. In the **Type** field, select **Google Tag Manager**.
4. In the **Name** field, provide the name for the integration you are creating to refer to it in the Oro application. Since you can create many Google Tag Manager integrations, make sure the name is meaningful.
5. In the **Container ID** field, provide the container ID that follows the *GTM-XXXXXXX* pattern. You can find container ID in your Google Tag Manager Account.
6. In the **Status** field, set the integration to *Active* to enable it. Should you need to disable it, select *Inactive* from the list.
7. In the **Default Owner**, select the owner of the integration.
8. Click **Save and Close**.

Once the integration is saved, it becomes available in the Integrations grid under **System > Integrations > Manage Integrations**.

To enable a Google Tag Manager integration for data mapping, connect it to the application in the system settings:
1. Navigate to **System > Configuration** in the main menu.
2. In the panel to the left, click **System Configuration > Integrations > Google Settings**.
3. In the **Google Tag Manager Settings** section, clear the **Use Default** check box and select a Google Tag Manager Integration from the list.


## Add Server Side Events

### Create Custom Collector
The easiest way to add a GTM message to a web page is to create a custom collector class.
Your collector class must implement `\Oro\Bundle\GoogleTagManagerBundle\DataLayer\Collector\CollectorInterface`
and be tagged by `oro_google_tag_manager.data_layer.collector`.
For example:

```php
<?php

namespace Acme\Bundle\AcmeBundle\DataLayer\Collector;

use Doctrine\Common\Collections\Collection;
use Oro\Bundle\GoogleTagManagerBundle\DataLayer\Collector\CollectorInterface;

class CustomCollector implements CollectorInterface
{
    public function handle(Collection $data): void
    {
        $data->add([
            'event' => 'acmeEventName',
            'my-custom-key' => 'My custom data',
        ]);
    }
}
```

```yaml
services:
    acme_bundle.data_layer.collector.user_detail:
        class: Acme\Bundle\AcmeBundle\DataLayer\Collector\CustomCollector
        tags:
            - { name: oro_google_tag_manager.data_layer.collector }

```

### Manual Add Event To DataLayerManager
In cases when you need to add data to the GTM data layer manually, use service `oro_google_tag_manager.data_layer.manager` directly.
The example below illustrates adding an event when the entity changes:

```php
<?php

namespace Acme\Bundle\AcmeBundle\EventListener;

use Acme\Bundle\AcmeBunde\Entity\SomeEntity;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Oro\Bundle\GoogleTagManagerBundle\DataLayer\DataLayerManager;
use Oro\Bundle\GoogleTagManagerBundle\Provider\GoogleTagManagerSettingsProviderInterface;

class ExampleEventListener
{
    /** @var DataLayerManager */
    private $dataLayerManager;

    /** @var GoogleTagManagerSettingsProviderInterface */
    private $settingsProvider;

    /** @var array */
    private $data = [];

    public function __construct(
        DataLayerManager $dataLayerManager,
        GoogleTagManagerSettingsProviderInterface $settingsProvider
    ) {
        $this->dataLayerManager = $dataLayerManager;
        $this->settingsProvider = $settingsProvider;
    }

    public function preUpdate(SomeEntity $entity, PreUpdateEventArgs $args): void
    {
        // Check enabled GTM integration
        if (!$this->isApplicable()) {
            return;
        }

        // For example, we will add message when changing a specific field 
        if ($args->hasChangedField('someFieldName')) {
            $this->data[] = [
                'oldValue' => $args->getOldValue('someFieldName'),
                'newValue' => $args->getNewValue('someFieldName'),
            ];
        }
    }

    public function postFlush(): void
    {
        // Add all collected messages to DataLayerManager
        foreach ($this->data as $data) {
            $this->dataLayerManager->add([
                'event' => 'acmeSomeEntityUpdate',
                'entityUpdate' => $data,
            ]);
        }

        // Clear listener
        $this->onClear();
    }

    public function onClear(): void
    {
        $this->data = [];
    }

    private function isApplicable(): bool
    {
        // Check enable GTM integration
        if (!$this->settingsProvider->getGoogleTagManagerSettings()) {
            return false;
        }
        
        // If necessary, check any other global conditions to apply this listener
        
        return true;
    }
}
```

Register this listener as a service:
```yaml
services:
    oro_google_tag_manager.event_listener.checkout:
    acme_bundle.event_listener.example:
        class: Acme\Bundle\AcmeBundle\EventListener\ExampleEventListener
        public: false
        arguments:
            - '@oro_google_tag_manager.data_layer.manager'
            - '@oro_google_tag_manager.provider.google_tag_manager_settings'
        tags:
            - { name: doctrine.orm.entity_listener, entity: 'Acme\Bundle\AcmeBunde\Entity\SomeEntity', event: preUpdate }
            - { name: doctrine.event_listener, event: postFlush }
            - { name: doctrine.event_listener, event: onClear }
```

## Add Client Side Events

### Product Events
Service `oro_google_tag_manager.provider.product_detail` is responsible for transferring product data to Google Analytics.
Below is an example of updating the product block for product lists via layout update functionality:

```yaml
layout:
    actions:
        - '@setBlockTheme':
            themes: 'OroGoogleTagManagerBundle:layouts:blank/imports/oro_product_list_item/oro_product_list_item.html.twig'
        - '@add':
            id: __google_tag_manager_product_model_expose
            parentId: __product
            blockType: block
            options:
                # This block must be rendered only when GTM integration is active
                visible: '=data["oro_google_tag_manager_settings"].isReady()'

```

```twig
{% block __oro_product_list_item__google_tag_manager_product_model_expose_widget %}
    {% if product is defined %}
        {# In this block, we have a Product entity from which we need to get data #}
        {% set productDetail = oro_google_tag_manager_product_detail(product) %}
        {% set attr = layout_attr_defaults(attr, {'~class': ' hidden', 'data-gtm-model': productDetail}) %}
        <div {{ block('block_attributes') }}></div>
    {% endif %}
{% endblock %}
```
 
See more in
[products-embedded-list-gtm-component.js](src/Oro/Bundle/GoogleTagManagerBundle/Resources/public/js/app/components/products-embedded-list-gtm-component.js)
and [product-details-gtm-helper.js](src/Oro/Bundle/GoogleTagManagerBundle/Resources/public/js/app/product-details-gtm-helper.js)

### Push GTM Message In JavaScript
When you need push some data to GTM data layer from javascript code, you need to trigger event `gtm:event:push`.
For example:
```javascript
var mediator = require('oroui/js/mediator');
mediator.trigger('gtm:event:push', {
    event: 'eventName',
    anyEventKeys: 'Any event data'
});
```
