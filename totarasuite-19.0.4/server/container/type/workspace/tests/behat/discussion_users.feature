@totara @totara_engage @container @container_workspace @engage @javascript @editor @editor_weka
Feature: User discussion page
  Background:
    Given I am on a totara site
    And I set the site theme to "ventura"
    And the following "users" exist:
      | username   | firstname | lastname | email             |
      | user_one   | User      | One      | one@example.com   |
      | user_two   | User      | Two      | two@example.com   |
      | user_three | User      | Three    | three@example.com |
    And the following "workspaces" exist in "container_workspace" plugin:
      | name          | owner    | summary         |
      | Workspace 101 | user_one | This is summary |

  Scenario: Viewing user profiles of someone with the same workspace
    Given I log in as "admin"
    And the following config values are set as admin:
      | totara_engage_allow_view_profiles | 1 |
    And I log out

    And I log in as "user_two"
    And I am on Find Workspaces
    And I click on "[aria-label='Workspace 101']" "css_element"
    And I click on "Join workspace Workspace 101" "button"
    And I activate the weka editor with css ".tui-workspaceDiscussionForm__editor"
    And I type "Discussion 100" in the weka editor
    And I wait for the next second
    And I click on "Post" "button"
    And I log out

    And I log in as "user_three"
    And I am on Find Workspaces
    And I click on "[aria-label='Workspace 101']" "css_element"
    And I click on "Join workspace Workspace 101" "button"
    And "User Two" "link" should exist
    When I follow "User Two"
    Then I should see "User details"
    And I log out

    And I log in as "admin"
    And the following config values are set as admin:
      | totara_engage_allow_view_profiles | 0 |
    And I log out

    And I log in as "user_three"
    And I am on Find Workspaces
    And I click on "[aria-label='Workspace 101']" "css_element"
    And "User Two" "link" should not exist
    When I am on profile page for user "user_two"
    Then I should see "The details of this user are not available to you"

    # Coverage test "View full discussion" page with the user comments and their profile links
    Given I am on Find Workspaces
    And I click on "[aria-label='Workspace 101']" "css_element"
    And I click on "Discussion's actions" "button"
    And I click on "View full discussion" "link"
    And "User Two" "link" should not exist
    And I activate the weka editor with css ".tui-commentForm__editor"
    And I type "Hello User One, this is User Three" in the weka editor
    And I wait for the next second
    And I click on "Comment" "button" in the ".tui-commentForm__form" "css_element"
    And I wait for the next second
    And I log out

    And I log in as "user_one"
    And I am on Find Workspaces
    And I click on "[aria-label='Workspace 101']" "css_element"
    And I click on "Discussion's actions" "button"
    When I click on "View full discussion" "link"
    Then "User Two" "link" should not exist
    And "User Three" "link" should not exist
    And I activate the weka editor with css ".tui-commentForm__editor"
    And I type "Hello User Three, this is User One" in the weka editor
    And I wait for the next second
    And I click on "Comment" "button" in the ".tui-commentForm__form" "css_element"
    And I wait for the next second
    And I log out

    And I log in as "user_two"
    And I am on Find Workspaces
    And I click on "[aria-label='Workspace 101']" "css_element"
    And I click on "Discussion's actions" "button"
    When I click on "View full discussion" "link"
    Then "User One" "link" should not exist
    And "User Three" "link" should not exist

    When I am on profile page for user "user_one"
    Then I should see "The details of this user are not available to you"
    And I log out

    And I log in as "admin"
    And the following config values are set as admin:
      | totara_engage_allow_view_profiles | 1 |
    And I log out

    And I log in as "user_two"
    And I am on Find Workspaces
    And I click on "[aria-label='Workspace 101']" "css_element"
    And I click on "Discussion's actions" "button"
    When I click on "View full discussion" "link"
    Then "User One" "link" should exist
    And "User Three" "link" should exist
    When I follow "User One"
    Then I should see "User details"
    And I log out

  Scenario: Viewing user profiles with "likes"
    # By default the user profile links should be disabled
    And I log in as "user_two"
    And I am on Find Workspaces
    And I click on "[aria-label='Workspace 101']" "css_element"
    And I click on "Join workspace Workspace 101" "button"
    And I activate the weka editor with css ".tui-workspaceDiscussionForm__editor"
    And I type "Discussion 100" in the weka editor
    And I wait for the next second
    And I click on "Post" "button"
    And I log out

    And I log in as "user_three"
    And I am on Find Workspaces
    And I click on "[aria-label='Workspace 101']" "css_element"
    And I click on "Join workspace Workspace 101" "button"

    When I click on "Like discussion" "button"
    Then I should see "(1)" in the ".tui-simpleLike__popover" "css_element"
    And I log out

    And I log in as "user_two"
    And I am on Find Workspaces
    And I click on "[aria-label='Workspace 101']" "css_element"
    And I click on "(1)" "link"
    And I should see "User Three" in the ".tui-likeRecordsModal__records" "css_element"
    And "User Three" "link" should not exist in the ".tui-likeRecordsModal__records" "css_element"

    When I am on profile page for user "user_three"
    Then I should see "The details of this user are not available to you"
    And I log out

    And I log in as "admin"
    And the following config values are set as admin:
      | totara_engage_allow_view_profiles | 1 |
    And I log out

    And I log in as "user_two"
    And I am on Find Workspaces
    And I click on "[aria-label='Workspace 101']" "css_element"
    And I click on "(1)" "link"
    And I should see "User Three" in the ".tui-likeRecordsModal__records" "css_element"
    And "User Three" "link" should exist in the ".tui-likeRecordsModal__records" "css_element"

    When I follow "User Three"
    Then I should see "User details"
    And I log out
