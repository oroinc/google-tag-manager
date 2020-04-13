@regression
@random-failed
@fixture-OroFlatRateShippingBundle:FlatRateIntegration.yml
@fixture-OroPaymentTermBundle:PaymentTermIntegration.yml
@fixture-OroCheckoutBundle:Checkout.yml
@fixture-OroGoogleTagManagerBundle:integration.yml
Feature: GTM events on checkout and purchase

  Scenario: Feature background
    Given I enable GTM integration
    And I set configuration property "oro_order.enable_purchase_history" to "1"
    And I signed in as AmandaRCole@example.org on the store frontend

  Scenario: Events on step "Billing Information"
    Given I open page with shopping list List 1
    And I click "Create Order"
    Then GTM data layer must contain the following message:
      """
      {
        "pageCategory": "checkout",
        "localizationId": "1"
      }
      """

    And GTM data layer must contain the following message:
      """
      {
        "visitorType": "Customer",
        "customerUserId": "1",
        "customerId": "1"
      }
      """

    And GTM data layer must contain the following message:
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
                "id": "SKU123",
                "name": "400-Watt Bulb Work Light",
                "variant": "item",
                "price": 2,
                "quantity": 5,
                "position": 1
              }
            ]
          }
        }
      }
      """

  Scenario: Events on step "Shipping Information"
    When I click "Continue"
    Then last message in the GTM data layer should be:
      """
      {
        "event": "checkout",
        "ecommerce": {
          "currencyCode": "USD",
          "checkout": {
            "actionField": {
              "step": 2,
              "option": "enter_shipping_address"
            },
            "products": [
              {
                "id": "SKU123",
                "name": "400-Watt Bulb Work Light",
                "variant": "item",
                "price": 2,
                "quantity": 5,
                "position": 1
              }
            ]
          }
        }
      }
      """

  Scenario: Events on step "Shipping Method"
    When I click "Continue"
    Then last message in the GTM data layer should be:
      """
      {
        "event": "checkout",
        "ecommerce": {
          "currencyCode": "USD",
          "checkout": {
            "actionField": {
              "step": 3,
              "option": "enter_shipping_method"
            },
            "products": [
              {
                "id": "SKU123",
                "name": "400-Watt Bulb Work Light",
                "variant": "item",
                "price": 2,
                "quantity": 5,
                "position": 1
              }
            ]
          }
        }
      }
      """

  Scenario: Events on step "Payment"
    When I click "Continue"
    Then last message in the GTM data layer should be:
      """
      {
        "event": "checkout",
        "ecommerce": {
          "currencyCode": "USD",
          "checkout": {
            "actionField": {
              "step": 4,
              "option": "enter_payment"
            },
            "products": [
              {
                "id": "SKU123",
                "name": "400-Watt Bulb Work Light",
                "variant": "item",
                "price": 2,
                "quantity": 5,
                "position": 1
              }
            ]
          }
        }
      }
      """

  Scenario: Events on step "Order Review"
    When I click "Continue"
    Then last message in the GTM data layer should be:
      """
      {
        "event": "checkout",
        "ecommerce": {
          "currencyCode": "USD",
          "checkout": {
            "actionField": {
              "step": 5,
              "option": "order_review"
            },
            "products": [
              {
                "id": "SKU123",
                "name": "400-Watt Bulb Work Light",
                "variant": "item",
                "price": 2,
                "quantity": 5,
                "position": 1
              }
            ]
          }
        }
      }
      """

  Scenario: Events success checkout
    When I check "Delete this shopping list after submitting order" on the "Order Review" checkout step and press Submit Order
    Then GTM data layer must contain the following message:
      """
      {
        "pageCategory": "checkout",
        "localizationId": "1"
      }
      """

    And GTM data layer must contain the following message:
      """
      {
        "visitorType": "Customer",
        "customerUserId": "1",
        "customerId": "1"
      }
      """

    And GTM data layer must contain the following message:
      """
      {
        "event": "purchase",
        "ecommerce": {
          "currencyCode": "USD",
          "purchase": {
            "actionField": {
              "id": 1,
              "revenue": 13,
              "tax": 0,
              "shipping": 3,
              "affiliation": "Default"
            },
            "products": [
              {
                "id": "SKU123",
                "name": "400-Watt Bulb Work Light",
                "variant": "item",
                "price": 2,
                "quantity": 5,
                "position": 1
              }
            ]
          },
          "shippingMethod": "Flat Rate",
          "paymentMethod": "Payment Term"
        }
      }
      """
    And GTM data layer must not contain the following message:
      """
      {
        "event": "removeFromCart",
        "ecommerce": {
          "currencyCode": "USD",
          "remove": {
            "products": [
              {
                "id": "SKU123",
                "name": "400-Watt Bulb Work Light",
                "variant": "item",
                "quantity": 5,
                "price": 2
              }
            ]
          }
        }
      }
      """

  Scenario: Events on previously purchased list
    When I follow "Account"
    And I click "Previously Purchased"
    And GTM data layer must contain the following message:
      """
        {
          "event": "productImpression",
          "ecommerce": {
            "currencyCode": "USD",
            "impressions": [
              {
                "id": "SKU123",
                "name": "400-Watt Bulb Work Light",
                "price": "2.00",
                "position": 0,
                "viewMode": "list-view",
                "list": "previously-purchased"
              }
            ]
          }
        }
      """

