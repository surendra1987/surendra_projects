@_file_upload @javascript @tool @totara @totara_hierarchy @tool_totara_sync
Feature: Verify that HR Import skips empty tables when 'Source contains all records' is set to 'yes' for CSV uploads.

  Background:
    Given I am on a totara site
    And I log in as "admin"
    And the following "users" exist:
      | username | firstname | lastname | email                   |
      | alice    | Alice     | Smith    | alice.smith@example.com |

    And I navigate to "Default settings" node in "Site administration > HR Import"
    And I set the following fields to these values:
    | File access | Upload Files |
    And I press "Save changes"

    And I navigate to "Manage elements" node in "Site administration > HR Import > Elements"
    And I "Enable" the "User" HR Import element

    And I navigate to "CSV" node in "Site administration > HR Import > Sources > User"
    And I press "Save changes"

  Scenario: Verify CSV is skipped when it contains no data.
    # We want to enable a second element to check the case when the first element fails to run, the second will be run without issue.
    Given I navigate to "Manage elements" node in "Site administration > HR Import > Elements"
    And I "Enable" the "Organisation" HR Import element
    And I navigate to "Organisation" node in "Site administration > HR Import > Elements"
    And I set the following fields to these values:
      | CSV                         | 1   |
      | Source contains all records | Yes |
    And I press "Save changes"
    And I navigate to "CSV" node in "Site administration > HR Import > Sources > Organisation"
    And I press "Save changes"

    And I navigate to "User" node in "Site administration > HR Import > Elements"
    And I set the following fields to these values:
      | CSV                         | 1   |
      | Source contains all records | Yes |
    And I press "Save changes"

    And I navigate to "Upload HR Import files" node in "Site administration > HR Import > Sources"
    And I upload "admin/tool/totara_sync/tests/fixtures/organisation_empty.csv" file to the filemanager in the "Organisation" "fieldset"
    When I press "Upload"
    Then I should see "HR Import files uploaded successfully"

    Given I navigate to "Upload HR Import files" node in "Site administration > HR Import > Sources"
    And I upload "admin/tool/totara_sync/tests/fixtures/user_one_record.csv" file to the filemanager in the "User" "fieldset"
    When I press "Upload"
    Then I should see "HR Import files uploaded successfully"

    Given I navigate to "Run HR Import" node in "Site administration > HR Import"
    When I press "Run HR Import"
    Then I should see "Error:org - HR Import for this element was skipped because the \"Source contains all records\" setting is enabled, but the provided source is empty, and so performing this action would remove all records for this element."
    Given I click on "here" "link"
    # We check for "HR Import finished" because it shows that even though the organisation step was skipped, the user step passed as expected
    Then I should see "HR Import for this element was skipped because the \"Source contains all records\" setting is enabled, but the provided source is empty, and so performing this action would remove all records for this element."
    And I should see "HR Import finished"

  Scenario: Verify when "Source contains all records" is set to "No" and I upload a empty file with headers it will not delete users.
    Given I navigate to "User" node in "Site administration > HR Import > Elements"
    And I set the following fields to these values:
      | CSV                         | 1  |
      | Source contains all records | No |
    And I press "Save changes"

    And I navigate to "Upload HR Import files" node in "Site administration > HR Import > Sources"
    And I upload "admin/tool/totara_sync/tests/fixtures/user_empty.csv" file to the filemanager in the "User" "fieldset"
    When I press "Upload"
    Then I should see "HR Import files uploaded successfully"

    Given I navigate to "Run HR Import" node in "Site administration > HR Import"
    When I press "Run HR Import"
    Then I should not see "Error:user - HR Import for this element was skipped because the \"Source contains all records\" setting is enabled, but the provided source is empty, and so performing this action would remove all records for this element."

    Given I navigate to "Manage users" node in "Site administration > Users"
    Then I should see "Alice Smith"
