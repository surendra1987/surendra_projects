@block @block_totara_report_manager @javascript @totara @totara_reportbuilder
Feature: Report builder report_manager block
  In order to test report builder report_manager block
  As an admin
  I will see error messages in a report_manager block when it contains a report which has settings that are invalid.

  Background:
    Given I am on a totara site
    And I log in as "admin"

  Scenario: Test placing a Report Manager block containing a report with problematic settings on a dashboard page.
    # For an embedded report, edit its settings so that it not hidden in User reports and all records are shown.
    And I navigate to "Manage embedded reports" node in "Site administration > Reports"
    And I set the field "report-name" to "Performance activity participant instances"
    And I press "id_submitgroupstandard_addfilter"
    And I follow "Performance activity participant instances"
    And I click on "id_hidden" "checkbox"
    And I press "Save changes"
    And I switch to "Content" tab
    And I click on "Show all records" "radio"
    And I press "Save changes"
    # Clone the embedded report with the dodgy settings, so we now have a User report based on it.
    And I navigate to "Manage embedded reports" node in "Site administration > Reports"
    And I click on "Clone report" "link"
    And I press "Clone"
    # Add a Report Manager block on the dashboard page, which will attempt to list user reports even if they have invalid settings.
    And I am on "Dashboard" page
    And I press "Customise this page"
    And I add the "Report Manager" block
    And I configure the "Report Manager" block
    And I press "Save changes"
    And I press "Stop customising this page"
    # We expect the page not to throw an error, but instead handle the error & display a message.
    And I should see "Some reports contain errors; please contact your administrator" in the "div.alert-message" "css_element"
