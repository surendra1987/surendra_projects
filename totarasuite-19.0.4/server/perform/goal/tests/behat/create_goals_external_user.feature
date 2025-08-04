@totara @perform @mod_perform @perform_element @perform_goal @javascript @vuejs
Feature: Create goal element in perform activity for external users

  Background:
    Given the following "users" exist:
      | username     | firstname | lastname | email                   |
      | user_subject | User      | Subject  | usersubject@example.com |
    And the following "activities" exist in "mod_perform" plugin:
      | activity_name  | activity_type | create_section | create_track | activity_status | anonymous_responses |
      | First Activity | appraisal     | false          | true         | Draft           | false               |
    And the following "activity settings" exist in "mod_perform" plugin:
      | activity_name  | multisection |
      | First Activity | no           |
    And the following "activity sections" exist in "mod_perform" plugin:
      | activity_name  | section_name |
      | First Activity | section1     |
    And the following "section relationships" exist in "mod_perform" plugin:
      | section_name | relationship        | can_view | can_answer |
      | section1     | subject             | yes      | yes        |
      | section1     | External respondent | yes      | yes        |

  Scenario: external user cannot create goal for subject
    Given I log in as "admin"
    And I navigate to the edit perform activities page for activity "First Activity"
    And I click on "Edit content elements" "link_or_button"
    And I add a "Create goal" activity content element
    When I set the following fields to these values:
      | rawTitle | Perform goal |
    And I save the activity content element
    And I navigate to the edit perform activities page for activity "First Activity"
    And I click on "Assignments" "link"
    And I click on "Assign users" "button"
    And I click on "Individual" "button" in the ".tui-performPASettingAssignment__heading-add .tui-dropdown__menu" "css_element"
    And I toggle the adder picker entry with "User Subject" for "Users"
    And I save my selections and close the adder
    And I press "Activate"
    And I confirm the tui confirmation modal
    And I log out
    And I trigger cron

    # Test the create perform goal element as subject
    And I log in as "user_subject"
    And I navigate to the outstanding perform activities list page
    And I click on "Select participants" "link" in the ".tui-actionCard" "css_element"
    And I click on "Add" "button"
    And I set the following fields to these values:
      | External respondent 1's name          | External Respondent  |
      | External respondent 1's email address | example@example.com  |
      | External respondent 2's name          | External Respondent2 |
      | External respondent 2's email address | example2@example.com |
    And I click on "Save" "button"
    And I click on "Back to all performance activities" "link"
    And I log out

    And I navigate to the external participants form for user "External Respondent"
    Then I should see "You don't have permission to create goals for User Subject"
