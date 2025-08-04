@_file_upload @javascript @tool @totara @totara_hierarchy @tool_totara_sync
Feature: Verify that when "Source contains all records" is set to "Yes" and a CSV file contains less records that currently present, a confirmation dialog is shown before importing.
  Background:
    Given I am on a totara site
    And I log in as "admin"

    And I navigate to "Default settings" node in "Site administration > HR Import"
    And I set the following fields to these values:
      | File access | Upload Files |
    And I press "Save changes"

  Scenario: Verify confirmation dialog showing warning about deleting positions and users is shown when the CSV import contains less records than the site
    Given the following "position frameworks" exist in "totara_hierarchy" plugin:
      | fullname      | idnumber |
      | Position FW 1 | PFW1     |
    And the following "positions" exist in "totara_hierarchy" plugin:
      | pos_framework | fullname   | shortname | idnumber | totarasync |
      | PFW1          | Position 4 | Pos4      | POS04    | 0          |
      | PFW1          | Position 5 | Pos5      | POS05    | 1          |
      | PFW1          | Position 6 | Pos6      | POS06    | 1          |
      | PFW1          | Position 7 | Pos7      | POS07    | 1          |
      | PFW1          | Position 8 | Pos8      | POS08    | 1          |
    And the following "users" exist:
      | username | firstname | lastname | email                   | totarasync |
      | alice    | Alice     | Smith    | alice.smith@example.com | 0          |
      | john     | John      | One      | john.one@example.com    | 1          |
      | david    | David     | Two      | david.two@example.com   | 1          |
      | jane     | Jane      | Three    | jane.three@example.com  | 1          |

    When I navigate to "Manage elements" node in "Site administration > HR Import > Elements"
    And I "Enable" the "Position" HR Import element
    And I "Enable" the "User" HR Import element

    When I navigate to "CSV" node in "Site administration > HR Import > Sources > Position"
    And I press "Save changes"
    And I navigate to "Position" node in "Site administration > HR Import > Elements"
    And I set the following fields to these values:
      | CSV                         | 1                         |
      | Source contains all records | Yes                       |
    And I press "Save changes"

    When I navigate to "CSV" node in "Site administration > HR Import > Sources > User"
    And I press "Save changes"
    And I navigate to "User" node in "Site administration > HR Import > Elements"
    And I set the following fields to these values:
      | CSV                         | 1                         |
      | Source contains all records | Yes                       |
      | Delete                      | Full delete internal user |
    And I press "Save changes"

    When I navigate to "Upload HR Import files" node in "Site administration > HR Import > Sources"
    And I upload "admin/tool/totara_sync/tests/fixtures/pos_partial_sync_1.csv" file to the filemanager in the "Position" "fieldset"
    And I press "Upload"
    And I upload "admin/tool/totara_sync/tests/fixtures/user_one_record.csv" file to the filemanager in the "User" "fieldset"
    And I press "Upload"
    Then I should see "HR Import files uploaded successfully"

    When I navigate to "Run HR Import" node in "Site administration > HR Import"
    And I press "Run HR Import"

    Then I should see "Confirm" in the "Confirmation" "dialogue"
    And I should see "The upload contains 25% less positions than the site currently has. This action will delete any missing positions." in the "Confirmation" "dialogue"
    And I should see "The upload contains 67% less users than the site currently has. This action will delete any missing users." in the "Confirmation" "dialogue"
    And I should see "Are you sure you want to continue?" in the "Confirmation" "dialogue"

    When I click on "Continue" "button" in the "Confirmation" "dialogue"
    Then I should not see "Error"
    And I should see "Running HR Import cron...Done!"

    When I navigate to "Manage positions" node in "Site administration > Positions"
    And I click on "Position FW 1" "link"
    Then I should see "Position 1"
    And I should see "Position 4"
    And I should not see "Position 5"

    When I navigate to "Manage users" node in "Site administration > Users"
    Then I should see "import ed"
    And I should see "Alice Smith"
    And I should not see "John One"

  Scenario: Verify confirmation dialogue shows when importing users via CSV and suspend internal user is selected
    Given the following "users" exist:
      | username | firstname | lastname | email                   | totarasync |
      | alice    | Alice     | Smith    | alice.smith@example.com | 0          |
      | john     | John      | One      | john.one@example.com    | 1          |
      | david    | David     | Two      | david.two@example.com   | 1          |
      | jane     | Jane      | Three    | jane.three@example.com  | 1          |
    When I navigate to "Manage elements" node in "Site administration > HR Import > Elements"
    And I "Enable" the "User" HR Import element
    And I navigate to "CSV" node in "Site administration > HR Import > Sources > User"
    And I press "Save changes"
    And I navigate to "User" node in "Site administration > HR Import > Elements"
    And I set the following fields to these values:
      | CSV                         | 1                     |
      | Source contains all records | Yes                   |
      | Delete                      | Suspend internal user |
    And I press "Save changes"
    And I navigate to "Upload HR Import files" node in "Site administration > HR Import > Sources"
    And I upload "admin/tool/totara_sync/tests/fixtures/user_one_record.csv" file to the filemanager in the "User" "fieldset"
    When I press "Upload"
    Then I should see "HR Import files uploaded successfully"

    When I navigate to "Run HR Import" node in "Site administration > HR Import"
    And I press "Run HR Import"

    Then I should see "Confirm" in the "Confirmation" "dialogue"
    And I should see "The upload contains 67% less users than the site currently has. This action will suspend any missing users." in the "Confirmation" "dialogue"
    And I should see "Are you sure you want to continue?" in the "Confirmation" "dialogue"

    When I click on "Continue" "button" in the "Confirmation" "dialogue"
    Then I should not see "Error"
    And I should see "Running HR Import cron...Done!"

    When I navigate to "Manage users" node in "Site administration > Users"
    Then I should see "import ed"
    And I should see "Alice Smith"
    And I should not see "John One"
