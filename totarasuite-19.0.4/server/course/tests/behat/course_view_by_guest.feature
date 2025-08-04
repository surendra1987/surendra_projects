@core @core_course @guest_access @javascript
Feature: Test course visibility on guest account

  Background:
    Given I am on a totara site
    And the following config values are set as admin:
      | guestloginbutton  | Show   |    |
      | autologinguests   | true   |    |
    And the following "courses" exist:
      | fullname  | shortname | visible |
      | Biology   | C1        | 1       |

  Scenario: View the course with guest access disabled
    Given I am on "Biology" course homepage
    And I wait to be redirected
    Then I should see "Sign in"
    And I should not see the "Navigation" block

  Scenario: View the course with guest access enable
    Given I log in as "admin"
    And I am on "Biology" course homepage
    Then I navigate to "Users > Enrolment methods" in current page administration
    And I click on "Enable" "link" in the "Guest access" "table_row"
    Then I log out
    And I am on "Biology" course homepage
    And I should see "You are using a guest account"
    And I should see "You are viewing as a ‘Guest’. Your progress will not be recorded"