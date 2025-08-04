@totara @totara_hierarchy @totara_hierarchy_goals @totara_customfield @javascript
Feature: Verify creation and use of personal goal types and custom fields.

  Background:
    Given I am on a totara site
    And the following "users" exist:
      | username | firstname | lastname | email             |
      | learner1 | Learner1  | Learner1 | learner1@example.com |
      | learner2 | Learner2  | Learner2 | learner2@example.com |
      | learner3 | Learner3  | Learner3 | learner3@example.com |
      | manager1 | Manager1  | Manager1 | manager1@example.com |
    And the following job assignments exist:
      | user     | manager  |
      | learner1 | manager1 |
    And the following "cohorts" exist:
      | name       | idnumber |
      | Audience 1 | A1       |
      | Audience 2 | A2       |
      | Audience 3 | A3       |
    And the following "cohort members" exist:
      | user     | cohort |
      | learner1 | A1     |
      | learner2 | A2     |
      | learner3 | A3     |

  Scenario: Verify a goal type can be successfully created, updated and deleted.

    # Create a new Personal Goal Type
    Given I log in as "admin"
    And I navigate to "Manage personal goal types" node in "Site administration > Legacy goals"
    And I press "Add a new personal goal type"
    And I set the following fields to these values:
      | Type full name         | Personal Goal Type 1             |
      | Goal type ID number    | PGT1                             |
      | Goal type description  | Personal Goal Type 1 description |
      | Goal type availability | Available to all users           |
    When I press "Save changes"
    Then I should see "The goal type \"Personal Goal Type 1\" has been created"

    # Update the Personal Goal Type.
    When I click on "Edit" "link" in the ".generaltable" "css_element"
    And I set the following fields to these values:
      | Type full name         | Personal Goal Type 1a               |
      | Goal type ID number    | PGT1a                               |
      | Goal type description  | Personal Goal Type 1a description   |
      | Goal type availability | Available only to certain audiences |
    And I press "Add audience(s)"
    And I click on "Audience 1" "link"
    And I click on "OK" "button" in the "Choose audience" "totaradialogue"
    Then I should see "Audience 1" in the "#goal-cohorts-table-enrolled" "css_element"
    # Save the chnages.
    When I press "Save changes"
    Then I should see "The goal type \"Personal Goal Type 1a\" has been updated"

    # Delete the Personal Goal Type.
    When I click on "Delete" "link" in the ".generaltable" "css_element"
    And I press "Continue"
    Then I should see "The goal type \"Personal Goal Type 1a\" has been completely deleted."
    And I should see "No goal types"

  Scenario: Verify audiences can be successfully added and removed from a goal type.

    # Create a new Personal Goal Type
    Given I log in as "admin"
    And I navigate to "Manage personal goal types" node in "Site administration > Legacy goals"
    And I press "Add a new personal goal type"
    And I set the following fields to these values:
      | Type full name         | Personal Goal Type 1                |
      | Goal type ID number    | PGT1                                |
      | Goal type description  | Personal Goal Type 1 description    |
      | Goal type availability | Available only to certain audiences |
    # Add some audiences.
    And I press "Add audience(s)"
    And I click on "Audience 1" "link"
    And I click on "Audience 2" "link"
    When I click on "OK" "button" in the "Choose audience" "totaradialogue"
    Then I should see "Audience 1" in the "#goal-cohorts-table-enrolled" "css_element"
    And I should see "Audience 2" in the "#goal-cohorts-table-enrolled" "css_element"

    # Add an another one to make sure it's appended correctly.
    When I press "Add audience(s)"
    And I click on "Audience 3" "link"
    And I click on "OK" "button" in the "Choose audience" "totaradialogue"
    Then I should see "Audience 3" in the "#goal-cohorts-table-enrolled" "css_element"

    # And then remove and audience so we can make sure they're stored correctly.
    When I click on "Delete" "link" in the "#goal-cohorts-table-enrolled" "css_element"
    Then I should not see "Audience 1" in the "#goal-cohorts-table-enrolled" "css_element"

    # Save the changes.
    When I press "Save changes"
    Then I should see "The goal type \"Personal Goal Type 1\" has been created"

    # Edit the personal goal type and check the correct audiences are present.
    Given I click on "Edit" "link" in the ".generaltable" "css_element"
    Then I should not see "Audience 1" in the "#goal-cohorts-table-enrolled" "css_element"
    And I should see "Audience 2" in the "#goal-cohorts-table-enrolled" "css_element"
    And I should see "Audience 3" in the "#goal-cohorts-table-enrolled" "css_element"

    # Delete an audience so we can make sure it's removed correctly.
    When I click on "Delete" "link" in the "#goal-cohorts-table-enrolled" "css_element"
    Then I should not see "Audience 2" in the "#goal-cohorts-table-enrolled" "css_element"

    # Save the changes.
    When I press "Save changes"
    Then I should see "The goal type \"Personal Goal Type 1\" has been updated"

    # Edit the personal goal type and check the correct audiences are present.
    When I click on "Edit" "link" in the ".generaltable" "css_element"
    Then I should not see "Audience 1" in the "#goal-cohorts-table-enrolled" "css_element"
    And I should not see "Audience 2" in the "#goal-cohorts-table-enrolled" "css_element"
    And I should see "Audience 3" in the "#goal-cohorts-table-enrolled" "css_element"

  Scenario: Verify the Show Details button show the details of the personal goal
    When I log in as "learner1"
    And I am on "Goals" page
    Then I should see "Personal goals"

    # Create a new personal goal
    When I press "Add personal goal"
    Then I should see "Create new personal goal"

    # Create the personal goal.
    And I set the following fields to these values:
      | Name        | Personal Goal 1             |
      | Description | Personal Goal 1 description |

    # Save the changes and continue editing.
    When I press "Save and continue editing"
    Then I should see "Edit personal goal: Personal Goal 1"

    When I press "Save changes"
    Then I should see "Personal Goal 1" in the ".personal_table" "css_element"
    And I should not see "Personal Goal 1 description" in the ".personal_table" "css_element"
    And I press "Show details"
    And I should see "Personal Goal 1 description" in the ".personal_table" "css_element"
    And I press "Hide details"
    And I should not see "Personal Goal 1 description" in the ".personal_table" "css_element"

  @_file_upload @totara_customfield
  Scenario: Verify custom fields can be successfully added to a personal goal type and personal goal.

    # Create a new Personal Goal Type
    Given I log in as "admin"
    And I navigate to "Manage personal goal types" node in "Site administration > Legacy goals"
    And I press "Add a new personal goal type"
    And I set the following fields to these values:
      | Type full name         | Personal Goal Type 1                |
      | Goal type ID number    | PGT1                                |
    # Save the changes.
    When I press "Save changes"
    Then I should see "The goal type \"Personal Goal Type 1\" has been created"

    # Select the goal type to create custom fields for.
    When I follow "Personal Goal Type 1"
    Then I should see "No fields have been defined"

    # Create a checkbox.
    When I set the field "Create a new custom field" to "Checkbox"
    And I set the following fields to these values:
      | Full name                   | Checkbox 1 |
      | Short name (must be unique) | checkbox1  |
    And I press "Save changes"
    Then I should see "Checkbox 1"

    # Create a datetime.
    When I set the field "Create a new custom field" to "Date/time"
    And I set the following fields to these values:
      | Full name                   | Datetime 1 |
      | Short name (must be unique) | datetime1  |
    And I press "Save changes"
    Then I should see "Datetime 1"

    # Create a file upload.
    When I set the field "Create a new custom field" to "File"
    And I set the following fields to these values:
      | Full name                   | File 1 |
      | Short name (must be unique) | file1  |
    And I press "Save changes"
    Then I should see "Datetime 1"

    # Create a menu of choices.
    When I set the field "Create a new custom field" to "Menu of choices"
    And I set the following fields to these values:
      | Full name                   | Menu of choices 1          |
      | Short name (must be unique) | menuofchoices1             |
    And I set the field "Menu options (one per line)" to multiline:
    """
    Choice 1
    Choice 2
    Choice 3
    """
    And I press "Save changes"
    Then I should see "Menu of choices 1"

    # Create a multi-select.
    When I set the field "Create a new custom field" to "Multi-select"
    And I set the following fields to these values:
      | Full name                   | Multi-select 1 |
      | Short name (must be unique) | multiselect1   |
    And I set the field "multiselectitem[0][option]" to "Select 1"
    And I set the field "multiselectitem[1][option]" to "Select 2"
    And I set the field "multiselectitem[2][option]" to "Select 3"
    And I press "Save changes"
    Then I should see "Multi-select 1"

    # Create a text area.
    When I set the field "Create a new custom field" to "Text area"
    And I set the following fields to these values:
      | Full name                   | Text area 1 |
      | Short name (must be unique) | textarea1   |
    And I press "Save changes"
    Then I should see "Text area 1"

    # Create a text input.
    When I set the field "Create a new custom field" to "Text input"
    And I set the following fields to these values:
      | Full name                   | Text input 1 |
      | Short name (must be unique) | textinput1   |
    And I press "Save changes"
    Then I should see "Text input 1"

    # Create a URL field.
    When I set the field "Create a new custom field" to "URL"
    And I set the following fields to these values:
      | Full name                   | URL 1 |
      | Short name (must be unique) | url1  |
    And I press "Save changes"
    Then I should see "URL 1"

    # Login as a learner and create a personal goal with the custom fields.
    When I log out
    And I log in as "learner1"
    And I am on "Goals" page
    Then I should see "Personal goals"

    # Create a new personal goal
    When I press "Add personal goal"
    Then I should see "Create new personal goal"

    # Create the personal goal.
    When I set the following fields to these values:
      | Name | Personal Goal 1      |
      | Type | Personal Goal Type 1 |
    And I press "Save changes"
    Then I should see "Personal Goal 1" in the ".personal_table" "css_element"

    # We can only add the custom field data after the goal has been created.
    When I click on "Edit" "link" in the ".personal_table" "css_element"
    And I set the following fields to these values:
      | Checkbox 1                     | 1                              |
      | customfield_datetime1[enabled] | 1                              |
      | customfield_datetime1[month]   | December                       |
      | customfield_datetime1[day]     | 31                             |
      | customfield_datetime1[year]    | 2035                           |
      | customfield_menuofchoices1     | Choice 1                       |
      | Select 1                       | 1                              |
      | Select 2                       | 1                              |
      | Select 3                       | 1                              |
      | Text area 1                    | Text area 1                    |
      | Text input 1                   | Text input 1                   |
      | customfield_url1[url]          | https://www.totaralearning.com |
      | customfield_url1[text]         | Totara LMS                     |
      | customfield_url1[target]       | 1                              |
    # The file upload won't work while there's an existing problem with the file manager loading.
    # Uncomment tag over scenario and lines below when problem with the file manager fixed.
    # And I upload "/totara/core/pix/logo.png" file to "File 1" filemanager
    And I press "Save changes"
    And I should see "Personal Goal Type 1"
    And I press "Show details"
    # Check that all the data has been added to the personal goal.
    Then I should see "Checkbox 1: Yes"
    And I should see "Datetime 1: 31 December 2035"
    And I should see "File 1:"
    # And I should see "logo.png"
    And I should see "Menu of choices 1: Choice 1"
    And I should see "Multi-select 1:"
    And I should see "Select 1"
    And I should see "Select 2"
    And I should see "Select 3"
    And I should see "Text area 1: Text area 1"
    And I should see "Text input 1: Text input 1"
    And I should see "URL 1: Totara LMS"
