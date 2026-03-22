@totara @perform @mod_perform @javascript @vuejs
Feature: Assign manual participant selector roles
  As an activity administrator
  I need to be able to assign manual participant selector roles in individual perform activities

  Background:
    Given the following "users" exist:
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
    And the following "activities" exist in "mod_perform" plugin:
      | activity_name | activity_type | activity_status | create_track | create_section |
      | AAA           | check-in      | Draft           | false        | false          |
    And the following "activity sections" exist in "mod_perform" plugin:
      | activity_name | section_name |
      | AAA           | section 1    |
    And the following "section relationships" exist in "mod_perform" plugin:
      | section_name | relationship |
      | section 1    | subject      |
      | section 1    | appraiser    |
    And the following "section elements" exist in "mod_perform" plugin:
      | section_name | element_name |
      | section 1    | short_text   |
    And the following "activity tracks" exist in "mod_perform" plugin:
      | activity_name | track_description |
      | AAA           | track 1           |
    And the following "track assignments" exist in "mod_perform" plugin:
      | track_description | assignment_type | assignment_name |
      | track 1           | cohort          | aud1            |

  Scenario: Select manual participant roles.
    When I log in as "admin"
    And I navigate to the manage perform activities page
    Then I should see the tui datatable contains:
      | Name | Type     | Status |
      | AAA  | Check-in | Draft  |

    When I follow "AAA"
    And I click on "Assignments" "link"
    Then I should see "Selection of participants"
    And I should see "Participants for each relationship below must be manually chosen by the selected role."
    And the field "Peers" matches value "1"
    And the field "Mentor" matches value "1"
    And the field "Reviewer" matches value "1"
    And the field "External respondent" matches value "1"

    When I set the field "Peers" to "6"
    And I set the field "Mentor" to "7"
    And I set the field "Reviewer" to "8"
    And I set the field "External respondent" to "9"

    And I navigate to the manage perform activities page
    And I follow "AAA"
    And I click on "Assignments" "link"
    Then the field "Peers" matches value "6"
    And the field "Mentor" matches value "7"
    And the field "Reviewer" matches value "8"
    And the field "External respondent" matches value "9"

    When I click on "Activate" "button"
    And I confirm the tui confirmation modal
    And I navigate to the manage perform activities page
    Then I should see the tui datatable contains:
      | Name | Type     | Status |
      | AAA  | Check-in | Active |

    When I follow "AAA"
    And I click on "Assignments" "link"
    Then "//select[@aria-label='Peer']" "xpath_element" should not exist
    And "//select[@aria-label='Mentor']" "xpath_element" should not exist
    And "//select[@aria-label='Reviewer']" "xpath_element" should not exist
    And "//select[@aria-label='External respondent']" "xpath_element" should not exist
