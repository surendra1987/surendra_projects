@totara @totara_program @totara_generator
Feature: Behat generators for programs work
  In order to use behat generators
  As a behat writer
  I need to be able to create programs via behat generator

  Background:
    Given I am on a totara site
    And the following "users" exist:
      | username | firstname | lastname | email               |
      | user001  | fn_001    | ln_001   | user001@example.com |
      | user002  | fn_002    | ln_002   | user002@example.com |
      | user003  | fn_003    | ln_003   | user003@example.com |

  @javascript
  Scenario: Verify the program generators work
    Given the following "programs" exist in "totara_program" plugin:
      | fullname                | shortname |
      | Generator Program Tests | gentest   |
    And the following "program assignments" exist in "totara_program" plugin:
      | program  | user    |
      |  gentest | user001 |
      |  gentest | user002 |
    When I log in as "admin"
    And I set the following administration settings values:
      | catalogtype | enhanced |
    And I click on "Find learning > Programs" in the totara menu
    And I should see "Generator Program Tests"
    And I click on "Generator Program Tests" "link"
    And I press "Edit program details"
    Then I should see "2 learner(s) assigned: 2 active, 0 exception(s)"

  @javascript
  Scenario: Verify the user interface works the same as program generators
    Given I log in as "admin"
    And I set the following administration settings values:
      | catalogtype | enhanced |
    And I click on "Find learning > Programs" in the totara menu
    And I press "Create Program"
    And I set the following fields to these values:
        | fullname  | Generator Program Tests |
        | shortname | gentest                 |
    And I press "Save changes"
    And I click on "Assignments" "link"
    And I set the field "Add a new" to "Individuals"
    And I click on "user001" "link" in the "add-assignment-dialog-5" "totaradialogue"
    And I click on "user002" "link" in the "add-assignment-dialog-5" "totaradialogue"
    And I click on "Ok" "button" in the "add-assignment-dialog-5" "totaradialogue"
    And I wait "1" seconds
    Then I should see "2 learner(s) assigned: 2 active, 0 exception(s)"
    And I click on "Find learning > Programs" in the totara menu
    Then I should see "Generator Program Tests"

  @javascript
  Scenario: Verify the shortcut to program edit page works
    Given the following "programs" exist in "totara_program" plugin:
      | fullname                | shortname |
      | Generator Program Tests | progtest  |
    And the following "certifications" exist in "totara_program" plugin:
      | fullname                | shortname |
      | Generator Certification | certtest  |
    And I log in as "admin"
    When I go to edit the program "progtest"
    Then I should see "This program is live. Any changes you make may affect learners progress and completion records."
    And I should see "Program Administration"
    And I should see "Generator Program Tests"
    When I go to edit the certification "certtest"
    Then I should see "This certification is live. Any changes you make may affect learners progress and completion records."
    And I should see "Certification Administration"
    And I should see "Generator Certification"