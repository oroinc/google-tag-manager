@regression
@feature-BB-21298
@fixture-OroGoogleTagManagerBundle:integration.yml
@fixture-OroGoogleTagManagerBundle:featured_products.yml
@fixture-OroGoogleTagManagerBundle:new_arrivals.yml
@fixture-OroGoogleTagManagerBundle:home_page_slider_content_widget_fixture.yml

Feature: GTM events on homepage

  Scenario: Feature background
    Given I enable GTM integration
    And I login as administrator

    And I enable GTM integration
    And I add Home Page Slider widget before content for "Homepage" page
    And I add New Arrivals widget after content for "Homepage" page
    And I add Featured Products widget after content for "Homepage" page

    # Change Featured Products order
    And I go to Reports & Segments / Manage Segments
    And I click edit "Featured Products" in grid
    And I click "Edit First Segment Column"
    And I fill "Segment Form" with:
      | Sorting | Desc |
    And I click "Save Column Button"
    And I click "Edit Second Segment Column"
    And I fill "Segment Form" with:
      | Sorting | None |
    And I click "Save Column Button"
    And I save and close form

    # Change New Arrivals order
    And I go to Reports & Segments / Manage Segments
    And I click edit "New Arrivals" in grid
    And I click "Edit First Segment Column"
    And I fill "Segment Form" with:
      | Sorting | Desc |
    And I click "Save Column Button"
    And I click "Edit Second Segment Column"
    And I fill "Segment Form" with:
      | Sorting | None |
    And I click "Save Column Button"
    And I save and close form
    And I set configuration property "oro_product.new_arrivals_max_items" to "6"

  Scenario: Check server events
    When I go to homepage
    Then GTM data layer must contain the following message:
      """
        {
          "visitorType": "Visitor"
        }
      """
    And GTM data layer must contain the following message:
      """
        {
            "pageCategory":"home",
            "localizationId": "1"
        }
      """
  Scenario: Check promo events on homepage slider
    Given I reload the page
    And do not change page on link click
    When I click "Third Dot On Home Page Slider"
    Then last message in the GTM data layer should be:
      """
        {
          "event": "view_promotion",
          "ecommerce": {
            "items": [{"creative_name": "home-page-slider", "item_name": "Best-Priced Medical Supplies", "index": 2}]
          }
        }
      """
    When I click "First Dot On Home Page Slider"
    Then last message in the GTM data layer should be:
      """
        {
          "event": "view_promotion",
          "ecommerce": {
            "items": [{"creative_name": "home-page-slider", "item_name": "Seasonal Sale", "index": 0}]
          }
        }
      """
    When I click "First Image Slide"
    And I wait 1 second
    Then GTM data layer must contain the following message:
      """
        {
          "event": "select_promotion",
          "eventCallback": {},
          "ecommerce": {
            "items": [{"creative_name": "home-page-slider", "item_name": "Seasonal Sale", "index": 0}]
          }
        }
      """

  Scenario: Check product events on featured products
    When I reload the page
    And do not change page on link click
    And I scroll to "Featured Products Next Button"
    Then I should see the following products in the "Featured Products Block":
      | SKU       | Product Price Your | Product Price Listed |
      | FEATURED6 | $9.333 / item      | $9.333 / item        |
      | FEATURED5 | $7.7775 / item     | $7.7775 / item       |
      | FEATURED4 | $6.222 / item      | $6.222 / item        |
      | FEATURED3 | $4.6665 / item     | $4.6665 / item       |
      | FEATURED2 | $3.111 / item      | $3.111 / item        |
    And GTM data layer must contain the following message:
      """
        {
          "ecommerce": {
            "currency": "USD",
            "items": [
              {
                "index": 0,
                "item_id": "FEATURED6",
                "item_list_name": "featured-products",
                "item_name": "Featured Product 6",
                "price": 9.333
              },
              {
                "index": 1,
                "item_id": "FEATURED5",
                "item_list_name": "featured-products",
                "item_name": "Featured Product 5",
                "price": 7.7775
              },
              {
                "index": 2,
                "item_id": "FEATURED4",
                "item_list_name": "featured-products",
                "item_name": "Featured Product 4",
                "price": 6.222
              },
              {
                "index": 3,
                "item_id": "FEATURED3",
                "item_list_name": "featured-products",
                "item_name": "Featured Product 3",
                "price": 4.6665
              },
              {
                "index": 4,
                "item_id": "FEATURED2",
                "item_list_name": "featured-products",
                "item_name": "Featured Product 2",
                "price": 3.111
              }
            ]
          },
          "event": "view_item_list"
        }
      """

    When I click "Featured Products Next Button"
    Then last message in the GTM data layer should be:
      """
        {
          "event": "view_item_list",
          "ecommerce": {
            "currency": "USD",
            "items": [
              {
                "item_id": "FEATURED1",
                "item_name": "Featured Product 1",
                "item_list_name": "featured-products",
                "index": 5,
                "price": 1.5555
              }
            ]
          }
        }
      """
    And I should see the following products in the "Featured Products Block":
      | SKU       | Product Price Your | Product Price Listed |
      | FEATURED5 | $7.7775 / item     | $7.7775 / item       |
      | FEATURED4 | $6.222 / item      | $6.222 / item        |
      | FEATURED3 | $4.6665 / item     | $4.6665 / item       |
      | FEATURED2 | $3.111 / item      | $3.111 / item        |
      | FEATURED1 | $1.5555 / item     | $1.5555 / item       |

    When I click "Featured Product 1"
    Then last message in the GTM data layer should be:
      """
        {
          "event": "select_item",
          "eventCallback": {},
          "ecommerce": {
            "item_list_name": "featured-products",
            "items": [
              {
                "item_id": "FEATURED1",
                "item_name": "Featured Product 1",
                "index": 5,
                "currency": "USD",
                "price": 1.5555
              }
            ]
          }
        }
      """

  Scenario: Check product events on new arrivals
    When I reload the page
    And do not change page on link click
    And I scroll to "New Arrivals Next Button"

    Then I should see the following products in the "New Arrivals Block":
      | SKU         | Product Price Your | Product Price Listed |
      | NEWARRIVAL6 | $10.0734 / item    | $10.0734 / item      |
      | NEWARRIVAL5 | $8.3945 / item     | $8.3945 / item       |
      | NEWARRIVAL4 | $6.7156 / item     | $6.7156 / item       |
      | NEWARRIVAL3 | $5.0367 / item     | $5.0367 / item       |
      | NEWARRIVAL2 | $3.3578 / item     | $3.3578 / item       |
    And GTM data layer must contain the following message:
      """
        {
          "ecommerce": {
            "currency": "USD",
            "items": [
              {
                "index": 0,
                "item_id": "NEWARRIVAL6",
                "item_list_name": "new-arrivals",
                "item_name": "New Arrival Product 6",
                "price": 10.0734
              },
              {
                "index": 1,
                "item_id": "NEWARRIVAL5",
                "item_list_name": "new-arrivals",
                "item_name": "New Arrival Product 5",
                "price": 8.3945
              },
              {
                "index": 2,
                "item_id": "NEWARRIVAL4",
                "item_list_name": "new-arrivals",
                "item_name": "New Arrival Product 4",
                "price": 6.7156
              },
              {
                "index": 3,
                "item_id": "NEWARRIVAL3",
                "item_list_name": "new-arrivals",
                "item_name": "New Arrival Product 3",
                "price": 5.0367
              },
              {
                "index": 4,
                "item_id": "NEWARRIVAL2",
                "item_list_name": "new-arrivals",
                "item_name": "New Arrival Product 2",
                "price": 3.3578
              }
            ]
          },
          "event": "view_item_list"
        }
      """

    When I click "New Arrivals Next Button"
    Then last message in the GTM data layer should be:
      """
        {
          "event": "view_item_list",
          "ecommerce": {
            "currency": "USD",
            "items": [
              {
                "item_id": "NEWARRIVAL1",
                "item_name": "New Arrival Product 1",
                "item_list_name": "new-arrivals",
                "index": 5,
                "price": 1.6789
              }
            ]
          }
        }
      """
    And I should see the following products in the "New Arrivals Block":
      | SKU         | Product Price Your | Product Price Listed |
      | NEWARRIVAL4 | $6.7156 / item     | $6.7156 / item       |
      | NEWARRIVAL3 | $5.0367 / item     | $5.0367 / item       |
      | NEWARRIVAL2 | $3.3578 / item     | $3.3578 / item       |
      | NEWARRIVAL1 | $1.6789 / item     | $1.6789 / item       |

    When I click "New Arrival Product 1"
    Then last message in the GTM data layer should be:
      """
        {
          "event": "select_item",
          "eventCallback": {},
          "ecommerce": {
            "item_list_name": "new-arrivals",
            "items": [
              {
                "item_id": "NEWARRIVAL1",
                "item_name": "New Arrival Product 1",
                "index": 5,
                "currency": "USD",
                "price": 1.6789
              }
            ]
          }
        }
      """
