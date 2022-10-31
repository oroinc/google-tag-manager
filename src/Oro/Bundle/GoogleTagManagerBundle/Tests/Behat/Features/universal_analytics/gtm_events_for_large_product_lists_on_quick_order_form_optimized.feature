@regression
@feature-BB-21298
@feature-BB-16952
@ticket-BB-21713
@fixture-OroFlatRateShippingBundle:FlatRateIntegration.yml
@fixture-OroPaymentTermBundle:PaymentTermIntegration.yml
@fixture-OroGoogleTagManagerBundle:integration.yml
@fixture-OroGoogleTagManagerBundle:many_products.yml

Feature: GTM events for large product lists on quick order form optimized

  Scenario: Feature background
    Given I enable GTM integration
    And I login as administrator
    And I go to System/Configuration
    And I follow "Commerce/Sales/Quick Order Form" on configuration sidebar
    And fill "Quick Order Configuration Form" with:
      | Enable Optimized Quick Order Form Default | false |
      | Enable Optimized Quick Order Form         | true  |
    And I save setting
    And I signed in as AmandaRCole@example.org on the store frontend

  Scenario: Batch events on quick order
    Given I click "Quick Order Form"
    And I click "Get Directions"
    And I should see that "UiDialog Title" contains "Import Excel .CSV File"
    And I download "the CSV template"
    And I close ui dialog
    And I fill quick order template with data:
      | Item Number | Quantity | Unit |
      | SKU1        | 1        | item |
      | SKU2        | 1        | item |
      | SKU3        | 1        | item |
      | SKU4        | 1        | item |
      | SKU5        | 1        | item |
      | SKU6        | 1        | item |
      | SKU7        | 1        | item |
      | SKU8        | 1        | item |
      | SKU9        | 1        | item |
      | SKU10       | 1        | item |
      | SKU11       | 1        | item |
      | SKU12       | 1        | item |
      | SKU13       | 1        | item |
      | SKU14       | 1        | item |
      | SKU15       | 1        | item |
      | SKU16       | 1        | item |
      | SKU17       | 1        | item |
      | SKU18       | 1        | item |
      | SKU19       | 1        | item |
      | SKU20       | 1        | item |
      | SKU21       | 1        | item |
      | SKU22       | 1        | item |
      | SKU23       | 1        | item |
      | SKU24       | 1        | item |
      | SKU25       | 1        | item |
      | SKU26       | 1        | item |
      | SKU27       | 1        | item |
      | SKU28       | 1        | item |
      | SKU29       | 1        | item |
      | SKU30       | 1        | item |
      | SKU31       | 1        | item |
      | SKU32       | 1        | item |
      | SKU33       | 1        | item |
      | SKU34       | 1        | item |
      | SKU35       | 1        | item |
    And I import file for quick order
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
                  "variant": "item",
                  "price": 10,
                  "quantity": 1
                },
                {
                  "id": "SKU2",
                  "name": "Product 2",
                  "variant": "item",
                  "price": 10,
                  "quantity": 1
                },
                {
                  "id": "SKU3",
                  "name": "Product 3",
                  "variant": "item",
                  "price": 10,
                  "quantity": 1
                },
                {
                  "id": "SKU4",
                  "name": "Product 4",
                  "variant": "item",
                  "price": 10,
                  "quantity": 1
                },
                {
                  "id": "SKU5",
                  "name": "Product 5",
                  "variant": "item",
                  "price": 10,
                  "quantity": 1
                },
                {
                  "id": "SKU6",
                  "name": "Product 6",
                  "variant": "item",
                  "price": 10,
                  "quantity": 1
                },
                {
                  "id": "SKU7",
                  "name": "Product 7",
                  "variant": "item",
                  "price": 10,
                  "quantity": 1
                },
                {
                  "id": "SKU8",
                  "name": "Product 8",
                  "variant": "item",
                  "price": 10,
                  "quantity": 1
                },
                {
                  "id": "SKU9",
                  "name": "Product 9",
                  "variant": "item",
                  "price": 10,
                  "quantity": 1
                },
                {
                  "id": "SKU10",
                  "name": "Product 10",
                  "variant": "item",
                  "price": 10,
                  "quantity": 1
                },
                {
                  "id": "SKU11",
                  "name": "Product 11",
                  "variant": "item",
                  "price": 10,
                  "quantity": 1
                },
                {
                  "id": "SKU12",
                  "name": "Product 12",
                  "variant": "item",
                  "price": 10,
                  "quantity": 1
                },
                {
                  "id": "SKU13",
                  "name": "Product 13",
                  "variant": "item",
                  "price": 10,
                  "quantity": 1
                },
                {
                  "id": "SKU14",
                  "name": "Product 14",
                  "variant": "item",
                  "price": 10,
                  "quantity": 1
                },
                {
                  "id": "SKU15",
                  "name": "Product 15",
                  "variant": "item",
                  "price": 10,
                  "quantity": 1
                },
                {
                  "id": "SKU16",
                  "name": "Product 16",
                  "variant": "item",
                  "price": 10,
                  "quantity": 1
                },
                {
                  "id": "SKU17",
                  "name": "Product 17",
                  "variant": "item",
                  "price": 10,
                  "quantity": 1
                },
                {
                  "id": "SKU18",
                  "name": "Product 18",
                  "variant": "item",
                  "price": 10,
                  "quantity": 1
                },
                {
                  "id": "SKU19",
                  "name": "Product 19",
                  "variant": "item",
                  "price": 10,
                  "quantity": 1
                },
                {
                  "id": "SKU20",
                  "name": "Product 20",
                  "variant": "item",
                  "price": 10,
                  "quantity": 1
                },
                {
                  "id": "SKU21",
                  "name": "Product 21",
                  "variant": "item",
                  "price": 10,
                  "quantity": 1
                },
                {
                  "id": "SKU22",
                  "name": "Product 22",
                  "variant": "item",
                  "price": 10,
                  "quantity": 1
                },
                {
                  "id": "SKU23",
                  "name": "Product 23",
                  "variant": "item",
                  "price": 10,
                  "quantity": 1
                },
                {
                  "id": "SKU24",
                  "name": "Product 24",
                  "variant": "item",
                  "price": 10,
                  "quantity": 1
                },
                {
                  "id": "SKU25",
                  "name": "Product 25",
                  "variant": "item",
                  "price": 10,
                  "quantity": 1
                },
                {
                  "id": "SKU26",
                  "name": "Product 26",
                  "variant": "item",
                  "price": 10,
                  "quantity": 1
                },
                {
                  "id": "SKU27",
                  "name": "Product 27",
                  "variant": "item",
                  "price": 10,
                  "quantity": 1
                },
                {
                  "id": "SKU28",
                  "name": "Product 28",
                  "variant": "item",
                  "price": 10,
                  "quantity": 1
                },
                {
                  "id": "SKU29",
                  "name": "Product 29",
                  "variant": "item",
                  "price": 10,
                  "quantity": 1
                },
                {
                  "id": "SKU30",
                  "name": "Product 30",
                  "variant": "item",
                  "price": 10,
                  "quantity": 1
                }
              ]
            }
          }
        }
      """

    And GTM data layer must contain the following message:
      """
        {
          "event": "addToCart",
          "ecommerce": {
            "currencyCode": "USD",
            "add": {
              "products": [
                {
                  "id": "SKU31",
                  "name": "Product 31",
                  "variant": "item",
                  "price": 10,
                  "quantity": 1
                },
                {
                  "id": "SKU32",
                  "name": "Product 32",
                  "variant": "item",
                  "price": 10,
                  "quantity": 1
                },
                {
                  "id": "SKU33",
                  "name": "Product 33",
                  "variant": "item",
                  "price": 10,
                  "quantity": 1
                },
                {
                  "id": "SKU34",
                  "name": "Product 34",
                  "variant": "item",
                  "price": 10,
                  "quantity": 1
                },
                {
                  "id": "SKU35",
                  "name": "Product 35",
                  "variant": "item",
                  "price": 10,
                  "quantity": 1
                }
              ]
            }
          }
        }
      """

  Scenario: Batch events on checkout
    When I open page with shopping list Shopping List
    And I click "Create Order"
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
                  "position": 1,
                  "id": "SKU1",
                  "name": "Product 1",
                  "variant": "item",
                  "price": 10,
                  "quantity": 1
                },
                {
                  "position": 2,
                  "id": "SKU2",
                  "name": "Product 2",
                  "variant": "item",
                  "price": 10,
                  "quantity": 1
                },
                {
                  "position": 3,
                  "id": "SKU3",
                  "name": "Product 3",
                  "variant": "item",
                  "price": 10,
                  "quantity": 1
                },
                {
                  "position": 4,
                  "id": "SKU4",
                  "name": "Product 4",
                  "variant": "item",
                  "price": 10,
                  "quantity": 1
                },
                {
                  "position": 5,
                  "id": "SKU5",
                  "name": "Product 5",
                  "variant": "item",
                  "price": 10,
                  "quantity": 1
                },
                {
                  "position": 6,
                  "id": "SKU6",
                  "name": "Product 6",
                  "variant": "item",
                  "price": 10,
                  "quantity": 1
                },
                {
                  "position": 7,
                  "id": "SKU7",
                  "name": "Product 7",
                  "variant": "item",
                  "price": 10,
                  "quantity": 1
                },
                {
                  "position": 8,
                  "id": "SKU8",
                  "name": "Product 8",
                  "variant": "item",
                  "price": 10,
                  "quantity": 1
                },
                {
                  "position": 9,
                  "id": "SKU9",
                  "name": "Product 9",
                  "variant": "item",
                  "price": 10,
                  "quantity": 1
                },
                {
                  "position": 10,
                  "id": "SKU10",
                  "name": "Product 10",
                  "variant": "item",
                  "price": 10,
                  "quantity": 1
                },
                {
                  "position": 11,
                  "id": "SKU11",
                  "name": "Product 11",
                  "variant": "item",
                  "price": 10,
                  "quantity": 1
                },
                {
                  "position": 12,
                  "id": "SKU12",
                  "name": "Product 12",
                  "variant": "item",
                  "price": 10,
                  "quantity": 1
                },
                {
                  "position": 13,
                  "id": "SKU13",
                  "name": "Product 13",
                  "variant": "item",
                  "price": 10,
                  "quantity": 1
                },
                {
                  "position": 14,
                  "id": "SKU14",
                  "name": "Product 14",
                  "variant": "item",
                  "price": 10,
                  "quantity": 1
                },
                {
                  "position": 15,
                  "id": "SKU15",
                  "name": "Product 15",
                  "variant": "item",
                  "price": 10,
                  "quantity": 1
                },
                {
                  "position": 16,
                  "id": "SKU16",
                  "name": "Product 16",
                  "variant": "item",
                  "price": 10,
                  "quantity": 1
                },
                {
                  "position": 17,
                  "id": "SKU17",
                  "name": "Product 17",
                  "variant": "item",
                  "price": 10,
                  "quantity": 1
                },
                {
                  "position": 18,
                  "id": "SKU18",
                  "name": "Product 18",
                  "variant": "item",
                  "price": 10,
                  "quantity": 1
                },
                {
                  "position": 19,
                  "id": "SKU19",
                  "name": "Product 19",
                  "variant": "item",
                  "price": 10,
                  "quantity": 1
                },
                {
                  "position": 20,
                  "id": "SKU20",
                  "name": "Product 20",
                  "variant": "item",
                  "price": 10,
                  "quantity": 1
                },
                {
                  "position": 21,
                  "id": "SKU21",
                  "name": "Product 21",
                  "variant": "item",
                  "price": 10,
                  "quantity": 1
                },
                {
                  "position": 22,
                  "id": "SKU22",
                  "name": "Product 22",
                  "variant": "item",
                  "price": 10,
                  "quantity": 1
                },
                {
                  "position": 23,
                  "id": "SKU23",
                  "name": "Product 23",
                  "variant": "item",
                  "price": 10,
                  "quantity": 1
                },
                {
                  "position": 24,
                  "id": "SKU24",
                  "name": "Product 24",
                  "variant": "item",
                  "price": 10,
                  "quantity": 1
                },
                {
                  "position": 25,
                  "id": "SKU25",
                  "name": "Product 25",
                  "variant": "item",
                  "price": 10,
                  "quantity": 1
                },
                {
                  "position": 26,
                  "id": "SKU26",
                  "name": "Product 26",
                  "variant": "item",
                  "price": 10,
                  "quantity": 1
                },
                {
                  "position": 27,
                  "id": "SKU27",
                  "name": "Product 27",
                  "variant": "item",
                  "price": 10,
                  "quantity": 1
                },
                {
                  "position": 28,
                  "id": "SKU28",
                  "name": "Product 28",
                  "variant": "item",
                  "price": 10,
                  "quantity": 1
                },
                {
                  "position": 29,
                  "id": "SKU29",
                  "name": "Product 29",
                  "variant": "item",
                  "price": 10,
                  "quantity": 1
                },
                {
                  "position": 30,
                  "id": "SKU30",
                  "name": "Product 30",
                  "variant": "item",
                  "price": 10,
                  "quantity": 1
                }
              ]
            }
          }
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
                  "position": 31,
                  "id": "SKU31",
                  "name": "Product 31",
                  "variant": "item",
                  "price": 10,
                  "quantity": 1
                },
                {
                  "position": 32,
                  "id": "SKU32",
                  "name": "Product 32",
                  "variant": "item",
                  "price": 10,
                  "quantity": 1
                },
                {
                  "position": 33,
                  "id": "SKU33",
                  "name": "Product 33",
                  "variant": "item",
                  "price": 10,
                  "quantity": 1
                },
                {
                  "position": 34,
                  "id": "SKU34",
                  "name": "Product 34",
                  "variant": "item",
                  "price": 10,
                  "quantity": 1
                },
                {
                  "position": 35,
                  "id": "SKU35",
                  "name": "Product 35",
                  "variant": "item",
                  "price": 10,
                  "quantity": 1
                }
              ]
            }
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
                  "position": 1,
                  "id": "SKU1",
                  "name": "Product 1",
                  "variant": "item",
                  "price": 10,
                  "quantity": 1
                },
                {
                  "position": 2,
                  "id": "SKU2",
                  "name": "Product 2",
                  "variant": "item",
                  "price": 10,
                  "quantity": 1
                },
                {
                  "position": 3,
                  "id": "SKU3",
                  "name": "Product 3",
                  "variant": "item",
                  "price": 10,
                  "quantity": 1
                },
                {
                  "position": 4,
                  "id": "SKU4",
                  "name": "Product 4",
                  "variant": "item",
                  "price": 10,
                  "quantity": 1
                },
                {
                  "position": 5,
                  "id": "SKU5",
                  "name": "Product 5",
                  "variant": "item",
                  "price": 10,
                  "quantity": 1
                },
                {
                  "position": 6,
                  "id": "SKU6",
                  "name": "Product 6",
                  "variant": "item",
                  "price": 10,
                  "quantity": 1
                },
                {
                  "position": 7,
                  "id": "SKU7",
                  "name": "Product 7",
                  "variant": "item",
                  "price": 10,
                  "quantity": 1
                },
                {
                  "position": 8,
                  "id": "SKU8",
                  "name": "Product 8",
                  "variant": "item",
                  "price": 10,
                  "quantity": 1
                },
                {
                  "position": 9,
                  "id": "SKU9",
                  "name": "Product 9",
                  "variant": "item",
                  "price": 10,
                  "quantity": 1
                },
                {
                  "position": 10,
                  "id": "SKU10",
                  "name": "Product 10",
                  "variant": "item",
                  "price": 10,
                  "quantity": 1
                },
                {
                  "position": 11,
                  "id": "SKU11",
                  "name": "Product 11",
                  "variant": "item",
                  "price": 10,
                  "quantity": 1
                },
                {
                  "position": 12,
                  "id": "SKU12",
                  "name": "Product 12",
                  "variant": "item",
                  "price": 10,
                  "quantity": 1
                },
                {
                  "position": 13,
                  "id": "SKU13",
                  "name": "Product 13",
                  "variant": "item",
                  "price": 10,
                  "quantity": 1
                },
                {
                  "position": 14,
                  "id": "SKU14",
                  "name": "Product 14",
                  "variant": "item",
                  "price": 10,
                  "quantity": 1
                },
                {
                  "position": 15,
                  "id": "SKU15",
                  "name": "Product 15",
                  "variant": "item",
                  "price": 10,
                  "quantity": 1
                },
                {
                  "position": 16,
                  "id": "SKU16",
                  "name": "Product 16",
                  "variant": "item",
                  "price": 10,
                  "quantity": 1
                },
                {
                  "position": 17,
                  "id": "SKU17",
                  "name": "Product 17",
                  "variant": "item",
                  "price": 10,
                  "quantity": 1
                },
                {
                  "position": 18,
                  "id": "SKU18",
                  "name": "Product 18",
                  "variant": "item",
                  "price": 10,
                  "quantity": 1
                },
                {
                  "position": 19,
                  "id": "SKU19",
                  "name": "Product 19",
                  "variant": "item",
                  "price": 10,
                  "quantity": 1
                },
                {
                  "position": 20,
                  "id": "SKU20",
                  "name": "Product 20",
                  "variant": "item",
                  "price": 10,
                  "quantity": 1
                },
                {
                  "position": 21,
                  "id": "SKU21",
                  "name": "Product 21",
                  "variant": "item",
                  "price": 10,
                  "quantity": 1
                },
                {
                  "position": 22,
                  "id": "SKU22",
                  "name": "Product 22",
                  "variant": "item",
                  "price": 10,
                  "quantity": 1
                },
                {
                  "position": 23,
                  "id": "SKU23",
                  "name": "Product 23",
                  "variant": "item",
                  "price": 10,
                  "quantity": 1
                },
                {
                  "position": 24,
                  "id": "SKU24",
                  "name": "Product 24",
                  "variant": "item",
                  "price": 10,
                  "quantity": 1
                },
                {
                  "position": 25,
                  "id": "SKU25",
                  "name": "Product 25",
                  "variant": "item",
                  "price": 10,
                  "quantity": 1
                },
                {
                  "position": 26,
                  "id": "SKU26",
                  "name": "Product 26",
                  "variant": "item",
                  "price": 10,
                  "quantity": 1
                },
                {
                  "position": 27,
                  "id": "SKU27",
                  "name": "Product 27",
                  "variant": "item",
                  "price": 10,
                  "quantity": 1
                },
                {
                  "position": 28,
                  "id": "SKU28",
                  "name": "Product 28",
                  "variant": "item",
                  "price": 10,
                  "quantity": 1
                },
                {
                  "position": 29,
                  "id": "SKU29",
                  "name": "Product 29",
                  "variant": "item",
                  "price": 10,
                  "quantity": 1
                },
                {
                  "position": 30,
                  "id": "SKU30",
                  "name": "Product 30",
                  "variant": "item",
                  "price": 10,
                  "quantity": 1
                }
              ]
            }
          }
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
                "step": 2,
                "option": "enter_shipping_address"
              },
              "products": [
                {
                  "position": 31,
                  "id": "SKU31",
                  "name": "Product 31",
                  "variant": "item",
                  "price": 10,
                  "quantity": 1
                },
                {
                  "position": 32,
                  "id": "SKU32",
                  "name": "Product 32",
                  "variant": "item",
                  "price": 10,
                  "quantity": 1
                },
                {
                  "position": 33,
                  "id": "SKU33",
                  "name": "Product 33",
                  "variant": "item",
                  "price": 10,
                  "quantity": 1
                },
                {
                  "position": 34,
                  "id": "SKU34",
                  "name": "Product 34",
                  "variant": "item",
                  "price": 10,
                  "quantity": 1
                },
                {
                  "position": 35,
                  "id": "SKU35",
                  "name": "Product 35",
                  "variant": "item",
                  "price": 10,
                  "quantity": 1
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
                  "position": 1,
                  "id": "SKU1",
                  "name": "Product 1",
                  "variant": "item",
                  "price": 10,
                  "quantity": 1
                },
                {
                  "position": 2,
                  "id": "SKU2",
                  "name": "Product 2",
                  "variant": "item",
                  "price": 10,
                  "quantity": 1
                },
                {
                  "position": 3,
                  "id": "SKU3",
                  "name": "Product 3",
                  "variant": "item",
                  "price": 10,
                  "quantity": 1
                },
                {
                  "position": 4,
                  "id": "SKU4",
                  "name": "Product 4",
                  "variant": "item",
                  "price": 10,
                  "quantity": 1
                },
                {
                  "position": 5,
                  "id": "SKU5",
                  "name": "Product 5",
                  "variant": "item",
                  "price": 10,
                  "quantity": 1
                },
                {
                  "position": 6,
                  "id": "SKU6",
                  "name": "Product 6",
                  "variant": "item",
                  "price": 10,
                  "quantity": 1
                },
                {
                  "position": 7,
                  "id": "SKU7",
                  "name": "Product 7",
                  "variant": "item",
                  "price": 10,
                  "quantity": 1
                },
                {
                  "position": 8,
                  "id": "SKU8",
                  "name": "Product 8",
                  "variant": "item",
                  "price": 10,
                  "quantity": 1
                },
                {
                  "position": 9,
                  "id": "SKU9",
                  "name": "Product 9",
                  "variant": "item",
                  "price": 10,
                  "quantity": 1
                },
                {
                  "position": 10,
                  "id": "SKU10",
                  "name": "Product 10",
                  "variant": "item",
                  "price": 10,
                  "quantity": 1
                },
                {
                  "position": 11,
                  "id": "SKU11",
                  "name": "Product 11",
                  "variant": "item",
                  "price": 10,
                  "quantity": 1
                },
                {
                  "position": 12,
                  "id": "SKU12",
                  "name": "Product 12",
                  "variant": "item",
                  "price": 10,
                  "quantity": 1
                },
                {
                  "position": 13,
                  "id": "SKU13",
                  "name": "Product 13",
                  "variant": "item",
                  "price": 10,
                  "quantity": 1
                },
                {
                  "position": 14,
                  "id": "SKU14",
                  "name": "Product 14",
                  "variant": "item",
                  "price": 10,
                  "quantity": 1
                },
                {
                  "position": 15,
                  "id": "SKU15",
                  "name": "Product 15",
                  "variant": "item",
                  "price": 10,
                  "quantity": 1
                },
                {
                  "position": 16,
                  "id": "SKU16",
                  "name": "Product 16",
                  "variant": "item",
                  "price": 10,
                  "quantity": 1
                },
                {
                  "position": 17,
                  "id": "SKU17",
                  "name": "Product 17",
                  "variant": "item",
                  "price": 10,
                  "quantity": 1
                },
                {
                  "position": 18,
                  "id": "SKU18",
                  "name": "Product 18",
                  "variant": "item",
                  "price": 10,
                  "quantity": 1
                },
                {
                  "position": 19,
                  "id": "SKU19",
                  "name": "Product 19",
                  "variant": "item",
                  "price": 10,
                  "quantity": 1
                },
                {
                  "position": 20,
                  "id": "SKU20",
                  "name": "Product 20",
                  "variant": "item",
                  "price": 10,
                  "quantity": 1
                },
                {
                  "position": 21,
                  "id": "SKU21",
                  "name": "Product 21",
                  "variant": "item",
                  "price": 10,
                  "quantity": 1
                },
                {
                  "position": 22,
                  "id": "SKU22",
                  "name": "Product 22",
                  "variant": "item",
                  "price": 10,
                  "quantity": 1
                },
                {
                  "position": 23,
                  "id": "SKU23",
                  "name": "Product 23",
                  "variant": "item",
                  "price": 10,
                  "quantity": 1
                },
                {
                  "position": 24,
                  "id": "SKU24",
                  "name": "Product 24",
                  "variant": "item",
                  "price": 10,
                  "quantity": 1
                },
                {
                  "position": 25,
                  "id": "SKU25",
                  "name": "Product 25",
                  "variant": "item",
                  "price": 10,
                  "quantity": 1
                },
                {
                  "position": 26,
                  "id": "SKU26",
                  "name": "Product 26",
                  "variant": "item",
                  "price": 10,
                  "quantity": 1
                },
                {
                  "position": 27,
                  "id": "SKU27",
                  "name": "Product 27",
                  "variant": "item",
                  "price": 10,
                  "quantity": 1
                },
                {
                  "position": 28,
                  "id": "SKU28",
                  "name": "Product 28",
                  "variant": "item",
                  "price": 10,
                  "quantity": 1
                },
                {
                  "position": 29,
                  "id": "SKU29",
                  "name": "Product 29",
                  "variant": "item",
                  "price": 10,
                  "quantity": 1
                },
                {
                  "position": 30,
                  "id": "SKU30",
                  "name": "Product 30",
                  "variant": "item",
                  "price": 10,
                  "quantity": 1
                }
              ]
            }
          }
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
                "step": 3,
                "option": "enter_shipping_method"
              },
              "products": [
                {
                  "position": 31,
                  "id": "SKU31",
                  "name": "Product 31",
                  "variant": "item",
                  "price": 10,
                  "quantity": 1
                },
                {
                  "position": 32,
                  "id": "SKU32",
                  "name": "Product 32",
                  "variant": "item",
                  "price": 10,
                  "quantity": 1
                },
                {
                  "position": 33,
                  "id": "SKU33",
                  "name": "Product 33",
                  "variant": "item",
                  "price": 10,
                  "quantity": 1
                },
                {
                  "position": 34,
                  "id": "SKU34",
                  "name": "Product 34",
                  "variant": "item",
                  "price": 10,
                  "quantity": 1
                },
                {
                  "position": 35,
                  "id": "SKU35",
                  "name": "Product 35",
                  "variant": "item",
                  "price": 10,
                  "quantity": 1
                }
              ]
            }
          }
        }
      """

  Scenario: Events on step "Payment"
    When I click "Continue"
    Then GTM data layer must contain the following message:
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
                  "position": 1,
                  "id": "SKU1",
                  "name": "Product 1",
                  "variant": "item",
                  "price": 10,
                  "quantity": 1
                },
                {
                  "position": 2,
                  "id": "SKU2",
                  "name": "Product 2",
                  "variant": "item",
                  "price": 10,
                  "quantity": 1
                },
                {
                  "position": 3,
                  "id": "SKU3",
                  "name": "Product 3",
                  "variant": "item",
                  "price": 10,
                  "quantity": 1
                },
                {
                  "position": 4,
                  "id": "SKU4",
                  "name": "Product 4",
                  "variant": "item",
                  "price": 10,
                  "quantity": 1
                },
                {
                  "position": 5,
                  "id": "SKU5",
                  "name": "Product 5",
                  "variant": "item",
                  "price": 10,
                  "quantity": 1
                },
                {
                  "position": 6,
                  "id": "SKU6",
                  "name": "Product 6",
                  "variant": "item",
                  "price": 10,
                  "quantity": 1
                },
                {
                  "position": 7,
                  "id": "SKU7",
                  "name": "Product 7",
                  "variant": "item",
                  "price": 10,
                  "quantity": 1
                },
                {
                  "position": 8,
                  "id": "SKU8",
                  "name": "Product 8",
                  "variant": "item",
                  "price": 10,
                  "quantity": 1
                },
                {
                  "position": 9,
                  "id": "SKU9",
                  "name": "Product 9",
                  "variant": "item",
                  "price": 10,
                  "quantity": 1
                },
                {
                  "position": 10,
                  "id": "SKU10",
                  "name": "Product 10",
                  "variant": "item",
                  "price": 10,
                  "quantity": 1
                },
                {
                  "position": 11,
                  "id": "SKU11",
                  "name": "Product 11",
                  "variant": "item",
                  "price": 10,
                  "quantity": 1
                },
                {
                  "position": 12,
                  "id": "SKU12",
                  "name": "Product 12",
                  "variant": "item",
                  "price": 10,
                  "quantity": 1
                },
                {
                  "position": 13,
                  "id": "SKU13",
                  "name": "Product 13",
                  "variant": "item",
                  "price": 10,
                  "quantity": 1
                },
                {
                  "position": 14,
                  "id": "SKU14",
                  "name": "Product 14",
                  "variant": "item",
                  "price": 10,
                  "quantity": 1
                },
                {
                  "position": 15,
                  "id": "SKU15",
                  "name": "Product 15",
                  "variant": "item",
                  "price": 10,
                  "quantity": 1
                },
                {
                  "position": 16,
                  "id": "SKU16",
                  "name": "Product 16",
                  "variant": "item",
                  "price": 10,
                  "quantity": 1
                },
                {
                  "position": 17,
                  "id": "SKU17",
                  "name": "Product 17",
                  "variant": "item",
                  "price": 10,
                  "quantity": 1
                },
                {
                  "position": 18,
                  "id": "SKU18",
                  "name": "Product 18",
                  "variant": "item",
                  "price": 10,
                  "quantity": 1
                },
                {
                  "position": 19,
                  "id": "SKU19",
                  "name": "Product 19",
                  "variant": "item",
                  "price": 10,
                  "quantity": 1
                },
                {
                  "position": 20,
                  "id": "SKU20",
                  "name": "Product 20",
                  "variant": "item",
                  "price": 10,
                  "quantity": 1
                },
                {
                  "position": 21,
                  "id": "SKU21",
                  "name": "Product 21",
                  "variant": "item",
                  "price": 10,
                  "quantity": 1
                },
                {
                  "position": 22,
                  "id": "SKU22",
                  "name": "Product 22",
                  "variant": "item",
                  "price": 10,
                  "quantity": 1
                },
                {
                  "position": 23,
                  "id": "SKU23",
                  "name": "Product 23",
                  "variant": "item",
                  "price": 10,
                  "quantity": 1
                },
                {
                  "position": 24,
                  "id": "SKU24",
                  "name": "Product 24",
                  "variant": "item",
                  "price": 10,
                  "quantity": 1
                },
                {
                  "position": 25,
                  "id": "SKU25",
                  "name": "Product 25",
                  "variant": "item",
                  "price": 10,
                  "quantity": 1
                },
                {
                  "position": 26,
                  "id": "SKU26",
                  "name": "Product 26",
                  "variant": "item",
                  "price": 10,
                  "quantity": 1
                },
                {
                  "position": 27,
                  "id": "SKU27",
                  "name": "Product 27",
                  "variant": "item",
                  "price": 10,
                  "quantity": 1
                },
                {
                  "position": 28,
                  "id": "SKU28",
                  "name": "Product 28",
                  "variant": "item",
                  "price": 10,
                  "quantity": 1
                },
                {
                  "position": 29,
                  "id": "SKU29",
                  "name": "Product 29",
                  "variant": "item",
                  "price": 10,
                  "quantity": 1
                },
                {
                  "position": 30,
                  "id": "SKU30",
                  "name": "Product 30",
                  "variant": "item",
                  "price": 10,
                  "quantity": 1
                }
              ]
            }
          }
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
                "step": 4,
                "option": "enter_payment"
              },
              "products": [
                {
                  "position": 31,
                  "id": "SKU31",
                  "name": "Product 31",
                  "variant": "item",
                  "price": 10,
                  "quantity": 1
                },
                {
                  "position": 32,
                  "id": "SKU32",
                  "name": "Product 32",
                  "variant": "item",
                  "price": 10,
                  "quantity": 1
                },
                {
                  "position": 33,
                  "id": "SKU33",
                  "name": "Product 33",
                  "variant": "item",
                  "price": 10,
                  "quantity": 1
                },
                {
                  "position": 34,
                  "id": "SKU34",
                  "name": "Product 34",
                  "variant": "item",
                  "price": 10,
                  "quantity": 1
                },
                {
                  "position": 35,
                  "id": "SKU35",
                  "name": "Product 35",
                  "variant": "item",
                  "price": 10,
                  "quantity": 1
                }
              ]
            }
          }
        }
      """

  Scenario: Events on step "Order Review"
    When I click "Continue"
    Then GTM data layer must contain the following message:
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
                  "position": 1,
                  "id": "SKU1",
                  "name": "Product 1",
                  "variant": "item",
                  "price": 10,
                  "quantity": 1
                },
                {
                  "position": 2,
                  "id": "SKU2",
                  "name": "Product 2",
                  "variant": "item",
                  "price": 10,
                  "quantity": 1
                },
                {
                  "position": 3,
                  "id": "SKU3",
                  "name": "Product 3",
                  "variant": "item",
                  "price": 10,
                  "quantity": 1
                },
                {
                  "position": 4,
                  "id": "SKU4",
                  "name": "Product 4",
                  "variant": "item",
                  "price": 10,
                  "quantity": 1
                },
                {
                  "position": 5,
                  "id": "SKU5",
                  "name": "Product 5",
                  "variant": "item",
                  "price": 10,
                  "quantity": 1
                },
                {
                  "position": 6,
                  "id": "SKU6",
                  "name": "Product 6",
                  "variant": "item",
                  "price": 10,
                  "quantity": 1
                },
                {
                  "position": 7,
                  "id": "SKU7",
                  "name": "Product 7",
                  "variant": "item",
                  "price": 10,
                  "quantity": 1
                },
                {
                  "position": 8,
                  "id": "SKU8",
                  "name": "Product 8",
                  "variant": "item",
                  "price": 10,
                  "quantity": 1
                },
                {
                  "position": 9,
                  "id": "SKU9",
                  "name": "Product 9",
                  "variant": "item",
                  "price": 10,
                  "quantity": 1
                },
                {
                  "position": 10,
                  "id": "SKU10",
                  "name": "Product 10",
                  "variant": "item",
                  "price": 10,
                  "quantity": 1
                },
                {
                  "position": 11,
                  "id": "SKU11",
                  "name": "Product 11",
                  "variant": "item",
                  "price": 10,
                  "quantity": 1
                },
                {
                  "position": 12,
                  "id": "SKU12",
                  "name": "Product 12",
                  "variant": "item",
                  "price": 10,
                  "quantity": 1
                },
                {
                  "position": 13,
                  "id": "SKU13",
                  "name": "Product 13",
                  "variant": "item",
                  "price": 10,
                  "quantity": 1
                },
                {
                  "position": 14,
                  "id": "SKU14",
                  "name": "Product 14",
                  "variant": "item",
                  "price": 10,
                  "quantity": 1
                },
                {
                  "position": 15,
                  "id": "SKU15",
                  "name": "Product 15",
                  "variant": "item",
                  "price": 10,
                  "quantity": 1
                },
                {
                  "position": 16,
                  "id": "SKU16",
                  "name": "Product 16",
                  "variant": "item",
                  "price": 10,
                  "quantity": 1
                },
                {
                  "position": 17,
                  "id": "SKU17",
                  "name": "Product 17",
                  "variant": "item",
                  "price": 10,
                  "quantity": 1
                },
                {
                  "position": 18,
                  "id": "SKU18",
                  "name": "Product 18",
                  "variant": "item",
                  "price": 10,
                  "quantity": 1
                },
                {
                  "position": 19,
                  "id": "SKU19",
                  "name": "Product 19",
                  "variant": "item",
                  "price": 10,
                  "quantity": 1
                },
                {
                  "position": 20,
                  "id": "SKU20",
                  "name": "Product 20",
                  "variant": "item",
                  "price": 10,
                  "quantity": 1
                },
                {
                  "position": 21,
                  "id": "SKU21",
                  "name": "Product 21",
                  "variant": "item",
                  "price": 10,
                  "quantity": 1
                },
                {
                  "position": 22,
                  "id": "SKU22",
                  "name": "Product 22",
                  "variant": "item",
                  "price": 10,
                  "quantity": 1
                },
                {
                  "position": 23,
                  "id": "SKU23",
                  "name": "Product 23",
                  "variant": "item",
                  "price": 10,
                  "quantity": 1
                },
                {
                  "position": 24,
                  "id": "SKU24",
                  "name": "Product 24",
                  "variant": "item",
                  "price": 10,
                  "quantity": 1
                },
                {
                  "position": 25,
                  "id": "SKU25",
                  "name": "Product 25",
                  "variant": "item",
                  "price": 10,
                  "quantity": 1
                },
                {
                  "position": 26,
                  "id": "SKU26",
                  "name": "Product 26",
                  "variant": "item",
                  "price": 10,
                  "quantity": 1
                },
                {
                  "position": 27,
                  "id": "SKU27",
                  "name": "Product 27",
                  "variant": "item",
                  "price": 10,
                  "quantity": 1
                },
                {
                  "position": 28,
                  "id": "SKU28",
                  "name": "Product 28",
                  "variant": "item",
                  "price": 10,
                  "quantity": 1
                },
                {
                  "position": 29,
                  "id": "SKU29",
                  "name": "Product 29",
                  "variant": "item",
                  "price": 10,
                  "quantity": 1
                },
                {
                  "position": 30,
                  "id": "SKU30",
                  "name": "Product 30",
                  "variant": "item",
                  "price": 10,
                  "quantity": 1
                }
              ]
            }
          }
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
                "step": 5,
                "option": "order_review"
              },
              "products": [
                {
                  "position": 31,
                  "id": "SKU31",
                  "name": "Product 31",
                  "variant": "item",
                  "price": 10,
                  "quantity": 1
                },
                {
                  "position": 32,
                  "id": "SKU32",
                  "name": "Product 32",
                  "variant": "item",
                  "price": 10,
                  "quantity": 1
                },
                {
                  "position": 33,
                  "id": "SKU33",
                  "name": "Product 33",
                  "variant": "item",
                  "price": 10,
                  "quantity": 1
                },
                {
                  "position": 34,
                  "id": "SKU34",
                  "name": "Product 34",
                  "variant": "item",
                  "price": 10,
                  "quantity": 1
                },
                {
                  "position": 35,
                  "id": "SKU35",
                  "name": "Product 35",
                  "variant": "item",
                  "price": 10,
                  "quantity": 1
                }
              ]
            }
          }
        }
      """

    When I click "Submit Order"
    Then GTM data layer must contain the following message:
      """
      {
        "event": "purchase",
        "ecommerce": {
          "currencyCode": "USD",
          "purchase": {
            "actionField": {
              "id": 1,
              "revenue": 353,
              "tax": 0,
              "shipping": 3,
              "affiliation": "Default"
            },
            "products": [
              {
                "position": 1,
                "id": "SKU1",
                "name": "Product 1",
                "variant": "item",
                "price": 10,
                "quantity": 1
              },
              {
                "position": 2,
                "id": "SKU2",
                "name": "Product 2",
                "variant": "item",
                "price": 10,
                "quantity": 1
              },
              {
                "position": 3,
                "id": "SKU3",
                "name": "Product 3",
                "variant": "item",
                "price": 10,
                "quantity": 1
              },
              {
                "position": 4,
                "id": "SKU4",
                "name": "Product 4",
                "variant": "item",
                "price": 10,
                "quantity": 1
              },
              {
                "position": 5,
                "id": "SKU5",
                "name": "Product 5",
                "variant": "item",
                "price": 10,
                "quantity": 1
              },
              {
                "position": 6,
                "id": "SKU6",
                "name": "Product 6",
                "variant": "item",
                "price": 10,
                "quantity": 1
              },
              {
                "position": 7,
                "id": "SKU7",
                "name": "Product 7",
                "variant": "item",
                "price": 10,
                "quantity": 1
              },
              {
                "position": 8,
                "id": "SKU8",
                "name": "Product 8",
                "variant": "item",
                "price": 10,
                "quantity": 1
              },
              {
                "position": 9,
                "id": "SKU9",
                "name": "Product 9",
                "variant": "item",
                "price": 10,
                "quantity": 1
              },
              {
                "position": 10,
                "id": "SKU10",
                "name": "Product 10",
                "variant": "item",
                "price": 10,
                "quantity": 1
              },
              {
                "position": 11,
                "id": "SKU11",
                "name": "Product 11",
                "variant": "item",
                "price": 10,
                "quantity": 1
              },
              {
                "position": 12,
                "id": "SKU12",
                "name": "Product 12",
                "variant": "item",
                "price": 10,
                "quantity": 1
              },
              {
                "position": 13,
                "id": "SKU13",
                "name": "Product 13",
                "variant": "item",
                "price": 10,
                "quantity": 1
              },
              {
                "position": 14,
                "id": "SKU14",
                "name": "Product 14",
                "variant": "item",
                "price": 10,
                "quantity": 1
              },
              {
                "position": 15,
                "id": "SKU15",
                "name": "Product 15",
                "variant": "item",
                "price": 10,
                "quantity": 1
              },
              {
                "position": 16,
                "id": "SKU16",
                "name": "Product 16",
                "variant": "item",
                "price": 10,
                "quantity": 1
              },
              {
                "position": 17,
                "id": "SKU17",
                "name": "Product 17",
                "variant": "item",
                "price": 10,
                "quantity": 1
              },
              {
                "position": 18,
                "id": "SKU18",
                "name": "Product 18",
                "variant": "item",
                "price": 10,
                "quantity": 1
              },
              {
                "position": 19,
                "id": "SKU19",
                "name": "Product 19",
                "variant": "item",
                "price": 10,
                "quantity": 1
              },
              {
                "position": 20,
                "id": "SKU20",
                "name": "Product 20",
                "variant": "item",
                "price": 10,
                "quantity": 1
              },
              {
                "position": 21,
                "id": "SKU21",
                "name": "Product 21",
                "variant": "item",
                "price": 10,
                "quantity": 1
              },
              {
                "position": 22,
                "id": "SKU22",
                "name": "Product 22",
                "variant": "item",
                "price": 10,
                "quantity": 1
              },
              {
                "position": 23,
                "id": "SKU23",
                "name": "Product 23",
                "variant": "item",
                "price": 10,
                "quantity": 1
              },
              {
                "position": 24,
                "id": "SKU24",
                "name": "Product 24",
                "variant": "item",
                "price": 10,
                "quantity": 1
              },
              {
                "position": 25,
                "id": "SKU25",
                "name": "Product 25",
                "variant": "item",
                "price": 10,
                "quantity": 1
              },
              {
                "position": 26,
                "id": "SKU26",
                "name": "Product 26",
                "variant": "item",
                "price": 10,
                "quantity": 1
              },
              {
                "position": 27,
                "id": "SKU27",
                "name": "Product 27",
                "variant": "item",
                "price": 10,
                "quantity": 1
              },
              {
                "position": 28,
                "id": "SKU28",
                "name": "Product 28",
                "variant": "item",
                "price": 10,
                "quantity": 1
              },
              {
                "position": 29,
                "id": "SKU29",
                "name": "Product 29",
                "variant": "item",
                "price": 10,
                "quantity": 1
              },
              {
                "position": 30,
                "id": "SKU30",
                "name": "Product 30",
                "variant": "item",
                "price": 10,
                "quantity": 1
              }
            ]
          },
          "shippingMethod": "Flat Rate",
          "paymentMethod": "Payment Term"
        }
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
              "id": 1
            },
            "products": [
              {
                "position": 31,
                "id": "SKU31",
                "name": "Product 31",
                "variant": "item",
                "price": 10,
                "quantity": 1
              },
              {
                "position": 32,
                "id": "SKU32",
                "name": "Product 32",
                "variant": "item",
                "price": 10,
                "quantity": 1
              },
              {
                "position": 33,
                "id": "SKU33",
                "name": "Product 33",
                "variant": "item",
                "price": 10,
                "quantity": 1
              },
              {
                "position": 34,
                "id": "SKU34",
                "name": "Product 34",
                "variant": "item",
                "price": 10,
                "quantity": 1
              },
              {
                "position": 35,
                "id": "SKU35",
                "name": "Product 35",
                "variant": "item",
                "price": 10,
                "quantity": 1
              }
            ]
          },
          "shippingMethod": "Flat Rate",
          "paymentMethod": "Payment Term"
        }
      }
      """
