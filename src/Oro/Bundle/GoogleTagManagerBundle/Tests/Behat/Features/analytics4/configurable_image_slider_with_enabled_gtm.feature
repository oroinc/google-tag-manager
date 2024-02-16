@regression
@ticket-BB-21429
@fixture-OroGoogleTagManagerBundle:integration.yml

Feature: Configurable image slider with enabled GTM
  In order to have image sliders displayed on the storefront
  As an Administrator
  I need to be able to create and modify the image slider widget in the back office

  Scenario: Feature background
    Given sessions active:
      | Admin | first_session  |
      | Buyer | second_session |
    Given I enable GTM integration

  Scenario: Create content widget
    Given I login as administrator
    When I go to Marketing/ Content Widgets
    And click edit "home-page-slider" in grid
    And fill "Content Widget Form" with:
      | Autoplay Speed (milliseconds) | 7000 |
    And fill "Image Slider Form" with:
      | Target 1 | New Window |
    And I save and close form
    Then I should see "Content widget has been saved" flash message
    And I should see next rows in "Slides" table
      | Slide Order | URL                                             | ALT IMAGE TEXT               | Text Alignment | Target Window |
      | 1           | /product/                                       | Seasonal Sale                | Left           | New Window    |
      | 2           | /navigation-root/new-arrivals/lighting-products | Bright New Day In Lighting   | Center         | Same Window   |
      | 3           | /medical/medical-apparel                        | Best-Priced Medical Supplies | Right          | Same Window   |

  Scenario: Check content widget of storefront
    Given I proceed as the Buyer
    When I am on the homepage
    And I set alias "homepage" for the current browser tab
    And I click "First Dot On Home Page Slider"
    Then GTM data layer must contain the following message:
      """
        {
          "event": "view_promotion",
          "ecommerce": {
            "items": [{"creative_name": "home-page-slider", "item_name": "Seasonal Sale", "index": 0}]
          }
        }
      """

    When I click "First Image Slide"
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

    And a new browser tab is opened and I switch to it
    And the url should match "/product"
    And I should see "All Products"
    When I switch to the browser tab "homepage"
    Then I should be on the homepage
