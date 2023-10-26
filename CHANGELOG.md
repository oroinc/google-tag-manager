The upgrade instructions are available at [Oro documentation website](https://doc.oroinc.com/master/backend/setup/upgrade-to-new-version/).

The current file describes significant changes in the code that may affect the upgrade of your customizations.

## Changes in the Google Tag Manager package versions



## UNRELEASED

### Changed

#### GoogleTagManagerBundle
* Changed the GTM data layer payload of `add_to_cart`, `remove_from_cart`, `view_item`, `begin_checkout`, `add_shipping_info`, `add_payment_info` events:
    * Added `value` element
    * Removed `currency` element when `value` is not present - affects only chunked payloads (when an original payload is divided into chunks by 30 items each)



## 4.1.0 (2020-01-31)

### Removed
* `*.class` parameters for all entities were removed from the dependency injection container.
The entity class names should be used directly, e.g. `'Oro\Bundle\EmailBundle\Entity\Email'`
instead of `'%oro_email.email.entity.class%'` (in service definitions, datagrid config files, placeholders, etc.), and
`\Oro\Bundle\EmailBundle\Entity\Email::class` instead of `$container->getParameter('oro_email.email.entity.class')`
(in PHP code).
