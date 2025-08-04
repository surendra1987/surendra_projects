@totara @perform @mod_perform @javascript @vuejs
Feature: Manage activity workflow settings

  Background:
    Given I am on a totara site
    And I log in as "admin"
    And the following "activities" exist in "mod_perform" plugin:
      | activity_name    | description      | activity_type | create_track | activity_status |
      | My Test Activity | My Test Activity | feedback      | true         | Draft           |

  Scenario: Test On completion setting
    # Activity not activated
    When I navigate to the manage perform activities page
    And I click on "My Test Activity" "link"
    And I click on "Visibility and closure" "link"
    Then the "Close on submission, responses will not be editable" tui toggle switch should be "off"
    When I click on the "Close on submission, responses will not be editable" tui toggle button
    And I reload the page
    And I click on "Visibility and closure" "link"
    Then the "Close on submission, responses will not be editable" tui toggle switch should be "on"

    # Activity activated
    When I manually activate the perform activity "My Test Activity"
    And I reload the page
    And I click on "Visibility and closure" "link"
    And I click on the "Close on submission, responses will not be editable" tui toggle button
    Then I should see "Only future instances and those that are not yet complete will remain open on completion. Already closed instances will remain that way."
    When I confirm the tui confirmation modal
    Then the "Close on submission, responses will not be editable" tui toggle switch should be "off"
    When I click on the "Close on submission, responses will not be editable" tui toggle button
    Then I should see "Only future instances and those that are not yet complete will be automatically closed on completion. Already completed instances will not be affected."
    When I click on "Cancel" "button" in the ".tui-modal" "css_element"
    Then the "Close on submission, responses will not be editable" tui toggle switch should be "off"

  Scenario: Test On due date setting
    # Activity not activated
    When I navigate to the manage perform activities page
    And I click on "My Test Activity" "link"
    And I click on "Visibility and closure" "link"
    Then the field "Close on due date (no due date set)" matches value "0"
    And the "Close on due date (no due date set)" "checkbox" should be disabled

    # Enable due date and ensure the 'On due date' toggle can be enabled
    When I click on "Creation" "link"
    Then I should not see "All active instances of the activity will close on the due date" in the ".tui-assignmentScheduleDueDate__description" "css_element"
    And the "Due date" tui toggle switch should be "off"
    Then I click on the "Due date" tui toggle button
    And the "Due date" tui toggle switch should be "on"
    And I click on "Update instance creation" "button"
    And I confirm the tui confirmation modal
    And I click on "Visibility and closure" "link"
    Then I should not see "Close on due date (no due date set)"
    And I should see "Close on due date"
    And the field "Close on due date" matches value "0"
    And I click on "Close on due date" tui "checkbox"
    And I reload the page
    And I click on "Visibility and closure" "link"
    Then the field "Close on due date" matches value "1"
    When I click on "Creation" "link"
    Then I should see "All active instances of the activity will close on the due date" in the ".tui-assignmentScheduleDueDate__description" "css_element"

    When the "Due date" tui toggle switch should be "on"
    And I click on the "Due date" tui toggle button
    Then I should see "Disable due date?"
    When I click on "Disable" "button" in the ".tui-modal" "css_element"
    And I click on "Update instance creation" "button"
    And I confirm the tui confirmation modal
    And I click on "Visibility and closure" "link"
    Then the field "Close on due date (no due date set)" matches value "0"
    And I click on "Creation" "link"
    And the "Due date" tui toggle switch should be "off"
    Then I click on the "Due date" tui toggle button
    And I click on "Update instance creation" "button"
    And I confirm the tui confirmation modal
    And I click on "Content" "link"

    # Activity activated
    When I manually activate the perform activity "My Test Activity"
    And I reload the page
    And I click on "Visibility and closure" "link"
    When I click on "Close on due date" tui "checkbox"

    Then I should see "All open and overdue activity instances, and any activity instances created in the future, will automatically close on the activity due date."
    When I confirm the tui confirmation modal
    Then the field "Close on due date" matches value "1"
    When I click on "Close on due date" tui "checkbox"
    Then I should see "All open activity instances and any activity instances created in the future will no longer automatically close on the activity due date."
    When I click on "Cancel" "button" in the ".tui-modal" "css_element"
    Then the field "Close on due date" matches value "1"

  Scenario: Test manual close setting
    # Manual close setting visible and not activated
    When I navigate to the manage perform activities page
    And I click on "My Test Activity" "link"
    And I click on "Visibility and closure" "link"
    And the field "Manual close" matches value "0"

    # Enable manual closure
    When I click on "Manual close" tui "checkbox"
    And I reload the page
    And I click on "Visibility and closure" "link"
    Then the field "Manual close" matches value "1"

    # Disable manual closure in active activity
    When I manually activate the perform activity "My Test Activity"
    And I reload the page
    And I click on "Visibility and closure" "link"
    When I click on "Manual close" tui "checkbox"
    Then I should see "Only open instances cannot be manually closed. Already closed instances will not be affected."
    When I click on "Cancel" "button" in the ".tui-modal" "css_element"
    Then the field "Manual close" matches value "1"

