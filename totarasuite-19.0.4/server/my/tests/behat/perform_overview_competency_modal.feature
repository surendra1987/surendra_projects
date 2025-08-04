@core @core_my @javascript @totara_competency @perform_overview
Feature: Competency modal in the perform overview page

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
      | fullname                          | idnumber | description                        | scale |
      | Competency Framework 2 with scale | CF2_ws   | Competency Framework 2 description | scale |
    And the following "competency" frameworks exist:
      | fullname                        | idnumber | description                        |
      | Competency Framework 2 no scale | CF2_ns   | Competency Framework 2 description |
    And the following "competency" hierarchy exists:
      | framework | fullname | idnumber | description                                           | assignavailability |
      | CF2_ns    | C0_ns    | C0_ns    | <b>desc</b>                                           | any                |
      | CF2_ns    | C1_ns    | C1_ns    | <b>desc</b>  <img src='/broken-image' alt='Image 1'/> | any                |
      | CF2_ns    | C2_ns    | C2_ns    | <b>desc</b>                                           | any                |
      | CF2_ns    | C3_ns    | C3_ns    | <b>desc</b>                                           | any                |
      | CF2_ns    | C4_ns    | C4_ns    | <b>desc</b>                                           | any                |
      | CF2_ns    | C5_ns    | C5_ns    | <b>desc</b>                                           | any                |
      | CF2_ns    | C6_ns    | C6_ns    | <b>desc</b>                                           | any                |
      | CF2_ns    | C7_ns    | C7_ns    | <b>desc</b>                                           | any                |
      | CF2_ns    | C8_ns    | C8_ns    | <b>desc</b>                                           | any                |
      | CF2_ns    | C9_ns    | C9_ns    | <b>desc</b>                                           | any                |
      | CF2_ns    | C10_ns   | C10_ns   | <b>desc</b>                                           | any                |
      | CF2_ns    | C11_ns   | C11_ns   | <b>desc</b>                                           | any                |
      | CF2_ns    | C12_ns   | C12_ns   | <b>desc</b>                                           | any                |
      | CF2_ws    | C6_ws    | C6_ws    | <b>desc</b>                                           | any                |
      | CF2_ws    | C7_ws    | C7_ws    | <b>desc</b>                                           | any                |
      | CF2_ws    | C8_ws    | C8_ws    | <b>desc</b>                                           | any                |
      | CF2_ws    | C9_ws    | C9_ws    | <b>desc</b>                                           | any                |
      | CF2_ws    | C10_ws   | C10_ws   | <b>desc</b>                                           | any                |
      | CF2_ws    | C11_ws   | C11_ws   | <b>desc</b>                                           | any                |
      | CF2_ws    | C12_ws   | C12_ws   | <b>desc</b>                                           | any                |
    And the following "manual pathways" exist in "totara_competency" plugin:
      | competency | roles   |
      | C6_ws      | self    |
      | C6_ws      | manager |
      | C7_ws      | self    |
      | C7_ws      | manager |
      | C8_ws      | self    |
      | C8_ws      | manager |
      | C9_ws      | self    |
      | C9_ws      | manager |
      | C10_ws     | self    |
      | C10_ws     | manager |
      | C11_ws     | self    |
      | C11_ws     | manager |
      | C12_ws     | self    |
      | C12_ws     | manager |

  Scenario: Competency overview view all modal
    Given I log in as "alice"
    And the following "cohorts" exist:
      | name      | idnumber |
      | Developer | dev      |
    And the following "cohort members" exist:
      | user  | cohort |
      | alice | dev    |

    And I create an assignment for competency "C0_ns" through cohort "Developer" and user "alice" and backdate it to "-800 days"
    And I create an assignment for competency "C1_ns" through cohort "Developer" and user "alice" and backdate it to "-11 days"
    And I create an assignment for competency "C2_ns" through cohort "Developer" and user "alice" and backdate it to "-10 days"
    And I create an assignment for competency "C3_ns" through cohort "Developer" and user "alice" and backdate it to "-9 days"
    And I create an assignment for competency "C4_ns" through cohort "Developer" and user "alice" and backdate it to "-8 days"
    And I create an assignment for competency "C5_ns" through cohort "Developer" and user "alice" and backdate it to "-7 days"
    And I create an assignment for competency "C6_ns" through cohort "Developer" and user "alice" and backdate it to "-6 days"
    And I create an assignment for competency "C7_ns" through cohort "Developer" and user "alice" and backdate it to "-5 days"
    And I create an assignment for competency "C8_ns" through cohort "Developer" and user "alice" and backdate it to "-4 days"
    And I create an assignment for competency "C9_ns" through cohort "Developer" and user "alice" and backdate it to "-3 days"
    And I create an assignment for competency "C10_ns" through cohort "Developer" and user "alice" and backdate it to "-2 days"
    And I create an assignment for competency "C11_ns" through cohort "Developer" and user "alice" and backdate it to "-1 days"
    And I create an assignment for competency "C12_ns" through cohort "Developer" and user "alice" and backdate it to "0 days"
    And I run the scheduled task "totara_competency\task\expand_assignments_task"
    And I run the scheduled task "totara_competency\task\competency_aggregation_queue"
    And I navigate to my overview
    # And I click on "12" "button" in the ".tui-overviewCompetenciesSection__content" "css_element"
    And I click on "not started" header count for competencies overview group
    And I should see "12 competencies not started"

    And I should see "C12_ns" in row "1" of the "competencies" view all modal
    And I should see the "assigned" date for competency "C12_ns" in row "1" of the "not started" competencies overview modal
    And I should see "Developer" in row "1" of the "competencies" view all modal

    And I should see "C11_ns" in row "2" of the "competencies" view all modal
    And I should see the "assigned" date for competency "C11_ns" in row "2" of the "not started" competencies overview modal
    And I should see "Developer" in row "2" of the "competencies" view all modal

    And I should see "C10_ns" in row "3" of the "competencies" view all modal
    And I should see the "assigned" date for competency "C10_ns" in row "3" of the "not started" competencies overview modal
    And I should see "Developer" in row "3" of the "competencies" view all modal

    And I should see "C9_ns" in row "4" of the "competencies" view all modal
    And I should see the "assigned" date for competency "C9_ns" in row "4" of the "not started" competencies overview modal
    And I should see "Developer" in row "4" of the "competencies" view all modal

    And I should see "C8_ns" in row "5" of the "competencies" view all modal
    And I should see the "assigned" date for competency "C8_ns" in row "5" of the "not started" competencies overview modal
    And I should see "Developer" in row "5" of the "competencies" view all modal

    And I should see "C7_ns" in row "6" of the "competencies" view all modal
    And I should see the "assigned" date for competency "C7_ns" in row "6" of the "not started" competencies overview modal
    And I should see "Developer" in row "6" of the "competencies" view all modal

    And I should see "C6_ns" in row "7" of the "competencies" view all modal
    And I should see the "assigned" date for competency "C6_ns" in row "7" of the "not started" competencies overview modal
    And I should see "Developer" in row "7" of the "competencies" view all modal

    And I should see "C5_ns" in row "8" of the "competencies" view all modal
    And I should see the "assigned" date for competency "C5_ns" in row "8" of the "not started" competencies overview modal
    And I should see "Developer" in row "8" of the "competencies" view all modal

    And I should see "C4_ns" in row "9" of the "competencies" view all modal
    And I should see the "assigned" date for competency "C4_ns" in row "9" of the "not started" competencies overview modal
    And I should see "Developer" in row "9" of the "competencies" view all modal

    And I should see "C3_ns" in row "10" of the "competencies" view all modal
    And I should see the "assigned" date for competency "C3_ns" in row "10" of the "not started" competencies overview modal
    And I should see "Developer" in row "10" of the "competencies" view all modal

    And I should not see "C2_ns"
    And I should not see "C1_ns"
    And I should not see "C0_ns"

    And I click on "Load more" "button"

    And I should see "C2_ns" in row "11" of the "competencies" view all modal
    And I should see the "assigned" date for competency "C2_ns" in row "11" of the "not started" competencies overview modal
    And I should see "Developer" in row "11" of the "competencies" view all modal
    And I should see "C1_ns" in row "12" of the "competencies" view all modal
    And I should see the "assigned" date for competency "C1_ns" in row "12" of the "not started" competencies overview modal
    And I should see "Developer" in row "12" of the "competencies" view all modal

    # We still shouldn't see the competency assigned more than 2 years ago
    And I should not see "C0_ns"

    # We can see the text and image in the description
    And I click on description in row "12" of the "competencies" view all modal
    Then I should see "desc" in row "12" of the "competencies" view all modal
    And "//img[contains(@alt, 'Image 1')]" "xpath_element" should exist in the ".tui-dataTableRow:nth-child(12)" "css_element"

  Scenario: Competency overview view all modal with achieved
    Given I log in as "alice"
    And the following "cohorts" exist:
      | name      | idnumber |
      | Developer | dev      |
      | Manager   | man      |
    And the following "cohort members" exist:
      | user  | cohort |
      | alice | dev    |
      | alice | man    |

    And I create an assignment for competency "C6_ws" through cohort "Developer" and user "alice" and backdate it to "-6 days"
    And I create an assignment for competency "C7_ws" through cohort "Manager" and user "alice" and backdate it to "-5 days"
    And I create an assignment for competency "C8_ws" through cohort "Manager" and user "alice" and backdate it to "-4 days"
    And I create an assignment for competency "C9_ws" through cohort "Manager" and user "alice" and backdate it to "-3 days"

    And I run the scheduled task "totara_competency\task\expand_assignments_task"
    And I run the scheduled task "totara_competency\task\competency_aggregation_queue"

    And I navigate to my overview
    And I click on "not started" header count for competencies overview group

    # Achieved
    And I click on "C7_ws" "link"
    And I click on "Add rating" "link"
    And I click on "Rate" "button"
    And I click on the "Achieved" tui radio
    And I click on "Done" "button"
    And I click on "Submit" "button"
    And I confirm the tui confirmation modal
    And I navigate to my overview
    And I click on "not started" header count for competencies overview group

    And I click on "C8_ws" "link" in the ".tui-overviewViewAllModal" "css_element"
    And I click on "Add rating" "link"
    And I click on "Rate" "button"
    And I click on the "Achieved" tui radio
    And I click on "Done" "button"
    And I click on "Submit" "button"
    And I confirm the tui confirmation modal
    And I navigate to my overview
    And I click on "not started" header count for competencies overview group

    And I click on "C9_ws" "link" in the ".tui-overviewViewAllModal" "css_element"
    And I click on "Add rating" "link"
    And I click on "Rate" "button"
    And I click on the "Achieved" tui radio
    And I click on "Done" "button"
    And I click on "Submit" "button"
    And I confirm the tui confirmation modal
    And I navigate to my overview
    And I click on "not started" header count for competencies overview group

    And I run the scheduled task "totara_competency\task\expand_assignments_task"
    And I run the scheduled task "totara_competency\task\competency_aggregation_queue"
    And I navigate to my overview

    And I click on "achieved" header count for competencies overview group
    And I should see "3 competencies achieved"

    And I should see "C9_ws" in row "1" of the "competencies" view all modal
    And I should see the "achieved" date for competency "C9_ws" in row "1" of the "progressed" competencies overview modal
    And I should see "Achieved" in row "1" of the "competencies" view all modal
    And I should see "Manager" in row "1" of the "competencies" view all modal

    And I should see "C8_ws" in row "2" of the "competencies" view all modal
    And I should see the "achieved" date for competency "C8_ws" in row "2" of the "progressed" competencies overview modal
    And I should see "Achieved" in row "2" of the "competencies" view all modal
    And I should see "Manager" in row "2" of the "competencies" view all modal

    And I should see "C7_ws" in row "3" of the "competencies" view all modal
    And I should see the "achieved" date for competency "C7_ws" in row "3" of the "progressed" competencies overview modal
    And I should see "Achieved" in row "3" of the "competencies" view all modal
    And I should see "Manager" in row "3" of the "competencies" view all modal
    And "Load more" "button" should not be visible

  Scenario: Competency overview view all modal with progressing
    Given I log in as "alice"
    And the following "cohorts" exist:
      | name      | idnumber |
      | Developer | dev      |
      | Manager   | man      |
    And the following "cohort members" exist:
      | user  | cohort |
      | alice | dev    |
      | alice | man    |

    And I create an assignment for competency "C9_ws" through cohort "Developer" and user "alice" and backdate it to "-2 days"
    And I create an assignment for competency "C10_ws" through cohort "Developer" and user "alice" and backdate it to "-2 days"
    And I create an assignment for competency "C11_ws" through cohort "Developer" and user "alice" and backdate it to "-1 days"

    # Assign C12_ws as admin
    And I log out
    And I log in as "admin"
    And I navigate to my overview for user "alice"
    And I click on "Competencies" "link" in the ".tui-overviewCompetenciesSection" "css_element"
    And I click on "Assign competencies" "link"
    And I click on ".tui-checkbox__label" "css_element" in the tui datatable row with "C12_ws" "Competency"
    Then I click on "Assign competencies" "button"
    And I confirm the tui confirmation modal
    And I log out
    And I log in as "alice"

    And I run the scheduled task "totara_competency\task\expand_assignments_task"
    And I run the scheduled task "totara_competency\task\competency_aggregation_queue"

    And I navigate to my overview
    And I click on "not started" header count for competencies overview group

    And I click on "C10_ws" "link"
    And I click on "Add rating" "link"
    And I click on "Rate" "button"
    And I click on the "Progressing" tui radio
    And I click on "Done" "button"
    And I click on "Submit" "button"
    And I confirm the tui confirmation modal
    And I navigate to my overview
    And I click on "not started" header count for competencies overview group

    And I click on "C11_ws" "link" in the ".tui-overviewViewAllModal" "css_element"
    And I click on "Add rating" "link"
    And I click on "Rate" "button"
    And I click on the "Progressing" tui radio
    And I click on "Done" "button"
    And I click on "Submit" "button"
    And I confirm the tui confirmation modal
    And I navigate to my overview
    And I click on "not started" header count for competencies overview group

    And I click on "C12_ws" "link" in the ".tui-overviewViewAllModal" "css_element"
    And I click on "Add rating" "link"
    And I click on "Rate" "button"
    And I click on the "Progressing" tui radio
    And I click on "Done" "button"
    And I click on "Submit" "button"
    And I confirm the tui confirmation modal
    And I run the scheduled task "totara_competency\task\expand_assignments_task"
    And I run the scheduled task "totara_competency\task\competency_aggregation_queue"
    And I navigate to my overview

    And I click on "progressed" header count for competencies overview group
    And I should see "3 competencies progressed"

    And I should see "C12_ws" in row "1" of the "competencies" view all modal
    And I should see the "updated" date for competency "C12_ws" in row "1" of the "progressed" competencies overview modal
    And I click on ".tui-overviewViewAllModal .tui-dataTableRow:nth-child(1) .tui-myPerformOverviewItemUpdated__button" "css_element"
    And I should see "Competency achievement level was updated to 'Progressing'"
    And I should see "Progressing" in row "1" of the "competencies" view all modal
    And I should see "Directly assigned" in row "1" of the "competencies" view all modal

    And I should see "C11_ws" in row "2" of the "competencies" view all modal
    And I should see the "updated" date for competency "C11_ws" in row "2" of the "progressed" competencies overview modal
    And I should see "Progressing" in row "2" of the "competencies" view all modal
    And I should see "Developer" in row "2" of the "competencies" view all modal

    And I should see "C10_ws" in row "3" of the "competencies" view all modal
    And I should see the "updated" date for competency "C10_ws" in row "3" of the "progressed" competencies overview modal
    And I should see "Progressing" in row "3" of the "competencies" view all modal
    And I should see "Developer" in row "3" of the "competencies" view all modal

    # Archive one of them and ensure it's removed from the overview page
    And I log out
    And I log in as "admin"
    And I navigate to "Competencies > Manage competencies" in site administration
    And I click on "Competency Framework 2 with scale" "link"
    And I click on "Delete" "link" in the "C12_ws" "table_row"
    And I click on "Yes" "button"
    And I log out
    And I log in as "alice"
    And I navigate to my overview
    And I should not see "C12_ws"
    # Ensure we can't open the modal if there are less than 3 items
    And ".tui-overviewStatusTable__header-countButton" "css_element" should not exist
