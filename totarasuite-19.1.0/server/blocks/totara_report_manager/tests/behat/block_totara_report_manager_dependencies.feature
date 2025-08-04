@block @block_totara_report_manager @javascript
Feature: Report Manager block can be added to the top region of the dashboard
  In order to check necessary explicit dependencies
  As a user
  I need to add Report Manager to the top region of the dashboard and verify its visibility

  Background:
    Given I am on a totara site
    And I log in as "admin"
    And I press "Customise this page"

  Scenario: Add a Report Manager block to the top region of the clear dashboard
    When I add the "Report Manager" block to the "top" region
    Then I should see the "Report Manager" block