@totara @totara_program @javascript
Feature: Check that the program view page coursesets link to courses based on course enrolment, visibility and program assignment status.

  Background:
    Given I am on a totara site
    And the following "users" exist:
      | username      | firstname       | lastname        | email                     |
      | learner1      | fn_learner1     | ln_learner1     | learner1@example.com      |
      | sitemanager1  | fn_sitemanager1 | ln_sitemanager1 | sitemanager1@example.com  |
    And the following "system role assigns" exist:
      | user         | role         | contextlevel | reference |
      | sitemanager1 | manager      | System       | System    |
    And the following "courses" exist:
      | fullname        | shortname | format | enablecompletion |
      | Test Course One | course1   | topics | 1                |
    And the following "programs" exist in "totara_program" plugin:
      | fullname       | shortname |
      | Test Program 1 | testprog1 |
    And I add a courseset with courses "course1" to "testprog1":
      | Set name              | set1        |
      | Learner must complete | All courses |

  Scenario: Course traditional visibility set to show, user not enrolled and not assigned to program.
    Given I log in as "learner1"
    When I am on "Test Program 1" program homepage
    Then I should see "Test Course One"
    Then "//button[@aria-label='Course Test Course One is not available' and @disabled]" "xpath_element" should exist
    And I log out

    When I log in as "sitemanager1"
    And I am on "Test Program 1" program homepage
    Then I should see "Test Course One"
    Then "//button[@aria-label='Launch course Test Course One']" "xpath_element" should exist

  Scenario: Course traditional visibility set to show, user enrolled but not assigned to program.
    Given the following "course enrolments" exist:
      | user     | course  | role           |
      | learner1 | course1 | student        |
    When I log in as "learner1"
    And I am on "Test Program 1" program homepage
    Then I should see "Test Course One"
    When I click on "Launch course Test Course One" "button"
    Then I should see "Topic 1"
    And I log out

    When I log in as "sitemanager1"
    And I am on "Test Program 1" program homepage
    Then I should see "Test Course One"
    And "//button[@aria-label='Launch course Test Course One']" "xpath_element" should exist

  Scenario: Course traditional visibility set to show, user not enrolled but is assigned to program.
    Given the following "program assignments" exist in "totara_program" plugin:
      | program     | user     |
      | testprog1   | learner1 |
    When I log in as "learner1"
    And I am on "Test Program 1" program homepage
    Then I should see "Test Course One"
    When I click on "Launch course Test Course One" "button"
    Then I should see "You have been enrolled in course Test Course One via required learning program Test Program 1."
    And I log out

    When I log in as "sitemanager1"
    And I am on "Test Program 1" program homepage
    Then I should see "Test Course One"
    And "//button[@aria-label='Launch course Test Course One']" "xpath_element" should exist

  Scenario: Course traditional visibility set to hide, user not enrolled and not assigned to program.
    Given I log in as "admin"
    And I am on "Test Course One" course homepage
    And I click on "Edit settings" "link" in the "Administration" "block"
    And I set the field "Course visibility" to "0"
    And I press "Save and display"
    And I log out

    When I log in as "learner1"
    And I am on "Test Program 1" program homepage
    Then I should see "Test Course One"
    Then "//button[@aria-label='Course Test Course One is not available' and @disabled]" "xpath_element" should exist
    And I log out

    When I log in as "sitemanager1"
    And I am on "Test Program 1" program homepage
    Then I should see "Test Course One"
    And "//button[@aria-label='Launch course Test Course One']" "xpath_element" should exist

  Scenario: Course traditional visibility set to hide, user enrolled but not assigned to program.
    Given I log in as "admin"
    And I am on "Test Course One" course homepage
    And I click on "Edit settings" "link" in the "Administration" "block"
    And I set the field "Course visibility" to "0"
    And I press "Save and display"
    And I log out

    When the following "course enrolments" exist:
      | user     | course  | role           |
      | learner1 | course1 | student        |
    And I log in as "learner1"
    And I am on "Test Program 1" program homepage
    Then I should see "Test Course One"
    Then "//button[@aria-label='Course Test Course One is not available' and @disabled]" "xpath_element" should exist
    And I log out

    When I log in as "sitemanager1"
    And I am on "Test Program 1" program homepage
    Then I should see "Test Course One"
    And "//button[@aria-label='Launch course Test Course One']" "xpath_element" should exist

  Scenario: Course traditional visibility set to hide, user not enrolled but is assigned to program.
    Given I log in as "admin"
    And I am on "Test Course One" course homepage
    And I click on "Edit settings" "link" in the "Administration" "block"
    And I set the field "Course visibility" to "0"
    And I press "Save and display"
    And I log out

    When the following "program assignments" exist in "totara_program" plugin:
      | program     | user     |
      | testprog1   | learner1 |
    And I log in as "learner1"
    And I am on "Test Program 1" program homepage
    Then I should see "Test Course One"
    Then "//button[@aria-label='Course Test Course One is not available' and @disabled]" "xpath_element" should exist
    And I log out

    When I log in as "sitemanager1"
    And I am on "Test Program 1" program homepage
    Then I should see "Test Course One"
    And "//button[@aria-label='Launch course Test Course One']" "xpath_element" should exist

  Scenario: Course with audience visibility set to no users, user not enrolled and not assigned to program.
    Given I log in as "admin"
    When I set the following administration settings values:
      | Enable audience-based visibility | 1 |
    And I am on "Test Course One" course homepage
    And I click on "Edit settings" "link" in the "Administration" "block"
    And I set the field "Visibility" to "No users"
    And I press "Save and display"
    Then I log out

    When I log in as "learner1"
    And I am on "Test Program 1" program homepage
    Then I should see "Test Course One"
    Then "//button[@aria-label='Course Test Course One is not available' and @disabled]" "xpath_element" should exist
    And I log out

    When I log in as "sitemanager1"
    And I am on "Test Program 1" program homepage
    Then I should see "Test Course One"
    And "//button[@aria-label='Launch course Test Course One']" "xpath_element" should exist

  Scenario: Course with audience visibility set to no users, user enrolled but not assigned to program.
    Given I log in as "admin"
    When I set the following administration settings values:
      | Enable audience-based visibility | 1 |
    And I am on "Test Course One" course homepage
    And I click on "Edit settings" "link" in the "Administration" "block"
    And I set the field "Visibility" to "No users"
    And I press "Save and display"
    Then I log out

    When the following "course enrolments" exist:
      | user     | course  | role           |
      | learner1 | course1 | student        |
    And I log in as "learner1"
    And I am on "Test Program 1" program homepage
    Then I should see "Test Course One"
    Then "//button[@aria-label='Course Test Course One is not available' and @disabled]" "xpath_element" should exist
    And I log out

    When I log in as "sitemanager1"
    And I am on "Test Program 1" program homepage
    Then I should see "Test Course One"
    And "//button[@aria-label='Launch course Test Course One']" "xpath_element" should exist

  Scenario: Course with audience visibility set to no users, user not enrolled but is assigned to program.
    Given I log in as "admin"
    When I set the following administration settings values:
      | Enable audience-based visibility | 1 |
    And I am on "Test Course One" course homepage
    And I click on "Edit settings" "link" in the "Administration" "block"
    And I set the field "Visibility" to "No users"
    And I press "Save and display"
    Then I log out

    When the following "program assignments" exist in "totara_program" plugin:
      | program     | user     |
      | testprog1   | learner1 |
    And I log in as "learner1"
    And I am on "Test Program 1" program homepage
    Then I should see "Test Course One"
    Then "//button[@aria-label='Course Test Course One is not available' and @disabled]" "xpath_element" should exist
    And I log out

    When I log in as "sitemanager1"
    And I am on "Test Program 1" program homepage
    Then I should see "Test Course One"
    And "//button[@aria-label='Launch course Test Course One']" "xpath_element" should exist

  Scenario: Course with audience visibility set to enrolled users, user not enrolled and not assigned to program.
    Given I log in as "admin"
    When I set the following administration settings values:
      | Enable audience-based visibility | 1 |
    And I am on "Test Course One" course homepage
    And I click on "Edit settings" "link" in the "Administration" "block"
    And I set the field "Visibility" to "Enrolled users only"
    And I press "Save and display"
    And I log out

    When I log in as "learner1"
    And I am on "Test Program 1" program homepage
    Then I should see "Test Course One"
    Then "//button[@aria-label='Course Test Course One is not available' and @disabled]" "xpath_element" should exist
    And I log out

    When I log in as "sitemanager1"
    And I am on "Test Program 1" program homepage
    Then I should see "Test Course One"
    And "//button[@aria-label='Launch course Test Course One']" "xpath_element" should exist

  Scenario: Course with audience visibility set to enrolled users, user enrolled but not assigned to program.
    Given I log in as "admin"
    When I set the following administration settings values:
      | Enable audience-based visibility | 1 |
    And I am on "Test Course One" course homepage
    And I click on "Edit settings" "link" in the "Administration" "block"
    And I set the field "Visibility" to "Enrolled users only"
    And I press "Save and display"
    And I log out

    When the following "course enrolments" exist:
      | user     | course  | role           |
      | learner1 | course1 | student        |
    And I log in as "learner1"
    And I am on "Test Program 1" program homepage
    Then I should see "Test Course One"
    When I click on "Launch course Test Course One" "button"
    Then I should see "Topic 1"
    And I log out

    When I log in as "sitemanager1"
    And I am on "Test Program 1" program homepage
    Then I should see "Test Course One"
    And "//button[@aria-label='Launch course Test Course One']" "xpath_element" should exist

  Scenario: Course with audience visibility set to enrolled users, user not enrolled but is assigned to program.
    Given I log in as "admin"
    When I set the following administration settings values:
      | Enable audience-based visibility | 1 |
    And I am on "Test Course One" course homepage
    And I click on "Edit settings" "link" in the "Administration" "block"
    And I set the field "Visibility" to "Enrolled users only"
    And I press "Save and display"
    Then I log out

    When the following "program assignments" exist in "totara_program" plugin:
      | program     | user     |
      | testprog1   | learner1 |
    And I log in as "learner1"
    And I am on "Test Program 1" program homepage
    Then I should see "Test Course One"
    When I click on "Launch course Test Course One" "button"
    Then I should see "You have been enrolled in course Test Course One via required learning program Test Program 1."
    And I log out

    When I log in as "sitemanager1"
    And I am on "Test Program 1" program homepage
    Then I should see "Test Course One"
    And "//button[@aria-label='Launch course Test Course One']" "xpath_element" should exist
