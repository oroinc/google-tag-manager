@skip
# @todo: Unskip when BAP-21877 will be resolved
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
      | Data Collection for | [Google Analytics 4] |
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
          "event": "view_item",
          "ecommerce": {
            "currency": "USD",
            "items": [
              {
                "item_id": "SKU2",
                "item_name": "Product 2",
                "item_category": "NewCategory",
                "price": 15.12
              }
            ]
          }
        }
      """

    And I should see the following prices on "Default Page":
      | Item | $15.12 |

    And GTM data layer must contain the following message:
      """
        {
          "ecommerce": {
            "currency": "USD",
            "items": [
              {
                "index": 0,
                "item_id": "RELATED1",
                "item_list_name": "related-products",
                "item_name": "Related Product 1",
                "price": 1.12
              },
              {
                "index": 1,
                "item_id": "RELATED2",
                "item_list_name": "related-products",
                "item_name": "Related Product 2",
                "price": 2.25
              },
              {
                "index": 2,
                "item_id": "RELATED3",
                "item_list_name": "related-products",
                "item_name": "Related Product 3",
                "price": 3.37
              }
            ]
          },
          "event": "view_item_list"
        }
      """

    And I should see the following products in the "Related Products Block":
      | SKU      | Product Price Your | Product Price Listed |
      | RELATED1 | $1.12 / item       | $1.12 / item         |
      | RELATED2 | $2.25 / item       | $2.25 / item         |
      | RELATED3 | $3.37 / item       | $3.37 / item         |

    And GTM data layer must contain the following message:
      """
        {
          "ecommerce": {
            "currency": "USD",
            "items": [
              {
                "index": 0,
                "item_id": "UPSELL1",
                "item_list_name": "upsell-products",
                "item_name": "Upsell Product 1",
                "price": 1.57
              },
              {
                "index": 1,
                "item_id": "UPSELL2",
                "item_list_name": "upsell-products",
                "item_name": "Upsell Product 2",
                "price": 3.14
              },
              {
                "index": 2,
                "item_id": "UPSELL3",
                "item_list_name": "upsell-products",
                "item_name": "Upsell Product 3",
                "price": 4.7
              }
            ]
          },
          "event": "view_item_list"
        }
      """

    And I should see the following products in the "Upsell Products Block":
      | SKU     | Product Price Your | Product Price Listed |
      | UPSELL1 | $1.57 / item       | $1.57 / item         |
      | UPSELL2 | $3.14 / item       | $3.14 / item         |
      | UPSELL3 | $4.70 / item       | $4.70 / item         |
