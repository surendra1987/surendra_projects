@totara @totara_webhook @javascript
Feature: Ensure that when creating webhooks, the endpoint always uses HTTPS

  Background:
    Given I am on a totara site
    And I log in as "admin"
    And I navigate to "Webhooks" node in "Site administration > Development > API"
    And I follow "Create"

  Scenario: When creating a webhook, I can see that HTTPS is enforced
    Then I should see "https://"

  Scenario: When I create a webhook, I should see that the endpoint always uses HTTPS
    Given I set the following fields to these values:
    | Name     | test    |
    | Endpoint | http://abc.com |
    When I click on "Submit" "button"
    Then I should see "https://abc.com"

  Scenario: When I create a webhook, I should not be able to provide an empty endpoint with an HTTP(s) schema
    Given I set the following fields to these values:
      | Endpoint | http:// |
    Then I should see "You must provide a URL"