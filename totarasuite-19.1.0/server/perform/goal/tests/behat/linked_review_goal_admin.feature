@totara @perform @mod_perform @perform_element @performelement_linked_review @perform_goal @javascript @vuejs
Feature: Manage performance activity review totara goal element.

  Background:
    Given the following "activities" exist in "mod_perform" plugin:
      | activity_name  | activity_type | create_section | create_track | activity_status | anonymous_responses |
      | First Activity | appraisal     | false          | true         | Draft           | false               |
    And the following "activity settings" exist in "mod_perform" plugin:
      | activity_name  | multisection |
      | First Activity | no           |
    And the following "activity sections" exist in "mod_perform" plugin:
      | activity_name  | section_name   |
      | First Activity | Single section |
    And the following "section relationships" exist in "mod_perform" plugin:
      | section_name   | relationship        | can_view | can_answer |
      | Single section | subject             | no       | yes        |
      | Single section | manager             | yes      | yes        |
      | Single section | appraiser           | yes      | no         |
      | Single section | external respondent | yes      | no         |

  Scenario: I can create and update totara goal review content elements.
    Given I log in as "admin"
    And I navigate to the edit perform activities page for activity "First Activity"
    Then I should see "First Activity"
    And I should see "Subject" in the ".tui-performActivitySection__participant-items" "css_element"

    # Add a perform goal review element and verify form validations
    When I click on "Edit content elements" "link_or_button"
    And I add a "Review items" activity content element

    # Title not entered
    And I save the activity content element
    Then I should see "Required"

    When I set the following fields to these values:
      | rawTitle     | Review totara goal |
      | content_type | perform_goal       |
    Then I should see "Selection participant"
    And I should see "Subject"
    And I should see "Manager"
    And I should see "Appraiser"
    And I should see "Allow updating progress in activity"
    And I should not see "Who can update goal progress?"
    When I click on "Show help for Selection participant" "button"
    And I should see "Only 'Responding participants' or 'View-only participants' added for this section are available as participants to select items to review. External participants are not able to select items." in the tui popover
    And I close the tui popover

    # Selection participant not yet selected
    When I save the activity content element
    Then I should see "Required"

    When I click on the "Subject" tui radio in the "Selection participant" tui radio group
    And I click on "Allow updating progress in activity" tui "checkbox"
    Then I should see "Who can update goal progress?"
    When I click on "Show help for Who can update goal progress?" "button"
    And I should see "Only one goal progress change will be submitted to the goal. If there are multiple people in the participant role, the first change submitted will be applied." in the tui popover
    And I close the tui popover

    # Change of goal participant not yet selected
    When I save the activity content element
    Then I should see "Required"

    # And I click on "Allow updating progress in activity" tui "checkbox"
    And I click on the "Subject" tui radio in the "Who can update goal progress?" tui radio group
    And I save the activity content element
    And I wait for pending js
    Then I should see "Element saved" in the tui success notification toast
    And I should see "Review totara goal"

    # Refresh the page to ensure all are stored and refetched correctly
    When I am on homepage
    And I navigate to the edit perform activities page for activity "First Activity"
    And I click on "Edit content elements" "link_or_button"

    And I should see "Goals example" in the ".tui-linkedReviewViewPerformGoal__title" "css_element" of perform admin element "Review totara goal"
    And I should see "This is an example of how a goal will display after a participant has selected it." in the ".tui-linkedReviewViewPerformGoal__description" "css_element" of perform admin element "Review totara goal"
    And I should see "Goal progression update" in the ".tui-performGoalLinkedReviewChangeStatusPreview__title" "css_element"
    And I should see "Awaiting your update on goal progress" in the ".tui-performGoalLinkedReviewChangeStatusPreview__content" "css_element"
    And I should see "Current: 0 / 100 (Not started)" in the ".tui-performGoalLinkedReviewChangeStatusPreview__content" "css_element"

    # Now edit an element
    When I click on the Edit element button for question "Review totara goal"
    Then I should see "Who can update goal progress?"
    When I click on "Allow updating progress in activity" tui "checkbox"
    Then I should not see "Who can update goal progress?"
    And I save the activity content element
    And I wait for pending js
    Then I should see "Element saved" in the tui success notification toast
    And I should see "Review totara goal"
    And I should not see "Goal progression update"
    And I should not see "Awaiting your update on goal progress"
    And I should not see "Current: 0 / 100 (Not started)"

    # Delete
    When I click on the Actions button for question "Review totara goal"
    And I click on "Delete" option in the dropdown menu
    Then I should see "Confirm delete element"
    When I confirm the tui confirmation modal
    And I wait for pending js
    Then I should see "Element deleted." in the tui success notification toast
    And I should not see "Review totara goal"