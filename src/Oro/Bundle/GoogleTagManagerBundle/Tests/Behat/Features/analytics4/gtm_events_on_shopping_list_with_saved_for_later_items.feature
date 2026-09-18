@regression
@ticket-BB-27880
@fixture-OroGoogleTagManagerBundle:integration.yml
@fixture-OroGoogleTagManagerBundle:products.yml

Feature: GTM events on shopping list with saved for later items

  Scenario: Feature background
    Given I enable GTM integration
    And I set configuration property "oro_shopping_list.saved_for_later_enabled" to "1"
    And I signed in as AmandaRCole@example.org on the store frontend

  Scenario: Add product to shopping list
    Given I type "SKU1" in "search"
    And I click "Search Button"
    When I click on "Add to Shopping List"
    And I should see "Product has been added to" flash message and I close it
    Then last message in the GTM data layer should be:
      """
        {
          "event": "add_to_cart",
          "ecommerce": {
            "currency": "USD",
            "value": 10.46,
            "items": [
              {
                "item_id": "SKU1",
                "item_name": "Product 1",
                "item_category": "NewCategory",
                "item_variant": "item",
                "quantity": 1,
                "price": 10.4555
              }
            ]
          }
        }
      """

  Scenario: Save product for later
    When I open page with shopping list Shopping List
    And I click "Save For Later" on row "SKU1" in grid "Frontend Shopping List Edit Grid"
    Then I should see "Are you sure you want to save this product for later?"
    When I click "Yes, Save" in confirmation dialogue
    Then I should see no records in "Frontend Shopping List Edit Grid" table

  Scenario: Delete shopping list that is empty but still holds a saved for later item
    When I click "Shopping List Actions"
    And I click "Delete"
    And I click "Yes, delete" in modal window
    And I should see "Shopping List deleted" flash message and I close it
    Then GTM data layer must contain the following message:
      """
        {
          "event": "remove_from_cart",
          "ecommerce": {
            "currency": "USD",
            "value": 10.46,
            "items": [
              {
                "item_id": "SKU1",
                "item_name": "Product 1",
                "item_category": "NewCategory",
                "item_variant": "item",
                "quantity": 1,
                "price": 10.4555
              }
            ]
          }
        }
      """
