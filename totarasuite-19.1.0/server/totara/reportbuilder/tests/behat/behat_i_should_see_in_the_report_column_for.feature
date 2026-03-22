@totara @totara_reportbuilder
Feature: Test behat can identify report builder columns

  Scenario: Can select rows by header value
    Given I am on a totara site
    And I log in as "admin"

    When I navigate to the "should_see_in_report_column" fixture in the "totara/reportbuilder" plugin
    # Lookup by the TH
    Then I should see "Row1Value" in the "c_col_value" report column for "Row1TH"
    And I should see "Row1TD" in the "c_col_td" report column for "Row1TH"
    And I should see "Row1TH" in the "c_col_th" report column for "Row1TH"

    # Lookup by the TD
    And I should see "Row1Value" in the "c_col_value" report column for "Row1TD"
    And I should see "Row1TD" in the "c_col_td" report column for "Row1TD"
    And I should see "Row1TH" in the "c_col_th" report column for "Row1TD"
