@totara @totara_feedback360 @totara_appraisal @perform @mod_perform @javascript @vuejs
Feature: Make sure user can see 360 feedback in Historic activities under Performance

  Background:
    Given I am on a totara site
    And I enable the "feedback360" advanced feature
    And I enable the "appraisals" advanced feature
    And the following "users" exist:
      | username | firstname | lastname | email             |
      | user1    | User      | One      | user1@example.com |
    And the following "cohorts" exist:
      | name     | idnumber |
      | Cohort 1 | CH1      |
    And the following "cohort members" exist:
      | user  | cohort |
      | user1 | CH1    |
    And the following config values are set as admin:
      | showhistoricactivities | 1 |
    And the following "feedback360" exist in "totara_feedback360" plugin:
      | name            |
      | Normal feedback |
    And the following "questions" exist in "totara_feedback360" plugin:
      | feedback360     | name      | type | default | ExtraInfo |
      | Normal feedback | question1 | text |         |           |
    And the following "assignments" exist in "totara_feedback360" plugin:
      | feedback360     | type     | id  |
      | Normal feedback | audience | CH1 |
    And I activate the "Normal feedback" feedback360

  Scenario: User still can see feedbacks
    And I log in as "user1"
    And I navigate to the outstanding perform activities list page
    When I click on "Historic activities" "link"
    Then I should see "Your historic activities"
    And I should see the tui datatable contains:
      | Activity title  | Type                   | Status |
      | Normal feedback | 360° Feedback (legacy) | Active |