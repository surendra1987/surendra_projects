@core_tag @core_ai @javascript
Feature: Manager can use the suggest tags feature when AI is enabled

  Background:
    Given the following "users" exist:
      | username | firstname | lastname | email                |
      | manager1 | Manager   | 1        | manager1@example.com |
    And the following "system role assigns" exist:
      | user     | course               | role    |
      | manager1 | Acceptance test site | manager |
    And the following "courses" exist:
      | fullname  | shortname |
      | Course 1  | c1        |
    And the following "tags" exist:
      | name         | isstandard |
      | Strawberry 1 | 1          |
      | Strawberry 2 | 1          |
      | Strawberry 3 | 1          |
      | Strawberry 4 | 1          |
      | Strawberry 5 | 1          |
      | Strawberry 6 | 1          |
    And the AI subsystem is ready for testing

  Scenario: Using the suggest tags feature
    And I log in as "manager1"
    And I am on "Course 1" course homepage
    And I navigate to "Edit settings" in current page administration
    And I expand all fieldsets
    Then I should see "AI-suggested tags"
    And I should not see "Strawberry"
    When I press "Generate tags"
    Then I should see "Regenerate tags"
    And I should see "Strawberry"
    When I click on ".form-aitags__suggestion-item[tabindex=0]" "css_element"
    Then I should see "Strawberry" in the ".form-autocomplete-selection" "css_element"
    And I should not see "Strawberry" in the ".aitags-suggestions-body" "css_element"
