@core @core_my @javascript
Feature: Perform overview page for competencies

  Background:
    Given I am on a totara site
    And the following "users" exist:
      | username | firstname | lastname | email                   |
      | alice    | Alice     | Smith    | alice.smith@example.com |
    And a competency scale called "scale" exists with the following values:
      | name          | description               | idnumber    | proficient | default | sortorder |
      | Over achieved | over achieved description | over        | 1          | 0       | 1         |
      | Achieved      | achieved description      | achieved    | 1          | 0       | 2         |
      | Progressing   | progressing description   | progressing | 0          | 1       | 3         |
      | Started       | started description       | started     | 0          | 0       | 4         |
    And the following "competency" frameworks exist:
      | fullname               | idnumber | description                        | scale |
      | Competency Framework 1 | CF1      | Competency Framework 1 description | scale |
    And the following "competency" hierarchy exists:
      | framework | fullname     | idnumber | description            | assignavailability |
      | CF1       | Competency 1 | C1       | Competency description | any                |
      | CF1       | Competency 2 | C2       | Competency description | any                |
      | CF1       | Competency 3 | C3       | Competency description | any                |
    And the following "manual pathways" exist in "totara_competency" plugin:
      | competency | roles   |
      | C1         | self    |
      | C1         | manager |
      | C2         | self    |
      | C2         | manager |
      | C3         | self    |
      | C3         | manager |

  Scenario: Competency overview when no competencies are assigned
    Given I log in as "alice"
    And I navigate to my overview
    Then ".tui-overviewCompetenciesSection .tui-myCompetenciesOverviewSection__chartArea" "css_element" should not be visible
    And I should see "No competencies are currently assigned for this period" in the ".tui-overviewCompetenciesSection__noCompetencies" "css_element"
    And "Self-assign competencies" "link" in the ".tui-overviewCompetenciesSection" "css_element" should be visible

  Scenario: Another users competency overview when no competencies are assigned
    Given I log in as "admin"
    And I navigate to my overview for user "alice"
    Then ".tui-overviewCompetenciesSection .tui-myCompetenciesOverviewSection__chartArea" "css_element" should not be visible
    And I should see "No competencies are currently assigned for this period" in the ".tui-overviewCompetenciesSection__noCompetencies" "css_element"
    And "Self-assign competencies" "link" in the ".tui-overviewCompetenciesSection" "css_element" should not be visible

  Scenario: Competency overview when 1 manually assigned to self
    Given I log in as "alice"
    And I navigate to my overview
    And I navigate to the competency self assignment page
    And I click on ".tui-checkbox__label" "css_element" in the tui datatable row with "Competency 1" "Competency"
    Then I click on "Assign competencies" "button"
    And I confirm the tui confirmation modal
    And I run the scheduled task "totara_competency\task\expand_assignments_task"
    And I run the scheduled task "totara_competency\task\competency_aggregation_queue"
    And I navigate to my overview
    And ".tui-overviewCompetenciesSection .tui-myPerformOverviewSection__chartArea" "css_element" should be visible
    And I should see "1" in the competencies overview total count
    And ".tui-overviewCount__dueContent" "css_element" should not exist in the ".tui-overviewCompetenciesSection" "css_element"

    # Not started content
    And I should see the "not started" competencies overview section
    And I should see "1" in the "not started" competencies overview section
    And I should see the "assigned" date for competency "Competency 1" in row "1" of the "not started" competencies overview section
    And I should see "Not started" in the ".tui-overviewCompetenciesSection__content-notStarted .tui-myPerformOverviewItemAchievementLevel" "css_element"
    And I should see "Self-assigned" in the ".tui-overviewCompetenciesSection__content-notStarted .tui-myPerformOverviewItemAssignmentType" "css_element"

  Scenario: Competency overview when 1 manually assigned by manager
    Given I log in as "alice"
    And the following "assignments" exist in "totara_competency" plugin:
      | competency | user_group_type | user_group |
      | C1         | user            | alice      |
    And I run the scheduled task "totara_competency\task\expand_assignments_task"
    And I run the scheduled task "totara_competency\task\competency_aggregation_queue"
    And I navigate to my overview
    And ".tui-overviewCompetenciesSection .tui-myPerformOverviewSection__chartArea" "css_element" should be visible
    And I should see "1" in the competencies overview total count
    And ".tui-overviewCount__dueContent" "css_element" should not exist in the ".tui-overviewCompetenciesSection" "css_element"

    # Not started content
    And I should see the "not started" competencies overview section
    And I should see "1" in the "not started" competencies overview section
    And I should see the "assigned" date for competency "Competency 1" in row "1" of the "not started" competencies overview section
    And I should see "Not started" in the ".tui-overviewCompetenciesSection__content-notStarted .tui-myPerformOverviewItemAchievementLevel" "css_element"
    And I should see "Directly assigned" in the ".tui-overviewCompetenciesSection__content-notStarted .tui-myPerformOverviewItemAssignmentType" "css_element"

  Scenario: Competency overview when 1 manually assigned through an audience "Developer"
    Given I log in as "alice"
    And the following "cohorts" exist:
      | name      | idnumber |
      | Developer | dev      |
    And the following "cohort members" exist:
      | user  | cohort |
      | alice | dev    |
    And the following "assignments" exist in "totara_competency" plugin:
      | competency | user_group_type | user_group |
      | C1         | cohort          | dev        |
    And I run the scheduled task "totara_competency\task\expand_assignments_task"
    And I run the scheduled task "totara_competency\task\competency_aggregation_queue"
    And I navigate to my overview
    And ".tui-overviewCompetenciesSection .tui-myPerformOverviewSection__chartArea" "css_element" should be visible
    And I should see "1" in the competencies overview total count
    And ".tui-overviewCount__dueContent" "css_element" should not exist in the ".tui-overviewCompetenciesSection" "css_element"

    # Not started content
    And I should see the "not started" competencies overview section
    And I should see "1" in the "not started" competencies overview section
    And I should see the "assigned" date for competency "Competency 1" in row "1" of the "not started" competencies overview section
    And I should see "Not started" in the ".tui-overviewCompetenciesSection__content-notStarted .tui-myPerformOverviewItemAchievementLevel" "css_element"
    And I should see "Developer" in the ".tui-overviewCompetenciesSection__content-notStarted .tui-myPerformOverviewItemAssignmentType" "css_element"

  Scenario: Competency overview when 1 manually assigned through an audience "Developer" over 2 years ago
    Given I log in as "alice"
    And the following "cohorts" exist:
      | name      | idnumber |
      | Developer | dev      |
    And the following "cohort members" exist:
      | user  | cohort |
      | alice | dev    |
    And I create an assignment for competency "Competency 1" through cohort "Developer" and user "alice" and backdate it to "-800 days"
    And I run the scheduled task "totara_competency\task\expand_assignments_task"
    And I run the scheduled task "totara_competency\task\competency_aggregation_queue"
    And I navigate to my overview
    Then ".tui-overviewCompetenciesSection .tui-myCompetenciesOverviewSection__chartArea" "css_element" should not be visible
    And I should see "No competencies are currently assigned for this period" in the ".tui-overviewCompetenciesSection__noCompetencies" "css_element"
    And "Self-assign competencies" "link" in the ".tui-overviewCompetenciesSection" "css_element" should be visible

  Scenario: Competency overview when 3 manually assigned to self
    Given I log in as "alice"
    And I navigate to my overview
    And I navigate to the competency self assignment page
    And I click on ".tui-checkbox__label" "css_element" in the tui datatable row with "Competency 1" "Competency"
    Then I click on "Assign competencies" "button"
    And I confirm the tui confirmation modal
    And I run the scheduled task "totara_competency\task\expand_assignments_task"
    And I run the scheduled task "totara_competency\task\competency_aggregation_queue"
    And I navigate to the competency self assignment page
    And I click on ".tui-checkbox__label" "css_element" in the tui datatable row with "Competency 2" "Competency"
    Then I click on "Assign competencies" "button"
    And I confirm the tui confirmation modal
    And I run the scheduled task "totara_competency\task\expand_assignments_task"
    And I run the scheduled task "totara_competency\task\competency_aggregation_queue"
    And I navigate to the competency self assignment page
    And I click on ".tui-checkbox__label" "css_element" in the tui datatable row with "Competency 3" "Competency"
    Then I click on "Assign competencies" "button"
    And I confirm the tui confirmation modal
    And I run the scheduled task "totara_competency\task\expand_assignments_task"
    And I run the scheduled task "totara_competency\task\competency_aggregation_queue"

    And I navigate to my overview
    And ".tui-overviewCompetenciesSection .tui-myPerformOverviewSection__chartArea" "css_element" should be visible
    And I should see "3" in the competencies overview total count
    And ".tui-overviewCount__dueContent" "css_element" should not exist in the ".tui-overviewCompetenciesSection" "css_element"

    # Not started content
    And I should see the "not started" competencies overview section
    And I should see "3" in the "not started" competencies overview section
    And I should see the "assigned" date for competency "Competency 3" in row "1" of the "not started" competencies overview section
    And I should see "Not started" in the ".tui-overviewCompetenciesSection__content-notStarted .tui-myPerformOverviewItemAchievementLevel" "css_element"
    And I should see "Self-assigned" in the ".tui-overviewCompetenciesSection__content-notStarted .tui-myPerformOverviewItemAssignmentType" "css_element"
    And I should see the "assigned" date for competency "Competency 2" in row "1" of the "not started" competencies overview section
    And I should see "Not started" in the ".tui-overviewCompetenciesSection__content-notStarted .tui-dataTableRow:nth-of-type(2) .tui-myPerformOverviewItemAchievementLevel" "css_element"
    And I should see "Self-assigned" in the ".tui-overviewCompetenciesSection__content-notStarted .tui-dataTableRow:nth-of-type(2) .tui-myPerformOverviewItemAssignmentType" "css_element"

  Scenario: Competency overview when 3 manually assigned to self and 1 progressing
    Given I log in as "alice"
    And I navigate to my overview
    And I navigate to the competency self assignment page
    And I click on ".tui-checkbox__label" "css_element" in the tui datatable row with "Competency 1" "Competency"
    Then I click on "Assign competencies" "button"
    And I confirm the tui confirmation modal
    And I run the scheduled task "totara_competency\task\expand_assignments_task"
    And I run the scheduled task "totara_competency\task\competency_aggregation_queue"
    And I navigate to the competency self assignment page
    And I click on ".tui-checkbox__label" "css_element" in the tui datatable row with "Competency 2" "Competency"
    Then I click on "Assign competencies" "button"
    And I confirm the tui confirmation modal
    And I navigate to the competency self assignment page
    And I click on ".tui-checkbox__label" "css_element" in the tui datatable row with "Competency 3" "Competency"
    Then I click on "Assign competencies" "button"
    And I confirm the tui confirmation modal
    And I run the scheduled task "totara_competency\task\expand_assignments_task"
    And I run the scheduled task "totara_competency\task\competency_aggregation_queue"
    And I navigate to my overview

    # Rate competency 2 as progressed
    And I click on "Competency 2" "link"
    And I click on "Add rating" "link"
    And I click on "Rate" "button"
    And I click on the "Progressing" tui radio
    And I click on "Done" "button"
    And I click on "Submit" "button"
    And I confirm the tui confirmation modal
    And I run the scheduled task "totara_competency\task\expand_assignments_task"
    And I run the scheduled task "totara_competency\task\competency_aggregation_queue"
    And I navigate to my overview

    # Progressed content
    And I should see the "progressed" competencies overview section
    And I should see "1" in the "progressed" competencies overview section
    And I should see the "updated" date for competency "Competency 2" in row "1" of the "progressed" competencies overview section
    And I should see "Progressing" in the ".tui-overviewCompetenciesSection__content-progressed .tui-myPerformOverviewItemAchievementLevel" "css_element"
    And I should see "Self-assigned" in the ".tui-overviewCompetenciesSection__content-notStarted .tui-myPerformOverviewItemAssignmentType" "css_element"
    And I click on ".tui-myPerformOverviewItemUpdated" "css_element"
    Then I should see "Competency achievement level was updated to 'Progressing'" in the ".tui-myPerformOverviewItemUpdated" "css_element"

    # Not started content
    And I should see the "not started" competencies overview section
    And I should see "2" in the "not started" competencies overview section
    And I should see the "assigned" date for competency "Competency 2" in row "1" of the "not started" competencies overview section
    And I should see "Not started" in the ".tui-overviewCompetenciesSection__content-notStarted .tui-myPerformOverviewItemAchievementLevel" "css_element"
    And I should see "Self-assigned" in the ".tui-overviewCompetenciesSection__content-notStarted .tui-myPerformOverviewItemAssignmentType" "css_element"
    And I should see the "assigned" date for competency "Competency 1" in row "1" of the "not started" competencies overview section
    And I should see "Not started" in the ".tui-overviewCompetenciesSection__content-notStarted .tui-dataTableRow:nth-of-type(2) .tui-myPerformOverviewItemAchievementLevel" "css_element"
    And I should see "Self-assigned" in the ".tui-overviewCompetenciesSection__content-notStarted .tui-dataTableRow:nth-of-type(2) .tui-myPerformOverviewItemAssignmentType" "css_element"

  Scenario: Competency overview when started on assignment
    Given I log in as "alice"
    And the following "onactivate" exist in "totara_criteria" plugin:
      | idnumber   | competency |
      | onactivate | C1         |
    And the following "criteria group pathways" exist in "totara_competency" plugin:
      | competency | scale_value | criteria   |
      | C1         | started     | onactivate |
    And I navigate to my overview
    And I navigate to the competency self assignment page
    And I click on ".tui-checkbox__label" "css_element" in the tui datatable row with "Competency 1" "Competency"
    Then I click on "Assign competencies" "button"
    And I confirm the tui confirmation modal
    And I run the scheduled task "totara_competency\task\expand_assignments_task"
    And I run the scheduled task "totara_competency\task\competency_aggregation_queue"
    And I navigate to my overview

    # Progressed content
    And I should see the "progressed" competencies overview section
    And I should see "1" in the "progressed" competencies overview section
    And I should see the "updated" date for competency "Competency 1" in row "1" of the "progressed" competencies overview section
    And I should see "Started" in the ".tui-overviewCompetenciesSection__content-progressed .tui-myPerformOverviewItemAchievementLevel" "css_element"
    And I should see "Self-assigned" in the ".tui-myPerformOverviewItemAssignmentType" "css_element"

    And I click on ".tui-myPerformOverviewItemUpdated" "css_element"
    Then I should see "Competency achievement level was updated to 'Started'" in the ".tui-myPerformOverviewItemUpdated" "css_element"

  Scenario: Competency overview when over achieved on assignment
    Given I log in as "alice"
    And the following "onactivate" exist in "totara_criteria" plugin:
      | idnumber   | competency |
      | onactivate | C1         |
    And the following "criteria group pathways" exist in "totara_competency" plugin:
      | competency | scale_value | criteria   |
      | C1         | over        | onactivate |
    And I navigate to my overview
    And I navigate to the competency self assignment page
    And I click on ".tui-checkbox__label" "css_element" in the tui datatable row with "Competency 1" "Competency"
    Then I click on "Assign competencies" "button"
    And I confirm the tui confirmation modal
    And I run the scheduled task "totara_competency\task\expand_assignments_task"
    And I run the scheduled task "totara_competency\task\competency_aggregation_queue"
    And I navigate to my overview

    # Achieved content
    And I should see the "achieved" competencies overview section
    And I should see "1" in the "achieved" competencies overview section header count
    And I should see the "achieved" date for competency "Competency 1" in row "1" of the "achieved" competencies overview section
    And I should see "Over achieved" in the ".tui-overviewCompetenciesSection__content-achieved .tui-myPerformOverviewItemAchievementLevel" "css_element"
    And I should see "Self-assigned" in the ".tui-myPerformOverviewItemAssignmentType" "css_element"

  Scenario: Competency overview when started on assignment and minimum proficiency is started
    Given I log in as "alice"
    And a competency scale called "scale2" exists with the following values:
      | name          | description               | idnumber     | proficient | default | sortorder |
      | Over achieved | over achieved description | over2        | 1          | 0       | 1         |
      | Achieved      | achieved description      | achieved2    | 1          | 0       | 2         |
      | Progressing   | progressing description   | progressing2 | 1          | 0       | 3         |
      | Started       | started description       | started2     | 1          | 1       | 4         |
    And the following "competency" frameworks exist:
      | fullname               | idnumber | description                        | scale  |
      | Competency Framework 2 | CF2      | Competency Framework 2 description | scale2 |
    And the following "competency" hierarchy exists:
      | framework | fullname        | idnumber | description            | assignavailability |
      | CF2       | Competency 1 F2 | C1_F2    | Competency description | any                |
    And the following "onactivate" exist in "totara_criteria" plugin:
      | idnumber   | competency |
      | onactivate | C1_F2      |
    And the following "criteria group pathways" exist in "totara_competency" plugin:
      | competency | scale_value | criteria   |
      | C1_F2      | started2    | onactivate |
    And I navigate to my overview
    And I navigate to the competency self assignment page
    And I click on ".tui-checkbox__label" "css_element" in the tui datatable row with "Competency 1 F2" "Competency"
    Then I click on "Assign competencies" "button"
    And I confirm the tui confirmation modal
    And I run the scheduled task "totara_competency\task\expand_assignments_task"
    And I run the scheduled task "totara_competency\task\competency_aggregation_queue"
    And I navigate to my overview

    # Progressed content
    And I should see the "achieved" competencies overview section
    And I should see "1" in the "achieved" competencies overview section header count
    And I should see the "achieved" date for competency "Competency 1 F2" in row "1" of the "achieved" competencies overview section
    And I should see "Started" in the ".tui-overviewCompetenciesSection__content-achieved .tui-myPerformOverviewItemAchievementLevel" "css_element"
    And I should see "Self-assigned" in the ".tui-myPerformOverviewItemAssignmentType" "css_element"

  Scenario: Competency overview when 1 set to progressing 40 days ago
    Given I log in as "alice"
    And I navigate to my overview
    And I navigate to the competency self assignment page
    And I click on ".tui-checkbox__label" "css_element" in the tui datatable row with "Competency 1" "Competency"
    Then I click on "Assign competencies" "button"
    And I confirm the tui confirmation modal
    And I run the scheduled task "totara_competency\task\expand_assignments_task"
    And I run the scheduled task "totara_competency\task\competency_aggregation_queue"
    And I navigate to the competency self assignment page
    And I click on ".tui-checkbox__label" "css_element" in the tui datatable row with "Competency 3" "Competency"
    Then I click on "Assign competencies" "button"
    And I confirm the tui confirmation modal
    And I run the scheduled task "totara_competency\task\expand_assignments_task"
    And I run the scheduled task "totara_competency\task\competency_aggregation_queue"
    And I navigate to my overview

    And I create an assignment for competency "Competency 2" and user "alice" with scale value "Progressing" and backdate it to "-40 days"
    And I run the scheduled task "totara_competency\task\expand_assignments_task"
    And I run the scheduled task "totara_competency\task\competency_aggregation_queue"

    # Not progressed content
    And I should see the "not progressed" competencies overview section
    And I should see "1" in the "not progressed" competencies overview section
    And I should see the "updated" date for competency "Competency 2" in row "1" of the "not progressed" competencies overview section
    And I click on ".tui-myPerformOverviewItemUpdated" "css_element"
    Then I should see "Competency achievement level was updated to 'Progressing'" in the ".tui-myPerformOverviewItemUpdated" "css_element"
    And I should see "Self-assigned" in the ".tui-overviewCompetenciesSection__content-notProgressed .tui-myPerformOverviewItemAssignmentType" "css_element"
    And I should see "3" in the competencies overview total count
    And I should see the "updated" date for competency "Competency 2" in row "1" of the "not progressed" competencies overview section
    When I select "30" from the "Period" singleselect
    And I should see the "not progressed" competencies overview section

    And I select "90" from the "Period" singleselect
    And I should see the "progressed" competencies overview section

    And I select "182" from the "Period" singleselect
    And I should see the "progressed" competencies overview section

    And I select "365" from the "Period" singleselect
    And I should see the "progressed" competencies overview section

  Scenario: Competency overview when 1 set to achieved 40 days ago
    Given I log in as "alice"
    And I navigate to my overview
    And I navigate to the competency self assignment page
    And I click on ".tui-checkbox__label" "css_element" in the tui datatable row with "Competency 1" "Competency"
    Then I click on "Assign competencies" "button"
    And I confirm the tui confirmation modal
    And I run the scheduled task "totara_competency\task\expand_assignments_task"
    And I run the scheduled task "totara_competency\task\competency_aggregation_queue"
    And I navigate to the competency self assignment page
    And I click on ".tui-checkbox__label" "css_element" in the tui datatable row with "Competency 3" "Competency"
    Then I click on "Assign competencies" "button"
    And I confirm the tui confirmation modal
    And I run the scheduled task "totara_competency\task\expand_assignments_task"
    And I run the scheduled task "totara_competency\task\competency_aggregation_queue"
    And I navigate to my overview

    And I create an assignment for competency "Competency 2" and user "alice" with scale value "Achieved" and backdate it to "-40 days"
    And I run the scheduled task "totara_competency\task\expand_assignments_task"
    And I run the scheduled task "totara_competency\task\competency_aggregation_queue"

    And I should see the "not started" competencies overview section
    And I should not see "Competency 2"

    When I select "30" from the "Period" singleselect
    And I should see the "not started" competencies overview section
    And I should not see "Competency 2"

    And I select "90" from the "Period" singleselect
    And I should see the "not started" competencies overview section
    And I should see the "achieved" competencies overview section
    And I should see "1" in the "achieved" competencies overview section
    And I should see the "achieved" date for competency "Competency 2" in row "1" of the "achieved" competencies overview section
    And I should see "Self-assigned" in the ".tui-overviewCompetenciesSection__content-achieved .tui-myPerformOverviewItemAssignmentType" "css_element"
    And I should see "3" in the competencies overview total count

    And I select "182" from the "Period" singleselect
    And I should see the "not started" competencies overview section
    And I should see the "achieved" competencies overview section

    And I select "365" from the "Period" singleselect
    And I should see the "not started" competencies overview section
    And I should see the "achieved" competencies overview section
