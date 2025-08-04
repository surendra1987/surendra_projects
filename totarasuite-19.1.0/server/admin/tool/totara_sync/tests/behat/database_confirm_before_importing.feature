@javascript @tool @totara @totara_hierarchy @tool_totara_sync
Feature: Verify that when "Source contains all records" is set to "Yes" and a database source contains less records that currently present, a confirmation dialog is shown before importing.

  Background:
    Given I am on a totara site
    And I log in as "admin"

  Scenario: Verify confirmation dialog showing warning about deleting positions and users is shown when the database import contains less records than the site
    Given the following "position frameworks" exist in "totara_hierarchy" plugin:
      | fullname      | idnumber |
      | Position FW 1 | PFW1     |
    And the following "positions" exist in "totara_hierarchy" plugin:
      | pos_framework | fullname   | shortname | idnumber | totarasync |
      | PFW1          | Position 1 | Pos1      | POS01    | 0          |
      | PFW1          | Position 2 | Pos2      | POS02    | 1          |
      | PFW1          | Position 3 | Pos3      | POS03    | 1          |
      | PFW1          | Position 4 | Pos4      | POS04    | 1          |
      | PFW1          | Position 5 | Pos5      | POS05    | 1          |
    And the following "users" exist:
      | username | firstname | lastname | email                   | totarasync |
      | alice    | Alice     | Smith    | alice.smith@example.com | 0          |
      | john     | John      | One      | john.one@example.com    | 1          |
      | david    | David     | Two      | david.two@example.com   | 1          |
      | jane     | Jane      | Three    | jane.three@example.com  | 1          |
    And the following "Position" HR Import database source exists:
      | idnumber | fullname   | deleted | frameworkidnumber | parentidnumber | timemodified |
      | 555      | Position 5 | 0       | PFW1              |                | 0            |
      | 666      | Position 6 | 0       | PFW1              |                | 0            |
      | 777      | Position 7 | 0       | PFW1              |                | 0            |
    And the following "User" HR Import database source exists:
      | idnumber | timemodified | username | firstname | lastname | email     | deleted |
      | 79       | 0            | imported | import    | ed       | import@ed | 0       |

    When I navigate to "Manage elements" node in "Site administration > HR Import > Elements"
    And I "Enable" the "Position" HR Import element
    And I navigate to "External Database" node in "Site administration > HR Import > Sources > Position"
    And I press "Save changes"
    And I navigate to "Position" node in "Site administration > HR Import > Elements"
    And I set the following fields to these values:
      | External Database           | 1   |
      | Source contains all records | Yes |
    And I press "Save changes"

    When I navigate to "Manage elements" node in "Site administration > HR Import > Elements"
    And I "Enable" the "User" HR Import element
    And I navigate to "External Database" node in "Site administration > HR Import > Sources > User"
    And I press "Save changes"
    And I navigate to "User" node in "Site administration > HR Import > Elements"
    And I set the following fields to these values:
      | External Database           | 1                         |
      | Source contains all records | Yes                       |
      | Delete                      | Full delete internal user |
    And I press "Save changes"

    When I navigate to "Run HR Import" node in "Site administration > HR Import"
    And I press "Run HR Import"

    Then I should see "Confirm" in the "Confirmation" "dialogue"
    And I should see "The upload contains 25% less positions than the site currently has. This action will delete any missing positions." in the "Confirmation" "dialogue"
    And I should see "The upload contains 67% less users than the site currently has. This action will delete any missing users." in the "Confirmation" "dialogue"
    And I should see "Are you sure you want to continue?" in the "Confirmation" "dialogue"

    When I click on "Continue" "button" in the "Confirmation" "dialogue"
    Then I should not see "Error"
    And I should see "Running HR Import cron...Done!"

  Scenario: Verify confirmation dialogue shows when importing users via a database and suspend internal user is selected
    Given the following "users" exist:
      | username | firstname | lastname | email                   | totarasync |
      | alice    | Alice     | Smith    | alice.smith@example.com | 0          |
      | john     | John      | One      | john.one@example.com    | 1          |
      | david    | David     | Two      | david.two@example.com   | 1          |
      | jane     | Jane      | Three    | jane.three@example.com  | 1          |
    And the following "User" HR Import database source exists:
      | idnumber | timemodified | username | firstname | lastname | email     | deleted |
      | 79       | 0            | imported | import    | ed       | import@ed | 0       |

    When I navigate to "Manage elements" node in "Site administration > HR Import > Elements"
    And I "Enable" the "User" HR Import element
    And I navigate to "External Database" node in "Site administration > HR Import > Sources > User"
    And I press "Save changes"
    And I navigate to "User" node in "Site administration > HR Import > Elements"
    And I set the following fields to these values:
      | External Database           | 1                     |
      | Source contains all records | Yes                   |
      | Delete                      | Suspend internal user |
    And I press "Save changes"

    When I navigate to "Run HR Import" node in "Site administration > HR Import"
    And I press "Run HR Import"

    Then I should see "Confirm" in the "Confirmation" "dialogue"
    And I should see "The upload contains 67% less users than the site currently has. This action will suspend any missing users." in the "Confirmation" "dialogue"
    And I should see "Are you sure you want to continue?" in the "Confirmation" "dialogue"

    When I click on "Continue" "button" in the "Confirmation" "dialogue"
    Then I should not see "Error"
    And I should see "Running HR Import cron...Done!"
