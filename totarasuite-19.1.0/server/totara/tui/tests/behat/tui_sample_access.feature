@totara @totara_tui @javascript
Feature: Test tui samples page access

  Scenario: Guest can view tui samples with permission set up
    Given I am on a totara site
    And I log in as "admin"
    And the following config values are set as admin:
      | guestloginbutton | Show |
      | autologinguests  | true |
    And I set the following system permissions of "Guest" role:
      | totara/tui:samples | Allow |
    And I log out
    And I navigate to the tui samples page
    Then I should see "Tui"
    And I should not see "nopermissions"

  Scenario: Chromeless tui samples on url query chromeless=true
    Given I am on a totara site
    And I log in as "admin"
    And I navigate to the tui samples page with the query "?chromeless=true"
    Then "#page-totara-tui-index" "css_element" should be visible
    Then ".tui-samples__sidebar" "css_element" should not be visible
    Then ".theme_inspire__nav" "css_element" should not be visible
    Then ".userToolbar" "css_element" should not be visible
    Then ".page-footer" "css_element" should not be visible
