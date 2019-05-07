<?php

namespace Oro\Bundle\GoogleTagManagerBundle\DataLayer\ConstantBag;

/**
 * Contains data layer attribute names and values.
 */
final class DataLayerAttributeBag
{
    /**
     * User type key. Customer or Visitor.
     */
    public const KEY_USER_TYPE = 'userType';

    /**
     * Customer user group name. Needed if user is logged in as customer user.
     */
    public const KEY_USER_GROUP = 'userGroup';

    /**
     * Category name. Needed on category page.
     */
    public const KEY_CATEGORY_NAME = 'categoryName';

    /**
     * Product category path. Needed on product page.
     */
    public const KEY_PRODUCT_CATEGORY_PATH = 'categoryPath';

    /**
     * Product brand name. Needed on product page.
     */
    public const KEY_PRODUCT_BRAND = 'brand';

    /**
     * Customer id. Needed if user is logged in as customer user.
     */
    public const KEY_CUSTOMER_ID = 'customerId';

    /**
     * Customer user id. Needed if user is logged in as customer user.
     */
    public const KEY_CUSTOMER_USER_ID = 'customerUserId';

    /**
     * User type value Visitor. Needed if user is not logged in.
     */
    public const VALUE_USER_TYPE_VISITOR = 'Visitor';

    /**
     * User type value Customer. Needed if user is logged in as customer user.
     */
    public const VALUE_USER_TYPE_CUSTOMER_USER = 'Customer';
}
