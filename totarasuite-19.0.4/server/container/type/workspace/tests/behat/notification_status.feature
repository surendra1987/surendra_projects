@container @totara @container_workspace @engage
Feature: Functionalities around mute button in workspace
  Background:
    Given I am on a totara site
    And the following "users" exist:
      | username | firstname | lastname | email           |
      | user_one | User      | One      | one@example.com |
      | user_two | User      | Two      | two@example.com |

    And the following "workspaces" exist in "container_workspace" plugin:
      | name          | owner    | summary           |
      | Workspace 101 | user_one | Workspace |

  @javascript
  Scenario: Normal user can only see mute button when joined the workspace
    Given I log in as "user_two"
    And I am on Find Workspaces
    When I click on "[aria-label='Workspace 101']" "css_element"
    Then "Mute workspace" "button" should not exist
    And "Unmute workspace" "button" should not exist
    When I click on "Join workspace" "button"
    Then "Mute workspace" "button" should exist
    And "Unmute workspace" "button" should not exist
    When I click on "Mute workspace" "button"
    Then "Unmute workspace" "button" should exist
    And "Mute workspace" "button" should not exist
    # We are making sure that the button stays the same.
    And I am on Find Workspaces
    When I click on "[aria-label='Workspace 101']" "css_element"
    Then "Unmute workspace" "button" should exist
    And "Mute workspace" "button" should not exist

  @javascript
  Scenario: Normal user can mute the workspace via drop down items
    Given I log in as "user_two"
    And I am on Find Workspaces
    And I click on "[aria-label='Workspace 101']" "css_element"
    When I click on "Join workspace" "button"
    Then I should see "Joined"
    When I click on "Joined" "button"
    Then I should see "Mute notifications"
    And I should not see "Unmute notifications"
    When I click on "Mute notifications" "link"
    Then "Unmute workspace" "button" should exist
    When I click on "Joined" "button"
    Then I should see "Unmute notifications"
    And I should not see "Mute notifications"
    When I click on "Unmute notifications" "link"
    Then "Mute workspace" "button" should exist
