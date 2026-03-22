@totara @totara_program @javascript @vuejs
Feature: Save course set on program content

  Background:
    Given I am on a totara site
    And the following "programs" exist in "totara_program" plugin:
      | fullname      | shortname | idnumber |
      | Program One   | prog1     | prog1    |
      | Program Two   | prog2     | prog2    |
      | Program Tree  | prog3     | prog2    |
    And the following "courses" exist:
      | fullname     | shortname | format | enablecompletion |
      | Course 1     | course1   | topics | 1                |
      | Course 2     | course2   | topics | 1                |
      | Course 3     | course3   | topics | 1                |
      | Course 4     | course4   | topics | 1                |
      | Course 5     | course5   | topics | 1                |
      | Course 6     | course6   | topics | 1                |
    And the following "users" exist:
      | username | firstname     | lastname | email                |
      | authuser | Authenticated | User     | authuser@example.com |
      | progman  | Program       | Manager  | progman@example.com  |
      | john     | John          | Smith    | john@example.com     |
      | mary     | Mary          | Jones    | mary@example.com     |
    And the following "roles" exist:
      | shortname   |
      | progmanager |
    And the following "role assigns" exist:
      | user    | role        | contextlevel  | reference |
      | progman | progmanager | Program       | prog1     |
    And I log in as "admin"
    # Enable legacy content view.
    And I navigate to "Learn settings" node in "Site administration > System information > Configure features"
    And I set the field "Enable legacy program content" to "1"
    And I press "Save changes"
    # Add a course set only program one.
    And I am on "Program One" program homepage
    And I press "Edit program details"
    And I switch to "Content" tab
    And I click on "addcontent_ce" "button" in the "#edit-program-content" "css_element"
    And I click on "Miscellaneous" "link" in the "addmulticourse" "totaradialogue"
    And I click on "Course 1" "link" in the "addmulticourse" "totaradialogue"
    And I click on "Course 2" "link" in the "addmulticourse" "totaradialogue"
    And I click on "Course 3" "link" in the "addmulticourse" "totaradialogue"
    And I click on "Course 4" "link" in the "addmulticourse" "totaradialogue"
    And I click on "Course 5" "link" in the "addmulticourse" "totaradialogue"
    And I click on "Ok" "button" in the "addmulticourse" "totaradialogue"
    And I wait "1" seconds
    And I press "Save changes"
    And I click on "Save all changes" "button"
    # Disable legacy content view.
    And I navigate to "Learn settings" node in "Site administration > System information > Configure features"
    And I set the field "Enable legacy program content" to "0"
    And I press "Save changes"
    # Add course custom fields
    When I navigate to "Custom fields" node in "Site administration > Courses"
    And I set the field "Create a new custom field" to "Text input"
    And I set the following fields to these values:
      | Full name     | testcustomscore |
      | Short name    | testcustomscore |
    And I press "Save changes"
    And I log out

  Scenario: New program content interface, the course set save button is enabled when the course is in edit.
    Given I log in as "admin"
    And I am on "Program One" program homepage
    When I press "Edit program details"
    And I switch to "Content" tab
    And I click on "Actions for Course set 1" "button"
    And I click on "Edit" option in the dropdown menu
    Then I should see "Save"
    And "//div[@class='tui-card'][1]//button[contains(normalize-space(string(.)), 'Save')][@disabled]" "xpath_element" should not exist
    # Exit from edit mode
    When I click on "Cancel" "button"
    Then I should not see "Save"
    And I log out

  Scenario: New program content interface, the course set save button is disabled when no courses are in the course set.
    Given I log in as "admin"
    And I am on "Program One" program homepage
    When I press "Edit program details"
    And I switch to "Content" tab
    When I click on "Add course set" "button"
    Then I should see "Save"
    And "//div[@class='tui-totara_program-courseSet'][2]//button[contains(normalize-space(string(.)), 'Save')][@disabled]" "xpath_element" should exist
    # Add courses to the course set
    Then I click on "Add courses" "button" in the "//div[@class='tui-totara_program-courseSet'][2]//form" "xpath_element"
    When I toggle the adder picker entry with "Course 1 Miscellaneous" for "Courses"
    When I toggle the adder picker entry with "Course 3 Miscellaneous" for "Courses"
    And I click on "Selected &#8237;( 2 )&#8237;" "link"
    Then I should see the following selected adder basket entries:
      | Courses                |
      | Course 3 Miscellaneous |
      | Course 1 Miscellaneous |
    When I save my selections and close the adder
    Then I should see "Save"
    And "//div[@class='tui-totara_program-courseSet'][2]//button[contains(normalize-space(string(.)), 'Save')][@disabled]" "xpath_element" should not exist
    And I log out

  Scenario: New program content interface, the course set can not save with the Minimum Courses Completed field containing a non-integer or negative integer.
    Given I log in as "admin"
    And I am on "Program One" program homepage
    When I press "Edit program details"
    And I switch to "Content" tab
    And I click on "Actions for Course set 1" "button"
    And I click on "Edit" option in the dropdown menu
    And I select "Some courses" from the "completiontype" singleselect
    And I set the field "mincourses" to "two"
    Then I should not see "two"
    When I set the field "mincourses" to "-1"
    Then I should see "Number must be 0 or more"
    When I click on "Save" "button"
    Then I should see "Number must be 0 or more"
    And I log out

  Scenario: New program content interface, the course set can not save with the Minimum Score field containing a non-integer or negative integer.
    Given I log in as "admin"
    And I am on "Program One" program homepage
    When I press "Edit program details"
    And I switch to "Content" tab
    And I click on "Actions for Course set 1" "button"
    And I click on "Edit" option in the dropdown menu
    And I select "Some courses" from the "completiontype" singleselect
    And I select "testcustomscore" from the "coursesumfield" singleselect
    And I set the field "coursesumfieldtotal" to "two"
    Then I should not see "two"
    When I set the field "coursesumfieldtotal" to "-1"
    Then I should see "Number must be 0 or more"
    When I click on "Save" "button"
    Then I should see "Number must be 0 or more"
    And I log out

  Scenario: New program content interface, the course set can not save with the Minimum Time Required field containing a non-integer or negative integer.
    Given I log in as "admin"
    And I am on "Program One" program homepage
    When I press "Edit program details"
    And I switch to "Content" tab
    And I click on "Actions for Course set 1" "button"
    And I click on "Edit" option in the dropdown menu
    And I set the field "amount" to "two"
    Then I should not see "two"
    When I set the field "amount" to "-1"
    Then I should see "Number must be 0 or more"
    When I click on "Save" "button"
    Then I should see "Number must be 0 or more"
    And I log out

  Scenario: New program content interface, a new course set can not create when a course set is in view mode.
    Given I log in as "admin"
    And I am on "Program One" program homepage
    When I press "Edit program details"
    And I switch to "Content" tab
    And I click on "Actions for Course set 1" "button"
    And I click on "Edit" option in the dropdown menu
    # Add course set button is disabled when another course set is in edit mode.
    And the "Add course set" "button_exact" should be disabled
    # Exit from edit mode
    When I click on "Cancel" "button"
    And the "Add course set" "button_exact" should be enabled
    And I log out

  Scenario: New program content interface, the Add Course Set button should be available when no course sets in the program.
    Given I log in as "admin"
    And I am on "Program One" program homepage
    When I press "Edit program details"
    And I switch to "Content" tab
    And I click on "Actions for Course set 1" "button"
    And I click on "Delete" option in the dropdown menu
    And I confirm the tui confirmation modal
    And the "Add course set" "button_exact" should be enabled
    And I log out

  Scenario: New program content interface, when the Course Set name is empty, it should add Course set 1 if it is the first course set.
    Given I log in as "admin"
    And I am on "Program One" program homepage
    When I press "Edit program details"
    And I switch to "Content" tab
    And I click on "Actions for Course set 1" "button"
    # Remove the existing course set.
    And I click on "Delete" option in the dropdown menu
    And I confirm the tui confirmation modal
    And I click on "Add course set" "button"
    Then the field with xpath "//div[@class='tui-totara_program-courseSet'][1]//form//input[@name='label']" matches value "Course set 1"
    # Add courses to the course set
    When I click on "Add courses" "button" in the "//div[@class='tui-totara_program-courseSet'][1]//form" "xpath_element"
    And I toggle the adder picker entry with "Course 1 Miscellaneous" for "Courses"
    And I toggle the adder picker entry with "Course 3 Miscellaneous" for "Courses"
    And I save my selections and close the adder
    And I should see "Save"
    And I set the field "label" to ""
    And I click on "Save" "button"
    And I wait "2" seconds
    # The label value on the first Course Set should be "Course set 1".
    And I click on "Actions for Course set 1" "button"
    And I click on "Edit" option in the dropdown menu
    Then the field with xpath "//div[@class='tui-totara_program-courseSet'][1]//form//input[@name='label']" matches value "Course set 1"
    And I click on "Cancel" "button"
    # Create second course set.
    When I click on "Add course set" "button"
    And the field with xpath "//div[@class='tui-totara_program-courseSet'][2]//form//input[@name='label']" matches value "Course set 2"
    And I click on "Add courses" "button" in the "//div[@class='tui-totara_program-courseSet'][2]//form" "xpath_element"
    And I toggle the adder picker entry with "Course 1 Miscellaneous" for "Courses"
    And I toggle the adder picker entry with "Course 3 Miscellaneous" for "Courses"
    And I save my selections and close the adder
    And I should see "Save"
    And I set the field "label" to ""
    And I click on "Save" "button"
    And I wait "2" seconds
    # The label value on the second Course Set should be "Course set 2".
    And I click on "Actions for Course set 2" "button"
    And I click on "Edit" option in the dropdown menu
    Then the field with xpath "//div[@class='tui-totara_program-courseSet'][2]//form//input[@name='label']" matches value "Course set 2"
    And I log out