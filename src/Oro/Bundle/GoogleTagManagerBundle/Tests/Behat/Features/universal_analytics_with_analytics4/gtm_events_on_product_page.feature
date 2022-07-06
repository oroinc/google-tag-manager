@regression
@feature-BB-21298
@fixture-OroGoogleTagManagerBundle:integration.yml
@fixture-OroGoogleTagManagerBundle:related_products.yml

Feature: GTM events on product page

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

  Scenario: Add product to shopping list
    Given I am on homepage
    And I type "SKU2" in "search"
    And I click "Search Button"
    When I click "Product 2"
    Then GTM data layer must contain the following message:
      """
        {
          "event": "productDetail",
          "ecommerce": {
            "currencyCode": "USD",
            "detail": {
              "products": [
                {
                  "id": "SKU2",
                  "name": "Product 2",
                  "category": "All Products / NewCategory",
                  "price": "15.1234"
                }
              ]
            }
          }
        }
      """

    And GTM data layer must contain the following message:
      """
        {
          "event": "view_item",
          "ecommerce": {
            "currency": "USD",
            "items": [
              {
                "item_id": "SKU2",
                "item_name": "Product 2",
                "item_category": "NewCategory",
                "price": 15.1234
              }
            ]
          }
        }
      """

    And I should see the following prices on "Default Page":
      | Item | $15.1234 |

    And GTM data layer must contain the following message:
      """
        {
          "event": "productImpression",
          "ecommerce": {
            "currencyCode": "USD",
            "impressions": [
              {
                "id": "RELATED1",
                "name": "Related Product 1",
                "list": "related-products",
                "position": 0,
                "price": "1.1234"
              },
              {
                "id": "RELATED2",
                "name": "Related Product 2",
                "list": "related-products",
                "position": 1,
                "price": "2.2468"
              },
              {
                "id": "RELATED3",
                "name": "Related Product 3",
                "list": "related-products",
                "position": 2,
                "price": "3.3702"
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
                "item_id": "RELATED1",
                "item_name": "Related Product 1",
                "item_list_name": "related-products",
                "index": 0,
                "price": 1.1234
              },
              {
                "item_id": "RELATED2",
                "item_name": "Related Product 2",
                "item_list_name": "related-products",
                "index": 1,
                "price": 2.2468
              },
              {
                "item_id": "RELATED3",
                "item_name": "Related Product 3",
                "item_list_name": "related-products",
                "index": 2,
                "price": 3.3702
              }
            ]
          }
        }
      """

    And I should see the following products in the "Related Products Block":
      | SKU      | Product Price Your | Product Price Listed |
      | RELATED1 | $1.1234 / item     | $1.1234 / item       |
      | RELATED2 | $2.2468 / item     | $2.2468 / item       |
      | RELATED3 | $3.3702 / item     | $3.3702 / item       |

    And GTM data layer must contain the following message:
      """
        {
          "event": "productImpression",
          "ecommerce": {
            "currencyCode": "USD",
            "impressions": [
              {
                "id": "UPSELL1",
                "name": "Upsell Product 1",
                "list": "upsell-products",
                "position": 0,
                "price": "1.5678"
              },
              {
                "id": "UPSELL2",
                "name": "Upsell Product 2",
                "list": "upsell-products",
                "position": 1,
                "price": "3.1356"
              },
              {
                "id": "UPSELL3",
                "name": "Upsell Product 3",
                "list": "upsell-products",
                "position": 2,
                "price": "4.7034"
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
                "item_id": "UPSELL1",
                "item_name": "Upsell Product 1",
                "item_list_name": "upsell-products",
                "index": 0,
                "price": 1.5678
              },
              {
                "item_id": "UPSELL2",
                "item_name": "Upsell Product 2",
                "item_list_name": "upsell-products",
                "index": 1,
                "price": 3.1356
              },
              {
                "item_id": "UPSELL3",
                "item_name": "Upsell Product 3",
                "item_list_name": "upsell-products",
                "index": 2,
                "price": 4.7034
              }
            ]
          }
        }
      """

    And I should see the following products in the "Upsell Products Block":
      | SKU     | Product Price Your | Product Price Listed |
      | UPSELL1 | $1.5678 / item     | $1.5678 / item       |
      | UPSELL2 | $3.1356 / item     | $3.1356 / item       |
      | UPSELL3 | $4.7034 / item     | $4.7034 / item       |
