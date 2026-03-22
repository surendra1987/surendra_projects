@totara @engage @container_workspace @container @javascript @vuejs @totara_catalog
Feature: Workspaces appear in the grid catalog
  Background:
    Given I am on a totara site
    And the following "users" exist:
      | username | firstname | lastname |
      | user1    | User      | One      |
      | user2    | User      | Two      |
    And the following "workspaces" exist in "container_workspace" plugin:
      | name              | owner  | summary                   | private | hidden |
      | Public workspace  | user1  | Public workspace summary  | 0       | 0      |
      | Private workspace | user1  | Private workspace summary | 1       | 0      |
      | Hidden workspace  | user1  | Hidden workspace summary  | 1       | 1      |
    And I log in as "admin"
    And I set the following administration settings values:
      | catalogtype | Grid |
    And I log out

  Scenario: Check workspaces are shown as expected in the catalog
    Given I log in as "user1"
    And I click on "Find learning" in the totara menu
    Then I should see "Public workspace"
    And I should see "Private workspace"
    And I should see "Hidden workspace"

    When I click on "Hidden workspace" "text"
    Then I should see "Hidden workspace summary"

    When I log out
    And I log in as "user2"
    And I click on "Find learning" in the totara menu
    Then I should see "Public workspace"
    And I should see "Private workspace"
    And I should not see "Hidden workspace"

    When I click on "Private workspace" "text"
    Then I should see "Private workspace summary"
    Then I should see "Request to join"

  Scenario: Search workspaces in the catalog
    Given I log in as "user1"
    And I click on "Find learning" in the totara menu
    And I set the field "Search" to "Hidden"
    And I click on "Search" "button"
    Then I should not see "Public workspace"
    And I should not see "Private workspace"
    And I should see "Hidden workspace"

    Given I log in as "user2"
    And I click on "Find learning" in the totara menu
    And I set the field "Search" to "Private"
    And I click on "Search" "button"
    Then I should not see "Public workspace"
    And I should see "Private workspace"
    And I should not see "Hidden workspace"
