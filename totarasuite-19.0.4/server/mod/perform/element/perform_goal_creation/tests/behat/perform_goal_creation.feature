@totara @perform @mod_perform @perform_element @perform_goal @javascript @vuejs
Feature: Manage performance activity create personal goal element.

  Background:
    Given the following "users" exist:
      | username     | firstname | lastname | email                   |
      | user_subject | User      | Subject  | usersubject@example.com |
      | user_manager | User      | Manager  | usermanager@example.com |
      | user_peer    | User      | Peer     | userpeer@example.com    |
    And the following job assignments exist:
      | user         | manager      |
      | user_subject | user_manager |
    And the following "activities" exist in "mod_perform" plugin:
      | activity_name  | activity_type | create_section | create_track | activity_status | anonymous_responses |
      | First Activity | appraisal     | false          | true         | Draft           | false               |
    And the following "activity settings" exist in "mod_perform" plugin:
      | activity_name  | multisection |
      | First Activity | no           |
    And the following "activity sections" exist in "mod_perform" plugin:
      | activity_name  | section_name |
      | First Activity | section1     |
    And the following "section relationships" exist in "mod_perform" plugin:
      | section_name | relationship | can_view | can_answer |
      | section1     | subject      | yes      | yes        |
      | section1     | manager      | yes      | yes        |
      | section1     | peer         | yes      | yes        |
    And the following "participants in section" exist in "performelement_linked_review" plugin:
      | section  | subject_user | user         | relationship | can_answer |
      | section1 | user_subject | user_subject | subject      | true       |
      | section1 | user_subject | user_manager | manager      | true       |
      | section1 | user_subject | user_peer    | perform_peer | true       |


  Scenario: I can create perform goal elements
    Given I log in as "admin"
    And I navigate to the edit perform activities page for activity "First Activity"
    Then I should see "First Activity"
    And I should see "Subject" in the ".tui-performActivitySection__participant-items" "css_element"

    When I click on "Edit content elements" "link_or_button"
    And I add a "Create goal" activity content element

    When I set the following fields to these values:
      | rawTitle | Perform goal |
    And I save the activity content element

    Then I should see "Perform goal" in the ".tui-performAdminCustomElement__content-titleText" "css_element"
    And I should see "This is an example of how a new goal will display after a participant has created it." in the ".tui-performGoalCreationAdminView" "css_element"
    And I should see "New goal example" in the ".tui-performGoalCreationAdminView__itemHeading" "css_element"
    And I should see "Updated" in the ".tui-performGoalCreationAdminView__itemContent-date" "css_element"
    And I should see "Start" in the ".tui-performGoalCreationAdminView__bar-heading" "css_element"
    And I should see "Due" in the ".tui-performGoalCreationAdminView__bar" "css_element"

  Scenario: I can create perform goals via the create perform goal element
    Given I log in as "admin"

    # Create and activate the activity
    And I navigate to the edit perform activities page for activity "First Activity"
    And I click on "Edit content elements" "link_or_button"
    And I add a "Create goal" activity content element
    When I set the following fields to these values:
      | rawTitle | Perform goal |
    And I save the activity content element
    And I navigate to the edit perform activities page for activity "First Activity"
    And I click on "Assignments" "link"
    And I click on "Assign users" "button"
    And I click on "Individual" "button" in the ".tui-performPASettingAssignment__heading-add .tui-dropdown__menu" "css_element"
    And I toggle the adder picker entry with "User Peer" for "Users"
    And I save my selections and close the adder
    And I press "Activate"
    And I confirm the tui confirmation modal
    Then I should see "was successfully activated." in the tui success notification toast
    And I log out
    And I trigger cron

    # Test the create perform goal element as subject
    And I log in as "user_subject"
    When I click on "Develop > Activities" in the totara menu
    And I click on "First Activity" "link"
    And "Create goal" "button" should exist
    And ".tui-modalContent" "css_element" should not exist
    And I click on "Create goal" "button"

    # Test the create goal tui modal
    Then I should see "Create goal" in the tui modal
    And I click on "Close" "button" in the ".tui-performGoalActionModalHeader" "css_element"
    And ".tui-modalContent" "css_element" should not exist
    And I click on "Create goal" "button"
    And ".tui-modalContent" "css_element" should exist
    And I set the "dates[start_date]" tui date selector to "2022-01-02"
    And I set the "dates[target_date]" tui date selector to "2021-01-02"
    And I activate the weka editor with css ".tui-performGoalForm__descriptionEditor"
    And I type "This is the description" in the weka editor
    And I click on "Create and close" "button"
    Then I should see "name" form field has the tui validation error "Required"
    And I wait until "Start date cannot be after the due date" "text" exists
    And I set the "dates[target_date]" tui date selector to "2023-01-02"
    And I set the following fields to these values:
      | name         | Test name |
      | target_value | 80        |
    And I click on "Create and close" "button"
    Then I should see "The goal was successfully created." in the tui success notification toast

    # First card
    And I wait until the page is ready
    And ".tui-performGoalCreationResponseDisplay__item" "css_element" should exist
    And I should see "Test name" in the ".tui-performGoalCreationResponseDisplay__item:nth-child(1) .tui-performGoalCreationResponseDisplay__itemHeading-title" "css_element"
    And I should see "Updated" in the ".tui-performGoalCreationResponseDisplay__item:nth-child(1) .tui-performGoalCreationResponseDisplay__itemContent-date" "css_element"
    And I should see "Start" in the ".tui-performGoalCreationResponseDisplay__item:nth-child(1) .tui-performGoalActivityCreationDetails__bar-listItem:nth-child(1) .tui-performGoalActivityCreationDetails__bar-heading" "css_element"
    And I should see "Due" in the ".tui-performGoalCreationResponseDisplay__item:nth-child(1) .tui-performGoalActivityCreationDetails__bar-listItem:nth-child(2) .tui-performGoalActivityCreationDetails__bar-heading" "css_element"
    And I should see "More details" in the ".tui-performGoalCreationResponseDisplay__item:nth-child(1) .tui-performGoalActivityCreationDetails__bar-expand" "css_element"
    And I should not see "Target" in the ".tui-performGoalCreationResponseDisplay__item:nth-child(1)" "css_element"
    And I click on "More details" "button" in the ".tui-performGoalCreationResponseDisplay__item:nth-child(1) .tui-performGoalActivityCreationDetails__bar-expand" "css_element"
    And I should see "Target" in the ".tui-performGoalCreationResponseDisplay__item:nth-child(1)" "css_element"

    # Create another
    And I click on "Create goal" "button"
    And I set the following fields to these values:
      | name | Test name 2 |
    And I click on "Create and close" "button"
    Then I should see "The goal was successfully created." in the tui success notification toast

    # Second card
    And ".tui-performGoalCreationResponseDisplay__item:nth-child(2)" "css_element" should exist
    And I should see "Test name 2" in the ".tui-performGoalCreationResponseDisplay__item:nth-child(2) .tui-performGoalCreationResponseDisplay__itemHeading-title" "css_element"
    And I should see "Updated" in the ".tui-performGoalCreationResponseDisplay__item:nth-child(2) .tui-performGoalCreationResponseDisplay__itemContent-date" "css_element"
    And I should see "More details" in the ".tui-performGoalCreationResponseDisplay__item:nth-child(2) .tui-performGoalActivityCreationDetails__bar-expand" "css_element"

    And I click on "Save as draft" "button"
    Then I should see "Draft saved" in the tui success notification toast
    And I reload the page
    And I should see "Test name" in the ".tui-performGoalCreationResponseDisplay__item:nth-child(1) .tui-performGoalCreationResponseDisplay__itemHeading-title" "css_element"
    And I should see "Test name 2" in the ".tui-performGoalCreationResponseDisplay__item:nth-child(2) .tui-performGoalCreationResponseDisplay__itemHeading-title" "css_element"

    # Create another
    And I click on "Create goal" "button"
    And I set the following fields to these values:
      | name | Test name 3 |
    And I click on "Create and close" "button"
    Then I should see "The goal was successfully created." in the tui success notification toast

    # Third card
    And ".tui-performGoalCreationResponseDisplay__item:nth-child(3)" "css_element" should exist
    And I should see "Test name 3" in the ".tui-performGoalCreationResponseDisplay__item:nth-child(3) .tui-performGoalCreationResponseDisplay__itemHeading-title" "css_element"
    And I should see "Updated" in the ".tui-performGoalCreationResponseDisplay__item:nth-child(3) .tui-performGoalCreationResponseDisplay__itemContent-date" "css_element"
    And I should see "More details" in the ".tui-performGoalCreationResponseDisplay__item:nth-child(3) .tui-performGoalActivityCreationDetails__bar-expand" "css_element"

    And I click on "Submit" "button"
    And I confirm the tui confirmation modal
    And I should see "Section submitted" in the tui success notification toast

    When I click on "Performance activities" "link"
    And I click on "First Activity" "link"
    And I should see "Test name" in the ".tui-performGoalCreationResponseDisplay__item:nth-child(1) .tui-performGoalCreationResponseDisplay__itemHeading-title" "css_element"
    And I should see "Test name 2" in the ".tui-performGoalCreationResponseDisplay__item:nth-child(2) .tui-performGoalCreationResponseDisplay__itemHeading-title" "css_element"
    And I should see "Test name 3" in the ".tui-performGoalCreationResponseDisplay__item:nth-child(3) .tui-performGoalCreationResponseDisplay__itemHeading-title" "css_element"

    And I log out

    # Test the create perform goal element as manager
    And I log in as "user_manager"
    And I navigate to the outstanding perform activities list page
    And I click on "As Manager" "link_or_button"
    And I click on "First Activity" "link"
    And "Create goal" "button" should exist
    And I click on "Create goal" "button"

    # Test the create goal tui modal
    Then I should see "Create goal" in the tui modal
    And I set the following fields to these values:
      | name         | Test name |
      | target_value | 1         |
    And I click on "Create and close" "button"
    Then I should see "The goal was successfully created." in the tui success notification toast
    And I click on "Save as draft" "button"
    Then I should see "Draft saved" in the tui success notification toast
    And I click on "Submit" "button"
    And I confirm the tui confirmation modal
    And I should see "Section submitted" in the tui success notification toast
    And I log out

    # Test the create goal element as peer (who cannot create a goal)
    And I log in as "user_peer"
    And I navigate to the outstanding perform activities list page
    And I click on "As Peer" "link_or_button"
    And I click on "First Activity" "link"
    Then "Create goal" "button" should not exist
    And I should see "You don't have permission to create goals for User Subject."


  Scenario: I can edit and delete create perform goal elements
    Given I log in as "admin"

    # Create and activate the activity
    And I navigate to the edit perform activities page for activity "First Activity"
    And I click on "Edit content elements" "link_or_button"
    And I add a "Create goal" activity content element
    When I set the following fields to these values:
      | rawTitle | Perform goal |
    And I save the activity content element
    And I navigate to the edit perform activities page for activity "First Activity"
    And I click on "Assignments" "link"
    And I click on "Assign users" "button"
    And I click on "Individual" "button" in the ".tui-performPASettingAssignment__heading-add .tui-dropdown__menu" "css_element"
    And I toggle the adder picker entry with "User Peer" for "Users"
    And I save my selections and close the adder
    And I press "Activate"
    And I confirm the tui confirmation modal
    Then I should see "was successfully activated." in the tui success notification toast
    And I log out
    And I trigger cron


    # Test the create perform goal element as subject
    And I log in as "user_subject"
    When I click on "Develop > Activities" in the totara menu
    And I click on "First Activity" "link"
    And "Create goal" "button" should exist
    And ".tui-modalContent" "css_element" should not exist
    And I click on "Create goal" "button"

    # Create a goal
    Then I should see "Create goal" in the tui modal
    And I click on "Close" "button" in the ".tui-performGoalActionModalHeader" "css_element"
    And ".tui-modalContent" "css_element" should not exist
    And I click on "Create goal" "button"
    And ".tui-modalContent" "css_element" should exist
    And I set the "dates[start_date]" tui date selector to "2022-01-02"
    And I set the "dates[target_date]" tui date selector to "2021-01-02"
    And I activate the weka editor with css ".tui-performGoalForm__descriptionEditor"
    And I type "This is the description" in the weka editor
    And I click on "Create and close" "button"
    Then I should see "name" form field has the tui validation error "Required"
    And I wait until "Start date cannot be after the due date" "text" exists
    And I set the "dates[target_date]" tui date selector to "2023-01-02"
    And I set the following fields to these values:
      | name         | Test name |
      | target_value | 80        |
    And I activate the weka editor with css ".tui-performGoalForm__descriptionEditor"
    And I click on "Create and close" "button"
    Then I should see "The goal was successfully created." in the tui success notification toast

    # Displayed goal
    And I wait until the page is ready
    And ".tui-performGoalCreationResponseDisplay__item" "css_element" should exist
    And I should see "Test name" in the ".tui-performGoalCreationResponseDisplay__item:nth-child(1) .tui-performGoalCreationResponseDisplay__itemHeading-title" "css_element"
    And I should see "Updated" in the ".tui-performGoalCreationResponseDisplay__item:nth-child(1) .tui-performGoalCreationResponseDisplay__itemContent-date" "css_element"
    And I should see "Start" in the ".tui-performGoalCreationResponseDisplay__item:nth-child(1) .tui-performGoalActivityCreationDetails__bar-listItem:nth-child(1) .tui-performGoalActivityCreationDetails__bar-heading" "css_element"
    And I should see "Due" in the ".tui-performGoalCreationResponseDisplay__item:nth-child(1) .tui-performGoalActivityCreationDetails__bar-listItem:nth-child(2) .tui-performGoalActivityCreationDetails__bar-heading" "css_element"
    And I should see "More details" in the ".tui-performGoalCreationResponseDisplay__item:nth-child(1) .tui-performGoalActivityCreationDetails__bar-expand" "css_element"
    And I should not see "Target" in the ".tui-performGoalCreationResponseDisplay__item:nth-child(1)" "css_element"
    And I click on "More details" "button" in the ".tui-performGoalCreationResponseDisplay__item:nth-child(1) .tui-performGoalActivityCreationDetails__bar-expand" "css_element"
    And I should see "Target" in the ".tui-performGoalCreationResponseDisplay__item:nth-child(1)" "css_element"

    # Edit goal (not saved as response)
    When I click on "Manage goal actions" "button" in the ".tui-performGoalCreationResponseDisplay__item:nth-child(1)" "css_element"
    And I click on "Edit" "link" in the ".tui-dropdown__menu--open" "css_element"
    Then I should see "Edit goal" in the tui modal
    And I set the following fields to these values:
      | name         | Modified goal |
      | target_value | 62            |
    And I activate the weka editor with css ".tui-performGoalForm__descriptionEditor"
    And I select the text "This is the description" in the weka editor
    And I type "Test goal description edited" in the weka editor
    And I click on "Save" "button" in the ".tui-modalContent__footer" "css_element"
    And I click on "Close" "button" in the ".tui-performGoalActionModalHeader" "css_element"
    And I should see "Modified goal" in the ".tui-performGoalCreationResponseDisplay__item:nth-child(1) .tui-performGoalCreationResponseDisplay__itemHeading-title" "css_element"
    And I should see "62" in the ".tui-performGoalCreationResponseDisplay__item:nth-child(1) .tui-performGoalActivityCreationDetails__content" "css_element"
    And I should see "Test goal description edited" in the ".tui-performGoalCreationResponseDisplay__item:nth-child(1) .tui-performGoalActivityCreationDetails__content" "css_element"

    # Edit goal as saved response
    And I click on "Save as draft" "button"
    Then I should see "Draft saved" in the tui success notification toast
    And I reload the page

    When I click on "Manage goal actions" "button" in the ".tui-performGoalCreationResponseDisplay__item:nth-child(1)" "css_element"
    And I click on "Edit" "link" in the ".tui-dropdown__menu--open" "css_element"
    Then I should see "Edit goal" in the tui modal
    And I set the following fields to these values:
      | name | Modified goal again |

    And I click on "Save" "button" in the ".tui-modalContent__footer" "css_element"
    And I click on "Close" "button" in the ".tui-performGoalActionModalHeader" "css_element"
    And I should see "Modified goal" in the ".tui-performGoalCreationResponseDisplay__item:nth-child(1) .tui-performGoalCreationResponseDisplay__itemHeading-title" "css_element"

    # Create Additonal goals
    And I click on "Create goal" "button"
    And I set the following fields to these values:
      | name | Goal 1 |
    And I click on "Create and close" "button"
    Then I should see "The goal was successfully created." in the tui success notification toast
    And I click on "Create goal" "button"
    And I set the following fields to these values:
      | name | Goal 2 |
    And I click on "Create and close" "button"
    Then I should see "The goal was successfully created." in the tui success notification toast
    And I click on "Create goal" "button"
    And I set the following fields to these values:
      | name | Goal 3 |
    And I click on "Create and close" "button"
    Then I should see "The goal was successfully created." in the tui success notification toast

    # Check the new goals are visible
    And ".tui-performGoalCreationResponseDisplay__item:nth-child(2)" "css_element" should exist
    And ".tui-performGoalCreationResponseDisplay__item:nth-child(3)" "css_element" should exist
    And ".tui-performGoalCreationResponseDisplay__item:nth-child(4)" "css_element" should exist
    And I should see "Goal 1" in the ".tui-performGoalCreationResponseDisplay__item:nth-child(2) .tui-performGoalCreationResponseDisplay__itemHeading-title" "css_element"
    And I should see "Goal 2" in the ".tui-performGoalCreationResponseDisplay__item:nth-child(3) .tui-performGoalCreationResponseDisplay__itemHeading-title" "css_element"
    And I should see "Goal 3" in the ".tui-performGoalCreationResponseDisplay__item:nth-child(4) .tui-performGoalCreationResponseDisplay__itemHeading-title" "css_element"

    # Delete a goal
    When I click on "Manage goal actions" "button" in the ".tui-performGoalCreationResponseDisplay__item:nth-child(1)" "css_element"
    And I click on "Delete" "link" in the ".tui-dropdown__menu--open" "css_element"
    And I should see "Delete goal" in the tui modal
    When I click on "Delete" "button"
    Then I should see "The goal was successfully deleted." in the tui success notification toast

    # Check the deleted goal is not visible
    And ".tui-performGoalCreationResponseDisplay__item:nth-child(2)" "css_element" should exist
    And ".tui-performGoalCreationResponseDisplay__item:nth-child(3)" "css_element" should exist
    And ".tui-performGoalCreationResponseDisplay__item:nth-child(4)" "css_element" should not exist

