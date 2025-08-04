@course @format_pathway
Feature: Save module options when converting between course types
  As an admin
  In order to freely convert between course types
  I need to be able to save my choices between conversions

  Background:
    Given the following "courses" exist:
      | fullname | shortname | category | format   |
      | Course 1 | C1        | 0        | topics   |
      | Course 2 | C2        | 0        | pathway  |
    And the following "activities" exist:
      | activity   | name    | intro       | course | section | idnumber | showdescription |
      | facetoface | seminar | description | C1     | 1       | s1       | 1               |
      | facetoface | seminar | description | C2     | 1       | s2       | 1               |
    And I log in as "admin"

  @javascript
  Scenario: Converting from topics to pathway removes expected features from the edit page
    Given I am on "Course 1" course homepage
    And I click on "Edit settings" "link"
    And I follow "Course format"
    And I set the following fields to these values:
        | Format | Pathway format |
    And I click on "Save and display" "button"
    And I click on "seminar" "link"
    When I click on "Show activity administration" "button"
    And I click on "Edit settings" "link" in the ".tui-format_pathway-activityToolbar" "css_element"
    Then I should not see "Display description on course page"

  @javascript
  Scenario: Converting from pathway to topics preserves the "showdescription" from the previous setting
    Given I am on "Course 2" course homepage
    And I click on "Show course administration" "button"
    And I click on "Edit settings" "link"
    And I follow "Course format"
    And I set the following fields to these values:
      | Format | Topics format |
    And I click on "Save and display" "button"
    When I click on "seminar" "link"
    And I click on "Edit settings" "link"
    Then the field "Display description on course page" matches value "1"
