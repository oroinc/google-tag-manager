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

Feature: GTM events on single page checkout and purchase

  Scenario: Feature background
    Given I enable GTM integration

    And I activate "Single Page Checkout" workflow
    And I set configuration property "oro_order.enable_purchase_history" to "1"
    And I signed in as AmandaRCole@example.org on the store frontend

  Scenario: Event "begin_checkout" is triggered when checkout starts
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

  Scenario: Event "add_shipping_info" is triggered when shipping method is set
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

    When I check "Flat Rate Two" on the checkout page
    Then last message in the GTM data layer should be:
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

    When I check "Payment Term Two" on the checkout page
    Then last message in the GTM data layer should be:
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

  Scenario: Event "begin_checkout" is triggered only when a checkout starts
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

  Scenario: Apply coupon
    When I scroll to "I have a Coupon Code"
    And I click "I have a Coupon Code"
    And I type "coupon-1" in "CouponCodeInput"
    And I click "Apply"
    Then I should see "Coupon code has been applied successfully, please review discounts" flash message

  Scenario: Event "purchase"
    When I check "Delete this shopping list after submitting order" on the checkout page
    And I wait "Submit Order" button
    And I click "Submit Order"
    And I see the "Thank You" page with "Thank You For Your Purchase!" title
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

  Scenario: Event "view_item_list" on a previously purchased list
    And I click "Account Dropdown"
    And I click "Previously Purchased"
    Then GTM data layer must contain the following message:
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
