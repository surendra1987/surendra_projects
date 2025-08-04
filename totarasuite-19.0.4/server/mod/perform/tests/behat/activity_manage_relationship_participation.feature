@totara @perform @mod_perform @javascript @vuejs
Feature: Manage relationship participation toggles
  Background:
    Given the following "users" exist:
      | username | firstname | lastname | email             |
      | user1    | user      | 1        | user1@example.com |
      | user2    | user      | 2        | user2@example.com |
    And the following "activities" exist in "mod_perform" plugin:
      | activity_name | activity_type | activity_status | create_track | create_section |
      | activity1     | check-in      | Draft           | true         | true           |

  Scenario: Select manual participant roles
    When I log in as "admin"
    And I navigate to the manage perform activities page
    Then I should see the tui datatable contains:
      | Name      | Type     | Status |
      | activity1 | Check-in | Draft  |

    When I follow "activity1"
    And I click on "Assignments" "link"
    Then I should see "Participation settings"
    And I should see "Override role and relationship change settings"
    And the "Override role and relationship change settings" tui toggle switch should be "off"
    And I should see "Add participant instances"
    And the field "Add participant instances" matches value "Disabled (global default)"
    And I should see "Close participant instances for removed participants"
    And the field "Close participant instances for removed participants" matches value "Disabled (global default)"

    When I click on the "Override role and relationship change settings" tui toggle button
    Then I should see "Activity saved" in the tui success notification toast and close it
    Then I should see "Add participant instances"
    And I should see "Close participant instances for removed participants"
    And the field "Add participant instances" matches value "Disabled (global default)"
    And the field "Close participant instances for removed participants" matches value "Disabled (global default)"

    And I set the field "Add participant instances" to "Enabled"
    Then I should see "Activity saved" in the tui success notification toast and close it
    And I set the field "Close participant instances for removed participants" to "Close all"
    Then I should see "Activity saved" in the tui success notification toast and close it
    And I reload the page
    And I click on "Assignments" "link"
    Then the "Override role and relationship change settings" tui toggle switch should be "on"
    And the field "Add participant instances" matches value "Enabled"
    And the field "Close participant instances for removed participants" matches value "Close all"

    # Make sure the toggle states persist even when hidden
    When I click on the "Override role and relationship change settings" tui toggle button
    Then I should see "Activity saved" in the tui success notification toast and close it
    And I click on the "Override role and relationship change settings" tui toggle button
    Then the "Override role and relationship change settings" tui toggle switch should be "on"
    And the field "Add participant instances" matches value "Disabled (global default)"
    And the field "Close participant instances for removed participants" matches value "Disabled (global default)"

    When I click on the "Override role and relationship change settings" tui toggle button
    Then I should see "Activity saved" in the tui success notification toast and close it
    And I reload the page
    And I click on "Assignments" "link"
    Then the "Override role and relationship change settings" tui toggle switch should be "off"
    And I click on the "Override role and relationship change settings" tui toggle button
    Then I should see "Activity saved" in the tui success notification toast and close it
    And the "Override role and relationship change settings" tui toggle switch should be "on"
    And the field "Add participant instances" matches value "Disabled (global default)"
    And the field "Close participant instances for removed participants" matches value "Disabled (global default)"
