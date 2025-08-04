@course @format_pathway @javascript
Feature: Show warning banner in pathway course if the course have incompatible activities

  Background:
    Given the following "users" exist:
      | username | firstname | lastname | email               |
      | student  | Student   | One      | student@example.com |
    And the following "courses" exist:
      | fullname | shortname | format  |
      | Spring   | Spring    | pathway |
    And the following "course enrolments" exist:
      | user     | course | role    |
      | student  | Spring | student |
    And the following "activities" exist:
      | activity | name               | intro | course | idnumber |
      | forum    | Test forum name    | intro | Spring | forum    |
      | data     | Test database name | intro | Spring | data1    |

  Scenario: Admin can see the incompatible activity warning banner in pathway course
    Given I log in as "admin"
    And I am on "Spring" course homepage
    And I should see "Test forum name"
    And I click on "Test database name" "link"
    Then I should see "This activity is incompatible with the pathway course format and won't be visible to learners."

  Scenario: Student can not see the incompatible activity warning banner in patway course
    Given I log in as "student"
    And I am on "Spring" course homepage
    Then I should see "Test forum name"
    And I should not see "Test database name"
    Then I should not see "This activity is incompatible with the pathway course format and won't be visible to learners."