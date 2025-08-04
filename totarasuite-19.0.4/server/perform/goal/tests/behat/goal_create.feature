@totara @perform_goal @javascript
Feature: As admin I can create a list of goals

  Scenario: List of goals test
    Given the following "users" exist:
      | username | firstname | lastname | email             |
      | user1    | user      | 1        | user1@example.com |
    And the following "goals" exist in "perform_goal" plugin:
      | name         | id_number                   | username | owner | created_at |
      | Perform goal | test-goal-behat-id-number_1 | user1    | admin | -2 days    |
    And the following "goal activities" exist in "perform_goal" plugin:
      | id_number                   | timestamp |
      | test-goal-behat-id-number_1 | -2 days   |
    And the following "goals" exist in "perform_goal" plugin:
      | name           | id_number                   |
      | Perform goal 2 | test-goal-behat-id-number_2 |
    And the following "goal activities" exist in "perform_goal" plugin:
      | id_number                   |
      | test-goal-behat-id-number_2 |
    When I log in as "admin"
    And I navigate to the manage perform activities page
    Then I should see "Manage performance activities"
