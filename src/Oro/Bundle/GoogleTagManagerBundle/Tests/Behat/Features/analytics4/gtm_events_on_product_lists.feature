@feature-BB-21298
@fixture-OroGoogleTagManagerBundle:integration.yml
@fixture-OroGoogleTagManagerBundle:products.yml
@regression
Feature: GTM events on product lists

  Scenario: Feature background
    Given I enable GTM integration

  Scenario: Check product events on products category page
    When I go to homepage
    Then GTM data layer must not contain the following message:
      """
        {
          "catalogPath": "All Products"
        }
      """
    And I click "NewCategory"
    When I sort frontend grid "Product Frontend Grid" by "Price (Low to High)"
    Then GTM data layer must contain the following message:
      """
        {
          "pageCategory": "category",
          "localizationId": "1"
        }
      """
    And GTM data layer must contain the following message:
      """
        {
          "event": "view_item_list",
          "ecommerce": {
            "currency": "USD",
            "items": [
              {
                "item_id": "SKU1",
                "item_name": "Product 1",
                "item_category": "NewCategory",
                "price": 10.4555,
                "index": 0,
                "view_mode": "list-view",
                "item_list_name": "product-index"
              },
              {
                "item_id": "SKU2",
                "item_name": "Product 2",
                "item_category": "NewCategory",
                "price": 15.1234,
                "index": 1,
                "view_mode": "list-view",
                "item_list_name": "product-index"
              }
            ]
          }
        }
      """

    When I filter "Any Text" as contains "SKU1"
    Then last message in the GTM data layer should be:
      """
        {
          "event": "view_item_list",
          "ecommerce": {
            "currency": "USD",
            "items": [
              {
                "item_id": "SKU1",
                "item_name": "Product 1",
                "item_category": "NewCategory",
                "price": 10.4555,
                "index": 0,
                "view_mode": "list-view",
                "item_list_name": "product-index"
              }
            ]
          }
        }
      """
    And I should see "$10.4555 / item" in the "Product Price Your" element
    And I should see "$10.4555 / item" in the "Product Price Listed" element

    When I click "Catalog Switcher Toggle"
    And I click "Gallery View"
    Then GTM data layer must contain the following message:
      """
        {
          "event": "view_item_list",
          "ecommerce": {
            "currency": "USD",
            "items": [
              {
                "item_id": "SKU1",
                "item_name": "Product 1",
                "item_category": "NewCategory",
                "price": 10.4555,
                "index": 0,
                "view_mode": "gallery-view",
                "item_list_name": "product-index"
              }
            ]
          }
        }
      """

    When I click "Catalog Switcher Toggle"
    And I click "No Image View"
    Then GTM data layer must contain the following message:
      """
        {
          "event": "view_item_list",
          "ecommerce": {
            "currency": "USD",
            "items": [
              {
                "item_id": "SKU1",
                "item_name": "Product 1",
                "item_category": "NewCategory",
                "price": 10.4555,
                "index": 0,
                "view_mode": "no-image-view",
                "item_list_name": "product-index"
              }
            ]
          }
        }
      """

    When do not change page on link click
    And I follow "View Details"
    Then last message in the GTM data layer should be:
      """
        {
          "event": "select_item",
          "eventCallback": {},
          "ecommerce": {
            "items": [
              {
                "item_id": "SKU1",
                "item_name": "Product 1",
                "item_category": "NewCategory",
                "price": 10.4555,
                "index": 0,
                "view_mode": "no-image-view",
                "currency": "USD"
              }
            ],
            "item_list_name": "product-index"
          }
        }
      """
    And I should see "$10.4555 / item" in the "Product Price Your" element
    And I should see "$10.4555 / item" in the "Product Price Listed" element

  Scenario: Check product events on products index page
    Given I reload the page
    And I click "All Products"
    And I click "Catalog Switcher Toggle"
    And I click "List View"
    And I sort frontend grid "Product Frontend Grid" by "Price (Low to High)"
    And GTM data layer must contain the following message:
      """
        {
          "pageCategory": "category",
          "localizationId": "1"
        }
      """
    And GTM data layer must contain the following message:
      """
        {
          "catalogPath": "All Products"
        }
      """
    And GTM data layer must contain the following message:
      """
        {
          "event": "view_item_list",
          "ecommerce": {
            "currency": "USD",
            "items": [
              {
                "item_id": "SKU1",
                "item_name": "Product 1",
                "item_category": "NewCategory",
                "price": 10.4555,
                "index": 0,
                "view_mode": "list-view",
                "item_list_name": "product-index"
              },
              {
                "item_id": "SKU2",
                "item_name": "Product 2",
                "item_category": "NewCategory",
                "price": 15.1234,
                "index": 1,
                "view_mode": "list-view",
                "item_list_name": "product-index"
              }
            ]
          }
        }
      """

    When I filter "Any Text" as contains "SKU1"
    Then last message in the GTM data layer should be:
      """
        {
          "event": "view_item_list",
          "ecommerce": {
            "currency": "USD",
            "items": [
              {
                "item_id": "SKU1",
                "item_name": "Product 1",
                "item_category": "NewCategory",
                "price": 10.4555,
                "index": 0,
                "view_mode": "list-view",
                "item_list_name": "product-index"
              }
            ]
          }
        }
      """

    And I should see "$10.4555 / item" in the "Product Price Your" element
    And I should see "$10.4555 / item" in the "Product Price Listed" element

    When do not change page on link click
    And I follow "Product 1"
    Then last message in the GTM data layer should be:
      """
        {
          "event": "select_item",
          "eventCallback": {},
          "ecommerce": {
            "items": [
              {
                "item_id": "SKU1",
                "item_name": "Product 1",
                "item_category": "NewCategory",
                "price": 10.4555,
                "index": 0,
                "view_mode": "list-view",
                "currency": "USD"
              }
            ],
            "item_list_name": "product-index"
          }
        }
      """
