@regression
@feature-BB-21298
@fixture-OroGoogleTagManagerBundle:integration.yml
@fixture-OroGoogleTagManagerBundle:products.yml
@fixture-OroGoogleTagManagerBundle:web_catalog.yml

Feature: GTM events on product in catalog

  Scenario: Feature background
    Given I enable GTM integration
    And I login as administrator

    And I go to Marketing/Web Catalogs
    And click "Edit Content Tree" on row "Default Web Catalog" in grid
    When I save form
    Then I should see "Content Node has been saved" flash message
    When click "All Products"
    And I save form
    Then I should see "Content Node has been saved" flash message

    And I set "Default Web Catalog" as default web catalog
    And I go to System/ Configuration
    And follow "Commerce/Catalog/Special Pages" on configuration sidebar
    And uncheck "Use default" for "Enable all products page" field
    And I check "Enable all products page"
    When save form
    Then I should see "Configuration saved" flash message

  Scenario: Check product events on all products page
    When I go to homepage
    Then GTM data layer must contain the following message:
      """
        {
          "catalogPath": "Root Node"
        }
      """
    And I follow "All Products"
    And GTM data layer must contain the following message:
      """
        {
          "catalogPath": "Root Node / All Products"
        }
      """
    And GTM data layer must contain the following message:
      """
        {
          "localizationId": "1"
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
                "item_list_name": "product-allproducts"
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
                "item_list_name": "product-allproducts"
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
                "item_list_name": "product-allproducts"
              }
            ]
          }
        }
      """

    When do not change page on link click
    And I click "Product Name Link"
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
            "item_list_name": "product-allproducts"
          }
        }
      """
