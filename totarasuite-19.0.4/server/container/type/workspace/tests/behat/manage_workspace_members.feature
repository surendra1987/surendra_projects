@totara @totara_engage @container @container_workspace @engage @javascript
Feature: Manage members in a workspaces
  Background:
    Given I am on a totara site
    And I set the site theme to "ventura"
    And the following "users" exist:
      | username   | firstname | middlename | lastname | email             |
      | userone    | User      | Jim        | One      | one@example.com   |
      | user_two   | User      | Jill       | 2        | two@example.com   |
      | user_three | User      | Jane       | 3        | three@example.com |
      | user_four  | User      | Joe        | 4        | four@example.com  |

    And the following "workspaces" exist in "container_workspace" plugin:
      | name          | summary | owner   |
      | Workspace 101 | Spaces  | userone |

    And I log in as "userone"
    And I click on "Your workspaces" in the totara menu
    And I click on "[aria-label='Workspace 101']" "css_element"
    And I click on "Owner" "button"
    And I click on "Add members" "link"
    And I toggle the selection of all rows in the tui select table
    And I click on "Add" "button"
    And I log out

  Scenario: Can add additional workspace owners
    Given I log in as "userone"
    And I am on Find Workspaces
    And I click on "[aria-label='Workspace 101']" "css_element"
    And I click on "Members (5)" "link" in the ".tui-tabBar" "css_element"
    And I should see "Admin User" in the ".tui-workspaceMembersTab__member:nth-child(1) .tui-miniProfileCard__description" "css_element"
    Then I should see "User One" in the ".tui-workspaceMembersTab__member:nth-child(2) .tui-miniProfileCard__row-link" "css_element"
    And I should see "User 2" in the ".tui-workspaceMembersTab__member:nth-child(3) .tui-miniProfileCard__description" "css_element"
    And I should see "User 3" in the ".tui-workspaceMembersTab__member:nth-child(4) .tui-miniProfileCard__description" "css_element"
    And I should see "User 4" in the ".tui-workspaceMembersTab__member:nth-child(5) .tui-miniProfileCard__description" "css_element"

    # Check can see current owner
    And I should see "Owner" in the ".tui-workspaceMembersTab__member:nth-child(2) .tui-workspaceMemberCard__ownerLabel" "css_element"

    # Add a new additional owner.
    When I click on "More actions for member User 2" "button" in the ".tui-workspaceMembersTab__member:nth-child(3)" "css_element"
    And I click on "Set as owner" "button" in the ".tui-dropdown__menu--open" "css_element"
    Then I should see "Set as owner" in the ".tui-modalContent__header-title" "css_element"
    And I should see "User 2 will be an owner of this workspace." in the ".tui-modalContent__content" "css_element"
    And I confirm the tui confirmation modal
    Then I should see "User 2 is now an owner of the Workspace 101 workspace." in the tui success notification toast and close it
    And I should see "Owner" in the ".tui-workspaceMembersTab__member:nth-child(3)" "css_element"

  Scenario: Can add and remove additional workspace owners
    Given I log in as "userone"
    And I am on Find Workspaces
    And I click on "[aria-label='Workspace 101']" "css_element"
    And I click on "Members (5)" "link" in the ".tui-tabBar" "css_element"

    # Add new additional owner
    When I click on "More actions for member User 3" "button" in the ".tui-workspaceMembersTab__member:nth-child(4)" "css_element"
    And I click on "Set as owner" "button" in the ".tui-dropdown__menu--open" "css_element"
    And I should see "User 3 will be an owner of this workspace." in the ".tui-modalContent__content" "css_element"
    And I confirm the tui confirmation modal
    Then I should see "User 3 is now an owner of the Workspace 101 workspace." in the tui success notification toast and close it

    # Remove the owner role from the user.
    When I click on "More actions for member User 3" "button" in the ".tui-workspaceMembersTab__member:nth-child(4)" "css_element"
    And I click on "Remove as owner" "button" in the ".tui-dropdown__menu--open" "css_element"
    And I should see "User 3 will be removed as an owner of this workspace." in the ".tui-modalContent__content" "css_element"
    And I confirm the tui confirmation modal
    Then I should see "User 3 has been successfully removed as the owner of the Workspace 101 workspace." in the tui success notification toast and close it
    # On removing an owner’s ownership, this user becomes a normal member.
    And I should not see "Owner" in the ".tui-workspaceMembersTab__member:nth-child(4)" "css_element"

    # Re-add the owner role to the user again.
    When I click on "More actions for member User 3" "button" in the ".tui-workspaceMembersTab__member:nth-child(4)" "css_element"
    And I click on "Set as owner" "button" in the ".tui-dropdown__menu--open" "css_element"
    And I should see "User 3 will be an owner of this workspace." in the ".tui-modalContent__content" "css_element"
    And I confirm the tui confirmation modal
    Then I should see "User 3 is now an owner of the Workspace 101 workspace." in the tui success notification toast and close it

  Scenario: Owner can remove themself as owner when there are multiple owners
    Given I log in as "userone"
    And I am on Find Workspaces
    And I click on "[aria-label='Workspace 101']" "css_element"
    And I click on "Members (5)" "link" in the ".tui-tabBar" "css_element"

    # Add new additional owner.
    When I click on "More actions for member User 3" "button" in the ".tui-workspaceMembersTab__member:nth-child(4)" "css_element"
    And I click on "Set as owner" "button" in the ".tui-dropdown__menu--open" "css_element"
    And I should see "User 3 will be an owner of this workspace." in the ".tui-modalContent__content" "css_element"
    And I confirm the tui confirmation modal
    Then I should see "User 3 is now an owner of the Workspace 101 workspace." in the tui success notification toast and close it

    # Remove the owner role from the logged-in user.
    When I click on "More actions for member User One" "button" in the ".tui-workspaceMembersTab__member:nth-child(2)" "css_element"
    And I click on "Remove as owner" "button" in the ".tui-dropdown__menu--open" "css_element"
    And I should see "User One will be removed as an owner of this workspace." in the ".tui-modalContent__content" "css_element"
    And I confirm the tui confirmation modal
    Then I should see "User One has been successfully removed as the owner of the Workspace 101 workspace." in the tui success notification toast and close it

  Scenario: If there is only one owner left, then cannot remove this owner
    Given I log in as "userone"
    And I am on Find Workspaces
    And I click on "[aria-label='Workspace 101']" "css_element"
    And I click on "Members (5)" "link" in the ".tui-tabBar" "css_element"
    # Should not be be able to remove the owner role from the logged-in user.
    Then I should not see "More actions for member User One" in the ".tui-workspaceMembersTab__member:nth-child(1)" "css_element"

  Scenario: Suspended users cannot be added as owners
    # Suspend an existing user member in a workspace.
    Given I log in as "admin"
    And I navigate to "Manage users" node in "Site administration > Users"
    And I click on "Manage login of User 3" "link" in the "User 3" "table_row"
    And I set the "Choose" Totara form field to "Suspend user account"
    And I press "Update"
    And I log out

    Given I log in as "userone"
    And I am on Find Workspaces
    And I click on "[aria-label='Workspace 101']" "css_element"
    And I click on "Members (5)" "link" in the ".tui-tabBar" "css_element"
    # In the meatballs menu for the member, I should not be able to click 'Set as owner'.
    And I click on "More actions for member User 3" "button" in the ".tui-workspaceMembersTab__member:nth-child(4)" "css_element"
    Then I should not see "Set as owner" in the ".tui-dropdown__menu--open" "css_element"
