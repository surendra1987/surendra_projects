@totara @totara_reportbuilder @javascript
Feature: Identify certain columns as row headers
  As an admin
  I should be able to mark certain columns as headers in my reports

  Background:
    Given I am on a totara site
    And the following "users" exist:
      | username | firstname | lastname | email             |
      | user1    | User      | One      | user1@example.com |
      | user2    | User      | Two      | user2@example.com |
      | user3    | User      | Three    | user3@example.com |
    And the following "standard_report" exist in "totara_reportbuilder" plugin:
      | fullname    | shortname          | source |
      | User report | report_user_report | user   |

  Scenario: Test the rowheader column option
    Given I log in as "admin"
    And I navigate to "Manage user reports" node in "Site administration > Reports"
    And I follow "User report"
    And I switch to "Columns" tab
    And I check the row header option in the  "Username" column in the report
    And I press "Save changes"
    When I follow "View This Report"
    Then "//div[contains(@class, 'totara-table-container')]//tbody//tr/th[contains(@class, 'user_username')]" "xpath_element" should exist
    And "//div[contains(@class, 'totara-table-container')]//tbody//tr/td[contains(@class, 'user_username')]" "xpath_element" should not exist

    When I press "Edit this report"
    And I switch to "Columns" tab
    And I uncheck the row header option in the  "Username" column in the report
    And I press "Save changes"
    And I follow "View This Report"
    Then "//div[contains(@class, 'totara-table-container')]//tbody//tr/th[contains(@class, 'user_username')]" "xpath_element" should not exist
    And "//div[contains(@class, 'totara-table-container')]//tbody//tr/td[contains(@class, 'user_username')]" "xpath_element" should exist
