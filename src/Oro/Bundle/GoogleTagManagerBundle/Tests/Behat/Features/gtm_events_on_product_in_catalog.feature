@regression
@random-failed
@fixture-OroGoogleTagManagerBundle:integration.yml
@fixture-OroGoogleTagManagerBundle:products.yml
@fixture-OroGoogleTagManagerBundle:web_catalog.yml
Feature: GTM events on product in catalog
  In order to ...
  As an ...
  I should be able to ...

  Scenario: Feature background
    Given I enable GTM integration
    And login as administrator

    Given I go to Marketing/Web Catalogs
    And click "Edit Content Tree" on row "Default Web Catalog" in grid
    When I save form
    Then I should see "Content Node has been saved" flash message
    When click "All Products"
    And I save form
    Then I should see "Content Node has been saved" flash message

    Given I set "Default Web Catalog" as default web catalog
    And I go to System/ Configuration
    And follow "Commerce/Catalog/Special Pages" on configuration sidebar
    And uncheck "Use default" for "Enable all products page" field
    And I check "Enable all products page"
    And save form
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
    Then GTM data layer must contain the following message:
      """
        {
          "catalogPath": "Root Node / All Products"
        }
      """
    Then GTM data layer must contain the following message:
      """
        {
          "localizationId": "1"
        }
      """

    When I filter "Any Text" as contains "SKU1"
    Then last message in the GTM data layer should be:
      """
        {
          "event": "productImpression",
          "ecommerce": {
            "currencyCode": "USD",
            "impressions": [
              {
                "id": "SKU1",
                "name": "Product 1",
                "category": "All Products / NewCategory",
                "price": "10.00",
                "position": 0,
                "viewMode": "list-view",
                "list": "product-allproducts"
              }
            ]
          }
        }
      """

    When I click "Gallery View"
    Then GTM data layer must contain the following message:
      """
        {
          "event": "productImpression",
          "ecommerce": {
            "currencyCode": "USD",
            "impressions": [
              {
                "id": "SKU1",
                "name": "Product 1",
                "category": "All Products / NewCategory",
                "price": "10.00",
                "position": 0,
                "viewMode": "gallery-view",
                "list": "product-allproducts"
              }
            ]
          }
        }
      """

    When I click "No Image View"
    Then GTM data layer must contain the following message:
      """
        {
          "event": "productImpression",
          "ecommerce": {
            "currencyCode": "USD",
            "impressions": [
              {
                "id": "SKU1",
                "name": "Product 1",
                "category": "All Products / NewCategory",
                "price": "10.00",
                "position": 0,
                "viewMode": "no-image-view",
                "list": "product-allproducts"
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
          "event": "productClick",
          "eventCallback": [],
          "ecommerce": {
            "click": {
              "products": [
                {
                  "id": "SKU1",
                  "name": "Product 1",
                  "category": "All Products / NewCategory",
                  "price": "10.00",
                  "position": 0,
                  "viewMode": "no-image-view"
                }
              ],
              "actionField": {
                "list": "product-allproducts"
              }
            }
          }
        }
      """
