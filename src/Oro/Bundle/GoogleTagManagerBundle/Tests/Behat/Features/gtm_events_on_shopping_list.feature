@regression
@random-failed
@fixture-OroGoogleTagManagerBundle:integration.yml
@fixture-OroGoogleTagManagerBundle:products.yml
Feature: GTM events on shopping list

  Scenario: Feature background
    Given I enable GTM integration
    And I signed in as AmandaRCole@example.org on the store frontend

  Scenario: Add product to shopping list
    Given I type "SKU1" in "search"
    And I click "Search Button"
    And I click on "Add to Shopping List"
    And I should see "Product has been added to" flash message
    Then last message in the GTM data layer should be:
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
                  "category": "All Products / NewCategory",
                  "variant": "item",
                  "quantity": 1,
                  "price": 10
                }
              ]
            }
          }
        }
      """

  Scenario: Remove product from shopping list in products lists
    When I click on "Shopping List Dropdown"
    And I click "Remove From Shopping List"
    Then last message in the GTM data layer should be:
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
                  "category": "All Products / NewCategory",
                  "variant": "item",
                  "quantity": 1,
                  "price": 10
                }
              ]
            }
          }
        }
      """
    And I click on "Add to Shopping List"

  Scenario: Update product quantity in shopping list
    When I type "3" in "Product Quantity"
    And I click "Update Shopping List"
    And I should see "Record has been successfully updated" flash message
    Then last message in the GTM data layer should be:
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
                  "category": "All Products / NewCategory",
                  "variant": "item",
                  "quantity": 2,
                  "price": 10
                }
              ]
            }
          }
        }
      """

  Scenario: Remove part of products from shopping list
    When I type "2" in "Product Quantity"
    And I click "Update Shopping List"
    And I should see "Record has been successfully updated" flash message
    Then last message in the GTM data layer should be:
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
                  "category": "All Products / NewCategory",
                  "variant": "item",
                  "quantity": 1,
                  "price": 10
                }
              ]
            }
          }
        }
      """

  Scenario: Change product unit
    When type "SKU2" in "search"
    And I click "Search Button"
    And I click on "Add to Shopping List"
    And I should see "Product has been added to" flash message
    Then last message in the GTM data layer should be:
      """
        {
          "event": "addToCart",
          "ecommerce": {
            "currencyCode": "USD",
            "add": {
              "products": [
                {
                  "id": "SKU2",
                  "name": "Product 2",
                  "category": "All Products / NewCategory",
                  "variant": "item",
                  "quantity": 1,
                  "price": 15
                }
              ]
            }
          }
        }
      """
    When I open page with shopping list Shopping List
    And I fill "Shopping List Line Item 2 Form" with:
      | Unit | set |
    Then GTM data layer must contain the following message:
      """
        {
          "event": "addToCart",
          "ecommerce": {
            "currencyCode": "USD",
            "add": {
              "products": [
                {
                  "id": "SKU2",
                  "name": "Product 2",
                  "category": "All Products / NewCategory",
                  "variant": "set",
                  "quantity": 1,
                  "price": 50
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
                  "id": "SKU2",
                  "name": "Product 2",
                  "category": "All Products / NewCategory",
                  "variant": "item",
                  "quantity": 1,
                  "price": 15
                }
              ]
            }
          }
        }
      """

  Scenario: Change notes
    Given I click "Add a Note to This Item"
    And I fill in "Shopping List Product Note" with "My notes"
    When I click on empty space
    And I should see "Record has been successfully updated" flash message
    Then last message in the GTM data layer should be:
      """
        {
          "event": "removeFromCart",
          "ecommerce": {
            "currencyCode": "USD",
            "remove": {
              "products": [
                {
                  "id": "SKU2",
                  "name": "Product 2",
                  "category": "All Products / NewCategory",
                  "variant": "item",
                  "quantity": 1,
                  "price": 15
                }
              ]
            }
          }
        }
      """

  Scenario: Clone shopping list
    When I click "Duplicate List"
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
                  "category": "All Products / NewCategory",
                  "variant": "item",
                  "quantity": 2,
                  "price": 10
                },
                {
                  "id": "SKU2",
                  "name": "Product 2",
                  "category": "All Products / NewCategory",
                  "variant": "set",
                  "quantity": 1,
                  "price": 50
                }
              ]
            }
          }
        }
      """

  Scenario: Remove item in shopping list
    When I delete line item 1 in "Shopping List Line Items Table"
    And I click "Yes, Delete"
    Then last message in the GTM data layer should be:
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
                  "category": "All Products / NewCategory",
                  "variant": "item",
                  "quantity": 2,
                  "price": 10
                }
              ]
            }
          }
        }
      """

  Scenario: Remove shopping list
    When I open page with shopping list Shopping List
    And I click "Delete"
    And I click "Yes, Delete"
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
                  "category": "All Products / NewCategory",
                  "variant": "item",
                  "quantity": 2,
                  "price": 10
                },
                {
                  "id": "SKU2",
                  "name": "Product 2",
                  "category": "All Products / NewCategory",
                  "variant": "set",
                  "quantity": 1,
                  "price": 50
                }
              ]
            }
          }
        }
      """
