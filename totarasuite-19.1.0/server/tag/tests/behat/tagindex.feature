@core @core_tag
Feature: Browsing tagged items
  In order to search by tag
  As a user
  I need to be able to browse tagged items

  Background:
    Given the following "users" exist:
      | username | firstname | lastname | email             | interests        |
      | user1    | User      | 1        | user1@example.com | Cat, Zebra       |
      | user2    | User      | 2        | user2@example.com | Cat, Dog, Zebra  |
      | user3    | User      | 3        | user3@example.com | Zebra            |
      | user4    | User      | 4        | user4@example.com | Zebra            |
      | user5    | User      | 5        | user5@example.com | Zebra            |
      | user6    | User      | 6        | user6@example.com | Zebra            |
    And the following "courses" exist:
      | fullname  | shortname | tags     |
      | Course 1  | c1        | Cat, Dog |
      | Course 2  | c2        | Cat      |
      | Course 3  | c3        | Cat      |
      | Course 4  | c4        | Cat      |
      | Course 5  | c5        | Cat      |
      | Course 6  | c6        | Cat      |
      | Course 7  | c7        | Cat      |
    And the following config values are set as admin:
      | unaddableblocks | | theme_boost|
    And the following "permission overrides" exist:
      | capability              | permission | role | contextlevel | reference |
      | moodle/user:viewdetails | Allow      | user | System       |           |

  @javascript
  Scenario: Browse tag index with javascript enabled
    When I log in as "user1"
    And I press "Customise this page"
    # TODO MDL-57120 "Tags" link not accessible without navigation block.
    And I add the "Navigation" block if not present
    And I navigate to "Tags" node in "Site pages"
    And I follow "Cat"
    Then I should see "Courses" in the "#tagarea-core-course" "css_element"
    And I should see "User interests" in the "#tagarea-core-user" "css_element"
    And I should see "Course 7"
    And I should see "Course 3"
    And I should not see "Course 2"
    And I should not see "Course 1"
    And I click on "More" "link" in the "#tagarea-core-course" "css_element"
    And I should see "Courses" in the "#tagarea-core-course" "css_element"
    And I should see "User interests" in the "#tagarea-core-user" "css_element"
    And I should not see "Course 7"
    And I should not see "Course 3"
    And I should see "Course 2"
    And I should see "Course 1"
    And I click on "Back" "link" in the "#tagarea-core-course" "css_element"
    And I should see "Courses" in the "#tagarea-core-course" "css_element"
    And I should see "User interests" in the "#tagarea-core-user" "css_element"
    And I should see "Course 7"
    And I should see "Course 3"
    And I should not see "Course 2"
    And I should not see "Course 1"
    And I follow "Show only tagged Courses"
    And I should see "Courses" in the "#tagarea-core-course" "css_element"
    And "#tagarea-core-user" "css_element" should not exist
    And I should see "Course 7"
    And I should see "Course 3"
    And I should see "Course 2"
    And I should see "Course 1"
    And I follow "Back to all items tagged with \"Cat\""
    And I should see "Courses" in the "#tagarea-core-course" "css_element"
    And I should see "User interests" in the "#tagarea-core-user" "css_element"
    And I should see "Course 7"
    And I should see "Course 3"
    And I should not see "Course2"
    And I should not see "Course1"
    And I log out

  @javascript
  Scenario Outline: Browse tag index and view other profiles
    Given the following "permission overrides" exist:
      | capability              | permission   | role | contextlevel | reference |
      | moodle/user:viewdetails | <permission> | user | System       |           |

    # Disable engage features as it grants viewdetails permission.
    And I log in as "admin"
    And I navigate to "Engage settings" node in "Site administration > System information > Configure features"
    And I set the field "Library" to "0"
    And I set the field "Workspaces" to "0"
    And I set the field "Recommendations" to "0"
    And I set the field "Microsoft Teams integration" to "0"
    And I press "Save changes"
    And I log out

    When I log in as "user1"
    And I press "Customise this page"
    And I add the "Navigation" block if not present
    And I navigate to "Tags" node in "Site pages"
    And I follow "Zebra"
    Then I should <action> "User 2"
    And I should <action> "User 3"
    And I should <action> "User 4"
    And I should <action> "User 5"

    Examples:
      | permission | action  |
      | Prevent    | not see |
      | Allow      | see     |
