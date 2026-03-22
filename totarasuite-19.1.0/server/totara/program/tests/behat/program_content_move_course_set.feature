@totara @totara_program @javascript
Feature: Move course set on program content

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
    And I log out

  Scenario: New program content interface, a single course set can not move.
    Given I log in as "admin"
    And I am on "Program One" program homepage
    When I press "Edit program details"
    And I switch to "Content" tab
    And I click on "Actions for Course set 1" "button"
    Then I should see "Move up" option disabled in the dropdown menu
    And I should see "Move down" option disabled in the dropdown menu
    And I log out

  Scenario: New program content interface, the first-course set can only be moved down.
    Given I log in as "admin"
    And I am on "Program One" program homepage
    When I press "Edit program details"
    And I switch to "Content" tab
    # Add a new course set
    And I click on "Add course set" "button"
    # Add courses to the course set
    And I click on "Add courses" "button" in the "//div[@class='tui-totara_program-courseSet'][2]//form" "xpath_element"
    And I toggle the adder picker entry with "Course 1 Miscellaneous" for "Courses"
    And I toggle the adder picker entry with "Course 3 Miscellaneous" for "Courses"
    And I save my selections and close the adder
    And I should see "Save"
    And I click on "Save" "button"
    And I wait "2" seconds
    # Check move up and down on the first-course set
    And I click on "Actions for Course set 1" "button"
    Then I should see "Move up" option disabled in the dropdown menu
    When I click on "Move down" option in the dropdown menu
    Then "//div[@class='tui-totara_program-courseSet']//h2[contains(text(), 'Course set 2')]" "xpath_element" should appear before "//div[@class='tui-totara_program-courseSet']//h2[contains(text(), 'Course set 1')]" "xpath_element"
    And I log out

  Scenario: New program content interface, the last-course set can only be moved up.
    Given I log in as "admin"
    And I am on "Program One" program homepage
    When I press "Edit program details"
    And I switch to "Content" tab
    # Add a new course set
    And I click on "Add course set" "button"
    # Add courses to the course set
    And I click on "Add courses" "button" in the "//div[@class='tui-totara_program-courseSet'][2]//form" "xpath_element"
    And I toggle the adder picker entry with "Course 1 Miscellaneous" for "Courses"
    And I toggle the adder picker entry with "Course 3 Miscellaneous" for "Courses"
    And I save my selections and close the adder
    And I should see "Save"
    And I click on "Save" "button"
    And I wait "2" seconds
    # Check move up and down on the last-course set
    And I click on "Actions for Course set 2" "button"
    And I should see "Move down" option disabled in the dropdown menu
    When I click on "Move up" option in the dropdown menu
    Then "//div[@class='tui-totara_program-courseSet']//h2[contains(text(), 'Course set 2')]" "xpath_element" should appear before "//div[@class='tui-totara_program-courseSet']//h2[contains(text(), 'Course set 1')]" "xpath_element"
    And I log out

  Scenario: New program content interface, the middle-course set can be moved up and down.
    Given I log in as "admin"
    And I am on "Program One" program homepage
    When I press "Edit program details"
    And I switch to "Content" tab
    # Add a new course set
    And I click on "Add course set" "button"
    # Add courses to the course set
    And I click on "Add courses" "button" in the "//div[@class='tui-totara_program-courseSet'][2]//form" "xpath_element"
    And I toggle the adder picker entry with "Course 1 Miscellaneous" for "Courses"
    And I toggle the adder picker entry with "Course 3 Miscellaneous" for "Courses"
    And I save my selections and close the adder
    And I should see "Save"
    And I click on "Save" "button"
    And I wait "2" seconds
    # Add another course set
    And I click on "Add course set" "button"
    # Add courses to the course set
    And I click on "Add courses" "button" in the "//div[@class='tui-totara_program-courseSet'][3]//form" "xpath_element"
    And I toggle the adder picker entry with "Course 1 Miscellaneous" for "Courses"
    And I toggle the adder picker entry with "Course 3 Miscellaneous" for "Courses"
    And I save my selections and close the adder
    And I should see "Save"
    And I click on "Save" "button"
    And I wait "2" seconds
    # Check move up and down on the last-course set
    And I click on "Actions for Course set 2" "button"
    When I click on "Move up" option in the dropdown menu
    Then "//div[@class='tui-totara_program-courseSet']//h2[contains(text(), 'Course set 2')]" "xpath_element" should appear before "//div[@class='tui-totara_program-courseSet']//h2[contains(text(), 'Course set 1')]" "xpath_element"
    And "//div[@class='tui-totara_program-courseSet']//h2[contains(text(), 'Course set 1')]" "xpath_element" should appear before "//div[@class='tui-totara_program-courseSet']//h2[contains(text(), 'Course set 3')]" "xpath_element"
    When I click on "Actions for Course set 1" "button"
    And I click on "Move down" option in the dropdown menu
    Then "//div[@class='tui-totara_program-courseSet']//h2[contains(text(), 'Course set 3')]" "xpath_element" should appear before "//div[@class='tui-totara_program-courseSet']//h2[contains(text(), 'Course set 1')]" "xpath_element"
    And I log out