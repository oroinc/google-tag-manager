@fixture-OroGoogleTagManagerBundle:integration.yml
@fixture-OroGoogleTagManagerBundle:featured_products.yml
@fixture-OroGoogleTagManagerBundle:new_arrivals.yml
Feature: GTM events on homepage

  Scenario: Feature background
    Given I enable GTM integration
    And I login as administrator

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
    And I set configuration property "oro_product.new_arrivals_max_items" to "5"

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
          "event": "promotionImpression",
          "ecommerce": {
            "promoView": {"promotions": [{"name": "Lorem ipsum", "creative": "home-page-slider", "position": 2}]}
          }
        }
      """

    When I click "First Dot On Home Page Slider"
    Then last message in the GTM data layer should be:
      """
        {
          "event": "promotionImpression",
          "ecommerce": {
            "promoView": {"promotions": [{"name": "Lorem ipsum", "creative": "home-page-slider", "position": 0}]}
          }
        }
      """

    When I click on "Call To Action On First Slide"
    Then last message in the GTM data layer should be:
      """
        {
          "event": "promotionClick",
          "eventCallback": [],
          "ecommerce": {
            "promoClick": {"promotions": [{"name": "Lorem ipsum", "creative": "home-page-slider", "position": 0}]}
          }
        }
      """

  Scenario: Check product events on featured products
    When I reload the page
    And do not change page on link click
    And I scroll to "Featured Products Next Button"
    Then GTM data layer must contain the following message:
      """
        {
          "event": "productImpression",
          "ecommerce": {
            "currencyCode": "USD",
            "impressions": [
              {
                "id": "FEATURED-5",
                "name": "Featured Product 5",
                "list": "featured-products",
                "position": 0
              },
              {
                "id": "FEATURED-4",
                "name": "Featured Product 4",
                "list": "featured-products",
                "position": 1
              },
              {
                "id": "FEATURED-3",
                "name": "Featured Product 3",
                "list": "featured-products",
                "position": 2
              },
              {
                "id": "FEATURED-2",
                "name": "Featured Product 2",
                "list": "featured-products",
                "position": 3
              }
            ]
          }
        }
      """

    When I click "Featured Products Next Button"
    Then last message in the GTM data layer should be:
      """
        {
          "event": "productImpression",
          "ecommerce": {
            "currencyCode": "USD",
            "impressions": [
              {
                "id": "FEATURED-1",
                "name": "Featured Product 1",
                "list": "featured-products",
                "position": 4
              }
            ]
          }
        }
      """

    When I click "Featured Product 1"
    Then last message in the GTM data layer should be:
      """
        {
          "event": "productClick",
          "eventCallback": [],
          "ecommerce": {
              "click": {
                  "actionField": {
                      "list": "featured-products"
                  },
                  "products": [
                      {
                          "id": "FEATURED-1",
                          "name": "Featured Product 1",
                          "position": 4
                      }
                  ]
              }
          }
        }
      """

  Scenario: Check product events on new arrivals
    When I reload the page
    And do not change page on link click
    And I scroll to "New Arrivals Next Button"
    Then GTM data layer must contain the following message:
      """
        {
          "event": "productImpression",
          "ecommerce": {
            "currencyCode": "USD",
            "impressions": [
              {
                "id": "NEW-ARRIVAL-5",
                "name": "New Arrival Product 5",
                "list": "new-arrivals",
                "position": 0
              },
              {
                "id": "NEW-ARRIVAL-4",
                "name": "New Arrival Product 4",
                "list": "new-arrivals",
                "position": 1
              },
              {
                "id": "NEW-ARRIVAL-3",
                "name": "New Arrival Product 3",
                "list": "new-arrivals",
                "position": 2
              },
              {
                "id": "NEW-ARRIVAL-2",
                "name": "New Arrival Product 2",
                "list": "new-arrivals",
                "position": 3
              }
            ]
          }
        }
      """

    When I click "New Arrivals Next Button"
    Then last message in the GTM data layer should be:
      """
        {
          "event": "productImpression",
          "ecommerce": {
            "currencyCode": "USD",
            "impressions": [
              {
                "id": "NEW-ARRIVAL-1",
                "name": "New Arrival Product 1",
                "list": "new-arrivals",
                "position": 4
              }
            ]
          }
        }
      """

    When I click "New Arrival Product 1"
    Then last message in the GTM data layer should be:
      """
        {
          "event": "productClick",
          "eventCallback": [],
          "ecommerce": {
              "click": {
                  "actionField": {
                      "list": "new-arrivals"
                  },
                  "products": [
                      {
                          "id": "NEW-ARRIVAL-1",
                          "name": "New Arrival Product 1",
                          "position": 4
                      }
                  ]
              }
          }
        }
      """
