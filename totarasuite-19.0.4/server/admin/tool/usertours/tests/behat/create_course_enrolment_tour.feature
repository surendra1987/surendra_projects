@tool @tool_usertours @enrol @enrol_self @totara_course
Feature: Add a new course enrolment tour
  Background:
    Given I am on a totara site
    And the following "courses" exist:
      | fullname | shortname | category | enablecompletion |
      | Course 1 | C1        | 0        | 1                |
    And the following "users" exist:
      | username | firstname | lastname | email |
      | student1 | Student | 1 | student1@example.com |
    And I log in as "admin"
    And I add a new user tour with:
      | Name                | Course Tour |
      | Description         | Course enrolment tour |
      | Apply to URL match  | /enrol/index.php% |
      | Tour is enabled     | 1 |
    And I add steps to the "Course Tour" tour:
      | targettype                  | Title             | Content |
      | Display in middle of page   | Welcome           | Welcome to your personal learning space. We'd like to give you a quick tour to show you some of the areas you may find helpful |
    And I log out

  @javascript
  Scenario: Test user tour on course enrolment
    When I log in as "admin"
    And I am on "Course 1" course homepage
    And I navigate to "Users > Enrolment methods" in current page administration
    And I click on "Enable" "link" in the "Self enrolment (Learner)" "table_row"
    When I log out

    And I log in as "student1"
    And I am on "Course 1" course homepage
    Then I should see "Welcome to your personal learning space. We'd like to give you a quick tour to show you some of the areas you may find helpful"
    And I press "End tour"