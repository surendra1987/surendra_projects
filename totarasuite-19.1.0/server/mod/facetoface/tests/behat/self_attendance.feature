@mod @mod_facetoface @totara @javascript
Feature: Self check-in
  In order to test self check-in event

  Background:
    Given I am on a totara site
    And the following "users" exist:
      | username | firstname | lastname | email                |
      | teacher1 | Terry3    | Teacher  | teacher@example.com  |
      | student1 | Sam1      | Student1 | student1@example.com |
    And the following "courses" exist:
      | fullname | shortname | category | enablecompletion |
      | Course 1 | C1        | 0        | 1                |
    And the following "seminars" exist in "mod_facetoface" plugin:
      | name              | intro                           | course |
      | Test seminar name | <p>Test seminar description</p> | C1     |
    And the following "course enrolments" exist:
      | user     | course | role           |
      | teacher1 | C1     | editingteacher |
      | student1 | C1     | student        |

  Scenario: Teacher can enable/disable self attendance
    Given I log in as "teacher1"
    And I am on "Course 1" course homepage
    And I am on "Test seminar name" seminar homepage
    And I follow "Add event"
    And I set the following fields to these values:
      | Enable self-attendance | 1 |
    And I press "Save changes"
    And I follow "Go to event"
    Then I should see "Self-attendance"
    And I should see "Copy link"
    And I should see "Download QR code"
    And I am on "Test seminar name" seminar homepage
    And I click on the seminar event action "Edit event" in row "#1"
    And I set the following fields to these values:
      | Enable self-attendance | 0 |
    And I press "Save changes"
    And I follow "Go to event"
    Then I should not see "Self-attendance"
    And I should not see "Copy link"
    And I should not see "Download QR code"

  Scenario: Teacher cannot enable self attendance for more than 60 days
    Given I log in as "teacher1"
    And I am on "Course 1" course homepage
    And I am on "Test seminar name" seminar homepage
    And I follow "Add event"
    And I set the following fields to these values:
      | Enable self-attendance      | 1                    |
      | selfattendance_open[day]    | 1                    |
      | selfattendance_open[month]  | 3                    |
      | selfattendance_open[year]   | 2025                 |
      | selfattendance_close[day]   | 1                    |
      | selfattendance_close[month] | 5                    |
      | selfattendance_close[year]  | 2025                 |
    And I press "Save changes"
    Then I should see "Self-attendance close time cannot be more than 60 days from the open time."

  Scenario: Can not self checkin when the learner does not book event
    Given I log in as "teacher1"
    And I am on "Course 1" course homepage
    And I am on "Test seminar name" seminar homepage
    And I follow "Add event"
    And I set the following fields to these values:
      | Enable self-attendance | 1 |
    And I press "Save changes"
    And I self check-in "Test seminar name"
    Then I should see "You are not booked for this event."

  Scenario: Student can self check-in
    Given I log in as "teacher1"
    And I am on "Course 1" course homepage
    And I follow "Test seminar name"

    And I follow "Add event"
    And I set the following fields to these values:
      | Enable self-attendance | 1 |
    And I press "Save changes"
    And I click on the seminar event action "Attendees" in row "#1"
    And I set the field "menuf2f-actions" to "Add users"
    And I set the field "potential users" to "Sam1 Student1, student1@example.com"
    And I press "Add"
    And I press "Continue"
    When I press "Confirm"
    And I log out

    And I log in as "student1"
    And I am on "Test seminar name" seminar homepage
    And I follow "Go to event"
    Then I should not see "Self-attendance"
    And I should see "Booked"
    And I self check-in "Test seminar name"
    Then I should see "Successfully marked your attendance."
    And I self check-in "Test seminar name"
    Then I should see "Your attendance has already been recorded for this session. No further action is needed."

  Scenario: Self check-in link is expired
    Given I log in as "teacher1"
    And I am on "Course 1" course homepage
    And I follow "Test seminar name"

    And I follow "Add event"
    And I set the following fields to these values:
      | Enable self-attendance      | 1                    |
      | selfattendance_open[day]    | 1                    |
      | selfattendance_open[month]  | 3                    |
      | selfattendance_open[year]   | ## last year ## Y ## |
      | selfattendance_close[day]   | 1                    |
      | selfattendance_close[month] | 4                    |
      | selfattendance_close[year]  | ## last year ## Y ## |
    And I press "Save changes"
    And I click on the seminar event action "Attendees" in row "#1"
    And I set the field "menuf2f-actions" to "Add users"
    And I set the field "potential users" to "Sam1 Student1, student1@example.com"
    And I press "Add"
    And I press "Continue"
    When I press "Confirm"
    And I log out
    And I log in as "student1"
    And I am on "Test seminar name" seminar homepage
    And I follow "Go to event"
    Then I should not see "Self-attendance"
    And I should see "Booked"
    And I self check-in "Test seminar name"
    Then I should see "The self check-in link has expired. Please contact your course trainer."
