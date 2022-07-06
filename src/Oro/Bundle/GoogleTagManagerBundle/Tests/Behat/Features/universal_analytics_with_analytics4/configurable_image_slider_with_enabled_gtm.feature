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
    And I enable GTM integration
    And I login as administrator
    When I go to System/Configuration
    And I follow "System/Integrations/Google Settings" on configuration sidebar
    And uncheck "Use default" for "Data Collection for" field
    And fill form with:
      | Data Collection for | [Universal Analytics, Google Analytics 4] |
    And I save setting
    Then I should see "Configuration saved" flash message

  Scenario: Create content widget
    When I go to Marketing/ Content Widgets
    And click edit "home-page-slider" in grid
    And fill "Image Slider Form" with:
      | Target 1 | New Window |
    And I save and close form
    Then I should see "Content widget has been saved" flash message
    And I should see next rows in "Slides" table
      | Slide Order | URL       | Title       | Text Alignment | Target Window |
      | 1           | /product/ | Lorem ipsum | Right          | New Window    |

  Scenario: Check content widget of storefront
    Given I proceed as the Buyer
    When I am on the homepage
    And I set alias "homepage" for the current browser tab
    And I click "First Dot On Home Page Slider"
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

    When I click "First Image Slide"
    Then GTM data layer must contain the following message:
      """
        {
          "event": "promotionClick",
          "eventCallback": {
              "cancel": []
          },
          "ecommerce": {
            "promoClick": {"promotions": [{"name": "Lorem ipsum", "creative": "home-page-slider", "position": 0}]}
          }
        }
      """

    And GTM data layer must contain the following message:
      """
        {
          "event": "select_promotion",
          "eventCallback": {
              "cancel": []
          },
          "ecommerce": {
            "items": [{"creative_name": "home-page-slider", "item_name": "Lorem ipsum", "index": 0}]
          }
        }
      """

    And a new browser tab is opened and I switch to it
    And the url should match "/product"
    And I should see "All Products"
    When I switch to the browser tab "homepage"
    Then I should be on the homepage
