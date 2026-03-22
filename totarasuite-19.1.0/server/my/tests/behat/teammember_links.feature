@totara @totara_reportbuilder @totara_appraisal @totara_feedback360 @totara_hierarchy_goals @javascript @core_my
Feature: Show only links to member information the manager has permission to see
  In order to prevent managers receiving no permission errors
  As a manager
  I need to see only links to member information that I have permission to see

  Background:
    Given I am on a totara site
    And I enable the "appraisals" advanced feature
    And I enable the "feedback360" advanced feature
    And the following "users" exist:
      | username | firstname | lastname | email                |
      | manager1 | Manager   | 1        | manager1@example.com |
      | user1    | User      | 1        | user1@example.com    |
      | user2    | User      | 2        | user2@example.com    |
    And the following job assignments exist:
      | user     | manager  | tempmanager | tempmanagerexpirydate |
      | user1    | manager1 | user2       | 2524474800            |

  Scenario: All links are shown with default permissions
    Given I log in as "manager1"
    When I am on "Team" page
    Then "User 1" "link" should exist in the "team_members" "table"
    And I should see the "User 1" image in the "User 1" "table_row"
    And "Plans" "link" should exist in the "User 1" "table_row"
    And "Profile" "link" should exist in the "User 1" "table_row"
    And "Bookings" "link" should exist in the "User 1" "table_row"
    And "Records" "link" should exist in the "User 1" "table_row"
    And "Appraisals (legacy)" "link" should exist in the "User 1" "table_row"
    And "360° Feedback (legacy)" "link" should exist in the "User 1" "table_row"
    And "Goals" "link" should exist in the "User 1" "table_row"
    And "Required" "link" should exist in the "User 1" "table_row"
    And "Competency profile" "link" should exist in the "User 1" "table_row"
    And "Evidence" "link" should exist in the "User 1" "table_row"
    And "Performance overview" "link" should exist in the "User 1" "table_row"

  Scenario: Plans link is not available if learningplans feature is not visible
    Given I log in as "admin"
    And I navigate to "Learn settings" node in "Site administration > System information > Configure features"
    And I set the field "Enable Learning Plans" to "0"
    And I press "Save changes"
    And I log out
    When I log in as "manager1"
    And I am on "Team" page
    Then "User 1" "link" should exist in the "team_members" "table"
    And "Plans" "link" should not exist in the "User 1" "table_row"

  Scenario: Plans link is not available if the manager may not view member plans
    Given I log in as "admin"
    And the following "system role assigns" exist:
      | user     | role         |
      | manager1 | staffmanager |
    And the following "permission overrides" exist:
      | capability                       | permission | role            | contextlevel | reference |
      | totara/plan:accessanyplan        | Prohibit   | staffmanager    | System       |           |
      | totara/plan:manageanyplan        | Prohibit   | staffmanager    | System       |           |
      | totara/plan:accessplan           | Prohibit   | staffmanager    | System       |           |
    And I log out
    When I log in as "manager1"
    And I am on "Team" page
    Then "User 1" "link" should exist in the "team_members" "table"
    And "Plans" "link" should not exist in the "User 1" "table_row"

  Scenario: Appraisals link is not available if appraisals feature is not visible
    Given I log in as "admin"
    And I navigate to "Perform settings" node in "Site administration > System information > Configure features"
    And I set the field "Enable legacy appraisals" to "0"
    And I press "Save changes"
    And I log out
    When I log in as "manager1"
    And I am on "Team" page
    Then "User 1" "link" should exist in the "team_members" "table"
    And "Appraisals (legacy)" "link" should not exist in the "User 1" "table_row"

  Scenario: Appraisals link is not available to temporary managers. Other links are
    Given I log in as "user2"
    And I am on "Team" page
    Then "User 1" "link" should exist in the "team_members" "table"
    And "Appraisals (legacy)" "link" should not exist in the "User 1" "table_row"
    And I should see the "User 1" image in the "User 1" "table_row"
    And "Plans" "link" should exist in the "User 1" "table_row"
    And "Profile" "link" should exist in the "User 1" "table_row"
    And "Bookings" "link" should exist in the "User 1" "table_row"
    And "Records" "link" should exist in the "User 1" "table_row"
    And "360° Feedback (legacy)" "link" should exist in the "User 1" "table_row"
    And "Goals" "link" should exist in the "User 1" "table_row"
    And "Required" "link" should exist in the "User 1" "table_row"

  Scenario: 360 Feedback link is not available if feedback360 feature is not visible
    Given I log in as "admin"
    And I navigate to "Perform settings" node in "Site administration > System information > Configure features"
    And I set the field "Enable legacy 360 feedback" to "0"
    And I press "Save changes"
    And I log out
    When I log in as "manager1"
    And I am on "Team" page
    Then "User 1" "link" should exist in the "team_members" "table"
    And "360° Feedback (legacy)" "link" should not exist in the "User 1" "table_row"

  Scenario: 360 Feedback link is not available if manager can't view staff feedback360
    Given I log in as "admin"
    And the following "permission overrides" exist:
      | capability                                        | permission | role          | contextlevel | reference |
      | totara/feedback360:viewstaffreceivedfeedback360   | Prohibit   | staffmanager  | User         | user1     |
      | totara/feedback360:viewstaffrequestedfeedback360  | Prohibit   | staffmanager  | User         | user1     |
    And I log out
    When I log in as "manager1"
    And I am on "Team" page
    Then "User 1" "link" should exist in the "team_members" "table"
    And "360° Feedback (legacy)" "link" should not exist in the "User 1" "table_row"

  Scenario: Goals link is not available if goals feature is not visible
    Given I log in as "admin"
    And I disable the "goals" advanced feature
    And I disable the "perform_goals" advanced feature
    And I log out
    When I log in as "manager1"
    And I am on "Team" page
    Then "User 1" "link" should exist in the "team_members" "table"
    And "Goals" "link" should not exist in the "User 1" "table_row"

  Scenario: Goals link is not available if manager can't view staff goals
    Given I log in as "admin"
    And the following "permission overrides" exist:
      | capability                              | permission | role          | contextlevel | reference |
      | totara/hierarchy:viewstaffcompanygoal   | Prohibit   | staffmanager  | User         | user1     |
      | totara/hierarchy:viewstaffpersonalgoal  | Prohibit   | staffmanager  | User         | user1     |
    And I log out
    When I log in as "manager1"
    And I am on "Team" page
    Then "User 1" "link" should exist in the "team_members" "table"
    And "Goals" "link" should not exist in the "User 1" "table_row"

  Scenario: Required link is not available if programs and certifications features are not visible
    When I disable the "certifications" advanced feature
    And I disable the "programs" advanced feature
    And I log in as "manager1"
    And I am on "Team" page
    Then "User 1" "link" should exist in the "team_members" "table"
    And "Required" "link" should not exist in the "User 1" "table_row"

  Scenario: Performance overview link is not available if goals, competencies and activities features are not visible
    # NOTE: when we change the "Goals" setting then it disables the Competency setting.
    Given I log in as "admin"
    And I navigate to "Perform settings" node in "Site administration > System information > Configure features"
    And I set the field "Enable Performance Activities" to "0"
    And I press "Save changes"
    And I disable the "goals" advanced feature
    And I disable the "perform_goals" advanced feature
    And I log out

    When I log in as "manager1"
    And I am on "Team" page
    Then "User 1" "link" should exist in the "team_members" "table"
    And "Performance overview" "link" should not exist in the "User 1" "table_row"

  Scenario: When Totara goals transition mode is on, both perform and legacy goals links are visible
    And I log in as "admin"
    And I enable the totara goal transition mode
    And I log out

    When I log in as "manager1"
    And I am on "Team" page
    Then "User 1" "link" should exist in the "team_members" "table"
    And "Legacy goals" "link" should exist in the "User 1" "table_row"
    And "Goals" "link" should exist in the "User 1" "table_row"
    And "Performance overview" "link" should exist in the "User 1" "table_row"

