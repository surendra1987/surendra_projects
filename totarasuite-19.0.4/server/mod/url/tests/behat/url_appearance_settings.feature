@mod @mod_url @javascript
Feature: Url activity with new option of appearance settings
  In order to see the display link option
  As course creator
  I choose the new option for a url activity

  Scenario: Admin user can see new url setting option
    Given I log in as "admin"
    And I navigate to "URL" node in "Site administration > Plugins > Activity modules"
    And I should see "Display link"

  @format_pathway
  Scenario: Course creator can create url activity with a display link option for a course with pathway format
    Given I log in as "admin"
    And the following "courses" exist:
      | fullname | shortname |  format  |
      | c101     | c101      |  pathway |
    And the following "activities" exist:
      | activity |      name                   |     intro | course | idnumber |
      |   url    | Test url appearance option  | n         | c101   | url      |
    And I am on "c101" course homepage with editing mode on
    And I follow "Test url appearance option"
    And I click on "Show activity administration" "button"
    And I click on "Edit settings" "link" in the ".tui-format_pathway-activityView__activityToolbar" "css_element"
    And I expand all fieldsets
    When I click on "//select[@id='id_display']" "xpath_element"
    Then I should see "Display link"

  Scenario: Course creator can create url activity with a display link option for a course with topics format
    Given I log in as "admin"
    And the following "courses" exist:
      | fullname | shortname |
      | c101     | c101      |
    And the following "activities" exist:
      | activity |      name                   |     intro | course | idnumber |
      |   url    | Test url appearance option  | n         | c101   | url      |
    And I am on "c101" course homepage with editing mode on
    And I follow "Test url appearance option"
    And I navigate to "Edit settings" node in "URL module administration"
    And I expand all fieldsets
    When I click on "//select[@id='id_display']" "xpath_element"
    Then I should see "Display link"