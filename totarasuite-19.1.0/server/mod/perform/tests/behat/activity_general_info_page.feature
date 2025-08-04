@totara @perform @mod_perform @javascript @vuejs
Feature: Create and update activity general info fields
  As an activity administrator
  I need to be able to update general activity fields.

  Background:
    Given I am on a totara site
    And the following "activities" exist in "mod_perform" plugin:
      | activity_name       | description                     | activity_type | create_track | create_section | activity_status |
      | My Test Activity #1 | My Test Activity #1 description | check-in      | true         | true           | Active          |
      | My Test Activity #2 | My Test Activity #2 description | check-in      | true         | true           | Draft           |

  Scenario: Populate the general info fields for a new activity
    Given I log in as "admin"
    And I navigate to the manage perform activities page
    And I click on "Add activity" "button"
    And I set the following fields to these values:
      | Title       | My Test Activity #3             |
      | Description | My Test Activity #3 description |
    # Set the 'Type' field to 'Feedback'
    And I set the field with css ".tui-modalContent .tui-formRow:last-child select" to "Feedback"

    When I click on "Create" "button"
    Then the "Content" tui tab should be active

    When I click on "Actions" "button" in the ".tui-performManageActivity__actions" "css_element"
    And I click on "Edit" "link" in the ".tui-dropdown__menu--open" "css_element"
    Then the following fields match these values:
      | Activity title | My Test Activity #3             |
      | Description    | My Test Activity #3 description |
    And I should see "Feedback" in the ".tui-select" "css_element"

    When I navigate to the manage perform activities page
    And I set the field "Sort by" to "Name"
    Then I should see the tui datatable contains:
      | Name                | Type     | Status |
      | My Test Activity #1 | Check-in | Active |
      | My Test Activity #2 | Check-in | Draft  |
      | My Test Activity #3 | Feedback | Draft  |


  Scenario: Activity can not be saved if title only contains whitespace
    Given I log in as "admin"
    And I navigate to the manage perform activities page
    And I click on "Add activity" "button"
    And I set the field "Title" to "  "
    # On this page we have also the filter with name Type so falling back to CSS selector
    And I set the field with css ".tui-modalContent select[name=type]" to "Feedback"
    And I click on "Create" "button"
    Then I should see "Required"

  Scenario: Edit the general info fields for an existing activity
    Given I log in as "admin"
    And I navigate to the manage perform activities page

    When I click on "My Test Activity #2" "link"
    Then the "Content" tui tab should be active

    When I click on "Actions" "button" in the ".tui-performManageActivity__actions" "css_element"
    And I click on "Edit" "link" in the ".tui-dropdown__menu--open" "css_element"
    Then the following fields match these values:
      | Activity title | My Test Activity #2             |
      | Description    | My Test Activity #2 description |
      | Type           | Check-in                        |

    When I set the following fields to these values:
      | Activity title | My Test Activity #2             |
      | Description    | My Test Activity #2 description |
      | Type           | Feedback                        |
    And I click on "Save" "button"
    And I navigate to the manage perform activities page
    And I set the field "Sort by" to "Name"
    Then I should see the tui datatable contains:
      | Name                | Type     | Status |
      | My Test Activity #1 | Check-in | Active |
      | My Test Activity #2 | Feedback | Draft  |

  Scenario: View and edit attribution and visibility settings
    Given I log in as "admin"
    And I navigate to the manage perform activities page

    When I click on "My Test Activity #2" "link"
    Then the "Content" tui tab should be active

    When I click on "Visibility and closure" "link"
    Then the "Anonymise responses" tui form row toggle switch should be "off"

    When I toggle the "Anonymise responses" tui form row toggle switch
    And I reload the page
    And I click on "Visibility and closure" "link"
    Then the "Anonymise responses" tui form row toggle switch should be "on"

    When I toggle the "Anonymise responses" tui form row toggle switch
    And I reload the page
    And I click on "Visibility and closure" "link"
    Then the "Anonymise responses" tui form row toggle switch should be "off"

  Scenario: Edit the general info fields can not be saved if title field only contains whitespace
    Given I log in as "admin"
    And I navigate to the manage perform activities page

    When I click on "My Test Activity #1" "link"
    And I click on "Actions" "button" in the ".tui-performManageActivity__actions" "css_element"
    And I click on "Edit" "link" in the ".tui-dropdown__menu--open" "css_element"

    Then the following fields match these values:
      | Activity title | My Test Activity #1             |
      | Description    | My Test Activity #1 description |

    When I set the field "Activity title" to "  "
    Then I click on "Save" "button"
    And I should see "name" form field has the tui validation error "Required"
    Then I click on "Cancel" "button"

  Scenario: Type and attribution settings are read only when activity status is active
    Given I log in as "admin"
    And I navigate to the manage perform activities page

    When I click on "My Test Activity #1" "link"
    And I click on "Actions" "button" in the ".tui-performManageActivity__actions" "css_element"
    And I click on "Edit" "link" in the ".tui-dropdown__menu--open" "css_element"
    Then I should see "Check-in" in the ".tui-performPASettingEditDetails__type-static" "css_element"
    # the drop-down should not exist for active activity
    And ".tui-performManageActivityGeneralInfo .tui-select" "css_element" should not exist

    Then I click on "Cancel" "button"
    And I click on "Visibility and closure" "link"
    And the "Anonymise responses" display only tui form row should contain "Disabled"

  Scenario: Click cancel button will revert to last saved changes
    Given I log in as "admin"
    And I navigate to the manage perform activities page
    And I click on "Add activity" "button"
    And I set the following fields to these values:
      | Title       | My Test Activity #3             |
      | Description | My Test Activity #3 description |
    # Set the 'Type' field to 'Feedback'
    And I set the field with css ".tui-modalContent .tui-formRow:last-child select" to "Feedback"

    When I click on "Create" "button"
    Then the "Content" tui tab should be active

    When I click on "Actions" "button" in the ".tui-performManageActivity__actions" "css_element"
    And I click on "Edit" "link" in the ".tui-dropdown__menu--open" "css_element"

    And I set the following fields to these values:
      | Activity title | My Test Activity |
      | Description    | My Test Activity |
      | Type           | Check-in         |
    And I click on "Cancel" "button" in the ".tui-modalContent__footer-buttons" "css_element"
    And I click on "Actions" "button" in the ".tui-performManageActivity__actions" "css_element"
    And I click on "Edit" "link" in the ".tui-dropdown__menu--open" "css_element"
    Then the following fields match these values:
      | Activity title | My Test Activity #3             |
      | Description    | My Test Activity #3 description |
      | Type           | Feedback                        |