@totara @totara_certification @javascript
Feature: Allow learners to self-enrol into certifications
  As a learner, I can self-enrol into a certification that allows it

  Background:
    Given I am on a totara site
    And the following "users" exist:
      | username | email             |
      | user1    | user1@example.com |
    And the following "certifications" exist in "totara_program" plugin:
      | fullname  | shortname   |
      | Open cert | cert-open   |
    And I log in as "admin"
    And I go to edit the certification "cert-open"
    And I click on "Assignments" "link"
    And I set the field "Add a new" to "Group"
    And I set the following fields to these values:
      | Name             | Open enrolment            |
      | Description      | This group can self enrol |
      | can_self_enrol   | 1                         |
      | can_self_unenrol | 1                         |
    And I press "Ok"
    And I click on "Set due date" "link" in the "Open enrolment" "table_row"
    And I set the field "completiontime" to "31/12/2050"
    And I press "Set fixed completion date"
    And I set the field "Add a new" to "Group"
    And I set the following fields to these values:
      | Name             | Closed enrolment |
      | Description      | closed enrolment |
      | can_self_enrol   | 0                |
      | can_self_unenrol | 0                |
    And I press "Ok"

  Scenario: Learner can self enrol into a simple certification
    Given I log in as "user1"
    And I am on "Open cert" certification homepage
    Then I should see "Enrol"
    When I press "Enrol"
    Then I should see "Are you sure you want to enrol with Open enrolment in Open cert?" in the ".tui-totara_program-enrolment-confirmation" "css_element"
    And I should see "This group can self enrol" in the ".tui-modal" "css_element"
    And I should see "31 December 2050" in the ".tui-totara_program-enrolment-option-card_item" "css_element"
    When I click on "Enrol" "button" in the ".tui-modal" "css_element"
    Then I should not see "Enrol"
    And I should see "Member of group 'Open enrolment'"
    When I press "Actions for Open cert"
    Then I should see "Enrolment details"

    When I log in as "admin"
    And I go to edit the program "cert-open"
    Then I should see "1 learner(s) assigned: 1 active, 0 exception(s)."

    # Withdraw
    When I log in as "user1"
    And I am on "Open cert" certification homepage
    And I press "Actions for Open cert"
    And I click on "Enrolment details" "link"
    Then I should see "Open enrolment"
    And the "Withdraw" "button" should be enabled
    When I click on "Withdraw" "button"
    Then I should see "Are you sure you want to withdraw from Open enrolment in Open cert?" in the ".tui-totara_program-enrolment-confirmation" "css_element"
    And I should see "You will be removed from this certification, but your progress and completion records won't be affected." in the ".tui-totara_program-enrolment-confirmation" "css_element"
    When I click on "Withdraw" "text" in the ".tui-btn--variant-primary" "css_element"
    Then I should not see "Actions for Open cert"
    And I should not see "Enrolment details"

    When I log in as "admin"
    And I go to edit the certification "cert-open"
    Then I should see "0 learner(s) assigned: 0 active, 0 exception(s)."

  Scenario: Learner can self enrol into a certification with multiple options
    Given the following "courses" exist:
      | fullname | shortname | enablecompletion |
      | Course 1 | course1   | 1                |
    When I set the field "Add a new" to "Group"
    And I set the following fields to these values:
      | Name             | Second open enrolment  |
      | Description      | 2nd enrolment          |
      | can_self_enrol   | 1                      |
      | can_self_unenrol | 0                      |
    And I press "Ok"

    And I set the field "Add a new" to "Group"
    And I set the following fields to these values:
      | Name             | Past enrolment |
      | Description      |                |
      | can_self_enrol   | 1              |
      | can_self_unenrol | 1              |
    And I press "Ok"
    And I click on "Set due date" "link" in the "Past enrolment" "table_row"
    And I set the field "completiontime" to "31/12/2015"
    And I press "Set fixed completion date"
    And I set the field "Add a new" to "Group"
    And I set the following fields to these values:
      | Name             | Exception enrolment |
      | Description      |                     |
      | can_self_enrol   | 1                   |
      | can_self_unenrol | 1                   |
    And I press "Ok"
    And I click on "Set due date" "link" in the "Exception enrolment" "table_row"
    And I set the following fields to these values:
      | timeamount | 10                |
      | timeperiod | Day(s)            |
      | eventtype  | Course completion |
    And I click on "Miscellaneous" "link" in the "Choose item" "totaradialogue"
    And I click on "Course 1" "link" in the "Choose item" "totaradialogue"
    And I click on "Ok" "button" in the "Choose item" "totaradialogue"
    And I press "Set time relative to event"
    And I click on "Set due date" "link" in the "Open enrolment" "table_row"
    And I set the following fields to these values:
      | timeamount | 30                      |
      | timeperiod | Day(s)                  |
      | eventtype  | Program enrollment date |
    And I press "Set time relative to event"

    When I log in as "user1"
    And I am on "Open cert" certification homepage
    Then I should see "Enrol"
    When I press "Enrol"
    Then I should see "Open enrolment" in the ".tui-modal" "css_element"
    And I should see "Second open enrolment" in the ".tui-modal" "css_element"
    And I should see "2nd enrolment" in the ".tui-modal" "css_element"
    And I should not see "Closed enrolment" in the ".tui-modal" "css_element"
    And I should not see "Past enrolment" in the ".tui-modal" "css_element"
    And I should not see "Exception enrolment" in the ".tui-modal" "css_element"
    And I should see "Enrol" in the ".tui-modal" "css_element"
    When I click on "Second open enrolment" "text" in the ".tui-modal" "css_element"
    Then I should see "Are you sure you want to enrol with Second open enrolment in Open cert?" in the ".tui-totara_program-enrolment-confirmation" "css_element"
    And I should see "You will not be allowed to withdraw from this Group." in the ".tui-totara_program-enrolment-confirmation" "css_element"
    When I click on "Enrol" "text" in the ".tui-modalContent .tui-btn--variant-primary" "css_element"
    Then I should not see "Enrol"
    And I should see "Member of group 'Second open enrolment'"
    And I press "Actions for Open cert"
    And I click on "Enrolment details" "link"
    # Can enrol into one group and withdraw from the other
    Then the "Withdraw" "button" should be disabled
    And the "Enrol" "button" should be enabled
    When I click on "Enrol" "button"
    Then I should see "Are you sure you want to add Open enrolment to your enrolment in Open cert?" in the ".tui-totara_program-enrolment-confirmation" "css_element"
    When I click on "Enrol" "text" in the ".tui-modalContent .tui-btn--variant-primary" "css_element"
    And I press "Actions for Open cert"
    And I click on "Enrolment details" "link"
    And I click on "Withdraw" "button" in the ".tui-totara_program-enrolment-options .tui-card--clickable" "css_element"
    Then I should see "Your other enrolments in this certification won’t be affected and you will remain in this certification." in the ".tui-totara_program-enrolment-confirmation" "css_element"

  Scenario: Learner can't withdraw from certifications with non groups enrolments
    Given the following "cohorts" exist:
      | name      | idnumber | contextlevel | reference |
      | Audience1 | aud1     | System       |           |
    And the following "cohort members" exist:
      | user  | cohort |
      | user1 | aud1   |
    And I set the field "Add a new" to "Audience"
    And I click on "Audience1" "link" in the "add-assignment-dialog-3" "totaradialogue"
    And I click on "Ok" "button" in the "add-assignment-dialog-3" "totaradialogue"

    Given I log in as "user1"
    And I am on "Open cert" certification homepage
    Then I should not see "Enrol"
    When I press "Actions for Open cert"
    And I click on "Enrolment details" "link"
    And the "Withdraw" "button" should be disabled

  Scenario: Learner can't self enrol into certifications if there are no groups allowing it
    Given the following "certifications" exist in "totara_program" plugin:
      | fullname    | shortname   |
      | Closed cert | cert-closed |
    When I click on "Edit group" "link" in the "Open enrolment" "table_row"
    And I set the following fields to these values:
      | can_self_enrol   | 0               |
    And I press "Ok"

    When I log in as "user1"
    And I am on "Open cert" certification homepage
    Then I should not see "Enrol"
    And I am on "Closed cert" certification homepage
    Then I should not see "Enrol"
