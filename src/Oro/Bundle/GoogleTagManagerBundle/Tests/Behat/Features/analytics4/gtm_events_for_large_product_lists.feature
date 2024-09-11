@regression
@feature-BB-21298
@fixture-OroFlatRateShippingBundle:FlatRateIntegration.yml
@fixture-OroPaymentTermBundle:PaymentTermIntegration.yml
@fixture-OroGoogleTagManagerBundle:integration.yml
@fixture-OroGoogleTagManagerBundle:many_products.yml

Feature: GTM events for large product lists

  Scenario: Feature background
    Given I enable GTM integration
    And I signed in as AmandaRCole@example.org on the store frontend

  Scenario: Batch events on add to shopping list from product datagrid
    When I type "" in "search"
    And I click "Search Button"
    And I select 50 records per page in "ProductFrontendGrid"
    And I scroll to top
    And I check all visible on page in "ProductFrontendGrid"
    And I wait for products to load
    And I click "Create New Shopping List" in "ProductFrontendMassPanelInBottomSticky" element
    And I click "Create and Add"
    Then I should see "35 products were added"
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
    And I click "Request Quote"
    And I click "Submit Request"
    Then GTM data layer must contain the following message:
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
    Given I click "Quick Order"
    And I click "What File Structure Is Accepted"
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

    When I click "Continue"
    And I click "Continue"
    Then GTM data layer must contain the following message:
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

    When I click "Continue"
    Then GTM data layer must contain the following message:
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

    When I click "Continue"
    And I click "Submit Order"
    Then GTM data layer must contain the following message:
      """
        {
          "event": "purchase",
          "ecommerce": {
            "affiliation": "Default",
            "currency": "USD",
            "value": 353,
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
            "transaction_id": "1"
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
