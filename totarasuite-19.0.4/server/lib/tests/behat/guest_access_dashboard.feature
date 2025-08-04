@core @guest_access @javascript
Feature: Guest user access for dashboard

  Background:
    Given I am on a totara site
    And the following config values are set as admin:
      | guestloginbutton  | Show   |    |
      | autologinguests   | true   |    |

  Scenario: Visit the dashboard as guest user with guest access disabled
    When I am on "Dashboard" page
    And I wait to be redirected
    Then I should see "Sign in"
    And I should not see the "Navigation" block

  Scenario: Visit the dashboard as guest user with guest access enabled
    When I log in as "admin"
    And I press "Manage dashboards"
    And I wait to be redirected
    And I click on "My Learning" "link"
    And I wait to be redirected
    And I press "Edit dashboard settings"
    And I wait to be redirected
    And I set the following fields to these values:
      | Allow guest access | Yes |
    And I press "Save changes"
    And I log out
    And I wait to be redirected
    And I am on "Dashboard" page
    Then I should see "You are using a guest account"