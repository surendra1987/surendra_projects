@core @totara @core_mfa @javascript @vuejs
Feature: Login with MFA after upgrading from a Guest User
  Background:
    Given I am on a totara site
    And the following "mfa instances" exist in "core_mfa" plugin:
      | user  | type   | label           | config              |
      | admin | button | instance active | {"secret": "efghi"} |
    And I log in as "admin"
    And I navigate to "Plugins > Multi-factor authentication > Manage multi-factor authentication" in site administration
    And I click on "Enable" "link" in the "Button" "table_row"
    And I log out

  Scenario: Upgrade guest session triggers MFA
    # Guest login
    When I log in as "guest"
    Then I should see "You are using a guest account"

    # Upgrade to admin login
    When I log in as "admin"
    Then I should see "Press the button"

    When I click on "Verify" "button"
    Then I should see "Home"

    # Logout & try again
    When I log out
    And I log in as "admin"
    Then I should see "Press the button"
    When I click on "Verify" "button"
    Then I should see "Home"