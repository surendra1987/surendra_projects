@totara @totara_program @javascript
Feature: Check program content

  Background:
    Given I am on a totara site
    And the following "programs" exist in "totara_program" plugin:
      | fullname    | shortname | idnumber |
      | Program One | prog1     | prog1    |
      | Program Two | prog2     | prog2    |
    And the following "courses" exist:
      | fullname     | shortname | format | enablecompletion |
      | Course 1     | course1   | topics | 1                |
      | Course 2     | course2   | topics | 1                |
      | Course 3     | course3   | topics | 1                |
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
    # Add a course set.
    And I am on "Program One" program homepage
    And I press "Edit program details"
    And I switch to "Content" tab
    And I click on "addcontent_ce" "button" in the "#edit-program-content" "css_element"
    And I click on "Miscellaneous" "link" in the "addmulticourse" "totaradialogue"
    And I click on "Course 2" "link" in the "addmulticourse" "totaradialogue"
    And I click on "Course 3" "link" in the "addmulticourse" "totaradialogue"
    And I click on "Ok" "button" in the "addmulticourse" "totaradialogue"
    And I wait "1" seconds
    And I press "Save changes"
    And I click on "Save all changes" "button"
    And I log out

  Scenario: New program content interface, the course set can be deleted
    Given I log in as "admin"
    # Disable legacy content view.
    And I navigate to "Learn settings" node in "Site administration > System information > Configure features"
    And I set the field "Enable legacy program content" to "0"
    And I press "Save changes"
    And I am on "Program One" program homepage
    When I press "Edit program details"
    And I switch to "Content" tab
    # New program content interface, course set's Delete and Edit buttons can access through the action menu.
    # Make sure the new course set menu contain the Edit and Delete button.
    When I click on "Actions for Course set 1" "button"
    And I should see "Edit" option in the dropdown menu
    And I should see "Delete" option in the dropdown menu
    And I click on "Delete" option in the dropdown menu
    # New program content interface, the course set's delete event should trigger a confirmation modal
    Then I should see "Delete course set: Course set 1" in the tui modal
    And I should see "Are you sure? This action cannot be undone." in the tui modal
    And I confirm the tui confirmation modal
    Then I should see "Successfully deleted course set" in the tui success notification toast
    And I close the tui notification toast
    Then I should not see "Course set 1"