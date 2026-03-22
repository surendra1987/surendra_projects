@totara @totara_program @javascript
Feature: Create course set on program content

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

  Scenario: New program content interface, existing courses on a course set should show as course cards.
    Given I log in as "admin"
    And I am on "Program One" program homepage
    When I press "Edit program details"
    And I switch to "Content" tab
    # Make sure two course cards are visible.
    And I should see "Course 2"
    And I should see "Course 3"
    And I log out

  Scenario: New program content interface, should be able close course adder without adding any new course.
    Given I log in as "admin"
    And I am on "Program One" program homepage
    When I press "Edit program details"
    And I switch to "Content" tab
    And I click on "Actions for Course set 1" "button"
    And I click on "Edit" option in the dropdown menu
    # Open course adder.
    And I click on "Add courses" "button" in the ".tui-totara_program-coursesGrid" "css_element"
    Then I should see "Select courses" in the tui modal
    # Select course 1.
    When I toggle the adder picker entry with "Course 6 Miscellaneous" for "Courses"
    And I click on "Selected &#8237;( 1 )&#8237;" "link"
    Then I should see the following selected adder basket entries:
      | Courses                |
      | Course 6 Miscellaneous |
    # Close the course adder.
    When I discard my selections and close the adder
    Then I should not see "Course 6"

  Scenario: New program content interface, should be able to add more courses to the existing course set.
    Given I log in as "admin"
    And I am on "Program One" program homepage
    When I press "Edit program details"
    And I switch to "Content" tab
    And I click on "Actions for Course set 1" "button"
    And I click on "Edit" option in the dropdown menu
    # Open course adder.
    And I click on "Add courses" "button" in the ".tui-totara_program-coursesGrid" "css_element"
    Then I should see "Select courses" in the tui modal
    # Check courses already in the course set are disabled on the course adder.
    And I should see the following disabled adder picker entries:
      | Courses   |
      | Course 5 Miscellaneous |
      | Course 4 Miscellaneous |
      | Course 3 Miscellaneous |
      | Course 2 Miscellaneous |
      | Course 1 Miscellaneous |
    And I should see the following unselected adder picker entries:
      | Courses   |
      | Course 6 Miscellaneous |
    When I toggle the adder picker entry with "Course 6 Miscellaneous" for "Courses"
    And I click on "Selected &#8237;( 1 )&#8237;" "link"
    Then I should see the following selected adder basket entries:
      | Courses                |
      | Course 6 Miscellaneous |
    When I save my selections and close the adder
    And I should see "Course 6"
    And I log out

  Scenario: New program content interface, should be able to create second course set and add courses.
    Given I log in as "admin"
    And I am on "Program One" program homepage
    And I press "Edit program details"
    When I switch to "Content" tab
    Then I should see "Add course set"
    And I should not see "Course set 2"
    When I click on "Add course set" "button"
    # Use xpath to identify second course set label and course add button.
    Then the field with xpath "//div[@class='tui-totara_program-courseSet'][2]//form//input[@name='label']" matches value "Course set 2"
    # Add courses to second course set.
    When I click on "Add courses" "button" in the "//div[@class='tui-totara_program-courseSet'][2]//form" "xpath_element"
    Then I should see the following unselected adder picker entries:
      | Courses   |
      | Course 6 Miscellaneous |
      | Course 5 Miscellaneous |
      | Course 4 Miscellaneous |
      | Course 3 Miscellaneous |
      | Course 2 Miscellaneous |
      | Course 1 Miscellaneous |
    When I toggle the adder picker entry with "Course 1 Miscellaneous" for "Courses"
    And I toggle the adder picker entry with "Course 2 Miscellaneous" for "Courses"
    And I toggle the adder picker entry with "Course 3 Miscellaneous" for "Courses"
    And I click on "Selected &#8237;( 3 )&#8237;" "link"
    Then I should see the following selected adder basket entries:
      | Courses                |
      | Course 3 Miscellaneous |
      | Course 2 Miscellaneous |
      | Course 1 Miscellaneous |
    When I save my selections and close the adder
    #And I wait "5" seconds
    Then I should see "Course 3"
    And I should see "Course 2"
    And I should see "Course 1"
    And I log out

  Scenario: New program content interface, should be able to create new course set and add courses.
    Given I log in as "admin"
    # Add course set to empty program content.
    And I am on "Program Two" program homepage
    And I press "Edit program details"
    When I switch to "Content" tab
    Then I should see "Add course set"
    And I should not see "Course set 1"
    When I click on "Add course set" "button"
    # Add courses to course set.
    And I click on "Add courses" "button" in the ".tui-totara_program-coursesGrid" "css_element"
    Then I should see "Select courses" in the tui modal
    # Check courses already in the course set are disabled on the course adder.
    And I should see the following unselected adder picker entries:
      | Courses   |
      | Course 6 Miscellaneous |
      | Course 5 Miscellaneous |
      | Course 4 Miscellaneous |
      | Course 3 Miscellaneous |
      | Course 2 Miscellaneous |
      | Course 1 Miscellaneous |
    When I toggle the adder picker entry with "Course 1 Miscellaneous" for "Courses"
    When I toggle the adder picker entry with "Course 3 Miscellaneous" for "Courses"
    And I click on "Selected &#8237;( 2 )&#8237;" "link"
    Then I should see the following selected adder basket entries:
      | Courses                |
      | Course 3 Miscellaneous |
      | Course 1 Miscellaneous |
    When I save my selections and close the adder
    Then I should see "Course 3"
    And I should see "Course 1"
    And I should not see "Course 2"
    And I log out

  Scenario: New program content interface, course sort order should be same as legacy.
    Given I log in as "admin"
    # Enable legacy content view.
    And I navigate to "Learn settings" node in "Site administration > System information > Configure features"
    And I set the field "Enable legacy program content" to "1"
    And I press "Save changes"
    # Add more course to program one.
    And I am on "Program One" program homepage
    When I press "Edit program details"
    And I switch to "Content" tab
     # Move course 1 down.
    When I click on "//*[text()='Course 2']//a[contains(@class, 'coursedownlink')]" "xpath_element"
     # Move course 4 up.
    And I click on "//*[text()='Course 4']//a[contains(@class, 'courseuplink')]" "xpath_element"
    When I press "Save changes"
    And I click on "Save all changes" "button"
    # Disable legacy content view.
    And I navigate to "Learn settings" node in "Site administration > System information > Configure features"
    And I set the field "Enable legacy program content" to "0"
    And I press "Save changes"
    And I am on "Program One" program homepage
    When I press "Edit program details"
    And I switch to "Content" tab
    And I click on "Actions for Course set 1" "button"
    And I click on "Edit" option in the dropdown menu
    And "//*[contains(text(), 'Course 1')]" "xpath_element" should appear before "//*[contains(text(), 'Course 3')]" "xpath_element"
    And "//*[contains(text(), 'Course 3')]" "xpath_element" should appear before "//*[contains(text(), 'Course 4')]" "xpath_element"
    And "//*[contains(text(), 'Course 4')]" "xpath_element" should appear before "//*[contains(text(), 'Course 2')]" "xpath_element"
    And "//*[contains(text(), 'Course 5')]" "xpath_element" should appear after "//*[contains(text(), 'Course 2')]" "xpath_element"

  Scenario: New program content interface, course set does not allow to save without any course.
    Given I log in as "admin"
    # Add course set to empty program content.
    And I am on "Program Two" program homepage
    And I press "Edit program details"
    When I switch to "Content" tab
    Then I should see "Add course set"
    And I should not see "Course set 1"
    When I click on "Add course set" "button"
    And I should not see "Course 1"
    And I should not see "Course 2"
    And I should not see "Course 3"
    And I should not see "Course 4"
    And I should not see "Course 5"
    # Save button is disabled.
    And the "Save" "button" should be disabled
    # Add course
    When I click on "Add courses" "button" in the ".tui-totara_program-coursesGrid" "css_element"
    And I toggle the adder picker entry with "Course 1 Miscellaneous" for "Courses"
    And I toggle the adder picker entry with "Course 3 Miscellaneous" for "Courses"
    And I save my selections and close the adder
    # Save button is no longer disabled.
    Then the "Save" "button" should be enabled