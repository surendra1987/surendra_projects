@totara @totara_hierarchy @totara_hierarchy_goals @perform_goal @javascript
Feature: Verify creation and use of personal goals in Totara goal transition mode.

  Background:
    Given I am on a totara site
    And the following "users" exist:
      | username | firstname | lastname | email             |
      | learner1 | Learner1  | Learner1 | learner1@example.com |
      | learner2 | Learner2  | Learner2 | learner2@example.com |
      | learner3 | Learner3  | Learner3 | learner3@example.com |
      | manager1 | Manager1  | Manager1 | manager1@example.com |
    And the following job assignments exist:
      | user     | manager  |
      | learner1 | manager1 |
    And the following "cohorts" exist:
      | name       | idnumber |
      | Audience 1 | A1       |
      | Audience 2 | A2       |
      | Audience 3 | A3       |
    And the following "cohort members" exist:
      | user     | cohort |
      | learner1 | A1     |
      | learner2 | A2     |
      | learner3 | A3     |

  Scenario: Verify the learner can see the legacy personal goal when the Totara goal transition mode is on
    Given I log in as "admin"
    And I navigate to "Manage personal goal types" node in "Site administration > Legacy goals"
    And I press "Add a new personal goal type"
    And I set the following fields to these values:
      | Type full name         | Personal Goal Type 1             |
      | Goal type ID number    | PGT1                             |
      | Goal type description  | Personal Goal Type 1 description |
      | Goal type availability | Available to all users           |
    When I press "Save changes"
    Then I should see "The goal type \"Personal Goal Type 1\" has been created"
    And I log out

    When I log in as "learner1"
    And I am on "Goals" page
    And "Add personal goal" "button" should exist
    Then I press "Add personal goal"
    # Create the personal goal.
    And I set the following fields to these values:
      | Name        | Personal Goal 1             |
      | Description | Personal Goal 1 description |
      | Type        | Personal Goal Type 1        |
      | Scale       | Goal scale                  |
    When I press "Save changes"
    Then I should see "Personal Goal 1" in the ".personal_table" "css_element"
    And "//tbody/tr/td[6]/a[@class='action-icon']/span[contains(., 'Edit')]" "xpath_element" should exist
    When I click on "Personal Goal 1" "link"
    Then "//h1/a[@class='action-icon']/span[contains(., 'Edit')]" "xpath_element" should exist
    And I should see "Personal goal" in the "//div[@class='view_personal_goal']" "xpath_element"
    And I should not see "Legacy personal goal" in the "//div[@class='view_personal_goal']" "xpath_element"
    And I log out

    And I log in as "admin"
    And I enable the totara goal transition mode
    And I log out

    When I log in as "learner1"
    And I am on "Goals" page
    Then I should see "Legacy goals"
    And I should see "Legacy personal goals"
    And "Add personal goal" "button" should not exist
    And "//tbody/tr/td[6]/a[@class='action-icon']/span[contains(., 'Edit')]" "xpath_element" should not exist
    And I should see "Personal Goal 1" in the ".personal_table" "css_element"
    When I click on "Personal Goal 1" "link"
    Then "//h1/a[@class='action-icon']/span[contains(., 'Edit')]" "xpath_element" should not exist
    And I should not see "Personal goal" in the "//div[@class='view_personal_goal']//h1" "xpath_element"
    And I should see "Legacy personal goal" in the "//div[@class='view_personal_goal']//h1" "xpath_element"
