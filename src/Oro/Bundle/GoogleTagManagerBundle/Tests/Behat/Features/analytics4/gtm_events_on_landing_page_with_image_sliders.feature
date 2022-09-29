@regression
@feature-BB-21702
@fixture-OroGoogleTagManagerBundle:integration.yml
@fixture-OroGoogleTagManagerBundle:image_slider_content_widget_fixture.yml
@fixture-OroGoogleTagManagerBundle:image_slider_content_widget_slugs_fixture.yml

Feature: GTM events on landing page with image sliders
  Scenario: Feature background
    Given I enable GTM integration
    And I login as administrator
    When I go to System/Configuration
    And I follow "System/Integrations/Google Settings" on configuration sidebar
    And uncheck "Use default" for "Data Collection for" field
    And fill form with:
      | Data Collection for | [Google Analytics 4] |
    And I save setting
    Then I should see "Configuration saved" flash message

  Scenario: Check image sliders on landing page
    When I go to homepage
    And I click "Image Sliders CMS Page"
    Then GTM data layer must contain the following message:
      """
        {
          "event": "view_promotion",
          "ecommerce": {
            "items": [{"creative_name": "image-slider", "item_name": "first slide", "index": 1}]
          }
        }
      """
    When I click "2" in "First Image Slider" element
    Then GTM data layer must contain the following message:
      """
        {
          "event": "view_promotion",
          "ecommerce": {
            "items": [{"creative_name": "image-slider", "item_name": "second slide", "index": 2}]
          }
        }
      """
    When I click "3" in "Second Image Slider" element
    Then GTM data layer must contain the following message:
      """
        {
          "event": "view_promotion",
          "ecommerce": {
            "items": [{"creative_name": "image-slider", "item_name": "third slide", "index": 3}]
          }
        }
      """
