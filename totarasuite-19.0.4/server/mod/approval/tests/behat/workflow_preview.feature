@totara @mod_approval @javascript @vuejs
Feature: Test approval workflow preview
  Background:
    Given I am on a totara site
    And the following "cohorts" exist:
      | name | idnumber |
      | aud1 | AUD001   |
    And the following "users" exist:
      | username  | firstname | lastname | email                 |
      | applicant | Applie    | Kaant    | applicant@example.com |
      | approver  | App Rou   | Vre      | approver@example.com  |
    And the following "cohort members" exist:
      | user      | cohort |
      | applicant | AUD001 |
    And the following "workflow types" exist in "mod_approval" plugin:
      | name              |
      | Hot workflow type |
    And the following "forms" exist in "mod_approval" plugin:
      | title     |
      | Test form |
    And the following "form versions" exist in "mod_approval" plugin:
      | form      | version | json_schema            |
      | Test form | 1       | test_form_lang_string  |
    And the following "workflows" exist in "mod_approval" plugin:
      | name          | description               | id_number | form      | workflow_type     | type   | identifier | assignment_id_number |
      | Cool workflow | Mild workflow description | WKF001    | Test form | Hot workflow type | cohort | AUD001     | AUD001               |
    And the following "workflow versions" exist in "mod_approval" plugin:
      | workflow | form_version | status |
      | WKF001   | 1            | draft  |
    And the following "workflow stages" exist in "mod_approval" plugin:
      | workflow | name         | type            |
      | WKF001   | First stage  | FORM_SUBMISSION |
      | WKF001   | Second stage | APPROVALS       |
      | WKF001   | Last stage   | FINISHED        |
    And the following "approval levels" exist in "mod_approval" plugin:
      | workflow_stage | name        |
      | Second stage   | First level |
    And the following "approvers" exist in "mod_approval" plugin:
      | assignment | approval_level | type | identifier |
      | AUD001     | First level    | user | approver   |
    And I publish the "Cool workflow" workflow
    And I run the scheduled task "mod_approval\task\role_map_regenerate_all"

  Scenario: View print page when login as applicant
    Given I log in as "applicant"
    And I visit the Applications Dashboard
    And I click on "New application" "button"
    And I activate the weka editor with css ".tui-mod_approval-applicationEdit__schemaForm"
    And I type "colorful images" in the weka editor
    And I wait for pending js
    Then I set the following fields to these values:
      | request   | Test          |
      | complete  | Yes           |
    And I click on "More actions" "button"
    And I click on "Print preview" option in the dropdown menu
    Then I should see "Discussed with manager?"
