@regression
@feature-BB-21298
@feature-BB-16952
@fixture-OroGoogleTagManagerBundle:integration.yml
@fixture-OroGoogleTagManagerBundle:related_products.yml

Feature: GTM events on product page

  Scenario: Feature background
    Given I enable GTM integration

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
                  "price": "15.12"
                }
              ]
            }
          }
        }
      """

    And I should see the following prices on "Default Page":
      | Item | $15.12 |

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
                "price": "1.12"
              },
              {
                "id": "RELATED2",
                "name": "Related Product 2",
                "list": "related-products",
                "position": 1,
                "price": "2.25"
              },
              {
                "id": "RELATED3",
                "name": "Related Product 3",
                "list": "related-products",
                "position": 2,
                "price": "3.37"
              }
            ]
          }
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
          "event": "productImpression",
          "ecommerce": {
            "currencyCode": "USD",
            "impressions": [
              {
                "id": "UPSELL1",
                "name": "Upsell Product 1",
                "list": "upsell-products",
                "position": 0,
                "price": "1.57"
              },
              {
                "id": "UPSELL2",
                "name": "Upsell Product 2",
                "list": "upsell-products",
                "position": 1,
                "price": "3.14"
              },
              {
                "id": "UPSELL3",
                "name": "Upsell Product 3",
                "list": "upsell-products",
                "position": 2,
                "price": "4.7"
              }
            ]
          }
        }
      """

    And I should see the following products in the "Upsell Products Block":
      | SKU     | Product Price Your | Product Price Listed |
      | UPSELL1 | $1.57 / item       | $1.57 / item         |
      | UPSELL2 | $3.14 / item       | $3.14 / item         |
      | UPSELL3 | $4.70 / item       | $4.70 / item         |
