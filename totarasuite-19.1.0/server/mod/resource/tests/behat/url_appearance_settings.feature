@mod @mod_resource @javascript
Feature: Resource activity with new option of appearance settings
  In order to see the display link option
  As course creator
  I choose the new option for a resource activity

  Scenario: Course creator can create resource activity with a display link option for a course with pathway format
    Given I log in as "admin"
    And the following "courses" exist:
      | fullname | shortname |  format  |
      | c101     | c101      |  pathway |
    And I am on "c101" course homepage with editing mode on
    And I add a "File" to section "1"
    And I expand all fieldsets
    When I click on "//select[@id='id_display']" "xpath_element"
    Then I should see "Display link"

  Scenario: Course creator can create resource activity with a display link option for a course with topics format
    Given I log in as "admin"
    And the following "courses" exist:
      | fullname | shortname |
      | c101     | c101      |
    And I am on "c101" course homepage with editing mode on
    And I add a "File" to section "1"
    And I expand all fieldsets
    When I click on "//select[@id='id_display']" "xpath_element"
    Then I should see "Display link"