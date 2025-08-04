@totara @totara_program @javascript
Feature: Admins can only edit one course set at a time
  In order to avoid confusion
  As an administrator
  I can only edit one course set at a time

  Background:
    Given I am on a totara site
    And the following config values are set as admin:
      | enablelegacyprogramcontent | 1  |
    And the following "courses" exist:
      | fullname | shortname | format | enablecompletion |
      | Course 1 | C1        | topics | 1                |
      | Course 2 | C2        | topics | 1                |
      | Course 3 | C3        | topics | 1                |
    And the following "programs" exist in "totara_program" plugin:
      | fullname  | shortname |
      | Program 1 | prog1     |
    And I log in as "admin"
    And I navigate to "Manage programs" node in "Site administration > Programs"
    And I click on "Miscellaneous" "link"
    And I click on "Program 1" "link"
    And I click on "Edit program details" "button"
    And I switch to "Content" tab
    And I click on "addcontent_ce" "button" in the "#edit-program-content" "css_element"
    And I click on "Miscellaneous" "link" in the "addmulticourse" "totaradialogue"
    And I click on "Course 1" "link" in the "addmulticourse" "totaradialogue"
    And I click on "Course 2" "link" in the "addmulticourse" "totaradialogue"
    And I click on "Ok" "button" in the "addmulticourse" "totaradialogue"
    And I wait "1" seconds
    And I click on "addcontent_ce" "button" in the "#edit-program-content" "css_element"
    And I click on "Miscellaneous" "link" in the "addmulticourse" "totaradialogue"
    And I click on "Course 3" "link" in the "addmulticourse" "totaradialogue"
    And I click on "Ok" "button" in the "addmulticourse" "totaradialogue"
    And I wait "1" seconds
    And I press "Save changes"
    And I click on "Save all changes" "button"
    And the following config values are set as admin:
      | enablelegacyprogramcontent | 0  |
    When I reload the page
    Then "Actions for Course set 1" "button" should exist
    And "Actions for Course set 2" "button" should exist

  Scenario: Check user can only edit one courseset at a time
    When I click on "Actions for Course set 1" "button"
    And I click on "Edit" option in the dropdown menu
    Then the "Add course set" "button" should be disabled
    And "Actions for Course set 1" "button" should not exist
    And "Actions for Course set 2" "button" should not exist

    When I click on "Cancel" "button"
    Then "Actions for Course set 1" "button" should exist
    And "Actions for Course set 2" "button" should exist
    And the "Add course set" "button" should be enabled

    When I click on "Actions for Course set 2" "button"
    And I click on "Edit" option in the dropdown menu
    And I click on "Save" "button"
    Then "Actions for Course set 1" "button" should exist
    And "Actions for Course set 2" "button" should exist
    And the "Add course set" "button" should be enabled

  Scenario: The add course set button disables editing of other course sets
    When I click on "Add course set" "button"
    Then the "Add course set" "button" should be disabled
    And "Actions for Course set 1" "button" should not exist
    And "Actions for Course set 2" "button" should not exist