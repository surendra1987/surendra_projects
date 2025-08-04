@totara @perform @mod_perform @perform_goal @totara_comment @javascript @vuejs
Feature: I can create and view goal comments

  Background:
    Given the following "users" exist:
      | username | firstname | lastname | email             |
      | user1    | User      | One      | user1@example.com |
      | user2    | User      | Two      | user2@example.com |
      | user3    | User      | Three    | user3@example.com |
    And the following job assignments exist:
      | user  | manager |
      | user1 | user2   |
      | user2 | user3   |
    And the following "system role assigns" exist:
      | user  | role         |
      | user2 | staffmanager |
    And the following "goals" exist in "perform_goal" plugin:
      | name         | id_number                   | username | owner | updated_at | start_date | target_date |
      | Perform goal | test-goal-behat-id-number_1 | user1    | admin | 2023-01-01 | 2022-01-01 | 2024-01-01  |
      | Second goal  | test-goal-behat-id-number_2 | user1    | admin | 2023-01-10 | 2022-01-10 | 2025-01-01  |
    And the following "goal tasks" exist in "perform_goal" plugin:
      | goal_id_number              | description        | completed_at | resource_id_string | resource_type |
      | test-goal-behat-id-number_2 | Simple goal task 1 |              |                    |               |
    And the following "comments" exist in "totara_comment" plugin:
      | name         | username | component    | area         | content                                                                                                   | format |
      | Perform goal | user1    | perform_goal | goal_comment | {"type":"doc","content":[{"type":"paragraph","content":[{"type":"text","text":"This is a great goal"}]}]} | 5      |
      | Perform goal | user2    | perform_goal | goal_comment | {"type":"doc","content":[{"type":"paragraph","content":[{"type":"text","text":"I agree on this"}]}]}      | 5      |
      | Second goal  | user2    | perform_goal | goal_comment | {"type":"doc","content":[{"type":"paragraph","content":[{"type":"text","text":"Not sure on this"}]}]}     | 5      |
      | Second goal  | user2    | perform_goal | goal_comment | {"type":"doc","content":[{"type":"paragraph","content":[{"type":"text","text":"I changed it"}]}]}         | 5      |
      | Second goal  | user2    | perform_goal | goal_comment | {"type":"doc","content":[{"type":"paragraph","content":[{"type":"text","text":"I changed it back"}]}]}    | 5      |

    And I disable the "goals" advanced feature

  Scenario: Goals landing page comments can be displayed on own goal page
    When I log in as "user1"
    And I navigate to the goals landing page
    # View goal
    And I click on "Perform goal" "button" in the ".tui-performGoals__goals" "css_element"
    And I should see "Goal" in the ".tui-performGoalActionModalHeader" "css_element"
    And "Tasks" "link" should exist in the ".tui-tabBar" "css_element"
    And "Comments" "link" should exist in the ".tui-tabBar" "css_element"

    # I can navigate and view comments
    When I click on "Comments" "link" in the ".tui-tabBar" "css_element"
    Then the "Comments" tui tab should be active
    # first comment
    And I should see "User One" in the ".tui-commentCard:nth-child(1) .tui-commentReplyHeader" "css_element"
    And I should see "This is a great goal" in the ".tui-commentCard:nth-child(1)" "css_element"
    # second comment
    And I should see "User Two" in the ".tui-commentCard:nth-child(2) .tui-commentReplyHeader" "css_element"
    And I should see "I agree on this" in the ".tui-commentCard:nth-child(2)" "css_element"

    # View another goal
    When I click on "Close" "button" in the ".tui-performGoalActionModalHeader" "css_element"
    And I click on "Second goal" "button" in the ".tui-performGoals__goals" "css_element"
    # I can navigate and view comments
    When I click on "Comments" "link" in the ".tui-tabBar" "css_element"
    Then the "Comments" tui tab should be active
    # first comment
    And I should see "User Two" in the ".tui-commentCard:nth-child(1) .tui-commentReplyHeader" "css_element"
    And I should see "Not sure on this" in the ".tui-commentCard:nth-child(1)" "css_element"
    # second comment
    And I should see "User Two" in the ".tui-commentCard:nth-child(2) .tui-commentReplyHeader" "css_element"
    And I should see "I changed it" in the ".tui-commentCard:nth-child(2)" "css_element"
    # third comment
    And I should see "User Two" in the ".tui-commentCard:nth-child(3) .tui-commentReplyHeader" "css_element"
    And I should see "I changed it back" in the ".tui-commentCard:nth-child(3)" "css_element"


  Scenario: Goals landing page comments can be displayed on direct reports page
    Given I log in as "user2"
    And I am on "Team" page
    Then "User One" "link" should exist in the "team_members" "table"
    When I click on "Goals" "link" in the "User One" "table_row"

    # View goal
    And I click on "Perform goal" "button" in the ".tui-performGoals__goals" "css_element"
    And I should see "Goal" in the ".tui-performGoalActionModalHeader" "css_element"
    And "Tasks" "link" should exist in the ".tui-tabBar" "css_element"
    And "Comments" "link" should exist in the ".tui-tabBar" "css_element"

    # I can navigate and view comments
    When I click on "Comments" "link" in the ".tui-tabBar" "css_element"
    Then the "Comments" tui tab should be active
    # first comment
    And I should see "User One" in the ".tui-commentCard:nth-child(1) .tui-commentReplyHeader" "css_element"
    And I should see "This is a great goal" in the ".tui-commentCard:nth-child(1)" "css_element"
    # second comment
    And I should see "User Two" in the ".tui-commentCard:nth-child(2) .tui-commentReplyHeader" "css_element"
    And I should see "I agree on this" in the ".tui-commentCard:nth-child(2)" "css_element"

    # View another goal
    When I click on "Close" "button" in the ".tui-performGoalActionModalHeader" "css_element"
    And I click on "Second goal" "button" in the ".tui-performGoals__goals" "css_element"
    # I can navigate and view comments
    When I click on "Comments" "link" in the ".tui-tabBar" "css_element"
    Then the "Comments" tui tab should be active
    # first comment
    And I should see "User Two" in the ".tui-commentCard:nth-child(1) .tui-commentReplyHeader" "css_element"
    And I should see "Not sure on this" in the ".tui-commentCard:nth-child(1)" "css_element"
    # second comment
    And I should see "User Two" in the ".tui-commentCard:nth-child(2) .tui-commentReplyHeader" "css_element"
    And I should see "I changed it" in the ".tui-commentCard:nth-child(2)" "css_element"
    # third comment
    And I should see "User Two" in the ".tui-commentCard:nth-child(3) .tui-commentReplyHeader" "css_element"
    And I should see "I changed it back" in the ".tui-commentCard:nth-child(3)" "css_element"


