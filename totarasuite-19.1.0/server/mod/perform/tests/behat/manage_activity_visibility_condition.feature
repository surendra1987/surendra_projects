@totara @perform @mod_perform @javascript @vuejs
Feature: As an activity administrator, I need to be able to update visibility condition
  so that I can control when answers are displayed to users

  Background:
    Given I am on a totara site
    And the following "activities" exist in "mod_perform" plugin:
      | activity_name    | description                  | activity_type | create_track | create_section | activity_status |
      | My Test Activity | My Test Activity description | check-in      | true         | false          | Draft           |
    And the following "users" exist:
      | username | firstname | lastname | email             |
      | user1    | user      | 1        | user1@example.com |
      | user2    | user      | 2        | user2@example.com |
    And the following "cohorts" exist:
      | name | idnumber |
      | aud1 | aud1     |
    And the following "cohort members" exist:
      | user  | cohort |
      | user1 | aud1   |
      | user2 | aud1   |
    And the following "activity sections" exist in "mod_perform" plugin:
      | activity_name    | section_name |
      | My Test Activity | section 1    |
    And the following "section relationships" exist in "mod_perform" plugin:
      | section_name | relationship |
      | section 1    | subject      |
    And the following "section elements" exist in "mod_perform" plugin:
      | section_name | element_name |
      | section 1    | short_text   |

  Scenario: Change anonymous response setting should affect visibility control
    Given I log in as "admin"
    And I navigate to the manage perform activities page

    When I click on "My Test Activity" "link"
    And I click on "Visibility and closure" "link"
    Then ".tui-radioGroup" "css_element" should exist
    And the "Anonymise responses" tui form row toggle switch should be "off"
    # visibility condition should be read only and set to 'all participants' when anonymous response is enabled
    When I toggle the "Anonymise responses" tui form row toggle switch
    Then the "Show responses when the section is submitted" "radio" should be disabled
    And the "Show responses when the section is submitted and closed" "radio" should be disabled
    And I should see "Show responses when all participants have submitted and closed the activity"
    # If toggled back, the visibility condition selected will be “All responding participants”
    # regardless of what was selected before anonymise had been toggled on.
    When I toggle the "Anonymise responses" tui form row toggle switch
    Then ".tui-radioGroup" "css_element" should exist
    And the "Show responses when all participants have submitted and closed the activity" radio button is selected
    Then I should see "Activity saved" in the tui success notification toast and close it

  Scenario: Show warning message on saving changes when condition is not none and automatic closure is disabled
    Given I log in as "admin"
    And I navigate to the manage perform activities page

    When I click on "My Test Activity" "link"
    Then ".tui-notificationBanner" "css_element" should not exist
    # Situation: automatic closure disabled & visibility condition is none
    When I click on "Visibility and closure" "link"
    Then the "Show responses when the section is submitted" radio button is selected
    And ".tui-notificationBanner" "css_element" should not exist
    # Situation: automatic closure disabled & visibility condition is not none
    When I click on the "Show responses when the section is submitted and closed" tui radio
    Then I should see "This condition cannot be met by participants without manual intervention, because automatic closure is currently disabled. Enable it as a closure setting." in the tui warning notification banner
    When I reload the page
    And I click on "Visibility and closure" "link"
    And I should see "Closure is set as a condition for response visibility, but this cannot be met by participants without manual intervention while automatic closure is disabled. Enable it, or change the Response visibility condition to \"Show responses when the section is submitted\"." in the ".tui-performPASettingClosure__warning" "css_element"

    When I click on the "Close on submission, responses will not be editable" tui toggle button
    Then I should not see "Closure is set as a condition for response visibility, but this cannot be met by participants without manual intervention while automatic closure is disabled. Enable it, or change the Response visibility condition to \"Show responses when the section is submitted\"."
    And I click on "Visibility and closure" "link"
    Then I should not see "This condition cannot be met by participants without manual intervention, because automatic closure is currently disabled. Enable it as a closure setting."

  Scenario: Visibility control should be editable after activation if anonymous response is not enabled
    Given I log in as "admin"
    And I navigate to the manage perform activities page
    When I click on "My Test Activity" "link"
    And I click on "Visibility and closure" "link"
    Then the "Show responses when the section is submitted" radio button is selected

    When I click on the "Show responses when the section is submitted and closed" tui radio
    And I click on "Assignments" "link"
    And I click on "Assign users" "button"
    And I click on "Audience" "button" in the ".tui-performPASettingAssignment__heading-add .tui-dropdown__menu" "css_element"
    And I toggle the adder picker entry with "aud1" for "Audience name"
    And I save my selections and close the adder
    And I reload the page
    And I click on "Activate" "button" in the ".tui-performManageActivity__actions" "css_element"
    And I confirm the tui confirmation modal
    Then I should see "was successfully activated." in the tui success notification toast

    When I click on "Visibility and closure" "link"
    Then ".tui-radioGroup" "css_element" should exist
    And the "Show responses when the section is submitted and closed" radio button is selected

  Scenario: Visibility control should not be editable after activation if anonymous response is enabled
    Given I log in as "admin"
    And I navigate to the manage perform activities page
    When I click on "My Test Activity" "link"
    And I click on "Visibility and closure" "link"
    Then the "Show responses when the section is submitted" radio button is selected

    When I toggle the "Anonymise responses" tui form row toggle switch
    And I click on "Assignments" "link"
    And I click on "Assign users" "button"
    And I click on "Audience" "button" in the ".tui-performPASettingAssignment__heading-add .tui-dropdown__menu" "css_element"
    And I toggle the adder picker entry with "aud1" for "Audience name"
    And I save my selections and close the adder
    And I reload the page
    And I click on "Activate" "button" in the ".tui-performManageActivity__actions" "css_element"
    And I confirm the tui confirmation modal
    Then I should see "was successfully activated." in the tui success notification toast

    When I click on "Visibility and closure" "link"
    Then the "Show responses when the section is submitted" "radio" should be disabled
    And the "Show responses when the section is submitted and closed" "radio" should be disabled
    And the "Show responses when all participants have submitted and closed the activity" radio button is selected