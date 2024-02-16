@regression
@feature-BB-21298
@feature-BB-16952
@fixture-OroGoogleTagManagerBundle:integration.yml
@fixture-OroGoogleTagManagerBundle:products.yml

Feature: GTM events on product search

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
                "price": "10.4555",
                "position": 0,
                "viewMode": "list-view",
                "list": "product-search"
              },
              {
                "id": "SKU2",
                "name": "Product 2",
                "category": "All Products / NewCategory",
                "price": "15.1234",
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
                "price": "10.4555",
                "position": 0,
                "viewMode": "list-view",
                "list": "product-search"
              }
            ]
          }
        }
      """

    And I should see "$10.4555 / item" in the "Product Price Your" element
    And I should see "$10.4555 / item" in the "Product Price Listed" element

    When do not change page on link click
    And I follow "Product 1"
    Then last message in the GTM data layer should be:
      """
        {
          "event": "productClick",
          "eventCallback": {},
          "ecommerce": {
            "currencyCode": "USD",
            "click": {
              "products": [
                {
                  "id": "SKU1",
                  "name": "Product 1",
                  "category": "All Products / NewCategory",
                  "price": "10.4555",
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
                "price": "10.4555",
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
                "price": "10.4555",
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
                "price": "10.4555",
                "position": 0,
                "viewMode": "no-image-view",
                "list": "product-search"
              }
            ]
          }
        }
      """

    And I should see "$10.4555 / item" in the "Product Price Your" element
    And I should see "$10.4555 / item" in the "Product Price Listed" element

    When do not change page on link click
    And I follow "View Details"
    Then last message in the GTM data layer should be:
      """
        {
          "event": "productClick",
          "eventCallback": {},
          "ecommerce": {
            "currencyCode": "USD",
            "click": {
              "products": [
                {
                  "id": "SKU1",
                  "name": "Product 1",
                  "category": "All Products / NewCategory",
                  "price": "10.4555",
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
