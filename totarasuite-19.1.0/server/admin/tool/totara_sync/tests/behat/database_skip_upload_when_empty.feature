@javascript @tool @totara @totara_hierarchy @tool_totara_sync
Feature: Verify that HR Import skips an empty database import when 'Source contains all records' is set to 'yes', and correctly logs the error .

  Background:
    Given I am on a totara site
    And I log in as "admin"
    And the following "users" exist:
      | username | firstname | lastname | email                   |
      | alice    | Alice     | Smith    | alice.smith@example.com |

    And the following "User" HR Import database source exists:
      | idnumber | timemodified | username | firstname | lastname | email | deleted |

    And I navigate to "Manage elements" node in "Site administration > HR Import > Elements"
    And I "Enable" the "User" HR Import element

    And I navigate to "External Database" node in "Site administration > HR Import > Sources > User"
    And I press "Save changes"

  Scenario: Verify an external database is skipped when it contains no data.
    Given I navigate to "User" node in "Site administration > HR Import > Elements"
    And I set the following fields to these values:
      | External Database           | 1   |
      | Source contains all records | Yes |
    And I press "Save changes"

    And I navigate to "Run HR Import" node in "Site administration > HR Import"
    When I press "Run HR Import"
    Then I should see "Error:user - HR Import for this element was skipped because the \"Source contains all records\" setting is enabled, but the provided source is empty, and so performing this action would remove all records for this element."

    Given I navigate to "Manage users" node in "Site administration > Users"
    Then I should see "Alice Smith"

  Scenario: Verify an external database is skipped when it contains no data and "Source contains all records" is set to "No".
    # We want to enable a second element to check the case when the first element fails to run, the second will be run without issue.
    Given I navigate to "Manage elements" node in "Site administration > HR Import > Elements"
    And I "Enable" the "Organisation" HR Import element

    And the following "Organisation" HR Import database source exists:
      | idnumber | fullname         | frameworkidnumber | parentidnumber | timemodified |
      | 0        | Head Office      | OF1               |                | 0            |
    And I navigate to "Organisation" node in "Site administration > HR Import > Elements"
    And I set the following fields to these values:
      | External Database           | 1   |
      | Source contains all records | Yes |
    And I press "Save changes"
    And I navigate to "External Database" node in "Site administration > HR Import > Sources > Organisation"
    And I press "Save changes"

    And I navigate to "User" node in "Site administration > HR Import > Elements"
    And I set the following fields to these values:
      | External Database           | 1   |
      | Source contains all records | No  |
    And I press "Save changes"

    Given I navigate to "Run HR Import" node in "Site administration > HR Import"
    When I press "Run HR Import"
    Then I should not see "Error:user - HR Import for this element was skipped because the \"Source contains all records\" setting is enabled, but the provided source is empty, and so performing this action would remove all records for this element."

    Given I navigate to "HR Import Log" node in "Site administration > HR Import"
    Then I should see "HR Import finished"
