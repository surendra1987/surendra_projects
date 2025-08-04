@totara @perform_goal @javascript
Feature: As admin I can create a list of goal tasks

  Scenario: List of goal tasks test
    Given the following "users" exist:
      | username | firstname | lastname | email             |
      | user1    | user      | 1        | user1@example.com |
    And the following "courses" exist:
      | fullname | shortname |
      | Course 1 | C1        |
    And the following "seminars" exist in "mod_facetoface" plugin:
      | name              | course  |
      | Test seminar name | C1      |
    And the following "course enrolments" exist:
      | user  | course | role    |
      | user1 | C1     | student |
    # Create a first goal
    And the following "goals" exist in "perform_goal" plugin:
      | name           | id_number                   | username | owner | created_at |
      | Perform goal 1 | test-goal-behat-id-number_1 | user1    | admin | -2 days    |
    And the following "goal activities" exist in "perform_goal" plugin:
      | id_number                   | timestamp |
      | test-goal-behat-id-number_1 | -2 days   |
    # Create a second goal
    And the following "goals" exist in "perform_goal" plugin:
      | name           | id_number                   | username | owner | created_at |
      | Perform goal 2 | test-goal-behat-id-number_2 | user1    | admin | -1 days    |
    And the following "goal activities" exist in "perform_goal" plugin:
      | id_number                   | timestamp |
      | test-goal-behat-id-number_2 | -1 days   |
    # Create the tasks for the first and second goals
    And the following "goal tasks" exist in "perform_goal" plugin:
      | goal_id_number              | description                                  | completed_at | resource_id_string | resource_type |
      | test-goal-behat-id-number_1 | My test-goal-behat-id-number_1 goal task 1-1 | 1            | C1                 | 1             |
      | test-goal-behat-id-number_1 | My test-goal-behat-id-number_1 goal task 2-1 |              |                    |               |
      | test-goal-behat-id-number_2 | My test-goal-behat-id-number_1 goal task 1-2 |              | C1                 | 1             |
      | test-goal-behat-id-number_2 | My test-goal-behat-id-number_1 goal task 2-2 | 1            |                    |               |
    # Create a third goal
    And the following "goals" exist in "perform_goal" plugin:
      | name           | id_number                   | username | owner | created_at |
      | Perform goal 3 | test-goal-behat-id-number_3 | user1    | admin | -1 days    |
    And the following "goal activities" exist in "perform_goal" plugin:
      | id_number                   | timestamp |
      | test-goal-behat-id-number_3 | -1 days   |
    And the following "goal tasks" exist in "perform_goal" plugin:
      | goal_id_number              | description                                  |
      | test-goal-behat-id-number_3 | My test-goal-behat-id-number_3 goal task 1-3 |

    When I log in as "user1"
    And I navigate to the goals landing page
    And I click on "Perform goal 1" "button" in the ".tui-performGoals__goals" "css_element"
    And I should see "Tasks" in the "div.tui-tabs.tui-tabs--horizontal" "css_element"
    And I click on "Close" "button" in the ".tui-performGoalActionModalHeader" "css_element"
    And I click on "Perform goal 2" "button" in the ".tui-performGoals__goals" "css_element"
    And I should see "Tasks" in the "div.tui-tabs.tui-tabs--horizontal" "css_element"
    And I click on "Close" "button" in the ".tui-performGoalActionModalHeader" "css_element"
    And I click on "Perform goal 3" "button" in the ".tui-performGoals__goals" "css_element"
    And I should see "Tasks" in the "div.tui-tabs.tui-tabs--horizontal" "css_element"
    And I click on "Close" "button" in the ".tui-performGoalActionModalHeader" "css_element"
    # Should not crash during this behat test