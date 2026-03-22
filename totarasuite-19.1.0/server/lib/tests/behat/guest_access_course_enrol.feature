@core @guest_access @javascript
Feature: Guest user access to course enrolment

  Background:
    Given I am on a totara site
    And the following config values are set as admin:
      | guestloginbutton  | Show   |    |
      | autologinguests   | true   |    |
    And the following "courses" exist:
      | fullname                       | shortname |
      | Course With Guest Enrolment    | C1        |
      | Course Without Guest Enrolment | C2        |

  Scenario: Visit course with guest enrolment as a site guest
    # Set up course enrolment method
    Given I log in as "admin"
    And I am on "Course With Guest Enrolment" course homepage
    And I navigate to "Enrolment methods" node in "Course administration > Users"
    And I click on "Edit" "link" in the "Guest access" "table_row"
    And I set the following fields to these values:
      | Allow guest access | Yes |
    And I press "Save changes"
    And I log out
    And I wait to be redirected

    # Navigate to course with guest enrolment enrol page
    And I am on "Course With Guest Enrolment" course enrol page
    And I wait to be redirected
    Then I should see "You are using a guest account"
    And I should see "Enrolment options"

  Scenario: Visit course without guest enrolment as a site guest
    Given I am on "Course Without Guest Enrolment" course enrol page
    And I wait to be redirected
    Then I should not see "You are using a guest account"
    And I am on "Login" page
