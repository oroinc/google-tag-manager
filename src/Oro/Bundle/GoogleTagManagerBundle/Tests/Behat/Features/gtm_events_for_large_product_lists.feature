@regression
@random-failed
@fixture-OroFlatRateShippingBundle:FlatRateIntegration.yml
@fixture-OroPaymentTermBundle:PaymentTermIntegration.yml
@fixture-OroGoogleTagManagerBundle:integration.yml
@fixture-OroGoogleTagManagerBundle:many_products.yml
Feature: GTM events for large product lists
  In order to ...
  As an ...
  I should be able to ...

  Scenario: Feature background
    Given I enable GTM integration
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
    And GTM data layer must contain addToCart events with 35 products

  Scenario: Batch events on request for quote
    When I open page with shopping list Shopping List
    And I click "More Actions"
    And I click "Request Quote"
    And I click "Submit Request"
    Then GTM data layer must contain addToCart events with 35 products

  Scenario: Batch events on delete shopping list
    When I open page with shopping list Shopping List
    And I click "Shopping List Actions"
    And I click "Delete"
    And I click "Yes, delete"
    Then GTM data layer must contain removeFromCart events with 35 products

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
    Then GTM data layer must contain addToCart events with 35 products

  Scenario: Batch events on checkout
    When I open page with shopping list Shopping List
    And I click "Create Order"
    Then GTM data layer must contain checkout events for step "enter_billing_address" with 35 products

    When I click "Continue"
    Then GTM data layer must contain checkout events for step "enter_shipping_address" with 35 products

    When I click "Continue"
    Then GTM data layer must contain checkout events for step "enter_shipping_method" with 35 products

    When I click "Continue"
    Then GTM data layer must contain checkout events for step "enter_payment" with 35 products

    When I click "Continue"
    Then GTM data layer must contain checkout events for step "order_review" with 35 products

    When I click "Submit Order"
    Then GTM data layer must contain purchase events with 35 products
