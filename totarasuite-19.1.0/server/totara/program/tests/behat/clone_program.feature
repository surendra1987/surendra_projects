@totara @totara_program @totara_notification @javascript @editor @editor_weka
Feature: Clone program
  Background:
    Given I am on a totara site
    And the following config values are set as admin:
      | enablelegacyprogramcontent | 1  |
    And the following "programs" exist in "totara_program" plugin:
      | fullname         | shortname |
      | Program to clone | prog 1    |
    And the following "courses" exist:
      | fullname | shortname | enablecompletion |
      | Course 1 | c1        | 1                |
      | Course 2 | c2        | 1                |
      | Course 3 | c3        | 1                |
    And the following "users" exist:
      | username | firstname | lastname | email                |
      | teacher1 | teacher   | One      | teacher1@example.com |
    And the following "system role assigns" exist:
      | user     | role           |
      | teacher1 | editingteacher |
    And I log in as "admin"
    And I navigate to "Manage programs" node in "Site administration > Programs"
    And I click on "Miscellaneous" "link"
    And I click on "Program to clone" "link"
    And I click on "Edit program details" "button"
    And I switch to "Details" tab
    And I set the following fields to these values:
      | ID      | pro1                 |
      | Summary | this is some summary |
      | Endnote | this is the endnote  |
    And I click on "Save changes" "button"
    And I switch to "Content" tab
    And I click on "addcontent_ce" "button" in the "#edit-program-content" "css_element"
    And I click on "Miscellaneous" "link" in the "addmulticourse" "totaradialogue"
    And I click on "Course 1" "link" in the "addmulticourse" "totaradialogue"
    And I click on "Course 2" "link" in the "addmulticourse" "totaradialogue"
    And I click on "Ok" "button" in the "addmulticourse" "totaradialogue"
    And I click on "Save changes" "button"
    And I click on "Save all changes" "button"
    And I switch to "Notifications" tab
    And I click on "Actions for Learner assigned in program event" "button"
    And I click on "Create notification" "link"
    And I set the field "Name" to "Test custom notification"
    When I click on the "Subject" tui checkbox in the "Recipient" tui checkbox group
    And I set the weka editor with css ".tui-notificationPreferenceForm__subjectEditor" to "Test custom notification subject"
    And I set the weka editor with css ".tui-notificationPreferenceForm__bodyEditor" to "Test custom notification body"
    And I click on "Save" "button"

  Scenario: Clone from the program page
    Given I am on totara catalog page
    And I click on "Program to clone" "text"
    And I click on "Edit program details" "button"
    Then I should see "Clone program"

    And I click on "Details" "link"
    Then I should see "Clone program"

    And I click on "Content" "link"
    Then I should see "Clone program"

    And I click on "Assignments" "link"
    Then I should see "Clone program"

    And I click on "Notifications" "link"
    Then I should see "Clone program"

    And I click on "Completion" "link"
    Then I should see "Clone program"

    When I click on "Clone program" "button"
    Then I should see "Clone Program to clone" in the ".tui-modalContent__header" "css_element"

    When I click on "Clone" "button" in the ".tui-modalContent" "css_element"
    Then I should see "Copy of Program to clone"
    And the following fields match these values:
      | Full name  | Copy of Program to clone |
      | Short name | Copy of prog 1           |
      | ID         |                          |
      | Summary    | this is some summary     |
      | Endnote    | this is the endnote      |

    When I switch to "Content" tab
    Then I should see "Course 1"
    And I should see "Course 2"
    And I should not see "Course 3"

    When I switch to "Notifications" tab
    And I click on "Learner assigned in program details" "button"
    Then I should see "Test custom notification"

  Scenario: User does not have permission to edit program details
    Given I set the following system permissions of "Editing Trainer" role:
      | capability                       | permission |
      | totara/program:cloneprogram      | Allow      |
      | totara/program:configuredetails  | Prevent    |
      | totara/program:configurecontent  | Allow      |
      | totara/program:configuremessages | Allow      |
    And I log out
    And I log in as "teacher1"
    Given I am on totara catalog page
    And I click on "Program to clone" "text"
    And I click on "Edit program details" "button"
    And I click on "Clone program" "button"
    And I click on "Clone" "button" in the ".tui-totaraProgramCloneConfirmationModal" "css_element"

    # User should be on the "Program overview" page
    Then I should see "Overview" in the ".block_settings .active_tree_node" "css_element"
    And I should see "Copy of Program to clone"
    And I should not see "Details" in the ".tabtree" "css_element"
    And I should not see "Save changes"
    And I should not see "Edit program details"

  Scenario: Cloned program is hidden on the creation
    Given I am on totara catalog page
    And I click on "Program to clone" "text"
    And I click on "Edit program details" "button"
    And I click on "Clone program" "button"
    And I click on "Clone" "button" in the ".tui-totaraProgramCloneConfirmationModal" "css_element"

    # User should be on the "Program Details" page
    Then I should see "Details" in the ".block_settings .active_tree_node" "css_element"
    And I should see "Copy of Program to clone"

    # The cloned program is hidden on the creation
    Given I am on totara catalog page
    And I should not see "Copy of Program to clone"
