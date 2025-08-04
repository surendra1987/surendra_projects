@container @workspace @container_workspace @totara @totara_engage @engage @javascript
Feature: Workspaces cannot be seen when the capability is disabled.

  Background:
    Given I am on a totara site
    And I enable the "container_workspace" advanced feature
    And the following "users" exist:
      | username | firstname | lastname | email          |
      | user1    | User      | One      | user1@test.com |
    And I log in as "admin"

  Scenario: Can see the workspaces menu items when it's enabled
    When I set the following system permissions of "Authenticated User" role:
      | container/workspace:workspacesview | Allow |
    And I log out
    And I log in as "user1"
    And I should see "Your workspaces" in the totara menu

  Scenario: Cannot see the workspaces menu items when it's disabled
    When I set the following system permissions of "Authenticated User" role:
      | container/workspace:workspacesview | Prohibit |
    And I log out
    And I log in as "user1"
    And I should not see "Your workspaces" in the totara menu