@regression
@feature-BB-21298
@fixture-OroGoogleTagManagerBundle:integration.yml
@fixture-OroGoogleTagManagerBundle:featured_products.yml
@fixture-OroGoogleTagManagerBundle:new_arrivals.yml

Feature: GTM events on homepage

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

    And go to Marketing/Content Widgets
    And I click edit "home-page-slider" in grid
    And fill "Content Widget Form" with:
      | Enable Autoplay | false |
    And I save and close form
    And I should see "Content widget has been saved" flash message

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
            "pageCategory":"home",
            "localizationId": "1"
        }
      """

  Scenario: Check promo events on homepage slider
    Given I reload the page
    And do not change page on link click
    When I click "Third Dot On Home Page Slider"
    Then GTM data layer must contain the following message:
      """
        {
          "event": "promotionImpression",
          "ecommerce": {
            "promoView": {"promotions": [{"name": "Lorem ipsum", "creative": "home-page-slider", "position": 2}]}
          }
        }
      """

    And GTM data layer must contain the following message:
      """
        {
          "event": "view_promotion",
          "ecommerce": {
            "items": [{"creative_name": "home-page-slider", "item_name": "Lorem ipsum", "index": 2}]
          }
        }
      """

    When I click "First Dot On Home Page Slider"
    Then GTM data layer must contain the following message:
      """
        {
          "event": "promotionImpression",
          "ecommerce": {
            "promoView": {"promotions": [{"name": "Lorem ipsum", "creative": "home-page-slider", "position": 0}]}
          }
        }
      """

    And GTM data layer must contain the following message:
      """
        {
          "event": "view_promotion",
          "ecommerce": {
            "items": [{"creative_name": "home-page-slider", "item_name": "Lorem ipsum", "index": 0}]
          }
        }
      """

    When I click on "Call To Action On First Slide"
    Then GTM data layer must contain the following message:
      """
        {
          "event": "promotionClick",
          "eventCallback": {},
          "ecommerce": {
            "promoClick": {"promotions": [{"name": "Lorem ipsum", "creative": "home-page-slider", "position": 0}]}
          }
        }
      """

    And GTM data layer must contain the following message:
      """
        {
          "event": "select_promotion",
          "eventCallback": {},
          "ecommerce": {
            "items": [{"creative_name": "home-page-slider", "item_name": "Lorem ipsum", "index": 0}]
          }
        }
      """

  Scenario: Check product events on featured products
    When I reload the page
    And do not change page on link click
    And I scroll to "Featured Products Next Button"
    Then I should see the following products in the "Featured Products Block":
      | SKU       | Product Price Your | Product Price Listed |
      | FEATURED5 | $7.7775 / item     | $7.7775 / item       |
      | FEATURED4 | $6.222 / item      | $6.222 / item        |
      | FEATURED3 | $4.6665 / item     | $4.6665 / item       |
      | FEATURED2 | $3.111 / item      | $3.111 / item        |
    And GTM data layer must contain the following message:
      """
        {
          "event": "productImpression",
          "ecommerce": {
            "currencyCode": "USD",
            "impressions": [
              {
                "id": "FEATURED5",
                "name": "Featured Product 5",
                "list": "featured-products",
                "position": 0,
                "price": "7.7775"
              },
              {
                "id": "FEATURED4",
                "name": "Featured Product 4",
                "list": "featured-products",
                "position": 1,
                "price": "6.222"
              },
              {
                "id": "FEATURED3",
                "name": "Featured Product 3",
                "list": "featured-products",
                "position": 2,
                "price": "4.6665"
              },
              {
                "id": "FEATURED2",
                "name": "Featured Product 2",
                "list": "featured-products",
                "position": 3,
                "price": "3.111"
              }
            ]
          }
        }
      """

    And GTM data layer must contain the following message:
      """
        {
          "event": "view_item_list",
          "ecommerce": {
            "currency": "USD",
            "items": [
              {
                "item_id": "FEATURED5",
                "item_name": "Featured Product 5",
                "item_list_name": "featured-products",
                "index": 0,
                "price": 7.7775
              },
              {
                "item_id": "FEATURED4",
                "item_name": "Featured Product 4",
                "item_list_name": "featured-products",
                "index": 1,
                "price": 6.222
              },
              {
                "item_id": "FEATURED3",
                "item_name": "Featured Product 3",
                "item_list_name": "featured-products",
                "index": 2,
                "price": 4.6665
              },
              {
                "item_id": "FEATURED2",
                "item_name": "Featured Product 2",
                "item_list_name": "featured-products",
                "index": 3,
                "price": 3.111
              }
            ]
          }
        }
      """

    When I click "Featured Products Next Button"
    Then GTM data layer must contain the following message:
      """
        {
          "event": "productImpression",
          "ecommerce": {
            "currencyCode": "USD",
            "impressions": [
              {
                "id": "FEATURED1",
                "name": "Featured Product 1",
                "list": "featured-products",
                "position": 4,
                "price": "1.5555"
              }
            ]
          }
        }
      """

    And GTM data layer must contain the following message:
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
                "index": 4,
                "price": 1.5555
              }
            ]
          }
        }
      """

    And I should see the following products in the "Featured Products Block":
      | SKU       | Product Price Your | Product Price Listed |
      | FEATURED4 | $6.222 / item      | $6.222 / item        |
      | FEATURED3 | $4.6665 / item     | $4.6665 / item       |
      | FEATURED2 | $3.111 / item      | $3.111 / item        |
      | FEATURED1 | $1.5555 / item     | $1.5555 / item       |

    When I click "Featured Product 1"
    Then GTM data layer must contain the following message:
      """
        {
          "event": "productClick",
          "eventCallback": {},
          "ecommerce": {
            "currencyCode": "USD",
            "click": {
                "actionField": {
                  "list": "featured-products"
                },
                "products": [
                  {
                    "id": "FEATURED1",
                    "name": "Featured Product 1",
                    "position": 4,
                    "price": "1.5555"
                  }
                ]
            }
          }
        }
      """

    And GTM data layer must contain the following message:
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
                "index": 4,
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
      | NEWARRIVAL5 | $8.3945 / item     | $8.3945 / item       |
      | NEWARRIVAL4 | $6.7156 / item     | $6.7156 / item       |
      | NEWARRIVAL3 | $5.0367 / item     | $5.0367 / item       |
      | NEWARRIVAL2 | $3.3578 / item     | $3.3578 / item       |
    And GTM data layer must contain the following message:
      """
        {
          "event": "productImpression",
          "ecommerce": {
            "currencyCode": "USD",
            "impressions": [
              {
                "id": "NEWARRIVAL5",
                "name": "New Arrival Product 5",
                "list": "new-arrivals",
                "position": 0,
                "price": "8.3945"
              },
              {
                "id": "NEWARRIVAL4",
                "name": "New Arrival Product 4",
                "list": "new-arrivals",
                "position": 1,
                "price": "6.7156"
              },
              {
                "id": "NEWARRIVAL3",
                "name": "New Arrival Product 3",
                "list": "new-arrivals",
                "position": 2,
                "price": "5.0367"
              },
              {
                "id": "NEWARRIVAL2",
                "name": "New Arrival Product 2",
                "list": "new-arrivals",
                "position": 3,
                "price": "3.3578"
              }
            ]
          }
        }
      """

    And GTM data layer must contain the following message:
      """
        {
          "event": "view_item_list",
          "ecommerce": {
            "currency": "USD",
            "items": [
              {
                "item_id": "NEWARRIVAL5",
                "item_name": "New Arrival Product 5",
                "item_list_name": "new-arrivals",
                "index": 0,
                "price": 8.3945
              },
              {
                "item_id": "NEWARRIVAL4",
                "item_name": "New Arrival Product 4",
                "item_list_name": "new-arrivals",
                "index": 1,
                "price": 6.7156
              },
              {
                "item_id": "NEWARRIVAL3",
                "item_name": "New Arrival Product 3",
                "item_list_name": "new-arrivals",
                "index": 2,
                "price": 5.0367
              },
              {
                "item_id": "NEWARRIVAL2",
                "item_name": "New Arrival Product 2",
                "item_list_name": "new-arrivals",
                "index": 3,
                "price": 3.3578
              }
            ]
          }
        }
      """

    When I click "New Arrivals Next Button"
    Then GTM data layer must contain the following message:
      """
        {
          "event": "productImpression",
          "ecommerce": {
            "currencyCode": "USD",
            "impressions": [
              {
                "id": "NEWARRIVAL1",
                "name": "New Arrival Product 1",
                "list": "new-arrivals",
                "position": 4,
                "price": "1.6789"
              }
            ]
          }
        }
      """

    And GTM data layer must contain the following message:
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
                "index": 4,
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
    Then GTM data layer must contain the following message:
      """
        {
          "event": "productClick",
          "eventCallback": {},
          "ecommerce": {
            "currencyCode": "USD",
            "click": {
              "actionField": {
                "list": "new-arrivals"
              },
              "products": [
                {
                  "id": "NEWARRIVAL1",
                  "name": "New Arrival Product 1",
                  "position": 4,
                  "price": "1.6789"
                }
              ]
            }
          }
        }
      """

    Then GTM data layer must contain the following message:
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
                "index": 4,
                "currency": "USD",
                "price": 1.6789
              }
            ]
          }
        }
      """
