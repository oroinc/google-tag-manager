@regression
@feature-BB-21298
@feature-BB-16952
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
    And I enable configuration options:
      | oro_shopping_list.availability_for_guests |
      | oro_checkout.guest_checkout               |

  Scenario: Set payment methods for Non-Authenticated Visitors group
    Given I proceed as the Admin
    And login as administrator
    And go to Customers/ Customer Groups
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
    Then I should see "Product has been added to" flash message

  Scenario: Event "checkout"
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

    And I click "Continue as a Guest"
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

  Scenario: Events on step "Shipping Information"
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
    And I click "Continue"
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
    When I fill form with:
      | First Name      | Andy         |
      | Last Name       | Derrick      |
      | Organization    | TestCompany  |
      | Street          | Fifth avenue |
      | City            | Berlin       |
      | Country         | Germany      |
      | State           | Berlin       |
      | Zip/Postal Code | 10115        |
    And I click "Continue"
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
                "price": 5.4555,
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
                "price": 5.4555,
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
        "event": "purchase",
        "ecommerce": {
          "currencyCode": "USD",
          "purchase": {
            "actionField": {
              "id": 1,
              "revenue": 29.28,
              "coupon": "First Promotion Name",
              "tax": 0,
              "shipping": 3,
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
                "price": 5.4555
              }
            ]
          }
        }
      }
      """
