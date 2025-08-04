@totara @totara_webhook @javascript
Feature: Ensure that when updating and viewing webhooks, the endpoint is always shown to use HTTPS

  Background:
    Given I am on a totara site
    And I log in as "admin"
    And I navigate to "Webhooks" node in "Site administration > Development > API"
    And I follow "Create"
    And I set the following fields to these values:
      | Name     | testHook    |
      | Endpoint | abc.com |
    And I click on "Submit" "button"

  Scenario: When I update a webhook, I should not be able to provide an empty endpoint
    Given I open the dropdown menu in the tui datatable row with "testHook" "Name"
    And I follow "Edit"
    When I set the following fields to these values:
      | Endpoint |  |
    Then I should see "Required"

  Scenario: When I update a webhook, I should not be able to provide an empty endpoint with an HTTP(s) schema
    Given I open the dropdown menu in the tui datatable row with "testHook" "Name"
    And I follow "Edit"
    When I set the following fields to these values:
      | Endpoint | http:// |
    Then I should see "You must provide a URL"

  Scenario: When I view a webhook, I should see that the endpoint is shown with an HTTPS schema
    Given I follow "testHook"
    Then I should see "https://abc.com"