@regression
@feature-BB-21702
@fixture-OroGoogleTagManagerBundle:integration.yml
@fixture-OroGoogleTagManagerBundle:image_slider_content_widget_fixture.yml

Feature: GTM events on landing page with image sliders

  Scenario: Feature background
    Given I enable GTM integration
    When I go to homepage
    And I click "Image Sliders CMS Page" in hamburger menu
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

