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

Feature: GTM events on guest checkout and purchase

  Scenario: Feature background
    Given sessions active:
      | Admin | first_session  |
      | Buyer | second_session |
    And I enable GTM integration
    And I proceed as the Admin
    And I login as administrator
    When I go to System/Configuration
    And I follow "System/Integrations/Google Settings" on configuration sidebar
    And uncheck "Use default" for "Data Collection for" field
    And fill form with:
      | Data Collection for | [Google Analytics 4] |
    And I save setting
    Then I should see "Configuration saved" flash message

    And I enable configuration options:
      | oro_shopping_list.availability_for_guests |
      | oro_checkout.guest_checkout               |

  Scenario: Set payment methods for Non-Authenticated Visitors group
    Given go to Customers/ Customer Groups
    And I click Edit Non-Authenticated Visitors in grid
    And I fill form with:
      | Payment Term | net 10 |
    When I save form
    Then I should see "Customer group has been saved" flash message

  Scenario: Create shopping list on frontend
    Given I proceed as the Buyer
    And I am on homepage
    When type "SKU123" in "search"
    And I click "SKU123"
    And I click "Add to Shopping List"
    Then I should see "Product has been added to" flash message and I close it

  Scenario: Event "begin_checkout" is triggered when checkout starts
    When I open shopping list widget
    And I click "View List"
    And click on "Create Order"
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
        "visitorType": "Visitor"
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

    When I open shopping list widget
    And I click "View List"
    And click on "Create Order"
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
    And I click "Continue as a Guest"

  Scenario: Event "add_shipping_info" is triggered when shipping method is set
    When I fill form with:
      | Email           | Andy001@example.com |
      | First Name      | Andy                |
      | Last Name       | Derrick             |
      | Organization    | TestCompany         |
      | Street          | Fifth avenue        |
      | City            | Berlin              |
      | Country         | Germany             |
      | State           | Berlin              |
      | Zip/Postal Code | 10115               |
    And I click "Ship to This Address"
    And I click "Continue"
    Then last message in the GTM data layer should be:
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

  Scenario: Event "add_payment_info" is triggered when payment method is set
    Given last message in the GTM data layer should be:
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

  Scenario: Apply coupon
    When I scroll to "I have a Coupon Code"
    And I click "I have a Coupon Code"
    And I type "coupon-1" in "CouponCodeInput"
    And I click "Apply"
    Then I should see "Coupon code has been applied successfully, please review discounts" flash message

  Scenario: Event "purchase"
    When I uncheck "Save my data and create an account" on the checkout page
    And I click "Submit Order"
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
        "visitorType": "Visitor"
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
