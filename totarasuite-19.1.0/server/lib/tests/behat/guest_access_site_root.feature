@core @guest_access
Feature: Guest user access to site root

  Background:
    Given I am on a totara site
    And the following config values are set as admin:
      | guestloginbutton  | Show   |    |
      | autologinguests   | true   |    |

  Scenario: Visit root URL as guest user
    Given I am on site homepage
    Then I should see "You are using a guest account"