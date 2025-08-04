@course @format_pathway @javascript
Feature: Navigation works as expected in the pathway course format
  As a User
  I should be able to navigate between course activities

  Background:
    Given the following "courses" exist:
      | fullname | shortname | format  |
      | Course 1 | C1        | pathway |
    And the following "activities" exist:
      | activity   | name    | course | section | idnumber |
      | forum      | Forum 1 | C1     | 1       | f1       |
      | forum      | Forum 2 | C1     | 3       | f2       |
    And the following "users" exist:
      | username |
      | test1    |
    And the following "course enrolments" exist:
      | user  | course | role    |
      | test1 | C1     | student |
    And I log in as "admin"
    And I log out

  Scenario: Pathway Navigation
    Given I log in as "test1"
    And I am on "Course 1" course homepage
    Then I should see "Forum 1" in the ".tui-format_pathway-activityView__activityContent" "css_element"
    And "Previous activity" "link" should not exist

    When I click on "Next activity" "link"
    Then I should see "Forum 2" in the ".tui-format_pathway-activityView__activityContent" "css_element"
    And "Next activity" "link" should not exist

    When I click on "Previous activity" "link"
    Then I should see "Forum 1" in the ".tui-format_pathway-activityView__activityContent" "css_element"

    When I click on "Next" "link" in the ".tui-format_pathway-activityFooter" "css_element"
    Then I should see "Forum 2" in the ".tui-format_pathway-activityView__activityContent" "css_element"
    And ".tui-format_pathway-activityFooter .tui-btn" "css_element" should not exist
