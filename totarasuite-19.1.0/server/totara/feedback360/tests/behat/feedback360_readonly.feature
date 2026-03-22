@totara @totara_feedback360 @javascript
Feature: Read only feedback360

  Background:
    Given I am on a totara site
    And I enable the "feedback360" advanced feature
    And the following "users" exist:
      | username | firstname | lastname | email             |
      | user1    | User      | One      | user1@example.com |
      | user2    | User      | Two      | user2@example.com |
      | user3    | User      | Three    | user3@example.com |
      | user4    | User      | Four     | user4@example.com |
      | user5    | User      | Five     | user5@example.com |
      | user6    | User      | Six      | user6@example.com |
      | user7    | User      | Seven    | user7@example.com |
    And the following "cohorts" exist:
      | name     | idnumber |
      | Cohort 1 | CH1      |
      | Cohort 2 | CH2      |
    And the following "cohort members" exist:
      | user  | cohort |
      | user1 | CH1    |
      | user2 | CH1    |
      | user3 | CH1    |
      | user4 | CH1    |
      | user5 | CH1    |
      | user6 | CH1    |
    And the following "feedback360" exist in "totara_feedback360" plugin:
      | name            |
      | Normal feedback |
    And the following "questions" exist in "totara_feedback360" plugin:
      | feedback360     | name      | type | default | ExtraInfo |
      | Normal feedback | question1 | text |         |           |
    And the following "assignments" exist in "totara_feedback360" plugin:
      | feedback360     | type     | id  |
      | Normal feedback | audience | CH1 |

  Scenario: check read only manage page of the legacy 360 feedback
    When I log in as "admin"
    And I navigate to "Manage 360° Feedback (legacy)" node in "Site administration > Legacy features"
    Then ".singlebutton" "css_element" should not exist in the "#region-main" "css_element"
    And I should not see "Activate" in the "Normal feedback" "table_row"
    And I should not see "Settings" in the "Normal feedback" "table_row"
    And I should not see "copy" in the "Normal feedback" "table_row"

  Scenario: check read only individual page of the legacy 360 feedback
    When I log in as "admin"
    And I navigate to "Manage 360° Feedback (legacy)" node in "Site administration > Legacy features"
    Then I should see "This legacy Performance Management feature is surpassed by new features which were introduced in a recent site upgrade"
    And I should see "Legacy 360 feedback has been made read-only. Active 360 feedback has been closed, and draft 360 feedback cannot be edited or activated"

    Then I click on "Normal feedback" "link" in the "Normal feedback" "table_row"
    And I should see "Legacy 360 feedback has been made read-only. Active 360 feedback has been closed, and draft 360 feedback cannot be edited or activated"
    And I should not see "Activate now"
    And "#id_submitbutton" "css_element" should not exist in the ".mform" "css_element"
    Then I click on "Content" "link" in the ".tabtree" "css_element"
    And I should see "Legacy 360 feedback has been made read-only. Active 360 feedback has been closed, and draft 360 feedback cannot be edited or activated"
    And "#fgroup_id_addquestgroup" "css_element" should not exist in the ".quest-container" "css_element"
    Then I click on "Assignments" "link" in the ".tabtree" "css_element"
    And I should see "Legacy 360 feedback has been made read-only. Active 360 feedback has been closed, and draft 360 feedback cannot be edited or activated"
    And ".custom-select" "css_element" should not exist in the "#region-main" "css_element"

  Scenario: check read only user facing pages of the legacy 360 feedback
    When I activate the "Normal feedback" feedback360
    And I log in as "user1"
    And I am on "360° Feedback" page
    Then I should see "Normal feedback"
    And I should see "Legacy 360 feedback has been made read-only. Active 360 feedback has been closed"

    When I follow "Normal feedback"
    Then I should see "This legacy 360 feedback has been closed and is read-only"
