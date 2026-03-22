@javascript @totara @totara_dashboard
Feature: Perform basic dashboard administration
  In order to ensure that dashboard work as expected
  As an admin
  I need to manage master version of dashboard layout

  Background:
    Given I am on a totara site
    And the following totara_dashboards exist:
      | name               | locked | published |
      | Dashboard for edit | 1      | 1         |

  Scenario: Add block to default dashboard
    When I log in as "admin"
    And I navigate to "Dashboards" node in "Site administration > Navigation"
    And I press "Create dashboard"
    And I set the following fields to these values:
      | Name | Behat Test Dashboard |
    And I click on "Available only to the following audiences" "radio"
    And I press "Create dashboard"
    Then I should see "Behat Test Dashboard" in the ".generaltable" "css_element"
    And I should see "Dashboard saved" in the ".alert-success" "css_element"

  Scenario: Edit dashboard
    Given I log in as "admin"
    And I navigate to "Dashboards" node in "Site administration > Navigation"
    And I should see "Dashboard for edit" in the ".generaltable" "css_element"
    And I click on ".edit" "css_element" in the "Dashboard for edit" "table_row"
    And I set the following fields to these values:
      | name   | Edited Behat Test Dashboard |
      | Locked | 0                           |
    And I click on "Available to no users" "radio"
    And I press "id_submitbutton"
    Then I should see "Edited Behat Test Dashboard" in the ".generaltable" "css_element"
    And I should see "Dashboard saved" in the ".alert-success" "css_element"

  Scenario: Check available to all dashboard management
    Given I log in as "admin"
    And I navigate to "Dashboards" node in "Site administration > Navigation"
    And I press "Create dashboard"
    And I set the following fields to these values:
      | Name   | Public dashboard |
      | Locked | 0                |
    And I click on "Available to all logged in users" "radio"
    And I press "Create dashboard"
    And I should see "Available to all logged in users" in the "Public dashboard" "table_row"
    And I click on "Edit dashboard" "link" in the "Public dashboard" "table_row"
    When I press "Save changes"
    Then I should see "Available to all logged in users" in the "Public dashboard" "table_row"

  Scenario: Assign audience to dashboard and then make it public
    Given I log in as "admin"
    Given the following "cohorts" exist:
      | name    | idnumber |
      | Cohort1 | COHORT1  |
      | Cohort2 | COHORT2  |
    And I navigate to "Dashboards" node in "Site administration > Navigation"
    And I press "Create dashboard"
    And I set the following fields to these values:
      | Name | Audience dashboard |
    And I click on "Available only to the following audiences" "radio"
    And I press "Assign new audiences"
    And I click on "Cohort1" "link"
    And I click on "Cohort2" "link"
    And I press "OK"
    Then I should see "Audience name"
    And I should see "Cohort1"
    And I should see "Cohort2"
    And I press "Create dashboard"
    And I should see "2" in the "Audience dashboard" "table_row"
    # Check saving changes
    And I click on "Edit dashboard" "link" in the "Audience dashboard" "table_row"
    And I click on "Available to all logged in users" "radio"
    When I press "Save changes"
    Then I should see "Available to all logged in users" in the "Audience dashboard" "table_row"

  Scenario: Delete dashboard
    Given I log in as "admin"
    And I navigate to "Dashboards" node in "Site administration > Navigation"
    And I click on ".delete" "css_element" in the "Dashboard for edit" "table_row"
    And I should see "Do you really want to remove dashboard"
    And I press "Continue"
    And I click on ".delete" "css_element" in the "My Learning" "table_row"
    And I should see "Do you really want to remove dashboard"
    And I press "Continue"
    Then ".generaltable" "css_element" should not exist
    And I should see "No dashboards"

  @javascript
  Scenario: Move dashboard sort order up
    Given the following totara_dashboards exist:
      | name        |
      | Dashboard 2 |
      | Dashboard 3 |
    And I log in as "admin"
    And I navigate to "Dashboards" node in "Site administration > Navigation"
    And I click on ".up" "css_element" in the "Dashboard 2" "table_row"
    Then "Dashboard 2" "link" should appear before "Dashboard for edit" "link"
    And "Dashboard for edit" "link" should appear before "Dashboard 3" "link"

  @javascript
  Scenario: Move dashboard sort order down
    Given the following totara_dashboards exist:
      | name        |
      | Dashboard 2 |
      | Dashboard 3 |
    And I log in as "admin"
    And I navigate to "Dashboards" node in "Site administration > Navigation"
    And I click on ".down" "css_element" in the "Dashboard 2" "table_row"
    Then "Dashboard 3" "link" should appear before "Dashboard 2" "link"
    And "Dashboard for edit" "link" should appear before "Dashboard 3" "link"

  Scenario: Manage dashboard with dashboard report feature flag on
    Given the following totara_dashboards exist:
      | name               | locked | published |
      | Report dashboard 1 | 1      | 1         |
      | Report dashboard 2 | 1      | 1         |
    When the following config values are set as admin:
      | totara_19_1_totara_dashboard_report | 1 |
    And I reload the page
    And I log in as "admin"
    And I navigate to "Dashboards" node in "Site administration > Navigation"
    Then I should see "Name" in the "Search by" "fieldset"
    Then I should see "Guest access" in the "Search by" "fieldset"
    Then I should see "Availability" in the "Search by" "fieldset"
    Then I should see "Dashboard for edit" in the ".embeddedshortname_dashboard_dashboards" "css_element"
    # Check newly added dashboard's sortorder without any changes
    Then dashboard "Report dashboard 1" sortorder is "2"
    Then dashboard "Report dashboard 2" sortorder is "3"
    # Check edit link and different display scenario works
    And I click on "Edit dashboard" "link" in the "Report dashboard 1" "table_row"
    And I set the field "sortorder[sortorder]" to ""
    And I press "Save changes"
    Then dashboard "Report dashboard 1" sortorder is "3"
    Then dashboard "Report dashboard 2" sortorder is "2"
    Then "Report dashboard 2" "text" should appear before "Report dashboard 1" "text" in the ".embeddedshortname_dashboard_dashboards" "css_element"
    And I click on "Edit dashboard" "link" in the "Report dashboard 1" "table_row"
    And I set the field "sortorder[sortorder]" to "2"
    And I press "Save changes"
    Then dashboard "Report dashboard 1" sortorder is "2"
    Then dashboard "Report dashboard 2" sortorder is "3"
    Then "Report dashboard 1" "text" should appear before "Report dashboard 2" "text" in the ".embeddedshortname_dashboard_dashboards" "css_element"
    # Confirm Clone link works
    And I click on "Actions" "button" in the "Report dashboard 1" "table_row"
    And I click on "Clone" "button" in the ".tui-dropdown__menu--open" "css_element"
    Then I should see "Do you really want to clone dashboard Report dashboard 1?"
    And I press "Continue"
    Then dashboard "Report dashboard 1 copy 1" sortorder is "4"
    # Confirm move up works
    And I click on "Actions" "button" in the "Report dashboard 2" "table_row"
    And I click on "Move up" "button" in the ".tui-dropdown__menu--open" "css_element"
    Then "Report dashboard 2" "text" should appear before "Report dashboard 1" "text" in the ".embeddedshortname_dashboard_dashboards" "css_element"
    # Confirm move down works
    And I click on "Actions" "button" in the "Report dashboard 2" "table_row"
    And I click on "Move down" "button" in the ".tui-dropdown__menu--open" "css_element"
    Then "Report dashboard 1" "text" should appear before "Report dashboard 2" "text" in the ".embeddedshortname_dashboard_dashboards" "css_element"
    # Confirm move to top works
    And "My Learning" "text" should appear before "Report dashboard 2" "text" in the ".embeddedshortname_dashboard_dashboards" "css_element"
    And I click on "Actions" "button" in the "Report dashboard 2" "table_row"
    And I click on "Move to top" "button" in the ".tui-dropdown__menu--open" "css_element"
    Then "Report dashboard 2" "text" should appear before "My Learning" "text" in the ".embeddedshortname_dashboard_dashboards" "css_element"
    # Confirm Delete link works
    And I click on "Actions" "button" in the "Report dashboard 2" "table_row"
    And I click on "Delete" "button" in the ".tui-dropdown__menu--open" "css_element"
    And I should see "Do you really want to remove dashboard"
    And I press "Cancel"
    # Add new dashboard with an edited sortorder
    And I press "Create dashboard"
    And I set the following fields to these values:
      | Name          | Dashboard 0 |
      | Display order | 0           |
    And I press "Create dashboard"
    Then dashboard "Dashboard 0" sortorder is "0"
    Then "Dashboard 0" "text" should appear before "My Learning" "text" in the ".embeddedshortname_dashboard_dashboards" "css_element"
