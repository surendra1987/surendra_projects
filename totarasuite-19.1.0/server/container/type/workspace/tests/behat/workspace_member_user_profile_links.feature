@totara @container @container_workspace @engage
Feature: Test workspace members for a user profile link
  Background:
    Given I am on a totara site
    And the following "users" exist:
      | username   | firstname | middlename | lastname | email             |
      | user_one   | User      | Jack       | One      | one@example.com   |
      | user_two   | User      | Jill       | Two      | two@example.com   |
      | user_three | User      | Jane       | Three    | three@example.com |
      | user_four  | User      | Joe        | Four     | four@example.com  |
    And the following "workspaces" exist in "container_workspace" plugin:
      | name          | owner    | summary           |
      | Workspace 102 | user_one | Workspace summary |
    And I log in as "user_one"
    And I click on "Your workspaces" in the totara menu
    And I click on "[aria-label='Workspace 102']" "css_element"
    And I click on "Owner" "button"
    And I click on "Add members" "link"
    And I toggle the selection of all rows in the tui select table
    And I click on "Add" "button"
    And I log out


  @javascript
  Scenario: Workspace user profile links with totara_engage_allow_view_profiles setting
    # By default the user profile links should be disabled
    Given I log in as "user_one"
    And I click on "Your workspaces" in the totara menu
    And I click on "[aria-label='Workspace 102']" "css_element"
    And I should see "Members (5)"
    When I click on "Members (5)" "link"
    Then I should see "User Two"
    And I should see "User Three"
    And I should see "User Four"
    And "User One" "link" should exist
    And "User Two" "link" should not exist
    And "User Three" "link" should not exist
    And "User Four" "link" should not exist
    When I am on profile page for user "user_two"
    Then I should see "The details of this user are not available to you"
    And I log out

    Given I log in as "admin"
    And the following config values are set as admin:
      | totara_engage_allow_view_profiles | 1 |
    And I log out

    And I log in as "user_one"
    And I click on "Your workspaces" in the totara menu
    And I click on "[aria-label='Workspace 102']" "css_element"
    And I should see "Members (5)"
    When I click on "Members (5)" "link"
    Then I should see "User Two"
    And I should see "User Three"
    And I should see "User Four"
    And "User One" "link" should exist
    And "User Two" "link" should exist
    And "User Three" "link" should exist
    And "User Four" "link" should exist
    When I follow "User Two"
    Then I should see "User details"
    And I log out

