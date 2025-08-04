@javascript @core_enrol @mod_approval
Feature: A learner can apply for a course using the course enrolment application workflow,
  and approvers can make decisions based on the application.

  Background:
    Given the following config values are set as admin:
      | config                   | value | plugin       |
      | enableapproval_workflows | 1     | mod_approval |
    And there is a course enrolment workflow with the name "Course Approval Workflow"
    And the following "users" exist:
      | username | firstname | lastname | email               |
      | teacher  | Teacher   | 1        | teacher@example.com |
      | student  | Student   | 1        | student@example.com |
    And the following job assignments exist:
      | user    | manager           |
      | student | teacher           |
    And the following "courses" exist:
      | fullname | shortname | format |
      | Course 1 | C1        | topics |
    And I log in as "admin"
    And I am on "Course 1" course homepage
    And I add "Self enrolment" enrolment method with:
      | Custom instance name | Workflow enrolment |
      | Approve enrolments   | Enrolment workflow |
    # NB: The name in "Approve enrolments" is the name of the workflow type, not the name of the workflow instance
    And I navigate to "Navigation > Main menu" in site administration
    And I click on "Edit" "link" in the "Approval" "table_row"
    And I set the following Totara form fields to these values:
      | Parent item | Top |
    And I press "Save changes"
    And I run the scheduled task "mod_approval\task\role_map_regenerate_all"
    And I log out

  Scenario: A user can apply for a course using the workflow
    Given I log in as "student"
    And I am on "Course 1" course enrol page
    And I click on "Request approval" "button"
    And I activate the weka editor "Reason for enrolling in this course"
    And I type "<p>a reason</p>" in the weka editor
    And I click on "Submit" "button"
    And I wait "1" seconds
    And I click on "Submit" "button" in the ".tui-modal" "css_element"
    And I log out
    And I log in as "teacher"
    And I visit the Applications Dashboard
    And I click on "Respond" "button"
    And I click on "Approve" "button"
    And I log out
    And I log in as "student"
    When I am on "Course 1" course homepage
    Then I should not see "Enrolment options"

  Scenario: An approver can deny an enrolment request, and the learner can resubmit
    Given I log in as "student"
    And I am on "Course 1" course enrol page
    And I click on "Request approval" "button"
    And I activate the weka editor "Reason for enrolling in this course"
    And I type "<p>a reason</p>" in the weka editor
    And I click on "Submit" "button"
    And I wait "1" seconds
    And I click on "Submit" "button" in the ".tui-modal" "css_element"
    And I log out
    And I log in as "teacher"
    And I visit the Applications Dashboard
    And I click on "Respond" "button"
    And I click on "Reject" "button"
    And I type "Rejected" in the weka editor
    And I click on "Confirm" "button"
    And I log in as "student"
    When I am on "Course 1" course homepage
    Then I should not see "Topic 1"
