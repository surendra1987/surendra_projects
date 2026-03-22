@totara @totara_reportbuilder @javascript
Feature: Check that enforced visibility restriction in course completion report can be configured

  Background:
    Given I am on a totara site
    And the following "users" exist:
      | username | firstname | lastname | email                |
      | trainer1 | Trainer   | One      | trainer1@example.com |
      | learner1 | Learner   | One      | learner1@example.com |
      | learner2 | Learner   | Two      | learner2@example.com |
    And the following "courses" exist:
      | fullname | shortname | enablecompletion |
      | Course 1 | C1        | 1                |
    And the following "course enrolments" exist:
      | user     | course | role           |
      | trainer1 | C1     | editingteacher |
      | learner2 | C1     | student        |
    And the following "cohorts" exist:
      | name       | idnumber |
      | Audience 1 | A1       |
    And the following "cohort members" exist:
      | user     | cohort |
      | learner1 | A1     |
    And the following "standard_report" exist in "totara_reportbuilder" plugin:
      | fullname                            | shortname                                  | source            |
      | Course Completion Visibility Report | report_course_completion_visibility_report | course_completion |
      | Courses Report                      | report_course_courses_report               | courses           |

  Scenario: Verify that hard-coded course visibility restrictions apply for users on the Course Completion report
    When I log in as "admin"
    And I set the following administration settings values:
      | Enable audience-based visibility | 1 |
    And I navigate to "Courses and categories" node in "Site administration > Courses"
    And I click on "Miscellaneous" "link"
    And I click on "Course 1" "link"
    And I click on "Edit" "link" in the ".course-detail-listing-actions" "css_element"
    And I set the following fields to these values:
      | Visibility | Enrolled users only |
    And I press "Save and display"
    And I navigate to my "Course Completion Visibility Report" report
    And I press "Edit this report"
    And I switch to "Access" tab
    And I set the field "All users can view this report" to "1"
    And I press "Save changes"
    And I log out
    And I log in as "learner2"
    When I navigate to my "Course Completion Visibility Report" report
    Then I should see "Course 1" in the "course_courselink" report column for "Learner Two"
    When I log out
    And I log in as "learner1"
    And I navigate to my "Course Completion Visibility Report" report
    Then I should not see "Course 1" in the "course_courselink" report column for "Learner Two"
    When I log out
    And I log in as "admin"
    And I navigate to "Courses and categories" node in "Site administration > Courses"
    And I click on "Miscellaneous" "link"
    And I click on "Course 1" "link"
    And I click on "Edit" "link" in the ".course-detail-listing-actions" "css_element"
    And I set the following fields to these values:
      | Visibility | Enrolled users and members of the selected audiences |
    And I press "Add visible audiences"
    And I follow "Audience 1"
    And I click on "OK" "button" in the "Course audiences (visible)" "totaradialogue"
    And I press "Save and display"
    And I log out
    And I log in as "learner2"
    And I navigate to my "Course Completion Visibility Report" report
    Then I should see "Course 1" in the "course_courselink" report column for "Learner Two"
    When I log out
    And I log in as "learner1"
    And I navigate to my "Course Completion Visibility Report" report
    Then I should see "Course 1" in the "course_courselink" report column for "Learner Two"

  Scenario: Verify that hard-coded course visibility restrictions can be disabled
    When I log in as "admin"
    And I set the following administration settings values:
      | Enable audience-based visibility | 1 |
    And I navigate to "Courses and categories" node in "Site administration > Courses"
    And I click on "Miscellaneous" "link"
    And I click on "Course 1" "link"
    And I click on "Edit" "link" in the ".course-detail-listing-actions" "css_element"
    And I set the following fields to these values:
      | Visibility | No users |
    And I press "Save and display"
    And I navigate to my "Course Completion Visibility Report" report
    And I press "Edit this report"
    And I switch to "Access" tab
    And I set the field "All users can view this report" to "1"
    And I press "Save changes"
    And I switch to "Content" tab
    Then I should see "This report has visibility restrictions enforced. To report on all records, disable the enforced visibility check on the general tab." in the ".alert-message" "css_element"
    # Don't save this we only want to make sure there's no other reason the checkbox is disable
    When I set the field "id_contentenabled_2" to "1"
    Then the "Show records based on audience, course and workspace visibility restrictions" "field" should be disabled
    When I switch to "General" tab
    Then I should see "Disable enforced visibility checks"
    And the field "Disable enforced visibility checks" matches value "0"
    When I set the field "Disable enforced visibility checks" to "1"
    And I press "Save changes"
    And I switch to "Content" tab
    Then I should not see "This report has visibility restrictions enforced. To report on all records, disable the enforced visibility check on the general tab."
    # Don't save this we only want to make sure there's no other reason the checkbox is disable
    When I set the field "id_contentenabled_2" to "1"
    And the "Show records based on audience, course and workspace visibility restrictions" "field" should be enabled
    When I log out
    And I log in as "learner2"
    And I navigate to my "Course Completion Visibility Report" report
    Then I should see "Course 1" in the "course_courselink" report column for "Learner Two"

  Scenario: Check that report who has no hard-coded restrictions don't show the setting
    When I log in as "admin"
    And I navigate to my "Courses Report" report
    And I press "Edit this report"
    And I switch to "Content" tab
    Then I should not see "This report has visibility restrictions enforced. To report on all records, disable the enforced visibility check on the general tab."
    When I switch to "General" tab
    Then I should not see "Disable enforced visibility checks"