@totara @perform @performelement_linked_review @totara_competency @javascript
Feature: Selecting competencies linked to a performance review

  Background:
    Given the following "users" exist:
      | username | firstname | lastname | email             |
      | user1    | User      | One      | user1@example.com |
      | user2    | User      | Two      | user2@example.com |
      | user3    | User      | Three    | user3@example.com |
    And the following job assignments exist:
      | user  | manager |
      | user1 | user2   |
      | user2 | user3   |

    And a competency scale called "scale1" exists with the following values:
      | name                   | description                            | idnumber    | proficient | default | sortorder |
      | Super Competent        | <strong>Is great at doing it.</strong> | super       | 1          | 0       | 1         |
      | Just Barely Competent  | Is okay at doing it.                   | barely      | 0          | 0       | 2         |
      | Incredibly Incompetent | <em>Is rubbish at doing it.</em>       | incompetent | 0          | 1       | 3         |
    And the following "competency" frameworks exist:
      | fullname             | idnumber | description                | scale  |
      | Competency Framework | fw       | Framework for competencies | scale1 |
    And the following "competency" hierarchy exists:
      | framework | fullname    | idnumber    | description                                  | assignavailability |
      | fw        | Math        | math        | Adding, subtracting, and other calculations. | any                |
      | fw        | Typing slow | typing_slow | The ability to type <em>slow.</em>           | any                |
      | fw        | Typing fast | typing_fast | The ability to type <em>fast.</em>           | any                |
    And the following "assignments" exist in "totara_competency" plugin:
      | competency  | user_group_type | user_group |
      | math        | user            | user1      |
      | typing_slow | user            | user1      |
      | typing_fast | user            | user1      |
    And I run the scheduled task "totara_competency\task\expand_assignments_task"

    And the following "activity with section and review element" exist in "performelement_linked_review" plugin:
      | activity_name | section_title | element_title     | content_type      | selection_relationships | content_type_settings                                       |
      | activity1     | section1      | Competency review | totara_competency | perform_peer            | {"enable_rating":true,"rating_relationship":"perform_peer"} |
    And the following "section relationships" exist in "mod_perform" plugin:
      | section_name | relationship | can_view | can_answer |
      | section1     | subject      | yes      | yes        |
      | section1     | manager      | yes      | yes        |
      | section1     | peer         | yes      | no         |
    And the following "participants in section" exist in "performelement_linked_review" plugin:
      | section  | subject_user | user  | relationship | can_answer |
      | section1 | user1        | user1 | subject      | true       |
      | section1 | user1        | user2 | manager      | true       |
      | section1 | user1        | user3 | perform_peer | false      |

  Scenario: Selecting participant can select competencies and give a rating
    When I log in as "user3"
    And I navigate to the outstanding perform activities list page
    And I click on "As Peer" "link_or_button"
    And I click on "activity1" "link"
    And I click on "Add competencies" "link_or_button"
    Then I should not see "No items to display" in the tui modal
    And I should see "Items selected: 0" in the tui modal
    Then I should see the tui datatable contains:
      | Competency  |
      | Math        |
      | Typing fast |
      | Typing slow |

    When I toggle the adder picker entry with "Typing fast" for "Competency"
    And I toggle the adder picker entry with "Typing slow" for "Competency"
    And I should see "Items selected: 2" in the tui modal
    And I click on "Add" "button" in the ".tui-modal" "css_element"
    And I click on "Confirm selection" "button"
    Then I should see "The ability to type fast"

    # Check that we can remove
    And "Remove" "button" should exist in the ".tui-linkedReviewParticipantForm__item:nth-child(1) .tui-linkedReviewParticipantForm__item-cardActions" "css_element"
    And I click on "Remove" "button" in the ".tui-linkedReviewParticipantForm__item:nth-child(1) .tui-linkedReviewParticipantForm__item-cardActions" "css_element"
    And I should see "This will remove the competency and your responses. Are you sure you would like to remove this competency?"
    And I confirm the tui confirmation modal
    And I should see "Successfully removed competency." in the tui success notification toast
    Then I should not see "The ability to type fast"

    And "Remove" "button" should exist in the ".tui-linkedReviewParticipantForm__item:nth-child(1) .tui-linkedReviewParticipantForm__item-cardActions" "css_element"

    When I set the following fields to these values:
      | scaleValue | Just Barely Competent |
    And I click on "Submit rating" "button"
    Then I should see "You've rated User One as \"Just Barely Competent\"" in the ".tui-modal" "css_element"
    And I should see "The competency rating will be updated. After submitting this rating, you cannot remove the competency or change its rating within this activity." in the ".tui-modal" "css_element"
    When I confirm the tui confirmation modal
    Then I should see "Rating saved" in the tui success notification toast
    And I should see "Final rating to be submitted for the competency" in the 1st selected content item for the "Competency review" linked review element
    And I should see "Rating by: User Three (Peer)" in the 1st selected content item for the "Competency review" linked review element
    And I should see "##today##j F Y##" in the 1st selected content item for the "Competency review" linked review element
    And I should see "Final rating: Just Barely Competent" in the 1st selected content item for the "Competency review" linked review element

    # Check the remove button is gone after rating
    And ".tui-linkedReviewParticipantForm__item:nth-child(1) .tui-linkedReviewParticipantForm__item-cardActions" "css_element" should not exist

    # Can continue to add
    And I click on "Add competencies" "link_or_button"
    Then I should not see "No items to display" in the tui modal
    Then I should see the tui datatable contains:
      | Competency  |
      | Math        |
      | Typing fast |
      | Typing slow |
    Then I should see the following disabled adder picker entries:
      | Competency  |
      | Typing slow |
    And I toggle the adder picker entry with "Math" for "Competency"
    And I click on "Add" "button" in the ".tui-modal" "css_element"
    And I click on "Confirm selection" "button"
    Then I should see "Math"
    And I should see "Typing slow"
    When I reload the page
    Then I should see "Math"
    And I should see "Typing slow"