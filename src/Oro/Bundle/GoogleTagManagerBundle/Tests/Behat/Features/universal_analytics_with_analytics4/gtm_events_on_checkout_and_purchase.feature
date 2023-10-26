@regression
@feature-BB-21298
@fixture-OroFlatRateShippingBundle:FlatRateIntegration.yml
@fixture-OroPaymentTermBundle:PaymentTermIntegration.yml
@fixture-OroCheckoutBundle:AdditionalIntegrations.yml
@fixture-OroCheckoutBundle:AdditionalPayment.yml
@fixture-OroCheckoutBundle:AdditionalShipping.yml
@fixture-OroPromotionBundle:promotions-with-coupons-basic.yml
@fixture-OroGoogleTagManagerBundle:checkout.yml
@fixture-OroGoogleTagManagerBundle:integration.yml

Feature: GTM events on checkout and purchase

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
                "price": 5.4555,
                "quantity": 5,
                "position": 1
              }
            ]
          }
        }
      }
      """


  Scenario: Google Analytics 4 event "begin_checkout" is triggered when checkout starts
    Given GTM data layer must contain the following message:
      """
      {
        "event": "begin_checkout",
        "ecommerce": {
          "currency": "USD",
          "value": 27.28,
          "items": [
            {
              "item_id": "SKU123",
              "item_name": "400-Watt Bulb Work Light",
              "item_variant": "item",
              "price": 5.4555,
              "quantity": 5,
              "index": 1
            }
          ]
        }
      }
      """

  Scenario: Google Analytics 4 event "begin_checkout" is triggered only when a checkout starts
    When I reload the page
    And GTM data layer must not contain the following message:
      """
      {
        "event": "begin_checkout",
        "ecommerce": {
          "currency": "USD",
          "value": 27.28,
          "items": [
            {
              "item_id": "SKU123",
              "item_name": "400-Watt Bulb Work Light",
              "item_variant": "item",
              "price": 5.4555,
              "quantity": 5,
              "index": 1
            }
          ]
        }
      }
      """

    When I open page with shopping list List 1
    And I click "Create Order"
    Then GTM data layer must contain the following message:
      """
      {
        "event": "begin_checkout",
        "ecommerce": {
          "currency": "USD",
          "value": 27.28,
          "items": [
            {
              "item_id": "SKU123",
              "item_name": "400-Watt Bulb Work Light",
              "item_variant": "item",
              "price": 5.4555,
              "quantity": 5,
              "index": 1
            }
          ]
        }
      }
      """

  Scenario: Events on step "Shipping Information"
    When I click "Continue"
    Then GTM data layer must contain the following message:
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
                "price": 5.4555,
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
    Then GTM data layer must contain the following message:
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
                "price": 5.4555,
                "quantity": 5,
                "position": 1
              }
            ]
          }
        }
      }
      """

  Scenario: Google Analytics 4 event "add_shipping_info" is triggered when shipping method is set
    Given GTM data layer must contain the following message:
      """
      {
        "event": "add_shipping_info",
        "ecommerce": {
          "currency": "USD",
          "value": 27.28,
          "shipping_tier": "Flat Rate",
          "items": [
            {
              "item_id": "SKU123",
              "item_name": "400-Watt Bulb Work Light",
              "item_variant": "item",
              "price": 5.4555,
              "quantity": 5,
              "index": 1
            }
          ]
        }
      }
      """

    When I check "Flat Rate Two" on the "Shipping Method" checkout step and press Continue
    Then GTM data layer must contain the following message:
      """
      {
        "event": "add_shipping_info",
        "ecommerce": {
          "currency": "USD",
          "value": 27.28,
          "shipping_tier": "Flat Rate Two",
          "items": [
            {
              "item_id": "SKU123",
              "item_name": "400-Watt Bulb Work Light",
              "item_variant": "item",
              "price": 5.4555,
              "quantity": 5,
              "index": 1
            }
          ]
        }
      }
      """

  Scenario: Events on step "Payment"
    Given GTM data layer must contain the following message:
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
                "price": 5.4555,
                "quantity": 5,
                "position": 1
              }
            ]
          }
        }
      }
      """

  Scenario: Event "add_payment_info" is triggered when payment method is set
    Given GTM data layer must contain the following message:
      """
      {
        "event": "add_payment_info",
        "ecommerce": {
          "currency": "USD",
          "value": 27.28,
          "payment_type": "Payment Term",
          "items": [
            {
              "item_id": "SKU123",
              "item_name": "400-Watt Bulb Work Light",
              "item_variant": "item",
              "price": 5.4555,
              "quantity": 5,
              "index": 1
            }
          ]
        }
      }
      """

    When I check "Payment Term Two" on the "Payment" checkout step and press Continue
    Then GTM data layer must contain the following message:
      """
      {
        "event": "add_payment_info",
        "ecommerce": {
          "currency": "USD",
          "value": 27.28,
          "payment_type": "Payment Term Two",
          "items": [
            {
              "item_id": "SKU123",
              "item_name": "400-Watt Bulb Work Light",
              "item_variant": "item",
              "price": 5.4555,
              "quantity": 5,
              "index": 1
            }
          ]
        }
      }
      """

  Scenario: Events on step "Order Review"
    Given GTM data layer must contain the following message:
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
                "price": 5.4555,
                "quantity": 5,
                "position": 1
              }
            ]
          }
        }
      }
      """

  Scenario: Apply coupon
    When I scroll to "I have a Coupon Code"
    And I click "I have a Coupon Code"
    And I type "coupon-1" in "CouponCodeInput"
    And I click "Apply"
    Then I should see "Coupon code has been applied successfully, please review discounts" flash message

  Scenario: Events on successful checkout
    When I check "Delete this shopping list after submitting order" on the "Order Review" checkout step and press Submit Order
    Then I see the "Thank You" page with "Thank You For Your Purchase!" title
    And GTM data layer must contain the following message:
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
              "revenue": 30.28,
              "coupon": "First Promotion Name",
              "tax": 0,
              "shipping": 4,
              "affiliation": "Default"
            },
            "products": [
              {
                "id": "SKU123",
                "name": "400-Watt Bulb Work Light",
                "variant": "item",
                "price": 5.4555,
                "quantity": 5,
                "position": 1
              }
            ]
          },
          "shippingMethod": "Flat Rate Two",
          "paymentMethod": "Payment Term Two"
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
                "price": 5.4555
              }
            ]
          }
        }
      }
      """

    And GTM data layer must contain the following message:
      """
      {
        "ecommerce": {
          "affiliation": "Default",
          "currency": "USD",
          "items": [
            {
              "index": 1,
              "item_id": "SKU123",
              "item_name": "400-Watt Bulb Work Light",
              "item_variant": "item",
              "price": 5.4555,
              "quantity": 5
            }
          ],
          "payment_type": "Payment Term Two",
          "shipping": 4,
          "shipping_tier": "Flat Rate Two",
          "tax": 0,
          "transaction_id": "1",
          "value": 30.28,
          "coupon": "First Promotion Name"
        },
        "event": "purchase"
      }
      """

    And GTM data layer must not contain the following message:
      """
      {
        "event": "remove_from_cart",
        "ecommerce": {
          "currency": "USD",
          "value": 27.28,
          "items": [
            {
              "item_id": "SKU123",
              "item_name": "400-Watt Bulb Work Light",
              "item_variant": "item",
              "quantity": 5,
              "price": 5.4555
            }
          ]
        }
      }
      """

  Scenario: Events on previously purchased list
    When I follow "Account"
    And I click "Previously Purchased"
    Then GTM data layer must contain the following message:
      """
        {
          "event": "productImpression",
          "ecommerce": {
            "currencyCode": "USD",
            "impressions": [
              {
                "id": "SKU123",
                "name": "400-Watt Bulb Work Light",
                "price": "5.4555",
                "position": 0,
                "viewMode": "list-view",
                "list": "previously-purchased"
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
              "index": 0,
              "item_id": "SKU123",
              "item_list_name": "previously-purchased",
              "item_name": "400-Watt Bulb Work Light",
              "price": 5.4555,
              "view_mode": "list-view"
            }
          ]
        }
      }
      """
