@regression
@feature-BB-21298
@fixture-OroGoogleTagManagerBundle:integration.yml
@fixture-OroGoogleTagManagerBundle:products.yml

Feature: GTM events on shopping list

  Scenario: Feature background
    Given I enable GTM integration
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

  Scenario: Remove product from shopping list in products lists
    When I click on "Shopping List Dropdown"
    And I click "Remove From Shopping List"
    Then last message in the GTM data layer should be:
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
    And I click on "Add to Shopping List"

  Scenario: Update product quantity in shopping list
    When I type "3" in "Product Quantity"
    And I click "Update Shopping List"
    Then I should see 'Product has been updated in "Shopping List"' flash message and I close it
    And last message in the GTM data layer should be:
      """
        {
          "event": "add_to_cart",
          "ecommerce": {
            "currency": "USD",
            "value": 20.91,
            "items": [
              {
                "item_id": "SKU1",
                "item_name": "Product 1",
                "item_category": "NewCategory",
                "item_variant": "item",
                "quantity": 2,
                "price": 10.4555
              }
            ]
          }
        }
      """

  Scenario: Remove part of products from shopping list
    When I type "2" in "Product Quantity"
    And I click "Update Shopping List"
    Then I should see 'Product has been updated in "Shopping List"' flash message and I close it
    And last message in the GTM data layer should be:
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

  Scenario: Change product unit
    When type "SKU2" in "search"
    And I click "Search Button"
    And I click on "Add to Shopping List"
    And I should see "Product has been added to" flash message and I close it
    Then last message in the GTM data layer should be:
      """
        {
          "event": "add_to_cart",
          "ecommerce": {
            "currency": "USD",
            "value": 15.12,
            "items": [
              {
                "item_id": "SKU2",
                "item_name": "Product 2",
                "item_category": "NewCategory",
                "item_variant": "item",
                "quantity": 1,
                "price": 15.1234
              }
            ]
          }
        }
      """
    When I open page with shopping list Shopping List
    And I click on "Shopping List Line Item 2 Quantity"
    And I fill "Shopping List Line Item Form" with:
      | Quantity | 2   |
      | Unit     | set |
    And I click on "Shopping List Line Item 2 Save Changes Button"
    Then GTM data layer must contain the following message:
      """
        {
          "event": "add_to_cart",
          "ecommerce": {
            "currency": "USD",
            "value": 101.36,
            "items": [
              {
                "item_id": "SKU2",
                "item_name": "Product 2",
                "item_category": "NewCategory",
                "item_variant": "set",
                "quantity": 2,
                "price": 50.6789
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
            "currency": "USD",
            "value": 15.12,
            "items": [
              {
                "item_id": "SKU2",
                "item_name": "Product 2",
                "item_category": "NewCategory",
                "item_variant": "item",
                "quantity": 1,
                "price": 15.1234
              }
            ]
          }
        }
      """

  Scenario: Change notes
    Given I reload the page
    When I click "Add Shopping List item Note" on row "SKU2" in grid
    And I fill in "Shopping List Product Note" with "My notes"
    And I click "Add"
    Then I should see "Line item note has been successfully updated" flash message and I close it
    And reload the page
    And GTM data layer must not contain the following message:
      """
        {
          "event": "add_to_cart",
          "ecommerce": {
            "currency": "USD",
            "value": 101.36,
            "items": [
              {
                "item_id": "SKU2",
                "item_name": "Product 2",
                "item_category": "NewCategory",
                "item_variant": "set",
                "quantity": 2,
                "price": 50.6789
              }
            ]
          }
        }
      """
    And GTM data layer must not contain the following message:
      """
        {
          "event": "remove_from_cart",
          "ecommerce": {
            "currency": "USD",
            "value": 15.12,
            "items": [
              {
                "item_id": "SKU2",
                "item_name": "Product 2",
                "item_category": "NewCategory",
                "item_variant": "item",
                "quantity": 1,
                "price": 15.1234
              }
            ]
          }
        }
      """

  Scenario: Clone shopping list
    When I click "Shopping List Actions"
    And I click "Duplicate"
    And I click "Yes, duplicate"
    Then I should see "The shopping list has been duplicated" flash message and I close it
    And GTM data layer must contain the following message:
      """
        {
          "event": "add_to_cart",
          "ecommerce": {
            "currency": "USD",
            "value": 122.27,
            "items": [
              {
                "item_id": "SKU1",
                "item_name": "Product 1",
                "item_category": "NewCategory",
                "item_variant": "item",
                "quantity": 2,
                "price": 10.4555
              },
              {
                "item_id": "SKU2",
                "item_name": "Product 2",
                "item_category": "NewCategory",
                "item_variant": "set",
                "quantity": 2,
                "price": 50.6789
              }
            ]
          }
        }
      """

  Scenario: Remove item in shopping list
    When I click Delete SKU1 in grid
    And I click "Yes, Delete" in modal window
    Then last message in the GTM data layer should be:
      """
        {
          "event": "remove_from_cart",
          "ecommerce": {
            "currency": "USD",
            "value": 20.91,
            "items": [
              {
                "item_id": "SKU1",
                "item_name": "Product 1",
                "item_category": "NewCategory",
                "item_variant": "item",
                "quantity": 2,
                "price": 10.4555
              }
            ]
          }
        }
      """

  Scenario: Remove shopping list
    When I open page with shopping list Shopping List
    And I click "Shopping List Actions"
    And I click "Delete"
    And I click "Yes, delete" in modal window
    Then GTM data layer must contain the following message:
      """
        {
          "event": "remove_from_cart",
          "ecommerce": {
            "currency": "USD",
            "value": 122.27,
            "items": [
              {
                "item_id": "SKU1",
                "item_name": "Product 1",
                "item_category": "NewCategory",
                "item_variant": "item",
                "quantity": 2,
                "price": 10.4555
              },
              {
                "item_id": "SKU2",
                "item_name": "Product 2",
                "item_category": "NewCategory",
                "item_variant": "set",
                "quantity": 2,
                "price": 50.6789
              }
            ]
          }
        }
      """
