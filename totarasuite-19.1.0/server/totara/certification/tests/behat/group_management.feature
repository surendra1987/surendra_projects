@totara @totara_certification @javascript
Feature: Managing group certification assignments
  Admin needs to be able to manage group certification assignments

  Background:
    Given I am on a totara site
    And the following "certifications" exist in "totara_program" plugin:
      | fullname           | shortname |
      | Test certification | cert1     |
    And the following "users" exist:
      | username | firstname | lastname | email |
      | learner1 | Learner   | One      | learner1@example.com |
      | learner2 | Learner   | Two      | learner2@example.com |
      | learner3 | Learner   | Three    | learner3@example.com |
    And I log in as "admin"
    And I navigate to "Manage certifications" node in "Site administration > Certifications"
    And I click on "Miscellaneous" "link"
    And I click on "Test cert" "link"
    And I click on "Edit certification details" "button"
    And I click on "Assignments" "link"

  Scenario: Admin can manage a certification assignment with type Group
    # Creating a new group
    When I set the field "Add a new" to "Group"
    Then the field "Name" matches value ""
    And the field "Description" matches value ""
    And "input[name=can_self_enrol]:not([checked=checked])" "css_element" should exist
    And "input[name=can_self_unenrol]:not([checked=checked])" "css_element" should exist
    When I set the following fields to these values:
      | Name             |                  |
      | Description      | Test description |
      | can_self_enrol   | 1                |
      | can_self_unenrol | 1                |
    And I press "Ok"
    Then I should see "Name is required"
    When I set the field "Name" to "Test grp"
    And I press "Ok"
    Then I should see "'Test grp' has been added to the certification"
    And I should see "View dates" in the "Test grp" "table_row"
    And I should see "0" in the "Test grp" "table_row"
    # Editing an existing group
    When I click on "Edit group" "link" in the "Test grp" "table_row"
    Then the field "Name" matches value "Test grp"
    And the field "Description" matches value "Test description"
    And "input[name=can_self_enrol][checked=checked]" "css_element" should exist
    And "input[name=can_self_unenrol][checked=checked]" "css_element" should exist
    When I set the following fields to these values:
      | Name             |                 |
      | Description      | New description |
      | can_self_enrol   | 0               |
      | can_self_unenrol | 0               |
    And I press "Ok"
    Then I should see "Name is required"
    When I set the field "Name" to "New name"
    And I press "Ok"
    Then I should not see "Group name"
    And I should see "'New name' has been updated"
    And I should see "View dates" in the "New name" "table_row"
    And I should see "0" in the "New name" "table_row"
    When I click on "Edit group" "link" in the "New name" "table_row"
    Then the field "Name" matches value "New name"
    And the field "Description" matches value "New description"
    And "input[name=can_self_enrol]:not([checked=checked])" "css_element" should exist
    And "input[name=can_self_unenrol]:not([checked=checked])" "css_element" should exist
    And I press "Ok"
    # Adding users into the group
    When I click on "Add users" "link" in the "New name" "table_row"
    And I click on "Learner One (learner1@example.com)" "link" in the "add-group-users-dialog" "totaradialogue"
    And I click on "Learner Two (learner2@example.com)" "link" in the "add-group-users-dialog" "totaradialogue"
    And I click on "Ok" "button" in the "add-group-users-dialog" "totaradialogue"
    Then I should see "2 learner(s) assigned: 2 active, 0 exception(s)"
    And I should see "2" in the "New name" "table_row"
    # Add more users
    When I click on "Add users" "link" in the "New name" "table_row"
    And I click on "Learner One (learner1@example.com)" "link" in the "add-group-users-dialog" "totaradialogue"
    And I click on "Search" "link" in the "add-group-users-dialog" "totaradialogue"
    And I set the field "id_query" to "three"
    And I click on "dialogsearchsubmitbutton" "button" in the "add-group-users-dialog" "totaradialogue"
    And I click on "Learner Three (learner3@example.com)" "link" in the "#search-tab" "css_element"
    And I click on "Ok" "button" in the "add-group-users-dialog" "totaradialogue"
    Then I should see "1 user(s) have been added to New name"
    And I should see "3 learner(s) assigned: 3 active, 0 exception(s)"
    And I should see "3" in the "New name" "table_row"
    When I click on "View dates" "link" in the "New name" "table_row"
    Then I should see "Learner One" in the "Learner One" "table_row"
    And I should see "Learner Three" in the "Learner Three" "table_row"
    Then I should see "Remove Learner One" in the "Learner One" "table_row"
    # Filter users in group
    When I set the field "user-fullname" to "two"
    And I click on "submitgroupstandard[addfilter]" "button"
    Then I should not see "Learner One"
    And I should see "Learner Two" in the "Learner Two" "table_row"
    When I click on "submitgroupstandard[clearstandardfilters]" "button"
    Then I should see "Learner One" in the "Learner One" "table_row"
    And I should see "Learner Two" in the "Learner Two" "table_row"
    # Remove users from group
    When I click on "Remove Learner One" "link" in the "Learner One" "table_row"
    Then I should not see "Remove Learner One"
    And I should see "2" in the "New name" "table_row"
    And I should see "2 learner(s) assigned: 2 active, 0 exception(s)"
    # Add user from view dates dialog
    When I click on "Add Users" "button" in the ".moodle-dialogue-bd" "css_element"
    And I click on "Learner One (learner1@example.com)" "link" in the "add-group-users-dialog" "totaradialogue"
    And I click on "Ok" "button" in the "add-group-users-dialog" "totaradialogue"
    Then I click on "View dates" "link" in the "New name" "table_row"
    Then I should see "Learner One" in the "Learner One" "table_row"
    And I should see "Learner Two" in the "Learner Two" "table_row"
    And I should see "Learner Three" in the "Learner Three" "table_row"
    # Creating another new group
    When I click on "Close" "button" in the ".moodle-dialogue" "css_element"
    When I set the field "Add a new" to "Group"
    Then the field "Name" matches value ""
    And the field "Description" matches value ""
    And "input[name=can_self_enrol]:not([checked=checked])" "css_element" should exist
    And "input[name=can_self_unenrol]:not([checked=checked])" "css_element" should exist
    When I set the following fields to these values:
      | Name             | Another group       |
      | Description      | Another description |
      | can_self_enrol   | 0                   |
      | can_self_unenrol | 0                   |
    And I press "Ok"
    Then I should see "'Another group' has been added to the certification"
    And I should see "View dates" in the "Another group" "table_row"
    And I should see "0" in the "Another group" "table_row"
    # Editing a previously created/edited group
    When I click on "Edit group" "link" in the "New name" "table_row"
    Then the field "Name" matches value "New name"

  Scenario: Admin can delete a certification assignment of type Group
    When I set the field "Add a new" to "Group"
    And I set the field "Name" to "Test group"
    And I press "Ok"
    And I click on "Add users" "link" in the "Test group" "table_row"
    And I click on "Learner One (learner1@example.com)" "link" in the "add-group-users-dialog" "totaradialogue"
    And I click on "Learner Two (learner2@example.com)" "link" in the "add-group-users-dialog" "totaradialogue"
    And I click on "Ok" "button" in the "add-group-users-dialog" "totaradialogue"
    Then I should see "2 learner(s) assigned: 2 active, 0 exception(s)"
    And I click on "Remove assignment" "link" in the "Test group" "table_row"
    Then I should see "Are you sure you want to remove this assignment?"
    When I press "Remove"
    Then I should see "'Test group' has been removed from the certification"
    And I should see "No results"