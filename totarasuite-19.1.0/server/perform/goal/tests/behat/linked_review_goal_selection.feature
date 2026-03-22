@totara @perform @mod_perform @perform_element @performelement_linked_review @perform_goal @javascript @vuejs
Feature: Selecting totara goals linked to a performance review

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

  Scenario: When I have no goals, nothing can happen
    When I log in as "user2"
    And I navigate to the outstanding perform activities list page
    And I click on "activity1" "link"
    And I click on "Add goals" "link_or_button"
    Then I should see "Select goals" in the tui modal
    And I should see "No items to display" in the tui modal
    And the "Add" "button" should be disabled in the ".tui-modalContent" "css_element"
    When I click on "Cancel" "button" in the ".tui-modal" "css_element"
    Then I should not see "Select personal goals"

  Scenario: Subjects goals display as expected in the adder
    When I log in as "user1"
    And I navigate to the outstanding perform activities list page
    And I click on "activity1" "link"
    And I click on "Add goals" "link_or_button"
    Then I should see "Goal" in the tui modal
    And I should see "Due date" in the tui modal
    And I should see the tui datatable contains:
      | Goal         | Due date  |
      | Perform goal | 1/01/2024 |
      | Second goal  | 1/01/2025 |

  Scenario: Goals adder contains search filter
    And the following "goals" exist in "perform_goal" plugin:
      | name        | id_number                   | username | owner | updated_at | start_date | target_date |
      | Third goal  | test-goal-behat-id-number_3 | user1    | admin | 2023-01-11 | 2022-01-11 | 2026-01-01  |
      | Fourth goal | test-goal-behat-id-number_4 | user1    | admin | 2023-01-12 | 2022-01-12 | 2027-01-01  |

    When I log in as "user1"
    And I navigate to the outstanding perform activities list page
    And I click on "activity1" "link"
    And I click on "Add goals" "link_or_button"
    And I should see the tui datatable contains:
      | Goal         | Due date  |
      | Perform goal | 1/01/2024 |
      | Second goal  | 1/01/2025 |
      | Third goal   | 1/01/2026 |
      | Fourth goal  | 1/01/2027 |

    When I set the following fields to these values:
      | Search | Fifth |
    Then I should see the tui datatable is empty

    When I set the following fields to these values:
      | Search | Fourth |
    Then I should see the tui datatable contains:
      | Goal        | Due date  |
      | Fourth goal | 1/01/2027 |

  Scenario: Waiting for another user to select perform goals
    When I log in as "user2"
    And I navigate to the outstanding perform activities list page
    And I click on "As Manager" "link_or_button"
    And I click on "activity1" "link"
    Then I should see "Awaiting goal selection from a Subject."

  Scenario: View only participant can select perform goals and change status
    Given the following "activity with section and review element" exist in "performelement_linked_review" plugin:
      | activity_name | section_title | element_title        | content_type | content_type_settings                                                |
      | activity2     | section2      | Personal goal review | perform_goal | {"enable_status_change":true,"status_change_relationship":"subject"} |
    And the following "section relationships" exist in "mod_perform" plugin:
      | section_name | relationship | can_view | can_answer |
      | section2     | subject      | yes      | no         |
    And the following "participants in section" exist in "performelement_linked_review" plugin:
      | section  | subject_user | user  | relationship |
      | section2 | user1        | user1 | subject      |

    When I log in as "user1"
    And I navigate to the outstanding perform activities list page
    And I click on "activity2" "link"
    And I click on "Add goals" "link_or_button"
    Then I should not see "No items to display" in the tui modal
    And I should see "Items selected: 0" in the tui modal
    And I should see the tui datatable contains:
      | Goal         | Due date  |
      | Perform goal | 1/01/2024 |
      | Second goal  | 1/01/2025 |

    When I set the following fields to these values:
      | Search | Perform goal C |
    Then I should see the tui datatable is empty
    And I should see "Items selected: 0" in the tui modal

    When I set the following fields to these values:
      | Search | Perform goal |
    Then I should see the tui datatable contains:
      | Goal         | Due date  |
      | Perform goal | 1/01/2024 |
    When I set the following fields to these values:
      | Search |  |
    And I toggle the adder picker entry with "Perform goal" for "Goal"
    And I toggle the adder picker entry with "Second goal" for "Goal"
    And I should see "Items selected: 2" in the tui modal
    And I click on "Add" "button" in the ".tui-modal" "css_element"
    And I click on "Confirm selection" "button"
    Then I should see "Perform goal"
    And I should see "Second goal"
    And I click on "Perform goal" "link_or_button"
    And I should see "Goal" in the ".tui-performGoalActionModalHeader" "css_element"
    When I reload the page
    Then I should see "Perform goal"
    And I should see "Second goal"

    # Check that we can remove
    And "Remove" "button" should exist in the ".tui-linkedReviewParticipantForm__item:nth-child(1) .tui-linkedReviewParticipantForm__item-cardActions" "css_element"
    And I click on "Remove" "button" in the ".tui-linkedReviewParticipantForm__item:nth-child(1) .tui-linkedReviewParticipantForm__item-cardActions" "css_element"
    And I should see "This will remove the goal and your responses. Are you sure you would like to remove this goal?"
    And I confirm the tui confirmation modal
    And I should see "Successfully removed goal." in the tui success notification toast
    Then I should not see "Perform goal"

    # Check the remove button is gone after updating progress
    And "Remove" "button" should exist in the ".tui-linkedReviewParticipantForm__item:nth-child(1) .tui-linkedReviewParticipantForm__item-cardActions" "css_element"
    And I click on "Update progress" "button"
    And I should see "After submitting this update, you cannot remove the goal or change its progress within this activity." in the tui info notification banner
    And I click on "Update" "button" in the ".tui-modalContent__footer" "css_element"
    And ".tui-linkedReviewParticipantForm__item:nth-child(1) .tui-linkedReviewParticipantForm__item-cardActions" "css_element" should not exist

    # Can continue to add
    And I click on "Add goals" "link_or_button"
    Then I should not see "No items to display" in the tui modal
    Then I should see the following disabled adder picker entries:
      | Goal        | Due date  |
      | Second goal | 1/01/2025 |
    And I should see the tui datatable contains:
      | Goal         | Due date  |
      | Perform goal | 1/01/2024 |
      | Second goal  | 1/01/2025 |
    And I toggle the adder picker entry with "Perform goal" for "Goal"
    And I click on "Add" "button" in the ".tui-modal" "css_element"
    And I click on "Confirm selection" "button"
    Then I should see "Perform goal"
    And I should see "Second goal"
    When I reload the page
    Then I should see "Perform goal"
    And I should see "Second goal"

  Scenario: Selecting view only participant can select perform goals
    Given the following "users" exist:
      | username | firstname | lastname | email             |
      | user4    | User      | Four     | user4@example.com |
    And the following job assignments exist:
      | user  | manager | appraiser |
      | user4 | user2   | user2     |
    And the following "permission overrides" exist:
      | capability                       | permission | role         | contextlevel | reference |
      | perform/goal:viewpersonalgoals   | Allow      | staffmanager | User         | user4     |
      | perform/goal:managepersonalgoals | Allow      | staffmanager | User         | user4     |
    And the following "activity with section and review element" exist in "performelement_linked_review" plugin:
      | activity_name | section_title | element_title        | content_type | selection_relationships | content_type_settings                                                   |
      | activity3     | section3      | Personal goal review | perform_goal | appraiser               | {"enable_status_change":false,"status_change_relationship":"appraiser"} |
    And the following "section relationships" exist in "mod_perform" plugin:
      | section_name | relationship | can_view | can_answer |
      | section3     | subject      | yes      | yes        |
      | section3     | manager      | yes      | yes        |
      | section3     | appraiser    | yes      | no         |
    And the following "participants in section" exist in "performelement_linked_review" plugin:
      | section  | subject_user | user  | relationship | can_answer | can_view |
      | section3 | user4        | user4 | subject      | true       | true     |
      | section3 | user4        | user2 | manager      | true       | true     |
      | section3 | user4        | user2 | appraiser    | false      | true     |
    And the following "goals" exist in "perform_goal" plugin:
      | name             | id_number                   | username | owner | updated_at | start_date | target_date |
      | Personal goal 4A | test-goal-behat-id-number_5 | user4    | user4 | 2023-01-01 | 2022-01-01 | 2024-01-01  |
      | Second goal 4B   | test-goal-behat-id-number_6 | user4    | user4 | 2023-01-10 | 2022-01-10 | 2025-01-01  |

    When I log in as "user2"
    And I navigate to the outstanding perform activities list page
    And I click on "As Appraiser" "link_or_button"
    And I click on "activity3" "link"
    And I click on "Add goals" "link_or_button"
    Then I should not see "No items to display" in the tui modal
    And I should see "Items selected: 0" in the tui modal
    Then I should see the tui datatable contains:
      | Goal             | Due date  |
      | Personal goal 4A | 1/01/2024 |
      | Second goal 4B   | 1/01/2025 |

    When I toggle the adder picker entry with "Personal goal 4A" for "Goal"
    And I should see "Items selected: 1" in the tui modal
    And I click on "Add" "button" in the ".tui-modal" "css_element"
    And I click on "Confirm selection" "button"
    Then I should see "Personal goal 4A"

    When I reload the page
    Then I should see "Personal goal 4A"

  Scenario: Subject can only add goals if there is no existing content when others have progressed the activity
    When I log in as "user2"
    And I navigate to the outstanding perform activities list page
    And I click on "As Manager" "link_or_button"
    And I click on "activity1" "link"
    Then I should see "Awaiting goal selection from a Subject."

    When I log out
    And I log in as "user1"
    And I navigate to the outstanding perform activities list page
    And I click on "activity1" "link"
    And I click on "Add goals" "link_or_button"
    And I toggle the adder picker entry with "Perform goal" for "Goal"
    And I click on "Add" "button" in the ".tui-modal" "css_element"
    And I click on "Confirm selection" "button"
    And I reload the page
    Then I should see "Perform goal"

    When I log out
    And I log in as "user2"
    And I navigate to the outstanding perform activities list page
    And I click on "As Manager" "link_or_button"
    And I click on "activity1" "link"
    Then I should see "Perform goal"

    When I log out
    And I log in as "user1"
    And I navigate to the outstanding perform activities list page
    And I click on "activity1" "link"
    Then "Add goals" "link_or_button" should not exist
