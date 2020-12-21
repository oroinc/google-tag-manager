@regression
@random-failed
@fixture-OroGoogleTagManagerBundle:integration.yml
@fixture-OroGoogleTagManagerBundle:related_products.yml
Feature: GTM events on product page
  In order to ...
  As an ...
  I should be able to ...

  Scenario: Feature background
    Given I enable GTM integration

  Scenario: Add product to shopping list
    And I am on homepage
    And I type "SKU2" in "search"
    And I click "Search Button"
    And I click "Product 2"
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
                  "price": "15.00"
                }
              ]
            }
          }
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
                "id": "RELATED-1",
                "name": "Related Product 1",
                "list": "related-products",
                "position": 0
              },
              {
                "id": "RELATED-2",
                "name": "Related Product 2",
                "list": "related-products",
                "position": 1
              },
              {
                "id": "RELATED-3",
                "name": "Related Product 3",
                "list": "related-products",
                "position": 2
              }
            ]
          }
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
                "id": "UPSELL-1",
                "name": "Upsell Product 1",
                "list": "upsell-products",
                "position": 0
              },
              {
                "id": "UPSELL-2",
                "name": "Upsell Product 2",
                "list": "upsell-products",
                "position": 1
              },
              {
                "id": "UPSELL-3",
                "name": "Upsell Product 3",
                "list": "upsell-products",
                "position": 2
              }
            ]
          }
        }
      """
