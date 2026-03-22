@core @core_user @javascript
Feature: Edit own language based on capability
  Background:
    Given I am on a totara site
    And the following "users" exist:
      | username | firstname | lastname | email             |
      | user1    | User      | One      | user1@example.com |
      | user2    | User      | Two      | user2@example.com |
      | user3    | User      | Three    | user3@example.com |
      | user4    | User      | Four     | user4@example.com |
      | user5    | Manager   | Manager  | user5@example.com |
      | user6    | Manager   | Manager2 | user6@example.com |
    And the following "roles" exist:
      | name      | shortname | archetype |
      | with_both | with_both |           |
      | only_prof | only_prof |           |
      | only_lang | only_lang |           |
      | neither   | neither   |           |
    And the following "permission overrides" exist:
      | capability                  | permission | role      | contextlevel | reference |
      | moodle/user:editownlanguage | Prevent    | user      | System       |           |
      | moodle/user:editownprofile  | Prevent    | user      | System       |           |
      | moodle/user:editownprofile  | Allow      | with_both | System       |           |
      | moodle/user:editownlanguage | Allow      | with_both | System       |           |
      | moodle/user:editownprofile  | Allow      | only_prof | System       |           |
      | moodle/user:editownlanguage | Allow      | only_lang | System       |           |
    And the following "system role assigns" exist:
      | user  | role      |
      | user1 | with_both |
      | user2 | only_prof |
      | user3 | only_lang |
      | user4 | neither   |
      | user5 | manager   |
      | user6 | manager   |
      | user6 | only_prof |


  Scenario Outline: The language preferences link is available with the correct capability
    Given I log in as "<user>"
    When I navigate to the user preference page for "<target>"
    Then I should see "Preferred language"
    When I follow "Preferred language"
    Then "Preferred language" "field" should exist
    When I press "Save changes"
    Then I should see "Preferences"

    Examples:
      | user  | target |
      | user1 | user1  |
      | user3 | user3  |
      | user5 | user5  |
      | user5 | user1  |
      | user6 | user1  |

  Scenario Outline: A user cannot edit their own language without the edit own language capability
    Given I log in as "<user>"
    When I follow "Preferences" in the user menu
    Then I should not see "Preferred language"

    Examples:
      | user  |
      | user2 |
      | user4 |
