@totara @totara_webhook @javascript
Feature: Ensure selected events are filtered from the list after being selected

  Background:
    Given I am on a totara site
    And I log in as "admin"
    And I navigate to "Webhooks" node in "Site administration > Development > API"
    And I follow "Create"
    And I set the following fields to these values:
      | Name     | testHook |
      | Endpoint | abc.com  |
    And I click on "Submit" "button"

  Scenario: When I update a webhook, I should not be able to provide an empty endpoint
    Given I open the dropdown menu in the tui datatable row with "testHook" "Name"
    And I follow "Edit"
    When I click on "[name=events]" "css_element"
    And I click on "Badge archived" option in the dropdown menu
    Then I should not see "Badge archived" option in the dropdown menu
    But I should see "Badge archived" in the ".tui-tagList" "css_element"

