@regression
@feature-BB-21298
@feature-BB-16952
@fixture-OroFlatRateShippingBundle:FlatRateIntegration.yml
@fixture-OroPaymentTermBundle:PaymentTermIntegration.yml
@fixture-OroCheckoutBundle:Payment.yml
@fixture-OroCheckoutBundle:Shipping.yml
@fixture-OroGoogleTagManagerBundle:integration.yml
@fixture-OroGoogleTagManagerBundle:products.yml

Feature: GTM events on quick order form

  Scenario: Feature background
    Given I enable GTM integration
    And I signed in as AmandaRCole@example.org on the store frontend

  Scenario: Check add to cart events on quick order form
    Given I click "Quick Order Form"
    And I fill "QuickAddForm" with:
      | SKU1 | SKU1 |
    And I wait for products to load
    And I fill "QuickAddForm" with:
      | SKU2 | SKU2 |
    And I wait for products to load
    And I fill "QuickAddForm" with:
      | SKU3 | SKU2 |
    And I wait for products to load
    And I fill "QuickAddForm" with:
      | QTY1  | 1   |
      | QTY2  | 2   |
      | UNIT2 | set |
      | QTY3  | 3   |
    And I click on empty space

    When I click "Add to Shopping List"
    Then GTM data layer must contain the following message:
      """
        {
          "event": "addToCart",
          "ecommerce": {
            "currencyCode": "USD",
            "add": {
              "products": [
                {
                  "id": "SKU1",
                  "name": "Product 1",
                  "category": "All Products / NewCategory",
                  "variant": "item",
                  "quantity": 1,
                  "price": 10.46
                },
                {
                  "id": "SKU2",
                  "name": "Product 2",
                  "category": "All Products / NewCategory",
                  "variant": "set",
                  "quantity": 2,
                  "price": 50.68
                },
                {
                  "id": "SKU2",
                  "name": "Product 2",
                  "category": "All Products / NewCategory",
                  "variant": "item",
                  "quantity": 3,
                  "price": 15.12
                }
              ]
            }
          }
        }
      """

  Scenario: Check create order events on quick order form
    Given I click "Quick Order Form"
    And I fill "QuickAddForm" with:
      | SKU1 | SKU1 |
    And I wait for products to load
    And I fill "QuickAddForm" with:
      | SKU2 | SKU2 |
    And I wait for products to load
    And I fill "QuickAddForm" with:
      | SKU3 | SKU2 |
    And I wait for products to load
    And I fill "QuickAddForm" with:
      | QTY1  | 1   |
      | QTY2  | 2   |
      | UNIT2 | set |
      | QTY3  | 3   |
    And I click on empty space

    When I click "Create Order"
    Then GTM data layer must contain the following message:
      """
        {
          "event": "checkout",
          "ecommerce": {
            "currencyCode": "USD",
            "checkout": {
              "actionField": {
                "step": 1,
                "option": "enter_billing_address"
              },
              "products": [
                {
                  "id": "SKU1",
                  "name": "Product 1",
                  "category": "All Products / NewCategory",
                  "variant": "item",
                  "price": 10.46,
                  "quantity": 1,
                  "position": 1
                },
                {
                  "id": "SKU2",
                  "name": "Product 2",
                  "category": "All Products / NewCategory",
                  "variant": "set",
                  "price": 50.68,
                  "quantity": 2,
                  "position": 2
                },
                {
                  "id": "SKU2",
                  "name": "Product 2",
                  "category": "All Products / NewCategory",
                  "variant": "item",
                  "price": 15.12,
                  "quantity": 3,
                  "position": 3
                }
              ]
            }
          }
        }
      """

  Scenario: Check add to cart events on request quote
    Given I click "Quick Order Form"
    And I fill "QuickAddForm" with:
      | SKU1 | SKU1 |
    And I wait for products to load
    And I fill "QuickAddForm" with:
      | SKU2 | SKU2 |
    And I wait for products to load
    And I fill "QuickAddForm" with:
      | SKU3 | SKU2 |
    And I wait for products to load
    And I fill "QuickAddForm" with:
      | QTY1  | 1   |
      | QTY2  | 2   |
      | UNIT2 | set |
      | QTY3  | 3   |
    And I click on empty space

    When I click "Get Quote"
    And I click "Submit Request"
    Then GTM data layer must contain the following message:
      """
        {
          "event": "addToCart",
          "ecommerce": {
            "currencyCode": "USD",
            "add": {
              "products": [
                {
                  "id": "SKU1",
                  "name": "Product 1",
                  "category": "All Products / NewCategory",
                  "variant": "item",
                  "quantity": 1,
                  "price": 10.46
                },
                {
                  "id": "SKU2",
                  "name": "Product 2",
                  "category": "All Products / NewCategory",
                  "variant": "set",
                  "quantity": 2,
                  "price": 50.68
                },
                {
                  "id": "SKU2",
                  "name": "Product 2",
                  "category": "All Products / NewCategory",
                  "variant": "item",
                  "quantity": 3,
                  "price": 15.12
                }
              ]
            }
          }
        }
      """
