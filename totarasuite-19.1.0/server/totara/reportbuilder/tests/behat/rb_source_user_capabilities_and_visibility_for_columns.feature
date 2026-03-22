@totara @totara_reportbuilder @javascript
Feature: visibility of columns in a report with a user report source.
  Depending on the capabilities assigned to the report view user, some columns will display or hide values and icons.

  Background:
    Given I am on a totara site
    And the following "users" exist:
      | username       | firstname | lastname | email               |
      | learner1       | Geddy     | Lee      | geddyl@example.com  |
      | learner2       | Neil      | Peart    | geddyl@example.com  |
      | reportviewer1  | Alex      | Lifeson  | alexl@example.com   |
    And the following "roles" exist:
      | shortname                 |
      | Custom_Role_Report_Viewer |
    And the following "role assigns" exist:
      | user          | role                      | contextlevel | reference |
      | reportviewer1 | Custom_Role_Report_Viewer | System       |           |
    And the following "courses" exist:
      | fullname      | shortname | enablecompletion |
      | Prog Rock 101 | PR101     | 1                |
    And the following "course enrolments" exist:
      | user     | course | role           |
      | learner1 | PR101  | student        |
      | learner2 | PR101  | student        |
    And the following "standard_report" exist in "totara_reportbuilder" plugin:
      | fullname                    | shortname                          | source | accessmode |
      | Custom User Report 1        | report_custom_user_report_1        | user   | 0          |
    And I log in as "admin"
    And I navigate to my "Custom User Report 1" report
    And I press "Edit this report"
    And I switch to "Columns" tab
    And I add the "User's My Learning Icons" column to the report
    And I press "Save changes"
    And I log out

  Scenario: rb_source_user_capabilities_and_visibility_for_columns000: view report without capabilities
    Given I log in as "reportviewer1"
    When I navigate to my "Custom User Report 1" report
    # We do not expect to see icons showing up because the reportviewer user lacks capabilities needed.
    And I should not see icon "recordoflearning" in the "user_userlearningicons" report column for "Geddy Lee"
    And I should not see icon "learningplan" in the "user_userlearningicons" report column for "Geddy Lee"
    And I should not see icon "recordoflearning" in the "user_userlearningicons" report column for "Neil Peart"
    And I should not see icon "learningplan" in the "user_userlearningicons" report column for "Neil Peart"
    And I log out

  Scenario: rb_source_user_capabilities_and_visibility_for_columns001: view report with capabilities
    Given the following "permission overrides" exist:
      | capability                       | permission | role                      | contextlevel | reference |
      | moodle/user:viewalldetails       | Allow      | Custom_Role_Report_Viewer | System       |           |
      | totara/core:viewrecordoflearning | Allow      | Custom_Role_Report_Viewer | System       |           |
      | totara/plan:accessanyplan        | Allow      | Custom_Role_Report_Viewer | System       |           |
    And I log in as "reportviewer1"
    When I navigate to my "Custom User Report 1" report
    # We expect to see both sets of icons showing up because the reportviewer user has all capabilities needed.
    And I should see icon "recordoflearning" in the "user_userlearningicons" report column for "Geddy Lee"
    And I should see icon "learningplan" in the "user_userlearningicons" report column for "Geddy Lee"
    And I should see icon "recordoflearning" in the "user_userlearningicons" report column for "Neil Peart"
    And I should see icon "learningplan" in the "user_userlearningicons" report column for "Neil Peart"
