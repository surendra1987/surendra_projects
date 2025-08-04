@totara @totara_engage @container @container_workspace @engage
Feature: Removing members from the workspace
  Background:
    Given I am on a totara site
    And I set the site theme to "ventura"
    And the following "users" exist:
      | username | firstname | lastname | email           |
      | user_one | User      | One      | one@example.com |
      | user_two | User      | Two      | two@example.com |
    And I log in as "user_one"
    And I click on "Your workspaces" in the totara menu
    And I click on "Create new" "button"
    And I set the field "Workspace name" to "Workspace 101"
    And I click on "Submit" "button"
    And I click on "[aria-label='Workspace 101']" "css_element"
    Then I should see "Members (1)"
    When I log out
    And I log in as "user_two"
    And I am on Find Workspaces
    And I click on "[aria-label='Workspace 101']" "css_element"
    And I click on "Join workspace" "button"
    Then I log out

  @javascript
  Scenario: Workspace owner remove member
    Given I log in as "user_one"
    When I click on "Your workspaces" in the totara menu
    And I click on "[aria-label='Workspace 101']" "css_element"
    Then I should see "Members (2)"
    And I click on "Members (2)" "link"
    And I should see "User Two"
    And I click on "More actions for member User Two" "button"
    And I should see "Remove from workspace"
    When I click on "Remove from workspace" "button" in the ".tui-dropdown__menu--open" "css_element"
    Then I should see "Are you sure you want to remove User Two from this workspace?"
    And I click on "Remove" "button" in the ".tui-modalContent__footer" "css_element"
    Then I should not see "User Two"

  @javascript
  Scenario: Remove members should not be available if capability is removed.
    Given I log in as "admin"
    And I set the following system permissions of "Workspace Owner" role:
      | container/workspace:removemember | Prohibit |
    And I log out
    And I log in as "user_one"
    When I click on "Your workspaces" in the totara menu
    And I click on "[aria-label='Workspace 101']" "css_element"
    Then I should see "Members (2)"
    And I click on "Members (2)" "link"
    And I should see "User Two"
    And I should not see "More actions for member User Two"

    # Admin should still see the option to remove members
    When I log out
    And I log in as "admin"
    And I am on Find Workspaces
    When I click on "[aria-label='Workspace 101']" "css_element"
    Then I should see "Members (2)"
    When I click on "Members (2)" "link"
    Then I should see "User Two"
    When I click on "More actions for member User Two" "button"
    And I should see "Remove"