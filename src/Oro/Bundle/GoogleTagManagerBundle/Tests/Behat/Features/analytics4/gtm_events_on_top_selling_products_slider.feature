@regression
@feature-BB-21298
@fixture-OroGoogleTagManagerBundle:integration.yml
@fixture-OroGoogleTagManagerBundle:top_selling_products.yml

Feature: GTM events on top selling products slider

  Scenario: Feature background
    Given I enable GTM integration
    And I login as administrator

    And go to Marketing/Content Widgets
    And I click edit "home-page-slider" in grid
    When fill "Content Widget Form" with:
      | Enable Autoplay | false |
    And I save and close form
    Then I should see "Content widget has been saved" flash message

  Scenario: Configure Top selling products
    Given I go to Products / Products
    And I click edit "TopSelling1" in grid
    And I set Images with:
      | Main | Listing | Additional |
      | 1    | 1       | 1          |
    And I click on "Digital Asset Choose"
    And I fill "Digital Asset Dialog Form" with:
      | File  | cat1.jpg |
      | Title | cat1.jpg |
    And I click "Upload"
    And click on cat1.jpg in grid
    When I save and close form
    Then I should see "Product has been saved" flash message
    And I go to Products / Products
    And I click edit "TopSelling2" in grid
    And I set Images with:
      | Main | Listing | Additional |
      | 1    | 1       | 1          |
    And I click on "Digital Asset Choose"
    And click on cat1.jpg in grid
    When I save and close form
    Then I should see "Product has been saved" flash message
    And I go to Products / Products
    And I click edit "TopSelling3" in grid
    And I set Images with:
      | Main | Listing | Additional |
      | 1    | 1       | 1          |
    And I click on "Digital Asset Choose"
    And click on cat1.jpg in grid
    When I save and close form
    Then I should see "Product has been saved" flash message
    And I go to Products / Products
    And I click edit "TopSelling4" in grid
    And I set Images with:
      | Main | Listing | Additional |
      | 1    | 1       | 1          |
    And I click on "Digital Asset Choose"
    And click on cat1.jpg in grid
    When I save and close form
    Then I should see "Product has been saved" flash message
    And I go to Products / Products
    And I click edit "TopSelling5" in grid
    And I set Images with:
      | Main | Listing | Additional |
      | 1    | 1       | 1          |
    And I click on "Digital Asset Choose"
    And click on cat1.jpg in grid
    When I save and close form
    Then I should see "Product has been saved" flash message
    And I go to Products / Products
    And I click edit "TopSelling6" in grid
    And I set Images with:
      | Main | Listing | Additional |
      | 1    | 1       | 1          |
    And I click on "Digital Asset Choose"
    And click on cat1.jpg in grid
    When I save and close form
    Then I should see "Product has been saved" flash message

  Scenario: Check product events for top selling products
    Given I go to homepage
    And do not change page on link click
    And I scroll to "Top Selling Products Next Button"
    Then I should see the following products in the "Top Selling Items Block":
      | SKU         | Product Price Your | Product Price Listed |
      | TopSelling1 | $1.4567 / item     | $1.4567 / item       |
      | TopSelling2 | $2.9134 / item     | $2.9134 / item       |
      | TopSelling3 | $4.3701 / item     | $4.3701 / item       |
      | TopSelling4 | $5.8268 / item     | $5.8268 / item       |
    And GTM data layer must contain the following message:
      """
        {
          "ecommerce": {
            "currency": "USD",
            "items": [
              {
                "index": 0,
                "item_id": "TopSelling1",
                "item_list_name": "top-selling",
                "item_name": "Top Selling Product 1",
                "price": 1.4567
              },
              {
                "index": 1,
                "item_id": "TopSelling2",
                "item_list_name": "top-selling",
                "item_name": "Top Selling Product 2",
                "price": 2.9134
              },
              {
                "index": 2,
                "item_id": "TopSelling3",
                "item_list_name": "top-selling",
                "item_name": "Top Selling Product 3",
                "price": 4.3701
              },
              {
                "index": 3,
                "item_id": "TopSelling4",
                "item_list_name": "top-selling",
                "item_name": "Top Selling Product 4",
                "price": 5.8268
              },
              {
                "index": 4,
                "item_id": "TopSelling5",
                "item_list_name": "top-selling",
                "item_name": "Top Selling Product 5",
                "price": 7.2835
              }
            ]
          },
          "event": "view_item_list"
        }
      """

  Scenario: Check product event on top selling slider click
    Given I click "Top Selling Products Next Button"
    Then last message in the GTM data layer should be:
      """
        {
          "event": "view_item_list",
          "ecommerce": {
            "currency": "USD",
            "items": [
              {
                "item_id": "TopSelling6",
                "item_name": "Top Selling Product 6",
                "item_list_name": "top-selling",
                "index": 5,
                "price": 8.7402
              }
            ]
          }
        }
      """

    And I should see the following products in the "Top Selling Items Block":
      | SKU         | Product Price Your | Product Price Listed |
      | TopSelling2 | $2.9134 / item     | $2.9134 / item       |
      | TopSelling3 | $4.3701 / item     | $4.3701 / item       |
      | TopSelling4 | $5.8268 / item     | $5.8268 / item       |
      | TopSelling5 | $7.2835 / item     | $7.2835 / item       |
      | TopSelling6 | $8.7402 / item     | $8.7402 / item       |

  Scenario: Check event on click on top selling item in the slider
    Given I click "Top Selling Product 5"
    Then last message in the GTM data layer should be:
      """
        {
          "event": "select_item",
          "ecommerce": {
            "items": [
              {
                "item_id": "TopSelling5",
                "item_name": "Top Selling Product 5",
                "index": 4,
                "currency": "USD",
                "price": 7.2835
              }
            ],
            "item_list_name": "top-selling"
          },
          "eventCallback": {
              "cancel": []
          }
        }
      """
