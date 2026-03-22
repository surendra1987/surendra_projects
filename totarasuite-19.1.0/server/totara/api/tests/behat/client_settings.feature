@totara @totara_api @vuejs @javascript
Feature: View API client settings.

  Background:
    Given I am on a totara site
    And I enable the "api" advanced feature
    And the following "users" exist:
      | username | firstname | lastname |
      | user1    | api       | user1    |
    And the following "api clients" exist in "totara_api" plugin:
      | name                 | username |
      | API client1          | user1    |

  Scenario: view introspection setting on API clients settings page
    Given I log in as "admin"
    And I enable the "api" advanced feature
    And I navigate to "Development > API > API clients" in site administration
    And I click on "Actions for API client1" "button"
    And I follow "Edit client settings"
    Then I should see "Enable GraphQL introspection"

  Scenario: client introspection enabled updates client_settings table
    Given I log in as "admin"
    And I enable the "api" advanced feature
    And I "disable" introspection for api client "API client1"
    And I navigate to "Development > API > API clients" in site administration
    And I click on "Actions for API client1" "button"
    And I follow "Edit client settings"
    And I should see "Enable GraphQL introspection"
    And "input[name=enableIntrospection]" "css_element" should exist
    And I click on the "enableIntrospection" tui checkbox
    And I click on "Save" "button"
    And I wait to be redirected
    Then I see introspection for api client "API client1" is "enabled"

  Scenario: client introspection disabled updates client_settings table
    Given I log in as "admin"
    And I enable the "api" advanced feature
    And I "enable" introspection for api client "API client1"
    And I navigate to "Development > API > API clients" in site administration
    And I click on "Actions for API client1" "button"
    And I follow "Edit client settings"
    And I should see "Enable GraphQL introspection"
    And "input[name=enableIntrospection]" "css_element" should exist
    And I click on the "enableIntrospection" tui checkbox
    And I click on "Save" "button"
    And I wait to be redirected
    Then I see introspection for api client "API client1" is "disabled"
