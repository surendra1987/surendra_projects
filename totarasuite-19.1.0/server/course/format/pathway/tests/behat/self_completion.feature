@format_pathway @completion @javascript
Feature: Pathway course self completion

  Background:
    Given I am on a totara site
    And the following "courses" exist:
      | fullname | shortname | format  | enablecompletion |
      | Course 1 | c1        | pathway | 1                |
    And the following "users" exist:
      | username | firstname | lastname |
      | user1    | User      | One      |
      | user2    | User      | Two      |
    And the following "course enrolments" exist:
      | user  | course | role    |
      | user1 | c1     | student |
      | user2 | c1     | student |
    And I log in as "admin"
    And I am on "Course 1" course homepage

    And I click on "Course completion" "link"
    And I expand all fieldsets
    And I click on "criteria_self_value" "checkbox"
    And I click on "Save changes" "button"
    And I log out

  Scenario: User self completes a course in the pathway format
    When I log in as "user1"
    And I am on "Course 1" course homepage
    And I click on "Mark as completed" "button"
    Then I should see "Course marked as completed"
    And I should not see "Mark as completed"
    And I log out

    When I log in as "admin"
    And I am on "Course 1" course homepage
    And I click on "Reports" "button"
    And I click on "Course completion" "link" in the "[role='region'][aria-label='Reports']" "css_element"
    And I should see "Completed" in the "User One" "table_row"
    And I should see "Not completed" in the "User Two" "table_row"