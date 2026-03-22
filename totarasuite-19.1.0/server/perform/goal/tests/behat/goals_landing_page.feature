@totara @perform @mod_perform @perform_goal @javascript @vuejs
Feature: I can view, edit, update and delete goals from the goals landing page

  Background:
    Given the following "users" exist:
      | username | firstname | lastname | email             |
      | user1    | user      | 1        | user1@example.com |
      | user2    | User      | Two      | user2@example.com |
      | user3    | User      | Three    | user3@example.com |
    And the following job assignments exist:
      | user  | manager |
      | user1 | user2   |
      | user3 | user2   |
    And the following "system role assigns" exist:
      | user  | role         |
      | user2 | staffmanager |
    And the following "goals" exist in "perform_goal" plugin:
      | name     | id_number                   | username | owner | updated_at | start_date | target_date | current_value |
      | ABC goal | test-goal-behat-id-number_1 | user3    | admin | 2023-01-01 | 2022-01-01 | 2024-01-01  | 0             |
      | CDE goal | test-goal-behat-id-number_2 | user3    | admin | 2023-01-02 | 2022-01-02 | 2025-01-02  | 0             |
      | EFG goal | test-goal-behat-id-number_3 | user3    | admin | 2023-01-03 | 2022-01-03 | 2025-01-03  | 0             |
      | GHI goal | test-goal-behat-id-number_4 | user3    | admin | 2023-01-04 | 2022-01-04 | 2025-01-04  | 0             |
      | 12345    | test-goal-behat-id-number_5 | user3    | admin | 2023-01-05 | 2022-01-05 | 2025-01-05  | 20.0          |
      | 678      | test-goal-behat-id-number_6 | user3    | admin | 2023-01-06 | 2022-01-06 | 2025-01-06  | 20.0          |
      | 9        | test-goal-behat-id-number_7 | user3    | admin | 2023-01-07 | 2022-01-07 | 2025-01-07  | 90.0          |



    And flavour "perform" is active
    # This is needed otherwise both legacy goals and totara goals will be
    # simultaneously active!
    And I disable the "goals" advanced feature

  Scenario: Goals landing page test.
    Given I log in as "user1"
    And I navigate to the goals landing page
    And I should see "You don't currently have any goals"
    And "Create goal" "button" should be visible
    And ".tui-performGoals__goals-card" "css_element" should not be visible
    And I click on "Create goal" "button"

    # Create and close
    And I should see "Create goal" in the ".tui-performGoalActionModalHeader" "css_element"
    And I activate the weka editor with css ".tui-performGoalForm__descriptionEditor"
    And I type "Test goal description" in the weka editor
    And I set the following fields to these values:
      | name         |  |
      | target_value |  |
    And I set the "dates[start_date]" tui date selector to "2022-01-02"
    And I set the "dates[target_date]" tui date selector to "2021-01-02"
    And I click on "Create and close" "button"
    And I should see "name" form field has the tui validation error "Required"
    And I should see "target_value" form field has the tui validation error "Required"
    And I wait until "Start date cannot be after the due date" "text" exists
    And I set the following fields to these values:
      | name         | Perform goal |
      | target_value | 100          |
    And I set the "dates[start_date]" tui date selector to "2022-01-02"
    And I set the "dates[target_date]" tui date selector to "2024-01-02"
    And I click on "Create and close" "button"
    And I should see "The goal was successfully created." in the tui success notification toast

    # Test create and view
    And I click on "Create goal" "button"
    And I should see "Create goal" in the ".tui-performGoalActionModalHeader" "css_element"
    And I set the following fields to these values:
      | name         | Perform goal 2 |
      | target_value | 50             |
    And I activate the weka editor with css ".tui-performGoalForm__descriptionEditor"
    And I type "Test goal description" in the weka editor
    And I set the "dates[start_date]" tui date selector to "2020-01-02"
    And I set the "dates[target_date]" tui date selector to "2025-01-02"
    And I click on "Create and view" "button"
    And I should see "The goal was successfully created." in the tui success notification toast
    And I should see "Goal" in the ".tui-performGoalActionModalHeader" "css_element"
    And I should see "Perform goal 2" in the ".tui-performGoalDetailsView__name" "css_element"
    And I should see "0 of 50 Target" in the ".tui-performGoalDetailsView__progress-current" "css_element"
    And I should see "2/01/2020 - 2/01/2025" in the ".tui-performGoalDetailsView__progress-date" "css_element"

    # Edit via modal
    And I click on "Manage goal actions" "button" in the ".tui-modalContent__header" "css_element"
    And I click on "Edit" "link" in the ".tui-modalContent__header .tui-performGoalsActionsMenu" "css_element"
    And I should see "Edit goal" in the ".tui-performGoalActionModalHeader" "css_element"
    And I click on "Cancel" "button" in the ".tui-modalContent__footer" "css_element"
    And I should not see "Edit goal" in the ".tui-performGoalActionModalHeader" "css_element"
    And I should see "Goal" in the ".tui-performGoalActionModalHeader" "css_element"
    And I click on "Manage goal actions" "button" in the ".tui-modalContent__header" "css_element"
    And I click on "Edit" "link" in the ".tui-modalContent__header .tui-performGoalsActionsMenu" "css_element"
    And I should see "Edit goal" in the ".tui-performGoalActionModalHeader" "css_element"
    And I set the following fields to these values:
      | name         | Perform goal 2 edited |
      | target_value | 60                    |
    And I activate the weka editor with css ".tui-performGoalForm__descriptionEditor"
    And I select the text "Test goal description" in the weka editor
    And I type "Test goal description edited" in the weka editor
    And I set the "dates[start_date]" tui date selector to "2019-01-02"
    And I set the "dates[target_date]" tui date selector to "2026-01-02"
    And I click on "Save" "button" in the ".tui-modalContent__footer" "css_element"
    And I should see "Goal" in the ".tui-performGoalActionModalHeader" "css_element"
    And I should see "Perform goal 2 edited" in the ".tui-performGoalDetailsView__name" "css_element"
    And I should see "0 of 60 Target" in the ".tui-performGoalDetailsView__progress-current" "css_element"
    And I should see "2/01/2019 - 2/01/2026" in the ".tui-performGoalDetailsView__progress-date" "css_element"

    # Update via modal
    And I click on "Manage goal actions" "button" in the ".tui-modalContent__header" "css_element"
    And I click on "Update progress" "link" in the ".tui-modalContent__header .tui-performGoalsActionsMenu" "css_element"
    And I should see "Update progress" in the ".tui-performGoalActionModalHeader" "css_element"
    And I click on "Cancel" "button" in the ".tui-modalContent__footer" "css_element"
    And I should not see "Update progress" in the ".tui-performGoalActionModalHeader" "css_element"
    And I should see "Goal" in the ".tui-performGoalActionModalHeader" "css_element"
    And I click on "Manage goal actions" "button" in the ".tui-modalContent__header" "css_element"
    And I click on "Update progress" "link" in the ".tui-modalContent__header .tui-performGoalsActionsMenu" "css_element"
    And I should see "Update progress" in the ".tui-performGoalActionModalHeader" "css_element"
    And I set the field "Progress" to "60"
    And I set the following fields to these values:
      | status | completed |
    And I click on "Update" "button" in the ".tui-modalContent__footer" "css_element"
    And I should see "Goal" in the ".tui-performGoalActionModalHeader" "css_element"
    And I should see "60 of 60 Target" in the ".tui-performGoalDetailsView__progress-current" "css_element"

    # Delete via modal
    And I click on "Manage goal actions" "button" in the ".tui-modalContent__header" "css_element"
    And I click on "Delete" "link" in the ".tui-modalContent__header .tui-performGoalsActionsMenu" "css_element"
    And I should see "Delete goal"
    And I click on "Cancel" "button"
    And I should not see "Delete goal"
    And I should see "Goal" in the ".tui-performGoalActionModalHeader" "css_element"
    And I click on "Manage goal actions" "button" in the ".tui-modalContent__header" "css_element"
    And I click on "Delete" "link" in the ".tui-modalContent__header .tui-performGoalsActionsMenu" "css_element"
    And I should see "Delete goal"
    And I click on "Delete" "button" in the ".tui-modalContent__footer-buttons" "css_element"
    And I should see "The goal was successfully deleted." in the tui success notification toast
    And I should not see "Perform goal 2 edited" in the ".tui-performGoals__goals" "css_element"

    # Test from the cards
    And I should see "Your personal goals" in the ".tui-performGoals__goals" "css_element"
    And I should see "Perform goal" in the ".tui-performGoals__goals" "css_element"

    # View
    And I click on "Perform goal" "button" in the ".tui-performGoals__goals" "css_element"
    And I should see "Goal" in the ".tui-performGoalActionModalHeader" "css_element"
    And I should see "Perform goal" in the ".tui-performGoalDetailsView__name" "css_element"
    And I should see "0 of 100 Target" in the ".tui-performGoalDetailsView__progress-current" "css_element"
    And I should see "2/01/2022 - 2/01/2024" in the ".tui-performGoalDetailsView__progress-date" "css_element"
    And I click on "Close" "button" in the ".tui-performGoalActionModalHeader" "css_element"

    # Edit
    And I click on "Manage goal actions" "button"
    And I click on "Edit" "link"
    And I should see "Edit goal" in the ".tui-performGoalActionModalHeader" "css_element"
    And I click on "Cancel" "button" in the ".tui-modalContent__footer" "css_element"
    And ".tui-modalContent" "css_element" should not be visible
    And I click on "Manage goal actions" "button"
    And I click on "Edit" "link"
    And I set the following fields to these values:
      | name         | Perform goal edited |
      | target_value | 50                  |
    And I set the "dates[start_date]" tui date selector to "2010-01-02"
    And I set the "dates[target_date]" tui date selector to "2011-01-02"
    And I click on "Save" "button" in the ".tui-modalContent__footer" "css_element"
    And I click on "Close" "button" in the ".tui-performGoalActionModalHeader" "css_element"
    And I should see "Perform goal edited" in the ".tui-performGoals__goals" "css_element"
    And I should see "2/01/2011" in the ".tui-performGoals__goals .tui-performGoalCard__details-due" "css_element"

    # Update
    And I click on "Perform goal" "button" in the ".tui-performGoals__goals" "css_element"
    And I should see "Goal" in the ".tui-performGoalActionModalHeader" "css_element"
    And I click on "Manage goal actions" "button" in the ".tui-modalContent__header" "css_element"
    And I click on "Update progress" "link" in the ".tui-modalContent__header .tui-performGoalsActionsMenu" "css_element"
    And I should see "Update progress" in the ".tui-performGoalActionModalHeader" "css_element"
    And I set the field "Progress" to "25"
    And I set the following fields to these values:
      | status | completed |
    And I click on "Update" "button" in the ".tui-modalContent__footer" "css_element"
    And I click on "Close" "button" in the ".tui-performGoalActionModalHeader" "css_element"
    And I should see "50%" in the ".tui-performGoals__goals .tui-performGoalCard__status" "css_element"
    And I should see "Completed" in the ".tui-performGoals__goals .tui-performGoalCard__status" "css_element"

    # Delete
    And I click on "Manage goal actions" "button"
    And I click on "Delete" "link"
    And I should see "Delete goal"
    And I click on "Cancel" "button"
    And ".tui-modalContent" "css_element" should not be visible
    And I click on "Manage goal actions" "button"
    And I click on "Delete" "link"
    And I click on "Delete" "button" in the ".tui-modalContent__footer-buttons" "css_element"
    And I should see "The goal was successfully deleted." in the tui success notification toast
    And I should see "You don't currently have any goals"
    And "Create goal" "button" should be visible
    And ".tui-performGoals__goals-card" "css_element" should not be visible

  Scenario: Goals landing page test as manager
    Given I log in as "user2"
    And I am on "Team" page
    Then "user 1" "link" should exist in the "team_members" "table"

    When I click on "Goals" "link" in the "user 1" "table_row"
    Then I should see "user 1 does not currently have any goals"
    And "Create goal" "button" should be visible
    And ".tui-miniProfileCard__description" "css_element" should be visible
    And I click on "Create goal" "button"

    # Create and close
    And I should see "Create goal" in the ".tui-performGoalActionModalHeader" "css_element"
    And I activate the weka editor with css ".tui-performGoalForm__descriptionEditor"
    And I type "Test goal description" in the weka editor
    And I set the following fields to these values:
      | name         | Perform goal from manager |
      | target_value | 100                       |
    And I set the "dates[start_date]" tui date selector to "2022-01-02"
    And I set the "dates[target_date]" tui date selector to "2024-01-02"
    And I click on "Create and close" "button"
    And I should see "The goal was successfully created." in the tui success notification toast
    And I should see "Perform goal from manager"


  Scenario: Goals landing page filtering
    Given I log in as "user3"
    And I navigate to the goals landing page
    And I should see "7" in the ".tui-performGoalsSortBar__count" "css_element"
    And I should see "ABC goal" in the ".tui-performGoalsList__card:nth-child(1) .tui-performGoalCard__details-name" "css_element"
    And I should see "CDE goal" in the ".tui-performGoalsList__card:nth-child(2) .tui-performGoalCard__details-name" "css_element"
    And I should see "12345" in the ".tui-performGoalsList__card:nth-child(5) .tui-performGoalCard__details-name" "css_element"

    # Check filter options display and function as expected
    When I click on "Filters" "button" in the ".tui-filterBarArea" "css_element"
    Then I should see "Cancelled" in the ".tui-filterBarAreaPopover__content:nth-child(1)" "css_element"
    And I should see "Completed" in the ".tui-filterBarAreaPopover__content:nth-child(1)" "css_element"
    And I should see "In progress" in the ".tui-filterBarAreaPopover__content:nth-child(1)" "css_element"
    And I should see "Not started" in the ".tui-filterBarAreaPopover__content:nth-child(1)" "css_element"
    And I click on "Completed" tui "checkbox"
    And I click on "Close" "button"

    Then ".tui-performGoalsSortBar__coun" "css_element" should not exist
    And I should see "No matching items found." in the ".tui-performGoalsSortBar__empty" "css_element"

    When I click on "Filters" "button"
    And I click on "Reset all" "button"
    And I click on "Not started" tui "checkbox"
    And I click on "Close" "button"
    And I should see "7" in the ".tui-performGoalsSortBar__count" "css_element"

    And I click on "EFG goal" "button" in the ".tui-performGoals__goals" "css_element"
    And I should see "Goal" in the ".tui-performGoalActionModalHeader" "css_element"
    And I click on "Manage goal actions" "button" in the ".tui-modalContent__header" "css_element"
    And I click on "Update progress" "link" in the ".tui-modalContent__header .tui-performGoalsActionsMenu" "css_element"
    And I should see "Update progress" in the ".tui-performGoalActionModalHeader" "css_element"
    And I set the following fields to these values:
      | status | completed |
    And I click on "Update" "button" in the ".tui-modalContent__footer" "css_element"
    And I click on "Close" "button" in the ".tui-performGoalActionModalHeader" "css_element"
    And I should see "6" in the ".tui-performGoalsSortBar__count" "css_element"

    When I click on "Filters" "button"
    And I click on "Reset all" "button"
    And I click on "Completed" tui "checkbox"
    And I click on "Cancelled" tui "checkbox"
    And I click on "Close" "button"
    And I should see "1" in the ".tui-performGoalsSortBar__count" "css_element"

    Then I click on "Filters" "button"
    And I click on "Reset all" "button"
    And I click on "Close" "button"
    When I set the field "Sort by" to "most_complete"
    Then I should see "9" in the ".tui-performGoalsList__card:nth-child(1) .tui-performGoalCard__details-name" "css_element"

    And I set the field "Search by goal" to "goal"
    And I should see "4" in the ".tui-performGoalsSortBar__count" "css_element"
