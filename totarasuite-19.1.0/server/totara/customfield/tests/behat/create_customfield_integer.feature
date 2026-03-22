@totara @totara_customfield @javascript
Feature: Administrators can add a custom integer input field to complete during course creation
  In order for the custom field to appear during course creation
  As admin
  I need to select the integer input custom field and add the relevant settings

  Scenario: Create a custom integer input
    Given I log in as "admin"
    When I navigate to "Custom fields" node in "Site administration > Courses"
    Then I should see "Create a new custom field"
    When I set the field "datatype" to "Integer input"
    Then I should see "Create a new \"Integer input\" custom field"

    When I set the following fields to these values:
      | fullname    | Custom Integer Input Field |
      | shortname   | integerinput               |
      | defaultdata | 20                         |
      | param1      | 10                         |
      | param2      | 40                         |
      | param3      | 5                          |
    And I press "Save changes"
    Then I should see "Custom Integer Input Field"

    When I go to the courses management page
    And I click on "Create new course" "link"
    Then I should see "Add a new course"
    When I expand all fieldsets
    Then I should see "Custom Integer Input Field"
    And the field "customfield_integerinput" matches value "20"
    And the "min" attribute of "customfield_integerinput" "field" should contain "10"
    And the "max" attribute of "customfield_integerinput" "field" should contain "40"
    And the "step" attribute of "customfield_integerinput" "field" should contain "5"

    When I set the following fields to these values:
      | fullname                 | Course One |
      | shortname                | course1    |
      | customfield_integerinput | 25         |
    And I press "Save and display"
    Then I should see "Course One" in the page title

    When I navigate to "Edit settings" node in "Course administration"
    And I expand all fieldsets
    Then the field "customfield_integerinput" matches value "25"