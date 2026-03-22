@totara @perform @mod_perform @perform_element @performelement_linked_review @perform_goal @javascript @vuejs
Feature: I can view, edit, and delete goal tasks

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
    And the following "activity with section and review element" exist in "performelement_linked_review" plugin:
      | activity_name | section_title | element_title       | content_type | content_type_settings                                                 |
      | activity1     | section1      | Perform goal review | perform_goal | {"enable_status_change":false,"status_change_relationship":"subject"} |
    And the following "child elements" exist in "mod_perform" plugin:
      | section  | parent_element      | element_plugin | element_title  |
      | section1 | Perform goal review | short_text     | child personal |
    And the following "participants in section" exist in "performelement_linked_review" plugin:
      | section  | subject_user | user  | relationship     | can_answer |
      | section1 | user1        | user1 | subject          | true       |
      | section1 | user1        | user2 | manager          | true       |
      | section1 | user1        | user3 | managers_manager | false      |
      | section1 | user2        | user2 | subject          | true       |
    And the following "goals" exist in "perform_goal" plugin:
      | name         | id_number                   | username | owner | updated_at | start_date | target_date |
      | Perform goal | test-goal-behat-id-number_1 | user1    | admin | 2023-01-01 | 2022-01-01 | 2024-01-01  |
      | Second goal  | test-goal-behat-id-number_2 | user1    | admin | 2023-01-10 | 2022-01-10 | 2025-01-01  |
    And the following "goal tasks" exist in "perform_goal" plugin:
      | goal_id_number              | description        | completed_at | resource_id_string | resource_type |
      | test-goal-behat-id-number_2 | Simple goal task 1 |              |                    |               |
    And the following "courses" exist:
      | fullname      | shortname | enablecompletion |
      | Course purple | C1        | 1                |
      | Course red    | C2        | 1                |
      | Course blue   | C3        | 1                |


  Scenario: Goals landing page tasks can be created and displayed
    When I log in as "user1"
    And I navigate to the goals landing page
    # View tasks
    And I click on "Perform goal" "button" in the ".tui-performGoals__goals" "css_element"
    And I should see "Goal" in the ".tui-performGoalActionModalHeader" "css_element"
    And I should see "Tasks" in the ".tui-tabBar__tabLabel" "css_element"
    And ".tui-performGoalDetailsTaskList__tasks-progress" "css_element" should not exist

    # I can navigate to create a new task UI
    And ".tui-performGoalDetailsTaskForm__form-input" "css_element" should exist
    And ".tui-performGoalDetailsTaskForm__form-barButtons" "css_element" should not exist
    When I set the following fields to these values:
      | description[create] | test |
    Then I should see "Save" in the ".tui-performGoalDetailsTaskForm__form-barButtons" "css_element"
    And I should see "Cancel" in the ".tui-performGoalDetailsTaskForm__form-barButtons" "css_element"
    And I should see "Add course" in the ".tui-performGoalDetailsTaskForm__form-bar" "css_element"

    # I can cancel a new task
    When I click on "Cancel" "button" in the ".tui-performGoalDetailsTaskList__add .tui-performGoalDetailsTaskForm__form-barButtons" "css_element"
    Then the following fields match these values:
      | description[create] |  |
    And ".tui-performGoalDetailsTaskForm__form-barButtons" "css_element" should not exist

    # I can save a new task and see it displayed
    When I set the following fields to these values:
      | description[create] | task 12345 |
    And I click on "Save" "button" in the ".tui-performGoalDetailsTaskList__add .tui-performGoalDetailsTaskForm__form-barButtons" "css_element"
    And I wait until ".tui-performGoalDetailsTaskList__tasks-itemsItem:nth-child(1)" "css_element" exists
    Then I should see "0/1 completed" in the ".tui-performGoalDetailsTaskList__tasks-progress" "css_element"
    And I should see "task 12345" in the ".tui-performGoalDetailsTaskList__tasks-itemsItem:nth-child(1)" "css_element"
    And the following fields match these values:
      | description[create] |  |


  Scenario: Goals landing page tasks can be deleted
    When I log in as "user1"
    And I navigate to the goals landing page
    # Create task
    And I click on "Perform goal" "button" in the ".tui-performGoals__goals" "css_element"
    And I set the following fields to these values:
      | description[create] | task 12345 |
    Then I click on "Save" "button" in the ".tui-performGoalDetailsTaskList__add .tui-performGoalDetailsTaskForm__form-barButtons" "css_element"
    And I wait until ".tui-performGoalDetailsTaskList__tasks-itemsItem:nth-child(1)" "css_element" exists
    And I should see "task 12345" in the ".tui-performGoalDetailsTaskList__tasks-itemsItem:nth-child(1)" "css_element"

    # Delete task
    And I click on "Actions for task 12345" "button" in the ".tui-performGoalDetailsTaskList__tasks-itemsItem:nth-child(1)" "css_element"
    And I click on "Delete" option in the dropdown menu
    And ".tui-performGoalDetailsTaskList__tasks-itemsItem:nth-child(1)" "css_element" should not exist

  Scenario: Goals landing page tasks can be completed
    When I log in as "user1"
    And I navigate to the goals landing page
    And I click on "Second goal" "button" in the ".tui-performGoals__goals" "css_element"
    And I should see "Simple goal task 1" in the ".tui-performGoalDetailsTaskList__tasks-itemsItem:nth-child(1)" "css_element"
    And I should see "0/1 completed" in the ".tui-performGoalDetailsTaskList__tasks-progress" "css_element"
    Then I click on ".tui-performGoalDetailsTaskList__tasks:nth-of-type(1) .tui-checkbox__label" "css_element"
    And I should see "1/1 completed" in the ".tui-performGoalDetailsTaskList__tasks-progress" "css_element"

    When I click on "Close" "button" in the ".tui-performGoalActionModalHeader" "css_element"
    And I should see "1/1" in the ".tui-performGoalsList__card:nth-child(2) .tui-performGoalCard__details-tasks" "css_element"

  Scenario: Goals landing page tasks can be created and displayed with course resources
    When I log in as "user1"
    And I navigate to the goals landing page
    # View tasks
    And I click on "Perform goal" "button" in the ".tui-performGoals__goals" "css_element"
    And I should see "Goal" in the ".tui-performGoalActionModalHeader" "css_element"
    And I should see "Tasks" in the ".tui-tabBar__tabLabel" "css_element"
    And ".tui-performGoalDetailsTaskList__tasks-progress" "css_element" should not exist

    # I can save a new task with a course resource
    When I set the following fields to these values:
      | description[create] | test123 |
    Then I should see "Add course" in the ".tui-performGoalDetailsTaskForm__form-bar" "css_element"

    # Select course
    When I click on "Add course" "button" in the ".tui-performGoalDetailsTaskForm__form-bar" "css_element"
    And I click on ".tui-dataTableRow:nth-child(3) .tui-radio__label" "css_element"
    Then I should see "Item selected: Course purple" in the ".tui-core_course-picker__selectionInfo" "css_element"

    When I click on "Add" "button" in the ".tui-core_course-coursePickerModal__buttons" "css_element"
    Then ".tui-performGoalDetailsTaskForm__form-barResourceCard" "css_element" should exist
    And ".tui-performGoalTaskCourseCard__image" "css_element" should exist
    And ".tui-performGoalTaskCourseCard__closeButton" "css_element" should exist
    And I should see "Course" in the ".tui-performGoalTaskCourseCard__content-label" "css_element"
    And I should see "Course purple" in the ".tui-performGoalTaskCourseCard__content-title" "css_element"

    Then I click on "Save" "button" in the ".tui-performGoalDetailsTaskList__add .tui-performGoalDetailsTaskForm__form-barButtons" "css_element"
    And I wait until ".tui-performGoalDetailsTaskList__tasks-itemsItem:nth-child(1)" "css_element" exists
    And I should see "test123" in the ".tui-performGoalDetailsTaskList__tasks-itemsItem:nth-child(1)" "css_element"
    And I should see "Course purple" in the ".tui-performGoalDetailsTaskList__tasks-itemsItem:nth-child(1)" "css_element"

    # Select course and then remove it
    When I click on "Add course" "button" in the ".tui-performGoalDetailsTaskForm__form-bar" "css_element"
    And I click on ".tui-dataTableRow:nth-child(1) .tui-radio__label" "css_element"
    Then I should see "Item selected: Course blue" in the ".tui-core_course-picker__selectionInfo" "css_element"
    When I click on "Add" "button" in the ".tui-core_course-coursePickerModal__buttons" "css_element"
    Then ".tui-performGoalDetailsTaskForm__form-barResourceCard" "css_element" should exist
    And ".tui-performGoalTaskCourseCard__image" "css_element" should exist
    And ".tui-performGoalTaskCourseCard__closeButton" "css_element" should exist
    And I should see "Course" in the ".tui-performGoalTaskCourseCard__content-label" "css_element"
    And I should see "Course blue" in the ".tui-performGoalDetailsTaskForm__form .tui-performGoalTaskCourseCard__content-title" "css_element"
    When I click on "Cancel" "button" in the ".tui-performGoalDetailsTaskList__add .tui-performGoalDetailsTaskForm__form-barButtons" "css_element"
    Then ".tui-performGoalDetailsTaskForm__form-barResourceCard" "css_element" should not exist

  Scenario: Goals landing page tasks can handle removed course resources
    When I log in as "user1"
    And I navigate to the goals landing page
    # View tasks
    And I click on "Perform goal" "button" in the ".tui-performGoals__goals" "css_element"

    # I can save a new task with a course resource
    When I set the following fields to these values:
      | description[create] | test123 |
    Then I should see "Add course" in the ".tui-performGoalDetailsTaskForm__form-bar" "css_element"
    # Select course
    When I click on "Add course" "button" in the ".tui-performGoalDetailsTaskForm__form-bar" "css_element"
    And I click on ".tui-dataTableRow:nth-child(3) .tui-radio__label" "css_element"
    Then I should see "Item selected: Course purple" in the ".tui-core_course-picker__selectionInfo" "css_element"
    When I click on "Add" "button" in the ".tui-core_course-coursePickerModal__buttons" "css_element"
    And I should see "Course purple" in the ".tui-performGoalTaskCourseCard__content-title" "css_element"
    # Save Task
    Then I click on "Save" "button" in the ".tui-performGoalDetailsTaskList__add .tui-performGoalDetailsTaskForm__form-barButtons" "css_element"
    And I wait until ".tui-performGoalDetailsTaskList__tasks-itemsItem:nth-child(1)" "css_element" exists
    And I should see "test123" in the ".tui-performGoalDetailsTaskList__tasks-itemsItem:nth-child(1)" "css_element"
    And I should see "Course purple" in the ".tui-performGoalDetailsTaskList__tasks-itemsItem:nth-child(1)" "css_element"

    When I click on "Close" "button" in the ".tui-performGoalActionModalHeader" "css_element"
    And I log in as "admin"
    And I navigate to "Courses and categories" node in "Site administration > Courses"
    And I click on "Miscellaneous" "text" in the ".category-listing" "css_element"
    And I go to the courses management page
    And I click on category "Miscellaneous" in the management interface
    And I click on "delete" action for "Course purple" in management course listing
    And I press "Delete"
    Then I should see "C1 has been completely deleted"

    # View tasks
    When I log in as "user1"
    And I navigate to the goals landing page
    And I click on "Perform goal" "button" in the ".tui-performGoals__goals" "css_element"
    Then I should see "test123" in the ".tui-performGoalDetailsTaskList__tasks-itemsItem:nth-child(1)" "css_element"
    And I should see "This course is no longer available" in the ".tui-performGoalDetailsTaskList__tasks-itemsItem:nth-child(1)" "css_element"

  Scenario: Goals landing page tasks can handle hidden course resources
    When I log in as "user1"
    And I navigate to the goals landing page
    # View tasks
    And I click on "Perform goal" "button" in the ".tui-performGoals__goals" "css_element"

    # I can save a new task with a course resource
    When I set the following fields to these values:
      | description[create] | test123 |
    Then I should see "Add course" in the ".tui-performGoalDetailsTaskForm__form-bar" "css_element"
    # Select course
    When I click on "Add course" "button" in the ".tui-performGoalDetailsTaskForm__form-bar" "css_element"
    And I click on ".tui-dataTableRow:nth-child(3) .tui-radio__label" "css_element"
    Then I should see "Item selected: Course purple" in the ".tui-core_course-picker__selectionInfo" "css_element"
    When I click on "Add" "button" in the ".tui-core_course-coursePickerModal__buttons" "css_element"
    And I should see "Course purple" in the ".tui-performGoalTaskCourseCard__content-title" "css_element"
    # Save Task
    Then I click on "Save" "button" in the ".tui-performGoalDetailsTaskList__add .tui-performGoalDetailsTaskForm__form-barButtons" "css_element"
    And I wait until ".tui-performGoalDetailsTaskList__tasks-itemsItem:nth-child(1)" "css_element" exists
    And I should see "test123" in the ".tui-performGoalDetailsTaskList__tasks-itemsItem:nth-child(1)" "css_element"
    And I should see "Course purple" in the ".tui-performGoalDetailsTaskList__tasks-itemsItem:nth-child(1)" "css_element"

    When I click on "Close" "button" in the ".tui-performGoalActionModalHeader" "css_element"
    And I log in as "admin"
    And I navigate to "Courses and categories" node in "Site administration > Courses"
    And I click on "Miscellaneous" "text" in the ".category-listing" "css_element"
    And I go to the courses management page
    And I click on category "Miscellaneous" in the management interface
    And I click on "hide" action for "Course purple" in management course listing

    # View tasks
    When I log in as "user1"
    And I navigate to the goals landing page
    And I click on "Perform goal" "button" in the ".tui-performGoals__goals" "css_element"
    Then I should see "test123" in the ".tui-performGoalDetailsTaskList__tasks-itemsItem:nth-child(1)" "css_element"
    And I should see "This course is no longer available" in the ".tui-performGoalDetailsTaskList__tasks-itemsItem:nth-child(1)" "css_element"


  Scenario: Goals landing page tasks can be edited
    When I log in as "user1"
    And I navigate to the goals landing page
    # Create task
    And I click on "Perform goal" "button" in the ".tui-performGoals__goals" "css_element"
    And I set the following fields to these values:
      | description[create] | task 12345 |
    Then I click on "Save" "button" in the ".tui-performGoalDetailsTaskList__add .tui-performGoalDetailsTaskForm__form-barButtons" "css_element"
    And I wait until ".tui-performGoalDetailsTaskList__tasks-itemsItem:nth-child(1)" "css_element" exists
    And I should see "task 12345" in the ".tui-performGoalDetailsTaskList__tasks-itemsItem:nth-child(1)" "css_element"

    And I click on "Actions for task 12345" "button" in the ".tui-performGoalDetailsTaskList__tasks-itemsItem:nth-child(1)" "css_element"
    And I click on "Edit" option in the dropdown menu

    # Edit course
    Then I should see "Save" in the ".tui-performGoalDetailsTaskList__tasks-itemsItem:nth-child(1) .tui-performGoalDetailsTaskForm__form-barButtons" "css_element"
    And I should see "Cancel" in the ".tui-performGoalDetailsTaskList__tasks-itemsItem:nth-child(1) .tui-performGoalDetailsTaskForm__form-barButtons" "css_element"
    And I should see "Add course" in the ".tui-performGoalDetailsTaskList__tasks-itemsItem:nth-child(1) .tui-performGoalDetailsTaskForm__form-bar" "css_element"
    And I set the following fields to these values:
      | description[edit] | updated description |

    # Select course
    When I click on "Add course" "button" in the ".tui-performGoalDetailsTaskForm__form-bar" "css_element"
    And I click on ".tui-dataTableRow:nth-child(3) .tui-radio__label" "css_element"
    And I click on "Add" "button" in the ".tui-core_course-coursePickerModal__buttons" "css_element"
    Then I should see "Course purple" in the ".tui-performGoalDetailsTaskList__tasks-itemsItem:nth-child(1) .tui-performGoalTaskCourseCard__content-title" "css_element"

    When I click on "Save" "button" in the ".tui-performGoalDetailsTaskList__tasks-itemsItem:nth-child(1) .tui-performGoalDetailsTaskForm__form-barButtons" "css_element"
    Then I should see "updated description" in the ".tui-performGoalDetailsTaskList__tasks-itemsItem:nth-child(1)" "css_element"
    And I should see "Course purple" in the ".tui-performGoalDetailsTaskList__tasks-itemsItem:nth-child(1)" "css_element"
