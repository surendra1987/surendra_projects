@totara @engage @container_workspace @container @javascript @vuejs @totara_catalog
Feature: Workspaces can be searched by tag in catalog
  Background:
    Given I am on a totara site
    And the following "tags" exist:
      | name  | isstandard |
      | One   | 1          |
      | Two   | 1          |
      | Three | 1          |
      | Four  | 1          |
    And the following "workspaces" exist in "container_workspace" plugin:
      | name          | owner | summary | summary_format |
      | Workspace one | admin |         | 5 |
      | Workspace two | admin |         | 5 |
    And I log in as "admin"
    And I set the following administration settings values:
      | catalogtype | Grid |
    And I am on totara catalog page
    And I follow "Configure catalogue"
    And I follow "Filters"
    And I set the field "Add another..." to "Default collection"
    And I click on "Save" "button"
    And I access the "Workspace one" workspace
    And I click on "Owner" "button"
    And I click on "Edit workspace" "link"
    And I click on "Tag list" "button" in the ".tui-topicsSelector" "css_element"
    And I click on "One" option in the dropdown menu
    And I click on "Save changes" "button"

  Scenario: Searching by tag
    Given I click on "Find learning" in the totara menu
    Then I should see "Workspace two"
    And I should see "Workspace one"
    When I click on "One" "link"
    Then I should not see "Workspace two"
    And I should see "Workspace one"