@totara @totara_customfield @javascript
Feature: Administrators can add a custom decimal input field to complete during course creation
  In order for the custom field to appear during course creation
  As admin
  I need to select the decimal input custom field and add the relevant settings

  Scenario: Create a custom decimal input
    Given I log in as "admin"
    When I navigate to "Custom fields" node in "Site administration > Courses"
    Then I should see "Create a new custom field"

    When I set the field "datatype" to "Decimal input"
    Then I should see "Create a new \"Decimal input\" custom field"

    When I set the following fields to these values:
      | fullname    | Custom Decimal Input Field   |
      | shortname   | decimalinput                 |
      | defaultdata | 20.5                         |
      | param1      | 10.5                         |
      | param2      | 40.5                         |
      | param3      | 0.5                          |
      | param4      | 3                            |
    And I press "Save changes"
    Then I should see "Custom Decimal Input Field"

    When I go to the courses management page
    And I click on "Create new course" "link"
    Then I should see "Add a new course"
    When I expand all fieldsets
    Then I should see "Custom Decimal Input Field"
    And the field "customfield_decimalinput" matches value "20.5"
    And the "min" attribute of "customfield_decimalinput" "field" should contain "10.500"
    And the "max" attribute of "customfield_decimalinput" "field" should contain "40.500"
    And the "step" attribute of "customfield_decimalinput" "field" should contain "0.500"

    When I set the following fields to these values:
      | fullname                 | Course One |
      | shortname                | course1    |
      | customfield_decimalinput | 25.5       |
    And I press "Save and display"
    Then I should see "Course One" in the page title

    When I navigate to "Edit settings" node in "Course administration"
    And I expand all fieldsets
    Then the field "customfield_decimalinput" matches value "25.500"