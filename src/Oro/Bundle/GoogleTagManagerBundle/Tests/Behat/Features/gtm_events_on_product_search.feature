@regression
@random-failed
@fixture-OroGoogleTagManagerBundle:integration.yml
@fixture-OroGoogleTagManagerBundle:products.yml
Feature: GTM events on product search
  In order to ...
  As an ...
  I should be able to ...

  Scenario: Feature background
    Given I enable GTM integration

  Scenario: Check product events on products search result list
    When I go to homepage
    And I type "" in "search"
    And I click "Search Button"
    And I sort frontend grid "Product Frontend Grid" by "Price (Low to High)"
    Then GTM data layer must contain the following message:
      """
        {
          "pageCategory": "search",
          "localizationId": "1"
        }
      """
    And GTM data layer must contain the following message:
      """
        {
          "event": "productImpression",
          "ecommerce": {
            "currencyCode": "USD",
            "impressions": [
              {
                "id": "SKU1",
                "name": "Product 1",
                "category": "All Products / NewCategory",
                "price": "10.00",
                "position": 0,
                "viewMode": "list-view",
                "list": "product-search"
              },
              {
                "id": "SKU2",
                "name": "Product 2",
                "category": "All Products / NewCategory",
                "price": "15.00",
                "position": 1,
                "viewMode": "list-view",
                "list": "product-search"
              }
            ]
          }
        }
      """

    When I filter "Any Text" as contains "SKU1"
    Then last message in the GTM data layer should be:
      """
        {
          "event": "productImpression",
          "ecommerce": {
            "currencyCode": "USD",
            "impressions": [
              {
                "id": "SKU1",
                "name": "Product 1",
                "category": "All Products / NewCategory",
                "price": "10.00",
                "position": 0,
                "viewMode": "list-view",
                "list": "product-search"
              }
            ]
          }
        }
      """

    When do not change page on link click
    And I follow "Product 1"
    Then last message in the GTM data layer should be:
      """
        {
          "event": "productClick",
          "eventCallback": [],
          "ecommerce": {
            "click": {
              "products": [
                {
                  "id": "SKU1",
                  "name": "Product 1",
                  "category": "All Products / NewCategory",
                  "price": "10.00",
                  "position": 0,
                  "viewMode": "list-view"
                }
              ],
              "actionField": {
                "list": "product-search"
              }
            }
          }
        }
      """

  Scenario: Check product events on products search result list for single found product
    When I go to homepage
    And I type "SKU1" in "search"
    And I click "Search Button"
    Then GTM data layer must contain the following message:
      """
        {
          "pageCategory": "search",
          "localizationId": "1"
        }
      """
    And GTM data layer must contain the following message:
      """
        {
          "event": "productImpression",
          "ecommerce": {
            "currencyCode": "USD",
            "impressions": [
              {
                "id": "SKU1",
                "name": "Product 1",
                "category": "All Products / NewCategory",
                "price": "10.00",
                "position": 0,
                "viewMode": "list-view",
                "list": "product-search"
              }
            ]
          }
        }
      """

    When I click "Gallery View"
    Then GTM data layer must contain the following message:
      """
        {
          "event": "productImpression",
          "ecommerce": {
            "currencyCode": "USD",
            "impressions": [
              {
                "id": "SKU1",
                "name": "Product 1",
                "category": "All Products / NewCategory",
                "price": "10.00",
                "position": 0,
                "viewMode": "gallery-view",
                "list": "product-search"
              }
            ]
          }
        }
      """

    When I click "No Image View"
    Then GTM data layer must contain the following message:
      """
        {
          "event": "productImpression",
          "ecommerce": {
            "currencyCode": "USD",
            "impressions": [
              {
                "id": "SKU1",
                "name": "Product 1",
                "category": "All Products / NewCategory",
                "price": "10.00",
                "position": 0,
                "viewMode": "no-image-view",
                "list": "product-search"
              }
            ]
          }
        }
      """

    When do not change page on link click
    And I follow "View Details"
    Then last message in the GTM data layer should be:
      """
        {
          "event": "productClick",
          "eventCallback": [],
          "ecommerce": {
            "click": {
              "products": [
                {
                  "id": "SKU1",
                  "name": "Product 1",
                  "category": "All Products / NewCategory",
                  "price": "10.00",
                  "position": 0,
                  "viewMode": "no-image-view"
                }
              ],
              "actionField": {
                "list": "product-search"
              }
            }
          }
        }
      """
