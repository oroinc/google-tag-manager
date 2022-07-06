@regression
@feature-BB-21298
@fixture-OroGoogleTagManagerBundle:integration.yml
@fixture-OroGoogleTagManagerBundle:products.yml

Feature: GTM events on product lists

  Scenario: Feature background
    Given I enable GTM integration
    And I login as administrator
    When I go to System/Configuration
    And I follow "System/Integrations/Google Settings" on configuration sidebar
    And uncheck "Use default" for "Data Collection for" field
    And fill form with:
      | Data Collection for | [Universal Analytics, Google Analytics 4] |
    And I save setting
    Then I should see "Configuration saved" flash message

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
          "event": "productImpression",
          "ecommerce": {
            "currencyCode": "USD",
            "impressions": [
              {
                "id": "SKU1",
                "name": "Product 1",
                "category": "All Products / NewCategory",
                "price": "10.4555",
                "position": 0,
                "viewMode": "list-view",
                "list": "product-index"
              },
              {
                "id": "SKU2",
                "name": "Product 2",
                "category": "All Products / NewCategory",
                "price": "15.1234",
                "position": 1,
                "viewMode": "list-view",
                "list": "product-index"
              }
            ]
          }
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
                "price": "10.4555",
                "position": 0,
                "viewMode": "list-view",
                "list": "product-index"
              }
            ]
          }
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
              }
            ]
          }
        }
      """
    And I should see "$10.4555 / item" in the "Product Price Your" element
    And I should see "$10.4555 / item" in the "Product Price Listed" element

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
                "price": "10.4555",
                "position": 0,
                "viewMode": "gallery-view",
                "list": "product-index"
              }
            ]
          }
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
                "view_mode": "gallery-view",
                "item_list_name": "product-index"
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
                "price": "10.4555",
                "position": 0,
                "viewMode": "no-image-view",
                "list": "product-index"
              }
            ]
          }
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
                "view_mode": "no-image-view",
                "item_list_name": "product-index"
              }
            ]
          }
        }
      """

    When do not change page on link click
    And I follow "View Details"
    Then GTM data layer must contain the following message:
      """
        {
          "event": "productClick",
          "eventCallback": {
              "cancel": []
          },
          "ecommerce": {
            "currencyCode": "USD",
            "click": {
              "products": [
                {
                  "id": "SKU1",
                  "name": "Product 1",
                  "category": "All Products / NewCategory",
                  "price": "10.4555",
                  "position": 0,
                  "viewMode": "no-image-view"
                }
              ],
              "actionField": {
                "list": "product-index"
              }
            }
          }
        }
      """

    And GTM data layer must contain the following message:
      """
        {
          "event": "select_item",
          "eventCallback": {
              "cancel": []
          },
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
    And I click "List View"
    And I sort frontend grid "Product Frontend Grid" by "Price (Low to High)"
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
          "catalogPath": "All Products"
        }
      """

    And GTM data layer must contain the following message:
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
                "price": "10.4555",
                "position": 0,
                "viewMode": "list-view",
                "list": "product-index"
              },
              {
                "id": "SKU2",
                "name": "Product 2",
                "category": "All Products / NewCategory",
                "price": "15.1234",
                "position": 1,
                "viewMode": "list-view",
                "list": "product-index"
              }
            ]
          }
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
                "price": "10.4555",
                "position": 0,
                "viewMode": "list-view",
                "list": "product-index"
              }
            ]
          }
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
              }
            ]
          }
        }
      """

    And I should see "$10.4555 / item" in the "Product Price Your" element
    And I should see "$10.4555 / item" in the "Product Price Listed" element

    When do not change page on link click
    And I follow "Product 1"
    Then GTM data layer must contain the following message:
      """
        {
          "event": "productClick",
          "eventCallback": {
              "cancel": []
          },
          "ecommerce": {
            "currencyCode": "USD",
            "click": {
              "products": [
                {
                  "id": "SKU1",
                  "name": "Product 1",
                  "category": "All Products / NewCategory",
                  "price": "10.4555",
                  "position": 0,
                  "viewMode": "list-view"
                }
              ],
              "actionField": {
                "list": "product-index"
              }
            }
          }
        }
      """

    And GTM data layer must contain the following message:
      """
        {
          "event": "select_item",
          "eventCallback": {
              "cancel": []
          },
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
