@gradereport @gradereport_grader @core_grades
Feature: We don't show hidden grades for users without the 'moodle/grade:viewhidden' capability on grader report
  In order to show grader report in secure way
  As a teacher without the 'moodle/grade:viewhidden' capability
  I should not see hidden grades in the grader report

  Background:
    Given the following "courses" exist:
      | fullname | shortname | format |
      | Course 1 | C1        | topics |
    And the following "users" exist:
      | username | firstname | lastname | email                |
      | teacher1 | Teacher   | 1        | teacher1@example.com |
      | student1 | Student   | 1        | student1@example.com |
      | student2 | Student   | 2        | student2@example.com |
    And the following "course enrolments" exist:
      | user     | course | role           |
      | teacher1 | C1     | editingteacher |
      | student1 | C1     | student        |
      | student2 | C1     | student        |
    And the following "activities" exist:
      | activity | course | section | name                   | intro                   | assignsubmission_onlinetext_enabled | submissiondrafts | idnumber |
      | assign   | C1     | 1       | Test assignment name 1 | Submit your online text | 1                                   | 0                | A1       |
      | assign   | C1     | 1       | Test assignment name 2 | submit your online text | 1                                   | 0                | A2       |
      | assign   | C1     | 1       | Test assignment name 3 | submit your online text | 1                                   | 0                | A3       |
    # Hidden manual grade item.
    And the following "grade items" exist:
      | itemname     | grademin | grademax | course | hidden |
      | Manual grade | 20       | 40       | C1     | 1      |
    And I log in as "admin"
    And I am on "Course 1" course homepage with editing mode on
    # Hide assignment 2 activity.
    And I open "Test assignment name 2" actions menu
    And I choose "Hide" in the open action menu
    And I navigate to "Setup > Gradebook setup" in the course gradebook
    # Hide assignment 3 grade item.
    And I click on "Edit" "link" in the "Test assignment name 3" "table_row"
    And I choose "Hide" in the open action menu
    And I navigate to "View > Grader report" in the course gradebook
    And I click on "Single view for Student 1" "link" in the "Student 1" "table_row"
    And I set the following fields to these values:
      | Override for Test assignment name 1 | 1 |
      | Override for Test assignment name 2 | 1 |
      | Override for Test assignment name 3 | 1 |
      | Test assignment name 1 | 80 |
      | Test assignment name 2 | 90 |
      | Test assignment name 3 | 10 |
      | Manual grade           | 30 |
    And I click on "Save" "button"
    And I click on "Continue" "button"
    And I navigate to "View > Grader report" in the course gradebook
    And I click on "Single view for Student 2" "link" in the "Student 2" "table_row"
    And I set the following fields to these values:
      | Override for Test assignment name 1 | 1 |
      | Override for Test assignment name 2 | 1 |
      | Override for Test assignment name 3 | 1 |
      | Test assignment name 1 | 70 |
      | Test assignment name 2 | 60 |
      | Test assignment name 3 | 50 |
      | Manual grade           | 40 |
    And I click on "Save" "button"
    And I click on "Continue" "button"

  @javascript
  Scenario: View grader report containing hidden activities or grade items or grades
    Given I log out
    And I log in as "teacher1"
    And I am on "Course 1" course homepage
    And I navigate to "View > Grader report" in the course gradebook
    Then the following should exist in the "user-grades" table:
      | -1-                | -3-                  | -4-       | -5-       | -6-       | -7-       | -8-       |
      | Student 1          | student1@example.com | 80.00     | 90.00     | 10.00     | 30.00     | 210.00    |
      | Student 2          | student2@example.com | 70.00     | 60.00     | 50.00     | 40.00     | 220.00    |
    When I click on "Grades for Student 1" "link" in the "Student 1" "table_row"
    And I set the field "View report as" to "Myself"
    Then I should see "Test assignment name 1"
    And I should see "Test assignment name 2"
    And I should see "Test assignment name 3"
    And I should see "Manual grade"
    When I navigate to "View > Grade history" in the course gradebook
    And I click on "Select users" "button"
    And I click on "Student 1" "text"
    And I click on "Finish selecting users" "button"
    And I click on "Submit" "button"
    Then I should see "30.00" in the ".gradereport_history #gradereport_history_r1_c5" "css_element"

    # Remove the capability to see hidden grades.
    When I log out
    And I log in as "admin"
    And I set the following system permissions of "Teacher" role:
      | capability | permission |
      | moodle/grade:viewhidden |  Prohibit |
    And I log out
    And I log in as "teacher1"
    And I am on "Course 1" course homepage
    And I navigate to "View > Grader report" in the course gradebook
    And the following should exist in the "user-grades" table:
      | -1-                | -3-                  | -4-       | -5-       | -6-       | -7-       | -8-       |
      | Student 1          | student1@example.com | 80.00     | -         | -         | -         | 80.00     |
      | Student 2          | student2@example.com | 70.00     | -         | -         | -         | 70.00     |
    When I click on "Grades for Student 1" "link" in the "Student 1" "table_row"
    Then I should see "Test assignment name 1"
    And I should not see "Test assignment name 2"
    And I should not see "Test assignment name 3"
    And I should not see "Manual grade"
    When I navigate to "View > Grade history" in the course gradebook
    And I click on "Select users" "button"
    And I click on "Student 1" "text"
    And I click on "Finish selecting users" "button"
    And I click on "Submit" "button"
    Then I should see "-" in the ".gradereport_history #gradereport_history_r1_c5" "css_element"
    And I should not see "30.00"
