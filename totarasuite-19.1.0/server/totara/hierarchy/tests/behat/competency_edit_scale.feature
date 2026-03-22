@totara @perform @totara_competency @javascript
Feature: Edit competency scales

  Background:
    Given I am on a totara site
    And the following "users" exist:
      | username  | firstname | lastname  |
      | user      | Manfred   | Koslowski |

  Scenario: Toggle setting for 'Least competent value means not started' for a used scale
    Given a competency scale called "Used scale" exists with the following values:
      | name                   | description             | idnumber     | proficient | default | sortorder |
      | Super Competent        | Is great at doing it.   | super        | 1          | 0       | 1         |
      | Just Barely Competent  | Is okay at doing it.    | barely       | 1          | 0       | 2         |
      | Incredibly Incompetent | Is rubbish at doing it. | incompetent  | 0          | 1       | 3         |
    And the following "competency" frameworks exist:
      | fullname                 | idnumber | scale   |
      | Competency Framework One | fw       | Used scale |
    And the following "competency" hierarchy exists:
      | framework | fullname   | idnumber | description                     |
      | fw        | Competency | comp1    | <strong>Competency One</strong> |
    And the following "assignments" exist in "totara_competency" plugin:
      | competency | user_group_type | user_group |
      | comp1      | user            | user       |
    And the following "manual pathways" exist in "totara_competency" plugin:
      | competency | roles  |
      | comp1      | self   |
    And the following "manual ratings" exist in "totara_competency" plugin:
      | competency | subject_user | rater_user | role   | scale_value  | comment             | date       |
      | comp1      | user         | user       | self   | barely       | My staff is alright | 2020-01-01 |
    And I run the scheduled task "totara_competency\task\expand_assignments_task"
    And I run the scheduled task "totara_competency\task\competency_aggregation_queue"

    When I log in as "admin"
    And I navigate to "Manage competencies" node in "Site administration > Competencies"
    Then I should see "Yes" in the "Used scale" "table_row"
    When I follow "Used scale"
    Then I should see "The least competent scale value is considered 'Not started' on the performance overview page."
    And I should see "Incredibly Incompetent (least competent)"

    # Setting is off by default
    And "input[name=leastcompetentscalenotstarted]:not(:checked)" "css_element" should exist
    When I click on "leastcompetentscalenotstarted" "checkbox"
    And I press "Save and apply changes"
    Then I should see "Confirm updates to scale values"
    When I press "Yes"
    Then "input[name=leastcompetentscalenotstarted]:checked" "css_element" should exist

    # Navigate away and back to make sure it was actually saved.
    When I navigate to "Manage competencies" node in "Site administration > Competencies"
    And I follow "Used scale"
    Then "input[name=leastcompetentscalenotstarted]:checked" "css_element" should exist

    # Deactivate again
    When I click on "leastcompetentscalenotstarted" "checkbox"
    And I press "Save and apply changes"
    Then I should see "Confirm updates to scale values"
    When I press "Yes"
    Then "input[name=leastcompetentscalenotstarted]:not(:checked)" "css_element" should exist

    # Navigate away and back to make sure it was actually saved.
    When I navigate to "Manage competencies" node in "Site administration > Competencies"
    And I follow "Used scale"
    Then "input[name=leastcompetentscalenotstarted]:not(:checked)" "css_element" should exist

  Scenario: Toggle setting for 'Least competent value means not started' for an unused scale
    Given a competency scale called "Unused scale" exists with the following values:
      | name                   | description             | idnumber     | proficient | default | sortorder |
      | Super Competent        | Is great at doing it.   | super        | 1          | 0       | 1         |
      | Just Barely Competent  | Is okay at doing it.    | barely       | 1          | 0       | 2         |
      | Incredibly Incompetent | Is rubbish at doing it. | incompetent  | 0          | 1       | 3         |

    When I log in as "admin"
    And I navigate to "Manage competencies" node in "Site administration > Competencies"
    Then I should see "No" in the "Unused scale" "table_row"
    When I follow "Unused scale"
    Then I should see "The least competent scale value is considered 'Not started' on the performance overview page."
    And I should see "Incredibly Incompetent (least competent)"

    # Setting is off by default
    And "input[name=leastcompetentscalenotstarted]:not(:checked)" "css_element" should exist
    When I click on "leastcompetentscalenotstarted" "checkbox"
    And I press "Save"
    Then "input[name=leastcompetentscalenotstarted]:checked" "css_element" should exist

    # Navigate away and back to make sure it was actually saved.
    When I navigate to "Manage competencies" node in "Site administration > Competencies"
    And I follow "Unused scale"
    Then "input[name=leastcompetentscalenotstarted]:checked" "css_element" should exist

    # Deactivate again
    When I click on "leastcompetentscalenotstarted" "checkbox"
    And I press "Save"
    Then "input[name=leastcompetentscalenotstarted]:not(:checked)" "css_element" should exist

    # Navigate away and back to make sure it was actually saved.
    When I navigate to "Manage competencies" node in "Site administration > Competencies"
    And I follow "Unused scale"
    Then "input[name=leastcompetentscalenotstarted]:not(:checked)" "css_element" should exist
