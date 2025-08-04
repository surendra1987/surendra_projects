@core @guest_access @javascript
Feature: Guest user access for catalog

  Background:
    Given I am on a totara site
    And the following config values are set as admin:
      | guestloginbutton  | Show   |    |
      | autologinguests   | true   |    |

  Scenario: Visit the catalog as guest user
    When I am on totara catalog page
    And I wait to be redirected
    Then I should see the "totara" catalog page
    And I should see "You are using a guest account"