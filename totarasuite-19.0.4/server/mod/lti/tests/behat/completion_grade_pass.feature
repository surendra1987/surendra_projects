@mod @mod_lti @core_grades @javascript @ndl
Feature: External tool activity completion with passing grade
  As an admin/course creator/editing trainer
  I would like to set the the activity completion criteria to have the option to set "require passing grade" like quizzes
  So that there is flexibility within activity completion criteria

  Background:
    Given the following config values are set as admin:
      | grade_decimalpoints | 2 |
    And the following "courses" exist:
      | fullname | shortname | category | enablecompletion |
      | course1  | course1   | 0        | 1                |
    And the following "users" exist:
      | username | firstname | lastname | email             |
      | user1    | One       | Uno      | user1@example.com |
      | user2    | Two       | Duex     | user2@example.com |
      | user3    | Three     | Toru     | user3@example.com |
      | user4    | Four      | Wha      | user4@example.com |
      | user5    | Five      | Cinq     | user5@example.com |
    And the following "course enrolments" exist:
     | user     | course   | role    |
     | user1    | course1  | student |
     | user2    | course1  | student |
     | user3    | course1  | student |
     | user4    | course1  | student |
     | user5    | course1  | student |
    And I log in as "admin"
    And I navigate to "Manage tools" node in "Site administration > Plugins > Activity modules > External tool"
    # Create tool type that supports content-item.
    And I follow "configure a tool manually"
    And I set the field "Tool name" to "Teaching Tool 1"
    And I set the field "Tool URL" to local url "/mod/lti/tests/fixtures/tool_provider.php"
    And I set the field "Tool configuration usage" to "Show in activity chooser and as a preconfigured tool"
    And I expand all fieldsets
    And I set the field "Content-Item Message" to "1"
    And I press "Save changes"
    And I am on "course1" course homepage with editing mode on
    And I add a "Teaching Tool 1" to section "1"
    And the "Select content" "button" should be enabled
    And I set the field "Activity name" to "Test tool activity 1"
    And I expand all fieldsets
    And I set the field "Launch container" to "Embed"
    And I press "Save and return to course"

  Scenario: Require grade is on, require passing grade is on, passing grade is 0
    Given I am on "course1" course homepage
    And I open "Test tool activity 1" actions menu
    And I choose "Edit settings" in the open action menu
    And I set the following fields to these values:
      | Grade to pass                                          | 0                                                 |
      | Completion tracking                                    | Show activity as complete when conditions are met |
      | Learner must receive a grade to complete this activity | 1                                                 |
      | Require passing grade                                  | 1                                                 |
    When I click on "Save and return to course" "button"
    Then I should see "Updating External tool"
    And I should see "'Grade to pass' must be greater than 0 when 'Require passing grade' activity completion setting is enabled"
    And I should see "This activity does not have a grade to pass set so you cannot use this option"

  # This test is implemented with the solution to TL-39530 to check that
  # the "completionpass" value is saved when the user deselects it.
  Scenario: Set and unset "Require passing grade" feature
    Given I am on "course1" course homepage
    And I open "Test tool activity 1" actions menu
    And I choose "Edit settings" in the open action menu
    And I set the following fields to these values:
      | Grade to pass                                          | 42                                                |
      | Completion tracking                                    | Show activity as complete when conditions are met |
      | Learner must receive a grade to complete this activity | 1                                                 |
      | Require passing grade                                  | 1                                                 |
    And I click on "Save and display" "button"
    And I navigate to "Edit settings" node in "External tool administration"
    And I set the following fields to these values:
      | Require passing grade | 0 |
    And I click on "Save and display" "button"
    And I navigate to "Edit settings" node in "External tool administration"
    When I expand all fieldsets
    Then the field "Require passing grade" matches value "0"
