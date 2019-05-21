<?php

namespace Oro\Bundle\GoogleTagManagerBundle\DataLayer\ConstantBag;

/**
 * Contains custom data layer attribute names and values.
 */
final class DataLayerAttributeBag
{
    public const KEY_PAGE_TYPE = 'pageCategory';

    /**
     * User type key. Customer or Visitor.
     */
    public const KEY_USER_TYPE = 'visitorType';

    /**
     * Category name. Needed on category page.
     */
    public const KEY_CATALOG_PATH = 'catalogPath';

    /**
     * Customer id. Needed if user is logged in as customer user.
     */
    public const KEY_CUSTOMER_ID = 'customerId';

    /**
     * Customer group name. Needed if user is logged in as customer user.
     */
    public const KEY_CUSTOMER_GROUP = 'customerGroup';

    /**
     * Customer user id. Needed if user is logged in as customer user.
     */
    public const KEY_CUSTOMER_USER_ID = 'customerUserId';

    /**
     * Localization id. Use both for customer user and visitor.
     */
    public const KEY_LOCALIZATION_ID = 'localizationId';

    /**
     * User type value Visitor. Needed if user is not logged in.
     */
    public const VALUE_USER_TYPE_VISITOR = 'Visitor';

    /**
     * User type value Customer. Needed if user is logged in as customer user.
     */
    public const VALUE_USER_TYPE_CUSTOMER_USER = 'Customer';
}
