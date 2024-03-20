@regression
@ticket-BB-21429
@fixture-OroGoogleTagManagerBundle:integration.yml
@fixture-OroGoogleTagManagerBundle:home_page_slider_content_widget_fixture.yml
Feature: Configurable image slider with enabled GTM
  In order to have image sliders displayed on the storefront
  As an Administrator
  I need to be able to create and modify the image slider widget in the back office

  Scenario: Feature background
    Given sessions active:
      | Admin | first_session  |
      | Buyer | second_session |
    And I enable GTM integration
    And I add Home Page Slider widget before content for "Homepage" page

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
