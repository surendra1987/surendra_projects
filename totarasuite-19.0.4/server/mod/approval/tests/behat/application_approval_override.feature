@totara @mod_approval @mod_approval_noengage @javascript @vuejs
Feature: Approval workflow assignment override approvers on applications

  Background:
    Given I am on a totara site
    And I disable the "engage_resources" advanced feature
    And I disable the "container_workspace" advanced feature
    And the following "users" exist:
      | username   | firstname   | lastname | email                  |
      | applicant  | App Li      | Kant     | applicant@example.com  |
      | applicant2 | Sub Org     | Kant     | applicant2@example.com |
      | applicant3 | Sub-sub Org | Kant     | applicant3@example.com |
      | approver1  | App Rou     | Vee      | approver1@example.com  |
      | approver2  | Vee Rou     | App      | approver2@example.com  |
      | approver3  | Rou Rou     | Rou      | approver3@example.com  |
      | manager    | Mana        | Geer     | manager@example.com    |
    And the following "role assigns" exist:
      | user    | role    | contextlevel | reference |
      | manager | manager | System       |           |
    And the following "organisation frameworks" exist in "totara_hierarchy" plugin:
      | fullname | idnumber |
      | orgfw    | orgfw    |
    And the following "organisations" exist in "totara_hierarchy" plugin:
      | org_framework | idnumber | fullname    | shortname | org_parent |
      | orgfw         | ORG001   | Test org    | org1      |            |
      | orgfw         | ORG002   | Sub org     | org2      | ORG001     |
      | orgfw         | ORG003   | Sub-sub org | org3      | ORG002     |
    And the following job assignments exist:
      | user       | fullname                   | idnumber | organisation | position |
      | applicant2 | Sub-org job assignment     | org01ja  | ORG002       |          |
      | applicant3 | Sub-sub-org job assignment | org02ja  | ORG003       |          |
    And the following "workflow types" exist in "mod_approval" plugin:
      | name               |
      | Test workflow type |
    And the following "forms" exist in "mod_approval" plugin:
      | title     |
      | Test form |
    And the following "form versions" exist in "mod_approval" plugin:
      | form      | version | json_schema |
      | Test form | 1       | test1       |
    And the following "workflows" exist in "mod_approval" plugin:
      | name          | description               | id_number | form      | workflow_type      | type         | identifier |
      | Test workflow | test workflow description | WKF001    | Test form | Test workflow type | organisation | ORG001     |
    And the following "workflow stages" exist in "mod_approval" plugin:
      | workflow | name         | type            |
      | WKF001   | Test stage 1 | FORM_SUBMISSION |
      | WKF001   | Test stage 2 | APPROVALS       |
      | WKF001   | Test stage 3 | FINISHED        |
    And the following "form views" exist in "mod_approval" plugin:
      | workflow_stage | field_key | required |
      | Test stage 1   | food      | true     |
      | Test stage 2   | food      | true     |
    And the following "assignments" exist in "mod_approval" plugin:
      | name                   | id_number | workflow | type         | identifier |
      | Sub-org assignment     | ASS002    | WKF001   | organisation | ORG002     |
      | Sub-sub-org assignment | ASS003    | WKF001   | organisation | ORG003     |
    And the following "approvers" exist in "mod_approval" plugin:
      | assignment | approval_level | type | identifier |
      | ORG001     | Level 1        | user | approver1  |
      | ASS002     | Level 1        | user | approver2  |
    And I run the scheduled task "mod_approval\task\role_map_regenerate_all"

  Scenario: Correct approver is shown on application when direct
    Given the following "applications" exist in "mod_approval" plugin:
      | title            | user       | workflow | assignment | creator    |
      | Test application | applicant2 | WKF001   | ASS002     | applicant2 |
    And the following "application submissions" exist in "mod_approval" plugin:
      | application      | user       | form_data        |
      | Test application | applicant2 | {"food":"pasta"} |
    And the following "application actions" exist in "mod_approval" plugin:
      | application      | user       | action |
      | Test application | applicant2 | submit |
    When I log in as "applicant2"
    And I visit the Applications Dashboard
    And I switch to "Your applications" tui tab
    And I click on "Test workflow type" "link"
    And I click on "View Level 1 approvers" "button"
    Then I should see "Vee Rou App"
    And I should not see "App Rou Vee"

  Scenario: Correct approver is shown on application when inherited
    Given the following "applications" exist in "mod_approval" plugin:
      | title             | user       | workflow | assignment | creator    |
      | Test application  | applicant2 | WKF001   | ASS002     | applicant2 |
      | Test application2 | applicant3 | WKF001   | ASS003     | applicant3 |
    And the following "application submissions" exist in "mod_approval" plugin:
      | application       | user       | form_data        |
      | Test application  | applicant2 | {"food":"pasta"} |
      | Test application2 | applicant3 | {"food":"pizza"} |
    And the following "application actions" exist in "mod_approval" plugin:
      | application       | user       | action |
      | Test application  | applicant2 | submit |
      | Test application2 | applicant3 | submit |
    When I log in as "manager"
    And I navigate to "Manage approval workflows" node in "Site administration > Approval workflows"
    And I click on "Test workflow" "link"
    And I follow "Test stage 2"
    And I click on "Approvals" "link" in the ".tui-mod_approval-workflowEdit__subSections" "css_element"
    And I click on "Configure approval overrides" "button"
    And I open the dropdown menu in the tui datatable row with "Sub org" "Assignment name"
    And I click on "Edit" option in the dropdown menu
    And I click on "Override Level 1" tui "checkbox"
    When I click on "Save" "button_exact"
    Then I should see "Overrides saved successfully" in the tui success notification toast
    And I log out
    When I log in as "applicant2"
    And I visit the Applications Dashboard
    And I switch to "Your applications" tui tab
    And I click on "Test workflow type" "link"
    And I click on "View Level 1 approvers" "button"
    Then I should not see "Vee Rou App"
    And I should see "App Rou Vee"
    And I log out
    When I log in as "applicant3"
    And I visit the Applications Dashboard
    And I switch to "Your applications" tui tab
    And I click on "Test workflow type" "link"
    And I click on "View Level 1 approvers" "button"
    Then I should not see "Vee Rou App"
    And I should see "App Rou Vee"
    And I log out

    When I log in as "manager"
    And I navigate to "Manage approval workflows" node in "Site administration > Approval workflows"
    And I click on "Test workflow" "link"
    And I follow "Test stage 2"
    And I click on "Approvals" "link" in the ".tui-mod_approval-workflowEdit__subSections" "css_element"
    And I click on "Configure approval overrides" "button"
    And I open the dropdown menu in the tui datatable row with "Sub org" "Assignment name"
    And I click on "Edit" option in the dropdown menu
    And I click on "Override Level 1" tui "checkbox"
    And I click on "Tag list Individuals for Level 1" "button"
    And I click on "Rou Rou Rou" option in the dropdown menu
    And I click on "Vee Rou App" option in the dropdown menu
    And I click on "Tag list Individuals for Level 1" "button"
    When I click on "Save" "button_exact"
    Then I should see "Overrides saved successfully" in the tui success notification toast
    And I log out
    When I log in as "applicant2"
    And I visit the Applications Dashboard
    And I switch to "Your applications" tui tab
    And I click on "Test workflow type" "link"
    And I click on "View Level 1 approvers" "button"
    Then I should see "Vee Rou App"
    And I should see "Rou Rou Rou"
    And I should not see "App Rou Vee"
    And I log out
    When I log in as "applicant3"
    And I visit the Applications Dashboard
    And I switch to "Your applications" tui tab
    And I click on "Test workflow type" "link"
    And I click on "View Level 1 approvers" "button"
    Then I should see "Vee Rou App"
    And I should see "Rou Rou Rou"
    And I should not see "App Rou Vee"


