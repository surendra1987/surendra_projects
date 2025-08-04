@core @core_course @core_completion @javascript
Feature: Course due dates
  As an admin I can set a due date for a course and expect the completion records for users to match the due date

  Background:
    Given I am on a totara site
    And the following "users" exist:
    | username | firstname | lastname  | email             |
    | user1    | User 1    | User 1    | user1@example.com |
    | user2    | User 2    | User 2    | user2@example.com |
    | user3    | User 3    | User 3    | user3@example.com |
    And the following "courses" exist:
    | fullname | shortname | summary                             | format | enablecompletion | duedate_op | duedate       | duedateoffsetamount | duedateoffsetunit |
    | Course 1 | C1        | <p>Course with fixed duedate</p>    | topics | 1                | 2          | ## +3 days ## |                     |                   |
    | Course 2 | C2        | <p>Course with relative duedate</p> | topics | 1                | 1          |               | 5                   | 3                 |
    | Course 3 | C3        | <p>Course without duedate</p>       | topics | 1                | 0          |               |                     |                   |
    And the following "standard_report" exist in "totara_reportbuilder" plugin:
      | fullname                  | shortname                       | source            |
      | Course Completion Report  | report_course_completion_report | course_completion |
    When I log in as "admin"
    And I navigate to my "Course Completion Report" report
    And I press "Edit this report"
    And I switch to "Columns" tab
    And I add the "Due Date" column to the report
    And I log out

  Scenario: Test fixed due date with manually enrolled users
    Given the following "course enrolments" exist:
      | user  | course | role    |
      | user1 | C1     | student |
      | user2 | C1     | student |
    When I log in as "admin"
    And I navigate to my "Course Completion Report" report
    And I follow "Show more..."
    And I set the field "course-fullname" to "Course 1"
    And I click on "Search" "button" in the ".fitem_actionbuttons" "css_element"
    Then I should see "## +3 days ##j M Y##" in the "User 1 User 1" "table_row"
    When I log out
    And I log in as "user1"
    Then I should see "## +3 days ##j M##"
    When I log out
    And I log in as "admin"
    And  I am on "Course 1" course homepage
    # Now enrol another user using the interface
    And I navigate to "Enrolled users" node in "Course administration > Users"
    And I click on "Enrol users" "button"
    And I click on "Enrol" "button" in the ".user-enroller-panel .user:nth-child(2)" "css_element"
    And I click on "Finish enrolling users" "button"
    Then I should see "Manual enrolments" in the "User 3" "table_row"
    And I navigate to my "Course Completion Report" report
    And I follow "Show more..."
    And I set the field "course-fullname" to "Course 1"
    And I click on "Search" "button" in the ".fitem_actionbuttons" "css_element"
    Then I should see "## +3 days ##j M Y##" in the "User 3 User 3" "table_row"
    When I log out
    And I log in as "user3"
    Then I should see "## +3 days ##j M##"

  Scenario: Test relative due date with manually enrolled users
    Given the following "course enrolments" exist:
      | user  | course | role    |
      | user1 | C2     | student |
      | user2 | C2     | student |
    When I log in as "admin"
    And I navigate to my "Course Completion Report" report
    And I follow "Show more..."
    And I set the field "course-fullname" to "Course 2"
    And I click on "Search" "button" in the ".fitem_actionbuttons" "css_element"
    Then I should see "## +5 days ##j M Y##" in the "User 1 User 1" "table_row"
    And I should see "## +5 days ##j M Y##" in the "User 2 User 2" "table_row"
    When I log out
    And I log in as "user1"
    Then I should see "## +5 days ##j M##"
    When I log out
    And I log in as "admin"
    And  I am on "Course 2" course homepage
    # Now enrol another user using the interface
    And I navigate to "Enrolled users" node in "Course administration > Users"
    And I click on "Enrol users" "button"
    And I click on "Enrol" "button" in the ".user-enroller-panel .user:nth-child(2)" "css_element"
    And I click on "Finish enrolling users" "button"
    Then I should see "Manual enrolments" in the "User 3" "table_row"
    When I navigate to my "Course Completion Report" report
    And I follow "Show more..."
    And I set the field "course-fullname" to "Course 2"
    And I click on "Search" "button" in the ".fitem_actionbuttons" "css_element"
    Then I should see "## +5 days ##j M Y##" in the "User 3 User 3" "table_row"
    When I log out
    And I log in as "user3"
    Then I should see "## +5 days ##j M##"

  Scenario: Test with no duedate and then change due date in course
    Given the following "course enrolments" exist:
      | user  | course | role    |
      | user1 | C3     | student |
    When I log in as "user1"
    Then I should not see "Due on"
    When I log out
    And I log in as "admin"
    And  I am on "Course 3" course homepage
    # Now enrol another user using the interface
    And I navigate to "Enrolled users" node in "Course administration > Users"
    And I click on "Enrol users" "button"
    And I click on "Enrol" "button" in the ".user-enroller-panel .user:nth-child(2)" "css_element"
    And I click on "Finish enrolling users" "button"
    Then I should see "Manual enrolments" in the "User 2" "table_row"
    When I log out
    And I log in as "user2"
    Then I should see "Course 3"
    And I should not see "Due on"
    When I log out
    And I log in as "admin"
    And I am on "Course 3" course homepage
    And I navigate to "Edit settings" in current page administration
    And I expand all fieldsets
    # Now set the due date to relative 10 days
    And I click on "id_duedate_op_1" "radio"
    And I set the field "duedateoffsetunit" to "Day(s)"
    And I set the field "duedateoffsetamount" to "10"
    And I press "Save and display"
    And I trigger cron
    And I am on "Course 3" course homepage
    And I navigate to my "Course Completion Report" report
    And I follow "Show more..."
    And I set the field "course-fullname" to "Course 3"
    And I click on "Search" "button" in the ".fitem_actionbuttons" "css_element"
    Then I should see "## +10 days ##j M Y##" in the "User 1 User 1" "table_row"
    When I log out
    And I log in as "user1"
    Then I should see "Course 3"
    And I should see "## +10 days ##j M##"
    When I log out
    And I log in as "admin"
    And  I am on "Course 3" course homepage
    # Now enrol another user using the interface
    And I navigate to "Enrolled users" node in "Course administration > Users"
    And I click on "Enrol users" "button"
    And I click on "Enrol" "button" in the ".user-enroller-panel .user:nth-child(2)" "css_element"
    And I click on "Finish enrolling users" "button"
    Then I should see "Manual enrolments" in the "User 3" "table_row"
    When I navigate to my "Course Completion Report" report
    And I follow "Show more..."
    And I set the field "course-fullname" to "Course 3"
    And I click on "Search" "button" in the ".fitem_actionbuttons" "css_element"
    Then I should see "## +10 days ##j M Y##" in the "User 3 User 3" "table_row"
    When I log out
    And I log in as "user3"
    Then I should see "Course 3"
    And I should see "## +10 days ##j M##"
    When I log out
    And I log in as "admin"
    And I am on "Course 3" course homepage
    And I navigate to "Edit settings" in current page administration
    And I expand all fieldsets
    # Now set the due date to fixed date
    And I click on "id_duedate_op_2" "radio"
    And I set the following fields to these values:
      | duedate[day]   | ## today ##j##      |
      | duedate[month] | ## today ##M##      |
      | duedate[year]  | ## +2 years ## Y ## |
    And I press "Save and display"
    And I trigger cron
    And I am on "Course 3" course homepage
    And I navigate to my "Course Completion Report" report
    And I follow "Show more..."
    And I set the field "course-fullname" to "Course 3"
    And I click on "Search" "button" in the ".fitem_actionbuttons" "css_element"
    Then I should see "## +2 years ##j M Y##" in the "User 1 User 1" "table_row"
    And I should see "## +2 years ##j M Y##" in the "User 2 User 2" "table_row"
    When I log out
    And I log in as "user1"
    Then I should see "Course 3"
    And I should see "## +2 years ##j M##"
    And I log out
    And I log in as "user2"
    Then I should see "Course 3"
    And I should see "## +2 years ##j M##"
    When I log out
    And I log in as "admin"
    And I am on "Course 3" course homepage
    And I navigate to "Edit settings" in current page administration
    And I expand all fieldsets
    # Now set the due date to None
    And I click on "id_duedate_op_0" "radio"
    And I press "Save and display"
    And I trigger cron
    And I am on "Course 3" course homepage
    And I navigate to my "Course Completion Report" report
    And I follow "Show more..."
    And I set the field "course-fullname" to "Course 3"
    And I click on "Search" "button" in the ".fitem_actionbuttons" "css_element"
    Then I should not see "## +2 years ##j M Y##" in the "User 1 User 1" "table_row"
    And I should not see "## +2 years ##j M Y##" in the "User 2 User 2" "table_row"
    When I log out
    And I log in as "user1"
    Then I should see "Course 3"
    And I should not see "Due on"

  Scenario: Test fixed due date with set audience enrolment
    Given the following "cohorts" exist:
      | name       | idnumber |
      | Audience 1 | a1       |
    And the following "cohort members" exist:
      | user  | cohort |
      | user1 | a1     |
    When I log in as "admin"
    And I am on "Course 1" course homepage
    And I add "Audience sync" enrolment method with:
      | Audience | Audience 1 |
    And I run the scheduled task "enrol_cohort\task\sync_members"
    And I trigger cron
    And I am on "Course 1" course homepage
    And I navigate to my "Course Completion Report" report
    And I follow "Show more..."
    And I set the field "course-fullname" to "Course 1"
    And I click on "Search" "button" in the ".fitem_actionbuttons" "css_element"
    Then I should see "## +3 days ##j M Y##" in the "User 1 User 1" "table_row"
    When I log out
    And I log in as "user1"
    Then I should see "Course 1"
    And I should see "## +3 days ##j M##"
    When I log out
    And I log in as "admin"
    And I add "User 2 User 2 (user2@example.com)" user to "Audience 1" cohort members
    And I run the scheduled task "enrol_cohort\task\sync_members"
    And I trigger cron
    And I am on "Course 1" course homepage
    And I navigate to my "Course Completion Report" report
    And I follow "Show more..."
    And I set the field "course-fullname" to "Course 1"
    And I click on "Search" "button" in the ".fitem_actionbuttons" "css_element"
    Then I should see "## +3 days ##j M Y##" in the "User 2 User 2" "table_row"
    When I log out
    And I log in as "user2"
    Then I should see "Course 1"
    And I should see "## +3 days ##j M##"

  Scenario: Test relative due date with set audience enrolment
    Given the following "cohorts" exist:
      | name       | idnumber |
      | Audience 1 | a1       |
    And the following "cohort members" exist:
      | user  | cohort |
      | user1 | a1     |
    When I log in as "admin"
    And I am on "Course 2" course homepage
    And I add "Audience sync" enrolment method with:
      | Audience | Audience 1 |
    And I run the scheduled task "enrol_cohort\task\sync_members"
    And I trigger cron
    And I am on "Course 2" course homepage
    And I navigate to my "Course Completion Report" report
    And I follow "Show more..."
    And I set the field "course-fullname" to "Course 2"
    And I click on "Search" "button" in the ".fitem_actionbuttons" "css_element"
    Then I should see "## +5 days ##j M Y##" in the "User 1 User 1" "table_row"
    When I log out
    And I log in as "user1"
    Then I should see "Course 2"
    And I should see "## +5 days ##j M##"
    When I log out
    And I log in as "admin"
    And I add "User 2 User 2 (user2@example.com)" user to "Audience 1" cohort members
    And I run the scheduled task "enrol_cohort\task\sync_members"
    And I trigger cron
    And I am on "Course 2" course homepage
    And I navigate to my "Course Completion Report" report
    And I follow "Show more..."
    And I set the field "course-fullname" to "Course 2"
    And I click on "Search" "button" in the ".fitem_actionbuttons" "css_element"
    Then I should see "## +5 days ##j M Y##" in the "User 1 User 1" "table_row"
    And I should see "## +5 days ##j M Y##" in the "User 2 User 2" "table_row"
    When I log out
    And I log in as "user2"
    Then I should see "Course 2"
    And I should see "## +5 days ##j M##"

  Scenario: Test fixed due date with dynamic audience enrolment
    Given the following "cohorts" exist:
      | name       | idnumber | cohorttype |
      | Audience 1 | a1       | 2          |
    And I log in as "admin"
    And I am on "Course 1" course homepage
    And I add "Audience sync" enrolment method with:
      | Audience | Audience 1 |
    And I run the scheduled task "enrol_cohort\task\sync_members"
    And I trigger cron
    And I am on "Course 1" course homepage
    And I navigate to "Audiences" node in "Site administration > Audiences"
    And I follow "Audience 1"
    And I switch to "Rule sets" tab
    And I set the field "id_addrulesetmenu" to "First name"
    And I wait "1" seconds
    And I set the field "id_equal" to "starts with"
    And I set the field "listofvalues" to "User"
    And I click on "Save" "button" in the "Add rule" "totaradialogue"
    And I wait "1" seconds
    And I press "Approve changes"
    # Now sync the new dynamic members
    And I run the scheduled task "totara_cohort\task\update_cohort_task"
    And I run the scheduled task "enrol_cohort\task\sync_members"
    And I run the scheduled task "core\task\completion_regular_task"
    And I am on "Course 1" course homepage
    And I navigate to my "Course Completion Report" report
    And I follow "Show more..."
    And I set the field "course-fullname" to "Course 1"
    And I click on "Search" "button" in the ".fitem_actionbuttons" "css_element"
    Then I should see "## +3 days ##j M Y##" in the "User 1 User 1" "table_row"
    And I should see "## +3 days ##j M Y##" in the "User 2 User 2" "table_row"
    And I should see "## +3 days ##j M Y##" in the "User 3 User 3" "table_row"
    When I log out
    And I log in as "user1"
    Then I should see "Course 1"
    And I should see "## +3 days ##j M##"
    When I log out
    And I log in as "user2"
    Then I should see "Course 1"
    And I should see "## +3 days ##j M##"
    When I log out
    And I log in as "admin"
    # Now create a new user who should dynamically be added to audience
    Given the following "users" exist:
      | username | firstname | lastname | email                 |
      | user4    | User 4    | User 4   | testuser1@example.com |
    # Now sync the new dynamic members
    And I run the scheduled task "totara_cohort\task\update_cohort_task"
    And I run the scheduled task "enrol_cohort\task\sync_members"
    And I run the scheduled task "core\task\completion_regular_task"
    And I am on "Course 1" course homepage
    And I navigate to my "Course Completion Report" report
    And I follow "Show more..."
    And I set the field "course-fullname" to "Course 1"
    And I click on "Search" "button" in the ".fitem_actionbuttons" "css_element"
    Then I should see "## +3 days ##j M Y##" in the "User 4 User 4" "table_row"
    When I log out
    And I log in as "user4"
    Then I should see "Course 1"
    And I should see "## +3 days ##j M##"

  Scenario: Test relative due date with dynamic audience enrolment
    Given the following "cohorts" exist:
      | name       | idnumber | cohorttype |
      | Audience 1 | a1       | 2          |
    And I log in as "admin"
    And I am on "Course 2" course homepage
    And I add "Audience sync" enrolment method with:
      | Audience | Audience 1 |
    And I run the scheduled task "enrol_cohort\task\sync_members"
    And I trigger cron
    And I am on "Course 2" course homepage
    And I navigate to "Audiences" node in "Site administration > Audiences"
    And I follow "Audience 1"
    And I switch to "Rule sets" tab
    And I set the field "id_addrulesetmenu" to "First name"
    And I wait "1" seconds
    And I set the field "id_equal" to "starts with"
    And I set the field "listofvalues" to "User"
    And I click on "Save" "button" in the "Add rule" "totaradialogue"
    And I wait "1" seconds
    And I press "Approve changes"
    # Now sync the new dynamic members
    And I run the scheduled task "totara_cohort\task\update_cohort_task"
    And I run the scheduled task "enrol_cohort\task\sync_members"
    And I run the scheduled task "core\task\completion_regular_task"
    And I am on "Course 2" course homepage
    And I navigate to my "Course Completion Report" report
    And I follow "Show more..."
    And I set the field "course-fullname" to "Course 2"
    And I click on "Search" "button" in the ".fitem_actionbuttons" "css_element"
    Then I should see "## +5 days ##j M Y##" in the "User 1 User 1" "table_row"
    And I should see "## +5 days ##j M Y##" in the "User 2 User 2" "table_row"
    When I log out
    And I log in as "user1"
    Then I should see "Course 2"
    And I should see "## +5 days ##j M##"
    When I log out
    And I log in as "user2"
    Then I should see "Course 2"
    And I should see "## +5 days ##j M##"
    When I log out
    And I log in as "admin"
    # Now create a new user who should dynamically be added to audience
    Given the following "users" exist:
      | username | firstname | lastname | email                 |
      | user4    | User 4    | User 4   | testuser1@example.com |
    # Now sync the new dynamic members
    And I run the scheduled task "totara_cohort\task\update_cohort_task"
    And I run the scheduled task "enrol_cohort\task\sync_members"
    And I run the scheduled task "core\task\completion_regular_task"
    And I am on "Course 2" course homepage
    And I navigate to my "Course Completion Report" report
    And I follow "Show more..."
    And I set the field "course-fullname" to "Course 2"
    And I click on "Search" "button" in the ".fitem_actionbuttons" "css_element"
    Then I should see "## +5 days ##j M Y##" in the "User 4 User 4" "table_row"
    When I log out
    And I log in as "user4"
    Then I should see "Course 2"
    And I should see "## +5 days ##j M##"