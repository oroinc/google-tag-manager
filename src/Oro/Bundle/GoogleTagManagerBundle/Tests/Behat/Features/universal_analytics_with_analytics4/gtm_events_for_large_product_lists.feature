@regression
@feature-BB-21298
@fixture-OroFlatRateShippingBundle:FlatRateIntegration.yml
@fixture-OroPaymentTermBundle:PaymentTermIntegration.yml
@fixture-OroGoogleTagManagerBundle:integration.yml
@fixture-OroGoogleTagManagerBundle:many_products.yml

Feature: GTM events for large product lists

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
    And I signed in as AmandaRCole@example.org on the store frontend

  Scenario: Batch events on add to shopping list from product datagrid
    When I type "" in "search"
    And I click "Search Button"
    And I select 50 records per page in "ProductFrontendGrid"
    And I check All Visible records in "ProductFrontendGrid"
    And I wait for products to load
    And I click "Create New Shopping List" link from mass action dropdown in "Product Frontend Grid"
    And I click "Create and Add"
    Then I should see "35 products were added"
    And GTM data layer must contain the following message:
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

    And GTM data layer must contain the following message:
      """
        {
          "event": "add_to_cart",
          "ecommerce": {
            "currency": "USD",
            "value": 350,
            "items": [
              {
                "item_id": "SKU1",
                "item_name": "Product 1",
                "item_variant": "item",
                "price": 10,
                "quantity": 1
              },
              {
                "item_id": "SKU2",
                "item_name": "Product 2",
                "item_variant": "item",
                "price": 10,
                "quantity": 1
              },
              {
                "item_id": "SKU3",
                "item_name": "Product 3",
                "item_variant": "item",
                "price": 10,
                "quantity": 1
              },
              {
                "item_id": "SKU4",
                "item_name": "Product 4",
                "item_variant": "item",
                "price": 10,
                "quantity": 1
              },
              {
                "item_id": "SKU5",
                "item_name": "Product 5",
                "item_variant": "item",
                "price": 10,
                "quantity": 1
              },
              {
                "item_id": "SKU6",
                "item_name": "Product 6",
                "item_variant": "item",
                "price": 10,
                "quantity": 1
              },
              {
                "item_id": "SKU7",
                "item_name": "Product 7",
                "item_variant": "item",
                "price": 10,
                "quantity": 1
              },
              {
                "item_id": "SKU8",
                "item_name": "Product 8",
                "item_variant": "item",
                "price": 10,
                "quantity": 1
              },
              {
                "item_id": "SKU9",
                "item_name": "Product 9",
                "item_variant": "item",
                "price": 10,
                "quantity": 1
              },
              {
                "item_id": "SKU10",
                "item_name": "Product 10",
                "item_variant": "item",
                "price": 10,
                "quantity": 1
              },
              {
                "item_id": "SKU11",
                "item_name": "Product 11",
                "item_variant": "item",
                "price": 10,
                "quantity": 1
              },
              {
                "item_id": "SKU12",
                "item_name": "Product 12",
                "item_variant": "item",
                "price": 10,
                "quantity": 1
              },
              {
                "item_id": "SKU13",
                "item_name": "Product 13",
                "item_variant": "item",
                "price": 10,
                "quantity": 1
              },
              {
                "item_id": "SKU14",
                "item_name": "Product 14",
                "item_variant": "item",
                "price": 10,
                "quantity": 1
              },
              {
                "item_id": "SKU15",
                "item_name": "Product 15",
                "item_variant": "item",
                "price": 10,
                "quantity": 1
              },
              {
                "item_id": "SKU16",
                "item_name": "Product 16",
                "item_variant": "item",
                "price": 10,
                "quantity": 1
              },
              {
                "item_id": "SKU17",
                "item_name": "Product 17",
                "item_variant": "item",
                "price": 10,
                "quantity": 1
              },
              {
                "item_id": "SKU18",
                "item_name": "Product 18",
                "item_variant": "item",
                "price": 10,
                "quantity": 1
              },
              {
                "item_id": "SKU19",
                "item_name": "Product 19",
                "item_variant": "item",
                "price": 10,
                "quantity": 1
              },
              {
                "item_id": "SKU20",
                "item_name": "Product 20",
                "item_variant": "item",
                "price": 10,
                "quantity": 1
              },
              {
                "item_id": "SKU21",
                "item_name": "Product 21",
                "item_variant": "item",
                "price": 10,
                "quantity": 1
              },
              {
                "item_id": "SKU22",
                "item_name": "Product 22",
                "item_variant": "item",
                "price": 10,
                "quantity": 1
              },
              {
                "item_id": "SKU23",
                "item_name": "Product 23",
                "item_variant": "item",
                "price": 10,
                "quantity": 1
              },
              {
                "item_id": "SKU24",
                "item_name": "Product 24",
                "item_variant": "item",
                "price": 10,
                "quantity": 1
              },
              {
                "item_id": "SKU25",
                "item_name": "Product 25",
                "item_variant": "item",
                "price": 10,
                "quantity": 1
              },
              {
                "item_id": "SKU26",
                "item_name": "Product 26",
                "item_variant": "item",
                "price": 10,
                "quantity": 1
              },
              {
                "item_id": "SKU27",
                "item_name": "Product 27",
                "item_variant": "item",
                "price": 10,
                "quantity": 1
              },
              {
                "item_id": "SKU28",
                "item_name": "Product 28",
                "item_variant": "item",
                "price": 10,
                "quantity": 1
              },
              {
                "item_id": "SKU29",
                "item_name": "Product 29",
                "item_variant": "item",
                "price": 10,
                "quantity": 1
              },
              {
                "item_id": "SKU30",
                "item_name": "Product 30",
                "item_variant": "item",
                "price": 10,
                "quantity": 1
              }
            ]
          }
        }
      """

    And GTM data layer must contain the following message:
      """
        {
          "event": "add_to_cart",
          "ecommerce": {
            "items": [
              {
                "item_id": "SKU31",
                "item_name": "Product 31",
                "item_variant": "item",
                "price": 10,
                "quantity": 1
              },
              {
                "item_id": "SKU32",
                "item_name": "Product 32",
                "item_variant": "item",
                "price": 10,
                "quantity": 1
              },
              {
                "item_id": "SKU33",
                "item_name": "Product 33",
                "item_variant": "item",
                "price": 10,
                "quantity": 1
              },
              {
                "item_id": "SKU34",
                "item_name": "Product 34",
                "item_variant": "item",
                "price": 10,
                "quantity": 1
              },
              {
                "item_id": "SKU35",
                "item_name": "Product 35",
                "item_variant": "item",
                "price": 10,
                "quantity": 1
              }
            ]
          }
        }
      """

  Scenario: Batch events on request for quote
    When I open page with shopping list Shopping List
    And I click "More Actions"
    And I click "Request Quote"
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

    And GTM data layer must contain the following message:
      """
        {
          "event": "add_to_cart",
          "ecommerce": {
            "currency": "USD",
            "value": 350,
            "items": [
              {
                "item_id": "SKU1",
                "item_name": "Product 1",
                "item_variant": "item",
                "price": 10,
                "quantity": 1
              },
              {
                "item_id": "SKU2",
                "item_name": "Product 2",
                "item_variant": "item",
                "price": 10,
                "quantity": 1
              },
              {
                "item_id": "SKU3",
                "item_name": "Product 3",
                "item_variant": "item",
                "price": 10,
                "quantity": 1
              },
              {
                "item_id": "SKU4",
                "item_name": "Product 4",
                "item_variant": "item",
                "price": 10,
                "quantity": 1
              },
              {
                "item_id": "SKU5",
                "item_name": "Product 5",
                "item_variant": "item",
                "price": 10,
                "quantity": 1
              },
              {
                "item_id": "SKU6",
                "item_name": "Product 6",
                "item_variant": "item",
                "price": 10,
                "quantity": 1
              },
              {
                "item_id": "SKU7",
                "item_name": "Product 7",
                "item_variant": "item",
                "price": 10,
                "quantity": 1
              },
              {
                "item_id": "SKU8",
                "item_name": "Product 8",
                "item_variant": "item",
                "price": 10,
                "quantity": 1
              },
              {
                "item_id": "SKU9",
                "item_name": "Product 9",
                "item_variant": "item",
                "price": 10,
                "quantity": 1
              },
              {
                "item_id": "SKU10",
                "item_name": "Product 10",
                "item_variant": "item",
                "price": 10,
                "quantity": 1
              },
              {
                "item_id": "SKU11",
                "item_name": "Product 11",
                "item_variant": "item",
                "price": 10,
                "quantity": 1
              },
              {
                "item_id": "SKU12",
                "item_name": "Product 12",
                "item_variant": "item",
                "price": 10,
                "quantity": 1
              },
              {
                "item_id": "SKU13",
                "item_name": "Product 13",
                "item_variant": "item",
                "price": 10,
                "quantity": 1
              },
              {
                "item_id": "SKU14",
                "item_name": "Product 14",
                "item_variant": "item",
                "price": 10,
                "quantity": 1
              },
              {
                "item_id": "SKU15",
                "item_name": "Product 15",
                "item_variant": "item",
                "price": 10,
                "quantity": 1
              },
              {
                "item_id": "SKU16",
                "item_name": "Product 16",
                "item_variant": "item",
                "price": 10,
                "quantity": 1
              },
              {
                "item_id": "SKU17",
                "item_name": "Product 17",
                "item_variant": "item",
                "price": 10,
                "quantity": 1
              },
              {
                "item_id": "SKU18",
                "item_name": "Product 18",
                "item_variant": "item",
                "price": 10,
                "quantity": 1
              },
              {
                "item_id": "SKU19",
                "item_name": "Product 19",
                "item_variant": "item",
                "price": 10,
                "quantity": 1
              },
              {
                "item_id": "SKU20",
                "item_name": "Product 20",
                "item_variant": "item",
                "price": 10,
                "quantity": 1
              },
              {
                "item_id": "SKU21",
                "item_name": "Product 21",
                "item_variant": "item",
                "price": 10,
                "quantity": 1
              },
              {
                "item_id": "SKU22",
                "item_name": "Product 22",
                "item_variant": "item",
                "price": 10,
                "quantity": 1
              },
              {
                "item_id": "SKU23",
                "item_name": "Product 23",
                "item_variant": "item",
                "price": 10,
                "quantity": 1
              },
              {
                "item_id": "SKU24",
                "item_name": "Product 24",
                "item_variant": "item",
                "price": 10,
                "quantity": 1
              },
              {
                "item_id": "SKU25",
                "item_name": "Product 25",
                "item_variant": "item",
                "price": 10,
                "quantity": 1
              },
              {
                "item_id": "SKU26",
                "item_name": "Product 26",
                "item_variant": "item",
                "price": 10,
                "quantity": 1
              },
              {
                "item_id": "SKU27",
                "item_name": "Product 27",
                "item_variant": "item",
                "price": 10,
                "quantity": 1
              },
              {
                "item_id": "SKU28",
                "item_name": "Product 28",
                "item_variant": "item",
                "price": 10,
                "quantity": 1
              },
              {
                "item_id": "SKU29",
                "item_name": "Product 29",
                "item_variant": "item",
                "price": 10,
                "quantity": 1
              },
              {
                "item_id": "SKU30",
                "item_name": "Product 30",
                "item_variant": "item",
                "price": 10,
                "quantity": 1
              }
            ]
          }
        }
      """

    And GTM data layer must contain the following message:
      """
        {
          "event": "add_to_cart",
          "ecommerce": {
            "items": [
              {
                "item_id": "SKU31",
                "item_name": "Product 31",
                "item_variant": "item",
                "price": 10,
                "quantity": 1
              },
              {
                "item_id": "SKU32",
                "item_name": "Product 32",
                "item_variant": "item",
                "price": 10,
                "quantity": 1
              },
              {
                "item_id": "SKU33",
                "item_name": "Product 33",
                "item_variant": "item",
                "price": 10,
                "quantity": 1
              },
              {
                "item_id": "SKU34",
                "item_name": "Product 34",
                "item_variant": "item",
                "price": 10,
                "quantity": 1
              },
              {
                "item_id": "SKU35",
                "item_name": "Product 35",
                "item_variant": "item",
                "price": 10,
                "quantity": 1
              }
            ]
          }
        }
      """

  Scenario: Batch events on delete shopping list
    When I open page with shopping list Shopping List
    And I click "Shopping List Actions"
    And I click "Delete"
    And I click "Yes, delete"
    Then GTM data layer must contain the following message:
      """
        {
          "event": "removeFromCart",
          "ecommerce": {
            "currencyCode": "USD",
            "remove": {
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
          "event": "removeFromCart",
          "ecommerce": {
            "currencyCode": "USD",
            "remove": {
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

    And GTM data layer must contain the following message:
      """
        {
          "event": "remove_from_cart",
          "ecommerce": {
            "currency": "USD",
            "value": 350,
            "items": [
              {
                "item_id": "SKU1",
                "item_name": "Product 1",
                "item_variant": "item",
                "price": 10,
                "quantity": 1
              },
              {
                "item_id": "SKU2",
                "item_name": "Product 2",
                "item_variant": "item",
                "price": 10,
                "quantity": 1
              },
              {
                "item_id": "SKU3",
                "item_name": "Product 3",
                "item_variant": "item",
                "price": 10,
                "quantity": 1
              },
              {
                "item_id": "SKU4",
                "item_name": "Product 4",
                "item_variant": "item",
                "price": 10,
                "quantity": 1
              },
              {
                "item_id": "SKU5",
                "item_name": "Product 5",
                "item_variant": "item",
                "price": 10,
                "quantity": 1
              },
              {
                "item_id": "SKU6",
                "item_name": "Product 6",
                "item_variant": "item",
                "price": 10,
                "quantity": 1
              },
              {
                "item_id": "SKU7",
                "item_name": "Product 7",
                "item_variant": "item",
                "price": 10,
                "quantity": 1
              },
              {
                "item_id": "SKU8",
                "item_name": "Product 8",
                "item_variant": "item",
                "price": 10,
                "quantity": 1
              },
              {
                "item_id": "SKU9",
                "item_name": "Product 9",
                "item_variant": "item",
                "price": 10,
                "quantity": 1
              },
              {
                "item_id": "SKU10",
                "item_name": "Product 10",
                "item_variant": "item",
                "price": 10,
                "quantity": 1
              },
              {
                "item_id": "SKU11",
                "item_name": "Product 11",
                "item_variant": "item",
                "price": 10,
                "quantity": 1
              },
              {
                "item_id": "SKU12",
                "item_name": "Product 12",
                "item_variant": "item",
                "price": 10,
                "quantity": 1
              },
              {
                "item_id": "SKU13",
                "item_name": "Product 13",
                "item_variant": "item",
                "price": 10,
                "quantity": 1
              },
              {
                "item_id": "SKU14",
                "item_name": "Product 14",
                "item_variant": "item",
                "price": 10,
                "quantity": 1
              },
              {
                "item_id": "SKU15",
                "item_name": "Product 15",
                "item_variant": "item",
                "price": 10,
                "quantity": 1
              },
              {
                "item_id": "SKU16",
                "item_name": "Product 16",
                "item_variant": "item",
                "price": 10,
                "quantity": 1
              },
              {
                "item_id": "SKU17",
                "item_name": "Product 17",
                "item_variant": "item",
                "price": 10,
                "quantity": 1
              },
              {
                "item_id": "SKU18",
                "item_name": "Product 18",
                "item_variant": "item",
                "price": 10,
                "quantity": 1
              },
              {
                "item_id": "SKU19",
                "item_name": "Product 19",
                "item_variant": "item",
                "price": 10,
                "quantity": 1
              },
              {
                "item_id": "SKU20",
                "item_name": "Product 20",
                "item_variant": "item",
                "price": 10,
                "quantity": 1
              },
              {
                "item_id": "SKU21",
                "item_name": "Product 21",
                "item_variant": "item",
                "price": 10,
                "quantity": 1
              },
              {
                "item_id": "SKU22",
                "item_name": "Product 22",
                "item_variant": "item",
                "price": 10,
                "quantity": 1
              },
              {
                "item_id": "SKU23",
                "item_name": "Product 23",
                "item_variant": "item",
                "price": 10,
                "quantity": 1
              },
              {
                "item_id": "SKU24",
                "item_name": "Product 24",
                "item_variant": "item",
                "price": 10,
                "quantity": 1
              },
              {
                "item_id": "SKU25",
                "item_name": "Product 25",
                "item_variant": "item",
                "price": 10,
                "quantity": 1
              },
              {
                "item_id": "SKU26",
                "item_name": "Product 26",
                "item_variant": "item",
                "price": 10,
                "quantity": 1
              },
              {
                "item_id": "SKU27",
                "item_name": "Product 27",
                "item_variant": "item",
                "price": 10,
                "quantity": 1
              },
              {
                "item_id": "SKU28",
                "item_name": "Product 28",
                "item_variant": "item",
                "price": 10,
                "quantity": 1
              },
              {
                "item_id": "SKU29",
                "item_name": "Product 29",
                "item_variant": "item",
                "price": 10,
                "quantity": 1
              },
              {
                "item_id": "SKU30",
                "item_name": "Product 30",
                "item_variant": "item",
                "price": 10,
                "quantity": 1
              }
            ]
          }
        }
      """

    And GTM data layer must contain the following message:
      """
        {
          "event": "remove_from_cart",
          "ecommerce": {
            "items": [
              {
                "item_id": "SKU31",
                "item_name": "Product 31",
                "item_variant": "item",
                "price": 10,
                "quantity": 1
              },
              {
                "item_id": "SKU32",
                "item_name": "Product 32",
                "item_variant": "item",
                "price": 10,
                "quantity": 1
              },
              {
                "item_id": "SKU33",
                "item_name": "Product 33",
                "item_variant": "item",
                "price": 10,
                "quantity": 1
              },
              {
                "item_id": "SKU34",
                "item_name": "Product 34",
                "item_variant": "item",
                "price": 10,
                "quantity": 1
              },
              {
                "item_id": "SKU35",
                "item_name": "Product 35",
                "item_variant": "item",
                "price": 10,
                "quantity": 1
              }
            ]
          }
        }
      """

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
    And I click "Add to Form"
    And I wait for products to load
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

    And GTM data layer must contain the following message:
      """
        {
          "event": "add_to_cart",
          "ecommerce": {
            "currency": "USD",
            "value": 350,
            "items": [
              {
                "item_id": "SKU1",
                "item_name": "Product 1",
                "item_variant": "item",
                "price": 10,
                "quantity": 1
              },
              {
                "item_id": "SKU2",
                "item_name": "Product 2",
                "item_variant": "item",
                "price": 10,
                "quantity": 1
              },
              {
                "item_id": "SKU3",
                "item_name": "Product 3",
                "item_variant": "item",
                "price": 10,
                "quantity": 1
              },
              {
                "item_id": "SKU4",
                "item_name": "Product 4",
                "item_variant": "item",
                "price": 10,
                "quantity": 1
              },
              {
                "item_id": "SKU5",
                "item_name": "Product 5",
                "item_variant": "item",
                "price": 10,
                "quantity": 1
              },
              {
                "item_id": "SKU6",
                "item_name": "Product 6",
                "item_variant": "item",
                "price": 10,
                "quantity": 1
              },
              {
                "item_id": "SKU7",
                "item_name": "Product 7",
                "item_variant": "item",
                "price": 10,
                "quantity": 1
              },
              {
                "item_id": "SKU8",
                "item_name": "Product 8",
                "item_variant": "item",
                "price": 10,
                "quantity": 1
              },
              {
                "item_id": "SKU9",
                "item_name": "Product 9",
                "item_variant": "item",
                "price": 10,
                "quantity": 1
              },
              {
                "item_id": "SKU10",
                "item_name": "Product 10",
                "item_variant": "item",
                "price": 10,
                "quantity": 1
              },
              {
                "item_id": "SKU11",
                "item_name": "Product 11",
                "item_variant": "item",
                "price": 10,
                "quantity": 1
              },
              {
                "item_id": "SKU12",
                "item_name": "Product 12",
                "item_variant": "item",
                "price": 10,
                "quantity": 1
              },
              {
                "item_id": "SKU13",
                "item_name": "Product 13",
                "item_variant": "item",
                "price": 10,
                "quantity": 1
              },
              {
                "item_id": "SKU14",
                "item_name": "Product 14",
                "item_variant": "item",
                "price": 10,
                "quantity": 1
              },
              {
                "item_id": "SKU15",
                "item_name": "Product 15",
                "item_variant": "item",
                "price": 10,
                "quantity": 1
              },
              {
                "item_id": "SKU16",
                "item_name": "Product 16",
                "item_variant": "item",
                "price": 10,
                "quantity": 1
              },
              {
                "item_id": "SKU17",
                "item_name": "Product 17",
                "item_variant": "item",
                "price": 10,
                "quantity": 1
              },
              {
                "item_id": "SKU18",
                "item_name": "Product 18",
                "item_variant": "item",
                "price": 10,
                "quantity": 1
              },
              {
                "item_id": "SKU19",
                "item_name": "Product 19",
                "item_variant": "item",
                "price": 10,
                "quantity": 1
              },
              {
                "item_id": "SKU20",
                "item_name": "Product 20",
                "item_variant": "item",
                "price": 10,
                "quantity": 1
              },
              {
                "item_id": "SKU21",
                "item_name": "Product 21",
                "item_variant": "item",
                "price": 10,
                "quantity": 1
              },
              {
                "item_id": "SKU22",
                "item_name": "Product 22",
                "item_variant": "item",
                "price": 10,
                "quantity": 1
              },
              {
                "item_id": "SKU23",
                "item_name": "Product 23",
                "item_variant": "item",
                "price": 10,
                "quantity": 1
              },
              {
                "item_id": "SKU24",
                "item_name": "Product 24",
                "item_variant": "item",
                "price": 10,
                "quantity": 1
              },
              {
                "item_id": "SKU25",
                "item_name": "Product 25",
                "item_variant": "item",
                "price": 10,
                "quantity": 1
              },
              {
                "item_id": "SKU26",
                "item_name": "Product 26",
                "item_variant": "item",
                "price": 10,
                "quantity": 1
              },
              {
                "item_id": "SKU27",
                "item_name": "Product 27",
                "item_variant": "item",
                "price": 10,
                "quantity": 1
              },
              {
                "item_id": "SKU28",
                "item_name": "Product 28",
                "item_variant": "item",
                "price": 10,
                "quantity": 1
              },
              {
                "item_id": "SKU29",
                "item_name": "Product 29",
                "item_variant": "item",
                "price": 10,
                "quantity": 1
              },
              {
                "item_id": "SKU30",
                "item_name": "Product 30",
                "item_variant": "item",
                "price": 10,
                "quantity": 1
              }
            ]
          }
        }
      """

    And GTM data layer must contain the following message:
      """
        {
          "event": "add_to_cart",
          "ecommerce": {
            "items": [
              {
                "item_id": "SKU31",
                "item_name": "Product 31",
                "item_variant": "item",
                "price": 10,
                "quantity": 1
              },
              {
                "item_id": "SKU32",
                "item_name": "Product 32",
                "item_variant": "item",
                "price": 10,
                "quantity": 1
              },
              {
                "item_id": "SKU33",
                "item_name": "Product 33",
                "item_variant": "item",
                "price": 10,
                "quantity": 1
              },
              {
                "item_id": "SKU34",
                "item_name": "Product 34",
                "item_variant": "item",
                "price": 10,
                "quantity": 1
              },
              {
                "item_id": "SKU35",
                "item_name": "Product 35",
                "item_variant": "item",
                "price": 10,
                "quantity": 1
              }
            ]
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

    And GTM data layer must contain the following message:
      """
        {
          "event": "begin_checkout",
          "ecommerce": {
            "currency": "USD",
            "value": 350,
            "items": [
              {
                "index": 1,
                "item_id": "SKU1",
                "item_name": "Product 1",
                "item_variant": "item",
                "price": 10,
                "quantity": 1
              },
              {
                "index": 2,
                "item_id": "SKU2",
                "item_name": "Product 2",
                "item_variant": "item",
                "price": 10,
                "quantity": 1
              },
              {
                "index": 3,
                "item_id": "SKU3",
                "item_name": "Product 3",
                "item_variant": "item",
                "price": 10,
                "quantity": 1
              },
              {
                "index": 4,
                "item_id": "SKU4",
                "item_name": "Product 4",
                "item_variant": "item",
                "price": 10,
                "quantity": 1
              },
              {
                "index": 5,
                "item_id": "SKU5",
                "item_name": "Product 5",
                "item_variant": "item",
                "price": 10,
                "quantity": 1
              },
              {
                "index": 6,
                "item_id": "SKU6",
                "item_name": "Product 6",
                "item_variant": "item",
                "price": 10,
                "quantity": 1
              },
              {
                "index": 7,
                "item_id": "SKU7",
                "item_name": "Product 7",
                "item_variant": "item",
                "price": 10,
                "quantity": 1
              },
              {
                "index": 8,
                "item_id": "SKU8",
                "item_name": "Product 8",
                "item_variant": "item",
                "price": 10,
                "quantity": 1
              },
              {
                "index": 9,
                "item_id": "SKU9",
                "item_name": "Product 9",
                "item_variant": "item",
                "price": 10,
                "quantity": 1
              },
              {
                "index": 10,
                "item_id": "SKU10",
                "item_name": "Product 10",
                "item_variant": "item",
                "price": 10,
                "quantity": 1
              },
              {
                "index": 11,
                "item_id": "SKU11",
                "item_name": "Product 11",
                "item_variant": "item",
                "price": 10,
                "quantity": 1
              },
              {
                "index": 12,
                "item_id": "SKU12",
                "item_name": "Product 12",
                "item_variant": "item",
                "price": 10,
                "quantity": 1
              },
              {
                "index": 13,
                "item_id": "SKU13",
                "item_name": "Product 13",
                "item_variant": "item",
                "price": 10,
                "quantity": 1
              },
              {
                "index": 14,
                "item_id": "SKU14",
                "item_name": "Product 14",
                "item_variant": "item",
                "price": 10,
                "quantity": 1
              },
              {
                "index": 15,
                "item_id": "SKU15",
                "item_name": "Product 15",
                "item_variant": "item",
                "price": 10,
                "quantity": 1
              },
              {
                "index": 16,
                "item_id": "SKU16",
                "item_name": "Product 16",
                "item_variant": "item",
                "price": 10,
                "quantity": 1
              },
              {
                "index": 17,
                "item_id": "SKU17",
                "item_name": "Product 17",
                "item_variant": "item",
                "price": 10,
                "quantity": 1
              },
              {
                "index": 18,
                "item_id": "SKU18",
                "item_name": "Product 18",
                "item_variant": "item",
                "price": 10,
                "quantity": 1
              },
              {
                "index": 19,
                "item_id": "SKU19",
                "item_name": "Product 19",
                "item_variant": "item",
                "price": 10,
                "quantity": 1
              },
              {
                "index": 20,
                "item_id": "SKU20",
                "item_name": "Product 20",
                "item_variant": "item",
                "price": 10,
                "quantity": 1
              },
              {
                "index": 21,
                "item_id": "SKU21",
                "item_name": "Product 21",
                "item_variant": "item",
                "price": 10,
                "quantity": 1
              },
              {
                "index": 22,
                "item_id": "SKU22",
                "item_name": "Product 22",
                "item_variant": "item",
                "price": 10,
                "quantity": 1
              },
              {
                "index": 23,
                "item_id": "SKU23",
                "item_name": "Product 23",
                "item_variant": "item",
                "price": 10,
                "quantity": 1
              },
              {
                "index": 24,
                "item_id": "SKU24",
                "item_name": "Product 24",
                "item_variant": "item",
                "price": 10,
                "quantity": 1
              },
              {
                "index": 25,
                "item_id": "SKU25",
                "item_name": "Product 25",
                "item_variant": "item",
                "price": 10,
                "quantity": 1
              },
              {
                "index": 26,
                "item_id": "SKU26",
                "item_name": "Product 26",
                "item_variant": "item",
                "price": 10,
                "quantity": 1
              },
              {
                "index": 27,
                "item_id": "SKU27",
                "item_name": "Product 27",
                "item_variant": "item",
                "price": 10,
                "quantity": 1
              },
              {
                "index": 28,
                "item_id": "SKU28",
                "item_name": "Product 28",
                "item_variant": "item",
                "price": 10,
                "quantity": 1
              },
              {
                "index": 29,
                "item_id": "SKU29",
                "item_name": "Product 29",
                "item_variant": "item",
                "price": 10,
                "quantity": 1
              },
              {
                "index": 30,
                "item_id": "SKU30",
                "item_name": "Product 30",
                "item_variant": "item",
                "price": 10,
                "quantity": 1
              }
            ]
          }
        }
      """

    And GTM data layer must contain the following message:
      """
        {
          "event": "begin_checkout",
          "ecommerce": {
            "items": [
              {
                "index": 31,
                "item_id": "SKU31",
                "item_name": "Product 31",
                "item_variant": "item",
                "price": 10,
                "quantity": 1
              },
              {
                "index": 32,
                "item_id": "SKU32",
                "item_name": "Product 32",
                "item_variant": "item",
                "price": 10,
                "quantity": 1
              },
              {
                "index": 33,
                "item_id": "SKU33",
                "item_name": "Product 33",
                "item_variant": "item",
                "price": 10,
                "quantity": 1
              },
              {
                "index": 34,
                "item_id": "SKU34",
                "item_name": "Product 34",
                "item_variant": "item",
                "price": 10,
                "quantity": 1
              },
              {
                "index": 35,
                "item_id": "SKU35",
                "item_name": "Product 35",
                "item_variant": "item",
                "price": 10,
                "quantity": 1
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

    And GTM data layer must contain the following message:
      """
        {
          "event": "add_shipping_info",
          "ecommerce": {
            "currency": "USD",
            "value": 350,
            "shipping_tier": "Flat Rate",
            "items": [
              {
                "index": 1,
                "item_id": "SKU1",
                "item_name": "Product 1",
                "item_variant": "item",
                "price": 10,
                "quantity": 1
              },
              {
                "index": 2,
                "item_id": "SKU2",
                "item_name": "Product 2",
                "item_variant": "item",
                "price": 10,
                "quantity": 1
              },
              {
                "index": 3,
                "item_id": "SKU3",
                "item_name": "Product 3",
                "item_variant": "item",
                "price": 10,
                "quantity": 1
              },
              {
                "index": 4,
                "item_id": "SKU4",
                "item_name": "Product 4",
                "item_variant": "item",
                "price": 10,
                "quantity": 1
              },
              {
                "index": 5,
                "item_id": "SKU5",
                "item_name": "Product 5",
                "item_variant": "item",
                "price": 10,
                "quantity": 1
              },
              {
                "index": 6,
                "item_id": "SKU6",
                "item_name": "Product 6",
                "item_variant": "item",
                "price": 10,
                "quantity": 1
              },
              {
                "index": 7,
                "item_id": "SKU7",
                "item_name": "Product 7",
                "item_variant": "item",
                "price": 10,
                "quantity": 1
              },
              {
                "index": 8,
                "item_id": "SKU8",
                "item_name": "Product 8",
                "item_variant": "item",
                "price": 10,
                "quantity": 1
              },
              {
                "index": 9,
                "item_id": "SKU9",
                "item_name": "Product 9",
                "item_variant": "item",
                "price": 10,
                "quantity": 1
              },
              {
                "index": 10,
                "item_id": "SKU10",
                "item_name": "Product 10",
                "item_variant": "item",
                "price": 10,
                "quantity": 1
              },
              {
                "index": 11,
                "item_id": "SKU11",
                "item_name": "Product 11",
                "item_variant": "item",
                "price": 10,
                "quantity": 1
              },
              {
                "index": 12,
                "item_id": "SKU12",
                "item_name": "Product 12",
                "item_variant": "item",
                "price": 10,
                "quantity": 1
              },
              {
                "index": 13,
                "item_id": "SKU13",
                "item_name": "Product 13",
                "item_variant": "item",
                "price": 10,
                "quantity": 1
              },
              {
                "index": 14,
                "item_id": "SKU14",
                "item_name": "Product 14",
                "item_variant": "item",
                "price": 10,
                "quantity": 1
              },
              {
                "index": 15,
                "item_id": "SKU15",
                "item_name": "Product 15",
                "item_variant": "item",
                "price": 10,
                "quantity": 1
              },
              {
                "index": 16,
                "item_id": "SKU16",
                "item_name": "Product 16",
                "item_variant": "item",
                "price": 10,
                "quantity": 1
              },
              {
                "index": 17,
                "item_id": "SKU17",
                "item_name": "Product 17",
                "item_variant": "item",
                "price": 10,
                "quantity": 1
              },
              {
                "index": 18,
                "item_id": "SKU18",
                "item_name": "Product 18",
                "item_variant": "item",
                "price": 10,
                "quantity": 1
              },
              {
                "index": 19,
                "item_id": "SKU19",
                "item_name": "Product 19",
                "item_variant": "item",
                "price": 10,
                "quantity": 1
              },
              {
                "index": 20,
                "item_id": "SKU20",
                "item_name": "Product 20",
                "item_variant": "item",
                "price": 10,
                "quantity": 1
              },
              {
                "index": 21,
                "item_id": "SKU21",
                "item_name": "Product 21",
                "item_variant": "item",
                "price": 10,
                "quantity": 1
              },
              {
                "index": 22,
                "item_id": "SKU22",
                "item_name": "Product 22",
                "item_variant": "item",
                "price": 10,
                "quantity": 1
              },
              {
                "index": 23,
                "item_id": "SKU23",
                "item_name": "Product 23",
                "item_variant": "item",
                "price": 10,
                "quantity": 1
              },
              {
                "index": 24,
                "item_id": "SKU24",
                "item_name": "Product 24",
                "item_variant": "item",
                "price": 10,
                "quantity": 1
              },
              {
                "index": 25,
                "item_id": "SKU25",
                "item_name": "Product 25",
                "item_variant": "item",
                "price": 10,
                "quantity": 1
              },
              {
                "index": 26,
                "item_id": "SKU26",
                "item_name": "Product 26",
                "item_variant": "item",
                "price": 10,
                "quantity": 1
              },
              {
                "index": 27,
                "item_id": "SKU27",
                "item_name": "Product 27",
                "item_variant": "item",
                "price": 10,
                "quantity": 1
              },
              {
                "index": 28,
                "item_id": "SKU28",
                "item_name": "Product 28",
                "item_variant": "item",
                "price": 10,
                "quantity": 1
              },
              {
                "index": 29,
                "item_id": "SKU29",
                "item_name": "Product 29",
                "item_variant": "item",
                "price": 10,
                "quantity": 1
              },
              {
                "index": 30,
                "item_id": "SKU30",
                "item_name": "Product 30",
                "item_variant": "item",
                "price": 10,
                "quantity": 1
              }
            ]
          }
        }
      """

    And GTM data layer must contain the following message:
      """
        {
          "event": "add_shipping_info",
          "ecommerce": {
            "shipping_tier": "Flat Rate",
            "items": [
              {
                "index": 31,
                "item_id": "SKU31",
                "item_name": "Product 31",
                "item_variant": "item",
                "price": 10,
                "quantity": 1
              },
              {
                "index": 32,
                "item_id": "SKU32",
                "item_name": "Product 32",
                "item_variant": "item",
                "price": 10,
                "quantity": 1
              },
              {
                "index": 33,
                "item_id": "SKU33",
                "item_name": "Product 33",
                "item_variant": "item",
                "price": 10,
                "quantity": 1
              },
              {
                "index": 34,
                "item_id": "SKU34",
                "item_name": "Product 34",
                "item_variant": "item",
                "price": 10,
                "quantity": 1
              },
              {
                "index": 35,
                "item_id": "SKU35",
                "item_name": "Product 35",
                "item_variant": "item",
                "price": 10,
                "quantity": 1
              }
            ]
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

    And GTM data layer must contain the following message:
      """
        {
          "event": "add_payment_info",
          "ecommerce": {
            "currency": "USD",
            "value": 350,
            "payment_type": "Payment Term",
            "items": [
              {
                "index": 1,
                "item_id": "SKU1",
                "item_name": "Product 1",
                "item_variant": "item",
                "price": 10,
                "quantity": 1
              },
              {
                "index": 2,
                "item_id": "SKU2",
                "item_name": "Product 2",
                "item_variant": "item",
                "price": 10,
                "quantity": 1
              },
              {
                "index": 3,
                "item_id": "SKU3",
                "item_name": "Product 3",
                "item_variant": "item",
                "price": 10,
                "quantity": 1
              },
              {
                "index": 4,
                "item_id": "SKU4",
                "item_name": "Product 4",
                "item_variant": "item",
                "price": 10,
                "quantity": 1
              },
              {
                "index": 5,
                "item_id": "SKU5",
                "item_name": "Product 5",
                "item_variant": "item",
                "price": 10,
                "quantity": 1
              },
              {
                "index": 6,
                "item_id": "SKU6",
                "item_name": "Product 6",
                "item_variant": "item",
                "price": 10,
                "quantity": 1
              },
              {
                "index": 7,
                "item_id": "SKU7",
                "item_name": "Product 7",
                "item_variant": "item",
                "price": 10,
                "quantity": 1
              },
              {
                "index": 8,
                "item_id": "SKU8",
                "item_name": "Product 8",
                "item_variant": "item",
                "price": 10,
                "quantity": 1
              },
              {
                "index": 9,
                "item_id": "SKU9",
                "item_name": "Product 9",
                "item_variant": "item",
                "price": 10,
                "quantity": 1
              },
              {
                "index": 10,
                "item_id": "SKU10",
                "item_name": "Product 10",
                "item_variant": "item",
                "price": 10,
                "quantity": 1
              },
              {
                "index": 11,
                "item_id": "SKU11",
                "item_name": "Product 11",
                "item_variant": "item",
                "price": 10,
                "quantity": 1
              },
              {
                "index": 12,
                "item_id": "SKU12",
                "item_name": "Product 12",
                "item_variant": "item",
                "price": 10,
                "quantity": 1
              },
              {
                "index": 13,
                "item_id": "SKU13",
                "item_name": "Product 13",
                "item_variant": "item",
                "price": 10,
                "quantity": 1
              },
              {
                "index": 14,
                "item_id": "SKU14",
                "item_name": "Product 14",
                "item_variant": "item",
                "price": 10,
                "quantity": 1
              },
              {
                "index": 15,
                "item_id": "SKU15",
                "item_name": "Product 15",
                "item_variant": "item",
                "price": 10,
                "quantity": 1
              },
              {
                "index": 16,
                "item_id": "SKU16",
                "item_name": "Product 16",
                "item_variant": "item",
                "price": 10,
                "quantity": 1
              },
              {
                "index": 17,
                "item_id": "SKU17",
                "item_name": "Product 17",
                "item_variant": "item",
                "price": 10,
                "quantity": 1
              },
              {
                "index": 18,
                "item_id": "SKU18",
                "item_name": "Product 18",
                "item_variant": "item",
                "price": 10,
                "quantity": 1
              },
              {
                "index": 19,
                "item_id": "SKU19",
                "item_name": "Product 19",
                "item_variant": "item",
                "price": 10,
                "quantity": 1
              },
              {
                "index": 20,
                "item_id": "SKU20",
                "item_name": "Product 20",
                "item_variant": "item",
                "price": 10,
                "quantity": 1
              },
              {
                "index": 21,
                "item_id": "SKU21",
                "item_name": "Product 21",
                "item_variant": "item",
                "price": 10,
                "quantity": 1
              },
              {
                "index": 22,
                "item_id": "SKU22",
                "item_name": "Product 22",
                "item_variant": "item",
                "price": 10,
                "quantity": 1
              },
              {
                "index": 23,
                "item_id": "SKU23",
                "item_name": "Product 23",
                "item_variant": "item",
                "price": 10,
                "quantity": 1
              },
              {
                "index": 24,
                "item_id": "SKU24",
                "item_name": "Product 24",
                "item_variant": "item",
                "price": 10,
                "quantity": 1
              },
              {
                "index": 25,
                "item_id": "SKU25",
                "item_name": "Product 25",
                "item_variant": "item",
                "price": 10,
                "quantity": 1
              },
              {
                "index": 26,
                "item_id": "SKU26",
                "item_name": "Product 26",
                "item_variant": "item",
                "price": 10,
                "quantity": 1
              },
              {
                "index": 27,
                "item_id": "SKU27",
                "item_name": "Product 27",
                "item_variant": "item",
                "price": 10,
                "quantity": 1
              },
              {
                "index": 28,
                "item_id": "SKU28",
                "item_name": "Product 28",
                "item_variant": "item",
                "price": 10,
                "quantity": 1
              },
              {
                "index": 29,
                "item_id": "SKU29",
                "item_name": "Product 29",
                "item_variant": "item",
                "price": 10,
                "quantity": 1
              },
              {
                "index": 30,
                "item_id": "SKU30",
                "item_name": "Product 30",
                "item_variant": "item",
                "price": 10,
                "quantity": 1
              }
            ]
          }
        }
      """

    And GTM data layer must contain the following message:
      """
        {
          "event": "add_payment_info",
          "ecommerce": {
            "payment_type": "Payment Term",
            "items": [
              {
                "index": 31,
                "item_id": "SKU31",
                "item_name": "Product 31",
                "item_variant": "item",
                "price": 10,
                "quantity": 1
              },
              {
                "index": 32,
                "item_id": "SKU32",
                "item_name": "Product 32",
                "item_variant": "item",
                "price": 10,
                "quantity": 1
              },
              {
                "index": 33,
                "item_id": "SKU33",
                "item_name": "Product 33",
                "item_variant": "item",
                "price": 10,
                "quantity": 1
              },
              {
                "index": 34,
                "item_id": "SKU34",
                "item_name": "Product 34",
                "item_variant": "item",
                "price": 10,
                "quantity": 1
              },
              {
                "index": 35,
                "item_id": "SKU35",
                "item_name": "Product 35",
                "item_variant": "item",
                "price": 10,
                "quantity": 1
              }
            ]
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

  Scenario: Events on successful checkout
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

    And GTM data layer must contain the following message:
      """
        {
          "event": "purchase",
          "ecommerce": {
            "affiliation": "Default",
            "currency": "USD",
            "items": [
              {
                "index": 1,
                "item_id": "SKU1",
                "item_name": "Product 1",
                "item_variant": "item",
                "price": 10,
                "quantity": 1
              },
              {
                "index": 2,
                "item_id": "SKU2",
                "item_name": "Product 2",
                "item_variant": "item",
                "price": 10,
                "quantity": 1
              },
              {
                "index": 3,
                "item_id": "SKU3",
                "item_name": "Product 3",
                "item_variant": "item",
                "price": 10,
                "quantity": 1
              },
              {
                "index": 4,
                "item_id": "SKU4",
                "item_name": "Product 4",
                "item_variant": "item",
                "price": 10,
                "quantity": 1
              },
              {
                "index": 5,
                "item_id": "SKU5",
                "item_name": "Product 5",
                "item_variant": "item",
                "price": 10,
                "quantity": 1
              },
              {
                "index": 6,
                "item_id": "SKU6",
                "item_name": "Product 6",
                "item_variant": "item",
                "price": 10,
                "quantity": 1
              },
              {
                "index": 7,
                "item_id": "SKU7",
                "item_name": "Product 7",
                "item_variant": "item",
                "price": 10,
                "quantity": 1
              },
              {
                "index": 8,
                "item_id": "SKU8",
                "item_name": "Product 8",
                "item_variant": "item",
                "price": 10,
                "quantity": 1
              },
              {
                "index": 9,
                "item_id": "SKU9",
                "item_name": "Product 9",
                "item_variant": "item",
                "price": 10,
                "quantity": 1
              },
              {
                "index": 10,
                "item_id": "SKU10",
                "item_name": "Product 10",
                "item_variant": "item",
                "price": 10,
                "quantity": 1
              },
              {
                "index": 11,
                "item_id": "SKU11",
                "item_name": "Product 11",
                "item_variant": "item",
                "price": 10,
                "quantity": 1
              },
              {
                "index": 12,
                "item_id": "SKU12",
                "item_name": "Product 12",
                "item_variant": "item",
                "price": 10,
                "quantity": 1
              },
              {
                "index": 13,
                "item_id": "SKU13",
                "item_name": "Product 13",
                "item_variant": "item",
                "price": 10,
                "quantity": 1
              },
              {
                "index": 14,
                "item_id": "SKU14",
                "item_name": "Product 14",
                "item_variant": "item",
                "price": 10,
                "quantity": 1
              },
              {
                "index": 15,
                "item_id": "SKU15",
                "item_name": "Product 15",
                "item_variant": "item",
                "price": 10,
                "quantity": 1
              },
              {
                "index": 16,
                "item_id": "SKU16",
                "item_name": "Product 16",
                "item_variant": "item",
                "price": 10,
                "quantity": 1
              },
              {
                "index": 17,
                "item_id": "SKU17",
                "item_name": "Product 17",
                "item_variant": "item",
                "price": 10,
                "quantity": 1
              },
              {
                "index": 18,
                "item_id": "SKU18",
                "item_name": "Product 18",
                "item_variant": "item",
                "price": 10,
                "quantity": 1
              },
              {
                "index": 19,
                "item_id": "SKU19",
                "item_name": "Product 19",
                "item_variant": "item",
                "price": 10,
                "quantity": 1
              },
              {
                "index": 20,
                "item_id": "SKU20",
                "item_name": "Product 20",
                "item_variant": "item",
                "price": 10,
                "quantity": 1
              },
              {
                "index": 21,
                "item_id": "SKU21",
                "item_name": "Product 21",
                "item_variant": "item",
                "price": 10,
                "quantity": 1
              },
              {
                "index": 22,
                "item_id": "SKU22",
                "item_name": "Product 22",
                "item_variant": "item",
                "price": 10,
                "quantity": 1
              },
              {
                "index": 23,
                "item_id": "SKU23",
                "item_name": "Product 23",
                "item_variant": "item",
                "price": 10,
                "quantity": 1
              },
              {
                "index": 24,
                "item_id": "SKU24",
                "item_name": "Product 24",
                "item_variant": "item",
                "price": 10,
                "quantity": 1
              },
              {
                "index": 25,
                "item_id": "SKU25",
                "item_name": "Product 25",
                "item_variant": "item",
                "price": 10,
                "quantity": 1
              },
              {
                "index": 26,
                "item_id": "SKU26",
                "item_name": "Product 26",
                "item_variant": "item",
                "price": 10,
                "quantity": 1
              },
              {
                "index": 27,
                "item_id": "SKU27",
                "item_name": "Product 27",
                "item_variant": "item",
                "price": 10,
                "quantity": 1
              },
              {
                "index": 28,
                "item_id": "SKU28",
                "item_name": "Product 28",
                "item_variant": "item",
                "price": 10,
                "quantity": 1
              },
              {
                "index": 29,
                "item_id": "SKU29",
                "item_name": "Product 29",
                "item_variant": "item",
                "price": 10,
                "quantity": 1
              },
              {
                "index": 30,
                "item_id": "SKU30",
                "item_name": "Product 30",
                "item_variant": "item",
                "price": 10,
                "quantity": 1
              }
            ],
            "payment_type": "Payment Term",
            "shipping": 3,
            "shipping_tier": "Flat Rate",
            "tax": 0,
            "transaction_id": "1",
            "value": 353
          }
        }
      """

    And GTM data layer must contain the following message:
      """
        {
          "event": "purchase",
          "ecommerce": {
            "items": [
              {
                "index": 31,
                "item_id": "SKU31",
                "item_name": "Product 31",
                "item_variant": "item",
                "price": 10,
                "quantity": 1
              },
              {
                "index": 32,
                "item_id": "SKU32",
                "item_name": "Product 32",
                "item_variant": "item",
                "price": 10,
                "quantity": 1
              },
              {
                "index": 33,
                "item_id": "SKU33",
                "item_name": "Product 33",
                "item_variant": "item",
                "price": 10,
                "quantity": 1
              },
              {
                "index": 34,
                "item_id": "SKU34",
                "item_name": "Product 34",
                "item_variant": "item",
                "price": 10,
                "quantity": 1
              },
              {
                "index": 35,
                "item_id": "SKU35",
                "item_name": "Product 35",
                "item_variant": "item",
                "price": 10,
                "quantity": 1
              }
            ],
            "payment_type": "Payment Term",
            "shipping_tier": "Flat Rate",
            "transaction_id": "1"
          }
        }
      """
