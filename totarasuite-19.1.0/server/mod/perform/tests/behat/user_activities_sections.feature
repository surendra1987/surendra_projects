@totara @perform @mod_perform @javascript @vuejs
Feature: Viewing the section list in the user activities view and navigating to a particular section
  Background:
    Given the following "users" exist:
      | username           | firstname | lastname | email                               |
      | john               | John      | One      | john.one@example.com                |
      | david              | David     | Two      | david.two@example.com               |
      | harry              | Harry     | Three    | harry.three@example.com             |
      | manager-appraiser1 | zcombined | a        | manager-appraiser.three@example.com |
      | manager-appraiser2 | zcombined | b        | manager-appraiser.four@example.com  |
      | manager-appraiser3 | zcombined | c        | manager-appraiser.five@example.com  |
      | manager-appraiser4 | zcombined | d        | manager-appraiser.six@example.com   |
      | manager-appraiser5 | zcombined | e        | manager-appraiser.seven@example.com |
      | manager-appraiser6 | zcombined | f        | manager-appraiser.eight@example.com |
      | manager-appraiser7 | zcombined | g        | manager-appraiser.nine@example.com  |
      | manager-appraiser8 | zcombined | h        | manager-appraiser.ten@example.com   |
    And the following job assignments exist:
      | user | manager            | appraiser          |
      | john | manager-appraiser1 | manager-appraiser1 |
      | john | manager-appraiser2 | manager-appraiser2 |
      | john | manager-appraiser3 | manager-appraiser3 |
      | john | manager-appraiser4 | manager-appraiser4 |
      | john | manager-appraiser5 | manager-appraiser5 |
      | john | manager-appraiser6 | manager-appraiser6 |
      | john | manager-appraiser7 | manager-appraiser7 |
      | john | manager-appraiser8 | manager-appraiser8 |
      | john | david              | harry              |
    And the following "activities" exist in "mod_perform" plugin:
      | activity_name                                   | activity_type | create_section | create_track | activity_status | anonymous_responses |
      | Single sect Activity with manual and auto close | feedback      | false          | false        | Active          | false               |
      | Multiple section activity with manual closure   | appraisal     | false          | false        | Active          | false               |
      | Anonymous responses - Multiple section Activity | appraisal     | false          | false        | Active          | true                |
      | Multiple section Activity                       | appraisal     | false          | false        | Active          | false               |
    And the following "activity settings" exist in "mod_perform" plugin:
      | activity_name                                   | close_on_completion | close_on_section_submission | multisection | manual_close |
      | Single sect Activity with manual and auto close | yes                 | yes                         | no           | yes          |
      | Anonymous responses - Multiple section Activity | yes                 | yes                         | yes          | no           |
      | Multiple section Activity                       | yes                 | yes                         | yes          | no           |
      | Multiple section activity with manual closure   | no                  | no                          | yes          | yes          |
    And the following "activity sections" exist in "mod_perform" plugin:
      | activity_name                                   | section_name   |
      | Anonymous responses - Multiple section Activity | Section anon 1 |
      | Anonymous responses - Multiple section Activity | Section anon 2 |
      | Anonymous responses - Multiple section Activity | Section anon 3 |
      | Multiple section Activity                       | Section 1      |
      | Multiple section Activity                       | Section 2      |
      | Multiple section Activity                       | Section 3      |
      | Multiple section activity with manual closure   | Sect 1         |
      | Multiple section activity with manual closure   | Sect 2         |
      | Multiple section activity with manual closure   | Sect 3         |
      | Single sect Activity with manual and auto close | Simple section |
    And the following "cohorts" exist:
      | name | idnumber | description | contextlevel | reference | cohorttype |
      | aud1 | aud1     | Audience 1  | System       | 0         | 1          |
    And the following "cohort members" exist:
      | user  | cohort |
      | john  | aud1   |
      | david | aud1   |
    And the following "activity tracks" exist in "mod_perform" plugin:
      | activity_name                                   | track_description | due_date_offset |
      | Anonymous responses - Multiple section Activity | track anon 1      | 1, DAY          |
      | Multiple section Activity                       | track 1           | 1, DAY          |
      | Multiple section activity with manual closure   | track manual 1    | 2, DAY          |
      | Single sect Activity with manual and auto close | track 3           | 3, DAY          |
    And the following "track assignments" exist in "mod_perform" plugin:
      | track_description | assignment_type | assignment_name |
      | track 1           | cohort          | aud1            |
      | track anon 1      | cohort          | aud1            |
      | track manual 1    | cohort          | aud1            |
      | track 3           | cohort          | aud1            |

    And the following "section relationships" exist in "mod_perform" plugin:
      | section_name   | relationship |
      | Section anon 1 | manager      |
      | Section anon 1 | appraiser    |
      | Section anon 2 | manager      |
      | Section anon 3 | subject      |
      | Section 1      | subject      |
      | Section 1      | manager      |
      | Section 1      | appraiser    |
      | Section 2      | manager      |
      | Section 3      | subject      |
      | Sect 1         | subject      |
      | Sect 2         | subject      |
      | Sect 3         | subject      |
      | Simple section | subject      |
      | Simple section | manager      |
    And the following "section elements" exist in "mod_perform" plugin:
      | section_name   | element_name | title      | is_required |
      | Section anon 1 | short_text   | Question 1 | 0           |
      | Section anon 2 | short_text   | Question 2 | 0           |
      | Section anon 3 | short_text   | Question 3 | 0           |
      | Section 1      | short_text   | Question 1 | 0           |
      | Section 2      | short_text   | Question 2 | 0           |
      | Section 3      | short_text   | Question 3 | 0           |
      | Sect 1         | short_text   | Question 1 | 0           |
      | Sect 2         | short_text   | Question 2 | 1           |
      | Sect 3         | short_text   | Question 3 | 0           |
      | Simple section | short_text   | Question 5 | 0           |
    And I run the scheduled task "mod_perform\task\expand_assignments_task"
    And I run the scheduled task "mod_perform\task\create_subject_instance_task"
    # Now add a second activity, this makes sure that the activities are in the right order
    Given the following "activities" exist in "mod_perform" plugin:
      | activity_name           | activity_type | create_section | create_track | activity_status |
      | Single section Activity | feedback      | false          | false        | Active          |
    And the following "activity settings" exist in "mod_perform" plugin:
      | activity_name           | close_on_completion | multisection |
      | Single section Activity | no                  | no           |
    And the following "activity sections" exist in "mod_perform" plugin:
      | activity_name           | section_name |
      | Single section Activity | Section 4    |
    And the following "activity tracks" exist in "mod_perform" plugin:
      | activity_name           | track_description |
      | Single section Activity | track 2           |
    And the following "track assignments" exist in "mod_perform" plugin:
      | track_description | assignment_type | assignment_name |
      | track 2           | cohort          | aud1            |
    And the following "section relationships" exist in "mod_perform" plugin:
      | section_name | relationship |
      | Section 4    | subject      |
      | Section 4    | manager      |
    And the following "section elements" exist in "mod_perform" plugin:
      | section_name | element_name | title      |
      | Section 4    | short_text   | Question 4 |
    And I run the scheduled task "mod_perform\task\expand_assignments_task"
    And I run the scheduled task "mod_perform\task\create_subject_instance_task"

  Scenario: List and complete sections as the subject with a single section
    Given I log in as "john"
    When I navigate to the outstanding perform activities list page
    # First row
    Then I should see "Single section Activity" in the ".tui-dataTableRow:nth-child(1) .tui-performUserActivityListTableItem__title" "css_element"
    And I should see "##today##j F Y##" in the ".tui-dataTableRow:nth-child(1) .tui-performUserActivityListTableItem__overview-created" "css_element"
    And I should see "Feedback" in the ".tui-dataTableRow:nth-child(1) .tui-performUserActivityListTableItem__overview-type" "css_element"
    And I should see "Not started" in the ".tui-dataTableRow:nth-child(1) .tui-performUserActivityListTableItem__progress-status" "css_element"
    And I should see "Activity is not started" in the ".tui-dataTableRow:nth-child(1) .tui-performUserActivityListTableItem__details-overallProgress" "css_element"
    # Second row
    And I should see "Multiple section Activity" in the ".tui-dataTableRow:nth-child(2) .tui-performUserActivityListTableItem__title" "css_element"
    And I should see "##today##j F Y##" in the ".tui-dataTableRow:nth-child(2) .tui-performUserActivityListTableItem__overview-created" "css_element"
    And I should see "Appraisal" in the ".tui-dataTableRow:nth-child(2) .tui-performUserActivityListTableItem__overview-type" "css_element"
    And I should see "Not started" in the ".tui-dataTableRow:nth-child(2) .tui-performUserActivityListTableItem__progress-status" "css_element"
    And I should see "Activity is not started" in the ".tui-dataTableRow:nth-child(2) .tui-performUserActivityListTableItem__details-overallProgress" "css_element"
    # Third row
    And I should see "Anonymous responses - Multiple section Activity" in the ".tui-dataTableRow:nth-child(3) .tui-performUserActivityListTableItem__title" "css_element"
    And I should see "##today##j F Y##" in the ".tui-dataTableRow:nth-child(3) .tui-performUserActivityListTableItem__overview-created" "css_element"
    And I should see "Appraisal" in the ".tui-dataTableRow:nth-child(3) .tui-performUserActivityListTableItem__overview-type" "css_element"
    And I should see "Not started" in the ".tui-dataTableRow:nth-child(3) .tui-performUserActivityListTableItem__progress-status" "css_element"
    And I should see "Activity is not started" in the ".tui-dataTableRow:nth-child(3) .tui-performUserActivityListTableItem__details-overallProgress" "css_element"

    When I click on "View details" "button" in the ".tui-dataTableRow:nth-child(1)" "css_element"
    Then I should not see "Section 4" in the tui modal
    And I should not see "Section" in the tui modal

    And I should see "Subject" in the ".tui-performUserActivityListSection .tui-dataTable:nth-child(1) .tui-dataTableRowHeader" "css_element"
    And I should see "You" in the ".tui-performUserActivityListSection .tui-dataTable:nth-child(1) .tui-dataTableRow:nth-child(1) .tui-dataTableCell:nth-child(1)" "css_element"
    And I should see "Not started" in the ".tui-performUserActivityListSection .tui-dataTable:nth-child(1) .tui-dataTableRow:nth-child(1) .tui-dataTableCell:nth-child(2)" "css_element"
    And I should see "Manager" in the ".tui-performUserActivityListSection .tui-dataTable:nth-child(2) .tui-dataTableRowHeader" "css_element"
    And I should see "David Two" in the ".tui-performUserActivityListSection .tui-dataTable:nth-child(2) .tui-dataTableRow:nth-child(1) .tui-dataTableCell:nth-child(1)" "css_element"
    And I should see "Not started" in the ".tui-performUserActivityListSection .tui-dataTable:nth-child(2) .tui-dataTableRow:nth-child(1) .tui-dataTableCell:nth-child(2)" "css_element"
    And I should see "zcombined a" in the ".tui-performUserActivityListSection .tui-dataTable:nth-child(2) .tui-dataTableRow:nth-child(2) .tui-dataTableCell:nth-child(1)" "css_element"
    And I should see "Not started" in the ".tui-performUserActivityListSection .tui-dataTable:nth-child(2) .tui-dataTableRow:nth-child(2) .tui-dataTableCell:nth-child(2)" "css_element"
    And I close the tui modal

    When I click on "Single section Activity" "link"
    Then I should see "Single section Activity" in the ".tui-pageHeading__title" "css_element"
    And I should see "Question 4"
    And I should see perform activity relationship to user "yourself"
    When I click on "Performance activities" "link"
    # First row
    Then I should see "Single section Activity" in the ".tui-dataTableRow:nth-child(1) .tui-performUserActivityListTableItem__title" "css_element"
    And I should see "##today##j F Y##" in the ".tui-dataTableRow:nth-child(1) .tui-performUserActivityListTableItem__overview-created" "css_element"
    And I should see "Feedback" in the ".tui-dataTableRow:nth-child(1) .tui-performUserActivityListTableItem__overview-type" "css_element"
    And I should see "In progress" in the ".tui-dataTableRow:nth-child(1) .tui-performUserActivityListTableItem__progress-status" "css_element"
    And I should see "Activity is in progress" in the ".tui-dataTableRow:nth-child(1) .tui-performUserActivityListTableItem__details-overallProgress" "css_element"
    # Second row
    And I should see "Multiple section Activity" in the ".tui-dataTableRow:nth-child(2) .tui-performUserActivityListTableItem__title" "css_element"
    And I should see "##today##j F Y##" in the ".tui-dataTableRow:nth-child(2) .tui-performUserActivityListTableItem__overview-created" "css_element"
    And I should see "Appraisal" in the ".tui-dataTableRow:nth-child(2) .tui-performUserActivityListTableItem__overview-type" "css_element"
    And I should see "Not started" in the ".tui-dataTableRow:nth-child(2) .tui-performUserActivityListTableItem__progress-status" "css_element"
    And I should see "Activity is not started" in the ".tui-dataTableRow:nth-child(2) .tui-performUserActivityListTableItem__details-overallProgress" "css_element"
    # Third row
    And I should see "Anonymous responses - Multiple section Activity" in the ".tui-dataTableRow:nth-child(3) .tui-performUserActivityListTableItem__title" "css_element"
    And I should see "##today##j F Y##" in the ".tui-dataTableRow:nth-child(3) .tui-performUserActivityListTableItem__overview-created" "css_element"
    And I should see "Appraisal" in the ".tui-dataTableRow:nth-child(3) .tui-performUserActivityListTableItem__overview-type" "css_element"
    And I should see "Not started" in the ".tui-dataTableRow:nth-child(3) .tui-performUserActivityListTableItem__progress-status" "css_element"
    And I should see "Activity is not started" in the ".tui-dataTableRow:nth-child(3) .tui-performUserActivityListTableItem__details-overallProgress" "css_element"

    When I click on "View details" "button" in the ".tui-dataTableRow:nth-child(1)" "css_element"
    And I should see "Subject" in the ".tui-performUserActivityListSection .tui-dataTable:nth-child(1) .tui-dataTableRowHeader" "css_element"
    And I should see "You" in the ".tui-performUserActivityListSection .tui-dataTable:nth-child(1) .tui-dataTableRow:nth-child(1) .tui-dataTableCell:nth-child(1)" "css_element"
    And I should see "In progress" in the ".tui-performUserActivityListSection .tui-dataTable:nth-child(1) .tui-dataTableRow:nth-child(1) .tui-dataTableCell:nth-child(2)" "css_element"
    And I should see "Manager" in the ".tui-performUserActivityListSection .tui-dataTable:nth-child(2) .tui-dataTableRowHeader" "css_element"
    And I should see "David Two" in the ".tui-performUserActivityListSection .tui-dataTable:nth-child(2) .tui-dataTableRow:nth-child(1) .tui-dataTableCell:nth-child(1)" "css_element"
    And I should see "Not started" in the ".tui-performUserActivityListSection .tui-dataTable:nth-child(2) .tui-dataTableRow:nth-child(1) .tui-dataTableCell:nth-child(2)" "css_element"
    And I should see "combined a" in the ".tui-performUserActivityListSection .tui-dataTable:nth-child(2) .tui-dataTableRow:nth-child(2) .tui-dataTableCell:nth-child(1)" "css_element"
    And I should see "Not started" in the ".tui-performUserActivityListSection .tui-dataTable:nth-child(2) .tui-dataTableRow:nth-child(2) .tui-dataTableCell:nth-child(2)" "css_element"
    And I close the tui modal

    When I click on "Single section Activity" "link"
    Then I should see "Single section Activity" in the ".tui-pageHeading__title" "css_element"
    And I should see "Question 4"
    And I should see perform activity relationship to user "yourself"
    When I click on "Submit" "button"
    And I confirm the tui confirmation modal
    And I should see "Section submitted" in the tui success notification toast

    # First row
    When I click on "Performance activities" "link"
    Then I should see "Single section Activity" in the ".tui-dataTableRow:nth-child(1) .tui-performUserActivityListTableItem__title" "css_element"
    And I should see "##today##j F Y##" in the ".tui-dataTableRow:nth-child(1) .tui-performUserActivityListTableItem__overview-created" "css_element"
    And I should see "Feedback" in the ".tui-dataTableRow:nth-child(1) .tui-performUserActivityListTableItem__overview-type" "css_element"
    And I should see "Complete" in the ".tui-dataTableRow:nth-child(1) .tui-performUserActivityListTableItem__progress-status" "css_element"
    And I should see "Activity is in progress" in the ".tui-dataTableRow:nth-child(1) .tui-performUserActivityListTableItem__details-overallProgress" "css_element"
    # Second row
    And I should see "Multiple section Activity" in the ".tui-dataTableRow:nth-child(2) .tui-performUserActivityListTableItem__title" "css_element"
    And I should see "##today##j F Y##" in the ".tui-dataTableRow:nth-child(2) .tui-performUserActivityListTableItem__overview-created" "css_element"
    And I should see "Appraisal" in the ".tui-dataTableRow:nth-child(2) .tui-performUserActivityListTableItem__overview-type" "css_element"
    And I should see "Not started" in the ".tui-dataTableRow:nth-child(2) .tui-performUserActivityListTableItem__progress-status" "css_element"
    And I should see "Activity is not started" in the ".tui-dataTableRow:nth-child(2) .tui-performUserActivityListTableItem__details-overallProgress" "css_element"
    # Third row
    And I should see "Anonymous responses - Multiple section Activity" in the ".tui-dataTableRow:nth-child(3) .tui-performUserActivityListTableItem__title" "css_element"
    And I should see "##today##j F Y##" in the ".tui-dataTableRow:nth-child(3) .tui-performUserActivityListTableItem__overview-created" "css_element"
    And I should see "Appraisal" in the ".tui-dataTableRow:nth-child(3) .tui-performUserActivityListTableItem__overview-type" "css_element"
    And I should see "Not started" in the ".tui-dataTableRow:nth-child(3) .tui-performUserActivityListTableItem__progress-status" "css_element"
    And I should see "Activity is not started" in the ".tui-dataTableRow:nth-child(3) .tui-performUserActivityListTableItem__details-overallProgress" "css_element"

    When I click on "View details" "button" in the ".tui-dataTableRow:nth-child(1)" "css_element"
    And I should see "Subject" in the ".tui-performUserActivityListSection .tui-dataTable:nth-child(1) .tui-dataTableRowHeader" "css_element"
    And I should see "You" in the ".tui-performUserActivityListSection .tui-dataTable:nth-child(1) .tui-dataTableRow:nth-child(1) .tui-dataTableCell:nth-child(1)" "css_element"
    And I should see "Complete" in the ".tui-performUserActivityListSection .tui-dataTable:nth-child(1) .tui-dataTableRow:nth-child(1) .tui-dataTableCell:nth-child(2)" "css_element"
    And ".tui-performUserActivityListSection__progressIcon-complete" "css_element" should exist in the ".tui-performUserActivityListSection .tui-dataTable:nth-child(1) .tui-dataTableRow:nth-child(1) .tui-dataTableCell:nth-child(2)" "css_element"
    And I should see "Manager" in the ".tui-performUserActivityListSection .tui-dataTable:nth-child(2) .tui-dataTableRowHeader" "css_element"
    And I should see "David Two" in the ".tui-performUserActivityListSection .tui-dataTable:nth-child(2) .tui-dataTableRow:nth-child(1) .tui-dataTableCell:nth-child(1)" "css_element"
    And I should see "Not started" in the ".tui-performUserActivityListSection .tui-dataTable:nth-child(2) .tui-dataTableRow:nth-child(1) .tui-dataTableCell:nth-child(2)" "css_element"
    And I should see "combined a" in the ".tui-performUserActivityListSection .tui-dataTable:nth-child(2) .tui-dataTableRow:nth-child(2) .tui-dataTableCell:nth-child(1)" "css_element"
    And I should see "Not started" in the ".tui-performUserActivityListSection .tui-dataTable:nth-child(2) .tui-dataTableRow:nth-child(2) .tui-dataTableCell:nth-child(2)" "css_element"

  Scenario: List and complete sections as the subject with multiple sections
    Given I log in as "john"
    When I navigate to the outstanding perform activities list page
    # First row
    Then I should see "Single section Activity" in the ".tui-dataTableRow:nth-child(1) .tui-performUserActivityListTableItem__title" "css_element"
    And I should see "##today##j F Y##" in the ".tui-dataTableRow:nth-child(1) .tui-performUserActivityListTableItem__overview-created" "css_element"
    And I should see "Feedback" in the ".tui-dataTableRow:nth-child(1) .tui-performUserActivityListTableItem__overview-type" "css_element"
    And I should see "Not started" in the ".tui-dataTableRow:nth-child(1) .tui-performUserActivityListTableItem__progress-status" "css_element"
    And I should see "Activity is not started" in the ".tui-dataTableRow:nth-child(1) .tui-performUserActivityListTableItem__details-overallProgress" "css_element"
    # Second row
    And I should see "Multiple section Activity" in the ".tui-dataTableRow:nth-child(2) .tui-performUserActivityListTableItem__title" "css_element"
    And I should see "##today##j F Y##" in the ".tui-dataTableRow:nth-child(2) .tui-performUserActivityListTableItem__overview-created" "css_element"
    And I should see "Appraisal" in the ".tui-dataTableRow:nth-child(2) .tui-performUserActivityListTableItem__overview-type" "css_element"
    And I should see "Not started" in the ".tui-dataTableRow:nth-child(2) .tui-performUserActivityListTableItem__progress-status" "css_element"
    And I should see "Activity is not started" in the ".tui-dataTableRow:nth-child(2) .tui-performUserActivityListTableItem__details-overallProgress" "css_element"
    # Third row
    And I should see "Anonymous responses - Multiple section Activity" in the ".tui-dataTableRow:nth-child(3) .tui-performUserActivityListTableItem__title" "css_element"
    And I should see "##today##j F Y##" in the ".tui-dataTableRow:nth-child(3) .tui-performUserActivityListTableItem__overview-created" "css_element"
    And I should see "Appraisal" in the ".tui-dataTableRow:nth-child(3) .tui-performUserActivityListTableItem__overview-type" "css_element"
    And I should see "Not started" in the ".tui-dataTableRow:nth-child(3) .tui-performUserActivityListTableItem__progress-status" "css_element"
    And I should see "Activity is not started" in the ".tui-dataTableRow:nth-child(3) .tui-performUserActivityListTableItem__details-overallProgress" "css_element"

    When I click on "View details" "button" in the ".tui-dataTableRow:nth-child(2)" "css_element"
    Then I should see "Section 1" in the tui modal
    And I should see "Section 2" in the tui modal
    And I should see "Section 3" in the tui modal

    And I should see "Subject" in the ".tui-progressTrackerNav__item:nth-child(1) .tui-dataTable:nth-child(1) .tui-dataTableRowHeader" "css_element"
    And I should see "You" in the ".tui-progressTrackerNav__item:nth-child(1) .tui-dataTable:nth-child(1) .tui-dataTableRow:nth-child(1) .tui-dataTableCell:nth-child(1)" "css_element"
    And I should see "Not started" in the ".tui-progressTrackerNav__item:nth-child(1) .tui-dataTable:nth-child(1) .tui-dataTableRow:nth-child(1) .tui-dataTableCell:nth-child(2)" "css_element"

    And I should see "Manager" in the ".tui-progressTrackerNav__item:nth-child(1) .tui-dataTable:nth-child(2) .tui-dataTableRowHeader" "css_element"
    And I should see "David Two" in the ".tui-progressTrackerNav__item:nth-child(1) .tui-dataTable:nth-child(2) .tui-dataTableRow:nth-child(1) .tui-dataTableCell:nth-child(1)" "css_element"
    And I should see "Not started" in the ".tui-progressTrackerNav__item:nth-child(1) .tui-dataTable:nth-child(2) .tui-dataTableRow:nth-child(1) .tui-dataTableCell:nth-child(2)" "css_element"
    And I should see "combined a" in the ".tui-progressTrackerNav__item:nth-child(1) .tui-dataTable:nth-child(2) .tui-dataTableRow:nth-child(2) .tui-dataTableCell:nth-child(1)" "css_element"
    And I should see "Not started" in the ".tui-progressTrackerNav__item:nth-child(1) .tui-dataTable:nth-child(2) .tui-dataTableRow:nth-child(2) .tui-dataTableCell:nth-child(2)" "css_element"

    And I should see "Appraiser" in the ".tui-progressTrackerNav__item:nth-child(1) .tui-dataTable:nth-child(4) .tui-dataTableRowHeader" "css_element"
    And I should see "Harry Three" in the ".tui-progressTrackerNav__item:nth-child(1) .tui-dataTable:nth-child(4) .tui-dataTableRow:nth-child(1) .tui-dataTableCell:nth-child(1)" "css_element"
    And I should see "Not started" in the ".tui-progressTrackerNav__item:nth-child(1) .tui-dataTable:nth-child(4) .tui-dataTableRow:nth-child(1) .tui-dataTableCell:nth-child(2)" "css_element"
    And I should see "combined a" in the ".tui-progressTrackerNav__item:nth-child(1) .tui-dataTable:nth-child(4) .tui-dataTableRow:nth-child(2) .tui-dataTableCell:nth-child(1)" "css_element"
    And I should see "Not started" in the ".tui-progressTrackerNav__item:nth-child(1) .tui-dataTable:nth-child(4) .tui-dataTableRow:nth-child(2) .tui-dataTableCell:nth-child(2)" "css_element"

    And I should see "Manager" in the ".tui-progressTrackerNav__item:nth-child(2) .tui-dataTable:nth-child(1) .tui-dataTableRowHeader" "css_element"
    And I should see "David Two" in the ".tui-progressTrackerNav__item:nth-child(2) .tui-dataTable:nth-child(1) .tui-dataTableRow:nth-child(1) .tui-dataTableCell:nth-child(1)" "css_element"
    And I should see "Not started" in the ".tui-progressTrackerNav__item:nth-child(2) .tui-dataTable:nth-child(1) .tui-dataTableRow:nth-child(1) .tui-dataTableCell:nth-child(2)" "css_element"
    And I should see "combined a" in the ".tui-progressTrackerNav__item:nth-child(2) .tui-dataTable:nth-child(1) .tui-dataTableRow:nth-child(2) .tui-dataTableCell:nth-child(1)" "css_element"
    And I should see "Not started" in the ".tui-progressTrackerNav__item:nth-child(2) .tui-dataTable:nth-child(1) .tui-dataTableRow:nth-child(2) .tui-dataTableCell:nth-child(2)" "css_element"

    And I should see "Subject" in the ".tui-progressTrackerNav__item:nth-child(3) .tui-dataTable:nth-child(1) .tui-dataTableRowHeader" "css_element"
    And I should see "You" in the ".tui-progressTrackerNav__item:nth-child(3) .tui-dataTable:nth-child(1) .tui-dataTableRow:nth-child(1) .tui-dataTableCell:nth-child(1)" "css_element"
    And I should see "Not started" in the ".tui-progressTrackerNav__item:nth-child(3) .tui-dataTable:nth-child(1) .tui-dataTableRow:nth-child(1) .tui-dataTableCell:nth-child(2)" "css_element"

    And "View section" "link" should exist in the ".tui-progressTrackerNav__item:nth-child(1) .tui-progressTrackerNav__itemContent" "css_element"
    And "View section" "link" should not exist in the ".tui-progressTrackerNav__item:nth-child(2) .tui-progressTrackerNav__itemContent" "css_element"
    And I should see "Section 2" in the ".tui-progressTrackerNav__item:nth-child(2) .tui-progressTrackerNav__itemContent" "css_element"
    And "View section" "link" should exist in the ".tui-progressTrackerNav__item:nth-child(3) .tui-progressTrackerNav__itemContent" "css_element"
    When I click on "View section" "link" in the ".tui-progressTrackerNav__item:nth-child(1) .tui-progressTrackerNav__itemContent" "css_element"
    Then I should see "Multiple section Activity" in the ".tui-pageHeading__title" "css_element"
    Then I should see "Section 1" in the ".tui-participantContent__sectionHeading-title" "css_element"
    And I should see "Question 1"
    And I should see perform activity relationship to user "yourself"
    When I click on "Performance activities" "link"

    # First row
    Then I should see "Single section Activity" in the ".tui-dataTableRow:nth-child(1) .tui-performUserActivityListTableItem__title" "css_element"
    And I should see "##today##j F Y##" in the ".tui-dataTableRow:nth-child(1) .tui-performUserActivityListTableItem__overview-created" "css_element"
    And I should see "Feedback" in the ".tui-dataTableRow:nth-child(1) .tui-performUserActivityListTableItem__overview-type" "css_element"
    And I should see "Not started" in the ".tui-dataTableRow:nth-child(1) .tui-performUserActivityListTableItem__progress-status" "css_element"
    And I should see "Activity is not started" in the ".tui-dataTableRow:nth-child(1) .tui-performUserActivityListTableItem__details-overallProgress" "css_element"
    # Second row
    And I should see "Multiple section Activity" in the ".tui-dataTableRow:nth-child(2) .tui-performUserActivityListTableItem__title" "css_element"
    And I should see "##today##j F Y##" in the ".tui-dataTableRow:nth-child(2) .tui-performUserActivityListTableItem__overview-created" "css_element"
    And I should see "Appraisal" in the ".tui-dataTableRow:nth-child(2) .tui-performUserActivityListTableItem__overview-type" "css_element"
    And I should see "In progress" in the ".tui-dataTableRow:nth-child(2) .tui-performUserActivityListTableItem__progress-status" "css_element"
    And I should see "Activity is in progress" in the ".tui-dataTableRow:nth-child(2) .tui-performUserActivityListTableItem__details-overallProgress" "css_element"
    # Third row
    And I should see "Anonymous responses - Multiple section Activity" in the ".tui-dataTableRow:nth-child(3) .tui-performUserActivityListTableItem__title" "css_element"
    And I should see "##today##j F Y##" in the ".tui-dataTableRow:nth-child(3) .tui-performUserActivityListTableItem__overview-created" "css_element"
    And I should see "Appraisal" in the ".tui-dataTableRow:nth-child(3) .tui-performUserActivityListTableItem__overview-type" "css_element"
    And I should see "Not started" in the ".tui-dataTableRow:nth-child(3) .tui-performUserActivityListTableItem__progress-status" "css_element"
    And I should see "Activity is not started" in the ".tui-dataTableRow:nth-child(3) .tui-performUserActivityListTableItem__details-overallProgress" "css_element"

    When I click on "View details" "button" in the ".tui-dataTableRow:nth-child(2)" "css_element"

    And I should see "Subject" in the ".tui-progressTrackerNav__item:nth-child(1) .tui-dataTable:nth-child(1) .tui-dataTableRowHeader" "css_element"
    And I should see "You" in the ".tui-progressTrackerNav__item:nth-child(1) .tui-dataTable:nth-child(1) .tui-dataTableRow:nth-child(1) .tui-dataTableCell:nth-child(1)" "css_element"
    And I should see "In progress" in the ".tui-progressTrackerNav__item:nth-child(1) .tui-dataTable:nth-child(1) .tui-dataTableRow:nth-child(1) .tui-dataTableCell:nth-child(2)" "css_element"

    And I should see "Manager" in the ".tui-progressTrackerNav__item:nth-child(1) .tui-dataTable:nth-child(2) .tui-dataTableRowHeader" "css_element"
    And I should see "David Two" in the ".tui-progressTrackerNav__item:nth-child(1) .tui-dataTable:nth-child(2) .tui-dataTableRow:nth-child(1) .tui-dataTableCell:nth-child(1)" "css_element"
    And I should see "Not started" in the ".tui-progressTrackerNav__item:nth-child(1) .tui-dataTable:nth-child(2) .tui-dataTableRow:nth-child(1) .tui-dataTableCell:nth-child(2)" "css_element"
    And I should see "combined a" in the ".tui-progressTrackerNav__item:nth-child(1) .tui-dataTable:nth-child(2) .tui-dataTableRow:nth-child(2) .tui-dataTableCell:nth-child(1)" "css_element"
    And I should see "Not started" in the ".tui-progressTrackerNav__item:nth-child(1) .tui-dataTable:nth-child(2) .tui-dataTableRow:nth-child(2) .tui-dataTableCell:nth-child(2)" "css_element"

    And I should see "Appraiser" in the ".tui-progressTrackerNav__item:nth-child(1) .tui-dataTable:nth-child(4) .tui-dataTableRowHeader" "css_element"
    And I should see "Harry Three" in the ".tui-progressTrackerNav__item:nth-child(1) .tui-dataTable:nth-child(4) .tui-dataTableRow:nth-child(1) .tui-dataTableCell:nth-child(1)" "css_element"
    And I should see "Not started" in the ".tui-progressTrackerNav__item:nth-child(1) .tui-dataTable:nth-child(4) .tui-dataTableRow:nth-child(1) .tui-dataTableCell:nth-child(2)" "css_element"
    And I should see "combined a" in the ".tui-progressTrackerNav__item:nth-child(1) .tui-dataTable:nth-child(4) .tui-dataTableRow:nth-child(2) .tui-dataTableCell:nth-child(1)" "css_element"
    And I should see "Not started" in the ".tui-progressTrackerNav__item:nth-child(1) .tui-dataTable:nth-child(4) .tui-dataTableRow:nth-child(2) .tui-dataTableCell:nth-child(2)" "css_element"

    And I should see "Manager" in the ".tui-progressTrackerNav__item:nth-child(2) .tui-dataTable:nth-child(1) .tui-dataTableRowHeader" "css_element"
    And I should see "David Two" in the ".tui-progressTrackerNav__item:nth-child(2) .tui-dataTable:nth-child(1) .tui-dataTableRow:nth-child(1) .tui-dataTableCell:nth-child(1)" "css_element"
    And I should see "Not started" in the ".tui-progressTrackerNav__item:nth-child(2) .tui-dataTable:nth-child(1) .tui-dataTableRow:nth-child(1) .tui-dataTableCell:nth-child(2)" "css_element"
    And I should see "combined a" in the ".tui-progressTrackerNav__item:nth-child(2) .tui-dataTable:nth-child(1) .tui-dataTableRow:nth-child(2) .tui-dataTableCell:nth-child(1)" "css_element"
    And I should see "Not started" in the ".tui-progressTrackerNav__item:nth-child(2) .tui-dataTable:nth-child(1) .tui-dataTableRow:nth-child(2) .tui-dataTableCell:nth-child(2)" "css_element"

    And I should see "Subject" in the ".tui-progressTrackerNav__item:nth-child(3) .tui-dataTable:nth-child(1) .tui-dataTableRowHeader" "css_element"
    And I should see "You" in the ".tui-progressTrackerNav__item:nth-child(3) .tui-dataTable:nth-child(1) .tui-dataTableRow:nth-child(1) .tui-dataTableCell:nth-child(1)" "css_element"
    And I should see "Not started" in the ".tui-progressTrackerNav__item:nth-child(3) .tui-dataTable:nth-child(1) .tui-dataTableRow:nth-child(1) .tui-dataTableCell:nth-child(2)" "css_element"

    When I click on "View section" "link" in the ".tui-progressTrackerNav__item:nth-child(3) .tui-progressTrackerNav__itemContent" "css_element"
    Then I should see "Multiple section Activity" in the ".tui-pageHeading__title" "css_element"
    Then I should see "Section 3" in the ".tui-participantContent__sectionHeading-title" "css_element"
    And I should see "Question 3"
    And I should see perform activity relationship to user "yourself"
    When I click on "Complete section" "button"
    And I confirm the tui confirmation modal
    And I close the tui notification toast
    And I click on "Performance activities" "link"

    # First row
    Then I should see "Single section Activity" in the ".tui-dataTableRow:nth-child(1) .tui-performUserActivityListTableItem__title" "css_element"
    And I should see "##today##j F Y##" in the ".tui-dataTableRow:nth-child(1) .tui-performUserActivityListTableItem__overview-created" "css_element"
    And I should see "Feedback" in the ".tui-dataTableRow:nth-child(1) .tui-performUserActivityListTableItem__overview-type" "css_element"
    And I should see "Not started" in the ".tui-dataTableRow:nth-child(1) .tui-performUserActivityListTableItem__progress-status" "css_element"
    And I should see "Activity is not started" in the ".tui-dataTableRow:nth-child(1) .tui-performUserActivityListTableItem__details-overallProgress" "css_element"
    # Second row
    And I should see "Multiple section Activity" in the ".tui-dataTableRow:nth-child(2) .tui-performUserActivityListTableItem__title" "css_element"
    And I should see "##today##j F Y##" in the ".tui-dataTableRow:nth-child(2) .tui-performUserActivityListTableItem__overview-created" "css_element"
    And I should see "Appraisal" in the ".tui-dataTableRow:nth-child(2) .tui-performUserActivityListTableItem__overview-type" "css_element"
    And I should see "In progress" in the ".tui-dataTableRow:nth-child(2) .tui-performUserActivityListTableItem__progress-status" "css_element"
    And I should see "Activity is in progress" in the ".tui-dataTableRow:nth-child(2) .tui-performUserActivityListTableItem__details-overallProgress" "css_element"
    # Third row
    And I should see "Anonymous responses - Multiple section Activity" in the ".tui-dataTableRow:nth-child(3) .tui-performUserActivityListTableItem__title" "css_element"
    And I should see "##today##j F Y##" in the ".tui-dataTableRow:nth-child(3) .tui-performUserActivityListTableItem__overview-created" "css_element"
    And I should see "Appraisal" in the ".tui-dataTableRow:nth-child(3) .tui-performUserActivityListTableItem__overview-type" "css_element"
    And I should see "Not started" in the ".tui-dataTableRow:nth-child(3) .tui-performUserActivityListTableItem__progress-status" "css_element"
    And I should see "Activity is not started" in the ".tui-dataTableRow:nth-child(3) .tui-performUserActivityListTableItem__details-overallProgress" "css_element"

    When I click on "View details" "button" in the ".tui-dataTableRow:nth-child(2)" "css_element"

    And I should see "Subject" in the ".tui-progressTrackerNav__item:nth-child(1) .tui-dataTable:nth-child(1) .tui-dataTableRowHeader" "css_element"
    And I should see "You" in the ".tui-progressTrackerNav__item:nth-child(1) .tui-dataTable:nth-child(1) .tui-dataTableRow:nth-child(1) .tui-dataTableCell:nth-child(1)" "css_element"
    And I should see "In progress" in the ".tui-progressTrackerNav__item:nth-child(1) .tui-dataTable:nth-child(1) .tui-dataTableRow:nth-child(1) .tui-dataTableCell:nth-child(2)" "css_element"

    And I should see "Manager" in the ".tui-progressTrackerNav__item:nth-child(1) .tui-dataTable:nth-child(2) .tui-dataTableRowHeader" "css_element"
    And I should see "David Two" in the ".tui-progressTrackerNav__item:nth-child(1) .tui-dataTable:nth-child(2) .tui-dataTableRow:nth-child(1) .tui-dataTableCell:nth-child(1)" "css_element"
    And I should see "Not started" in the ".tui-progressTrackerNav__item:nth-child(1) .tui-dataTable:nth-child(2) .tui-dataTableRow:nth-child(1) .tui-dataTableCell:nth-child(2)" "css_element"
    And I should see "combined a" in the ".tui-progressTrackerNav__item:nth-child(1) .tui-dataTable:nth-child(2) .tui-dataTableRow:nth-child(2) .tui-dataTableCell:nth-child(1)" "css_element"
    And I should see "Not started" in the ".tui-progressTrackerNav__item:nth-child(1) .tui-dataTable:nth-child(2) .tui-dataTableRow:nth-child(2) .tui-dataTableCell:nth-child(2)" "css_element"

    And I should see "Appraiser" in the ".tui-progressTrackerNav__item:nth-child(1) .tui-dataTable:nth-child(4) .tui-dataTableRowHeader" "css_element"
    And I should see "Harry Three" in the ".tui-progressTrackerNav__item:nth-child(1) .tui-dataTable:nth-child(4) .tui-dataTableRow:nth-child(1) .tui-dataTableCell:nth-child(1)" "css_element"
    And I should see "Not started" in the ".tui-progressTrackerNav__item:nth-child(1) .tui-dataTable:nth-child(4) .tui-dataTableRow:nth-child(1) .tui-dataTableCell:nth-child(2)" "css_element"
    And I should see "combined a" in the ".tui-progressTrackerNav__item:nth-child(1) .tui-dataTable:nth-child(4) .tui-dataTableRow:nth-child(2) .tui-dataTableCell:nth-child(1)" "css_element"
    And I should see "Not started" in the ".tui-progressTrackerNav__item:nth-child(1) .tui-dataTable:nth-child(4) .tui-dataTableRow:nth-child(2) .tui-dataTableCell:nth-child(2)" "css_element"

    And I should see "Manager" in the ".tui-progressTrackerNav__item:nth-child(2) .tui-dataTable:nth-child(1) .tui-dataTableRowHeader" "css_element"
    And I should see "David Two" in the ".tui-progressTrackerNav__item:nth-child(2) .tui-dataTable:nth-child(1) .tui-dataTableRow:nth-child(1) .tui-dataTableCell:nth-child(1)" "css_element"
    And I should see "Not started" in the ".tui-progressTrackerNav__item:nth-child(2) .tui-dataTable:nth-child(1) .tui-dataTableRow:nth-child(1) .tui-dataTableCell:nth-child(2)" "css_element"
    And I should see "combined a" in the ".tui-progressTrackerNav__item:nth-child(2) .tui-dataTable:nth-child(1) .tui-dataTableRow:nth-child(2) .tui-dataTableCell:nth-child(1)" "css_element"
    And I should see "Not started" in the ".tui-progressTrackerNav__item:nth-child(2) .tui-dataTable:nth-child(1) .tui-dataTableRow:nth-child(2) .tui-dataTableCell:nth-child(2)" "css_element"

    And I should see "Subject" in the ".tui-progressTrackerNav__item:nth-child(3) .tui-dataTable:nth-child(1) .tui-dataTableRowHeader" "css_element"
    And I should see "You" in the ".tui-progressTrackerNav__item:nth-child(3) .tui-dataTable:nth-child(1) .tui-dataTableRow:nth-child(1) .tui-dataTableCell:nth-child(1)" "css_element"
    And I should see "Complete" in the ".tui-progressTrackerNav__item:nth-child(3) .tui-dataTable:nth-child(1) .tui-dataTableRow:nth-child(1) .tui-dataTableCell:nth-child(2)" "css_element"
    And ".tui-performUserActivityListSection__progressIcon-complete" "css_element" should exist in the ".tui-progressTrackerNav__item:nth-child(3) .tui-dataTable:nth-child(1) .tui-dataTableRow:nth-child(1) .tui-dataTableCell:nth-child(2)" "css_element"
    And I should see "Closed" in the ".tui-progressTrackerNav__item:nth-child(3) .tui-dataTable:nth-child(1) .tui-dataTableRow:nth-child(1) .tui-dataTableCell:nth-child(2) .tui-performUserActivityListSection__closed" "css_element"

  Scenario: List and complete sections as the subject with multiple sections with anonymous answers

    Given I log in as "manager-appraiser1"
    When I navigate to the outstanding perform activities list page
    And I click on "As Manager" "link"
    When I click on "View details" "button" in the ".tui-dataTableRow:nth-child(3)" "css_element"
    When I click on "View section" "link" in the ".tui-progressTrackerNav__item:nth-child(1) .tui-progressTrackerNav__itemContent" "css_element"
    When I click on "Complete section" "button"
    And I confirm the tui confirmation modal
    And I log out

    And I log in as "john"

    When I navigate to the outstanding perform activities list page
    # First row
    Then I should see "Single section Activity" in the ".tui-dataTableRow:nth-child(1) .tui-performUserActivityListTableItem__title" "css_element"
    And I should see "##today##j F Y##" in the ".tui-dataTableRow:nth-child(1) .tui-performUserActivityListTableItem__overview-created" "css_element"
    And I should see "Feedback" in the ".tui-dataTableRow:nth-child(1) .tui-performUserActivityListTableItem__overview-type" "css_element"
    And I should see "Not started" in the ".tui-dataTableRow:nth-child(1) .tui-performUserActivityListTableItem__progress-status" "css_element"
    And I should see "Activity is not started" in the ".tui-dataTableRow:nth-child(1) .tui-performUserActivityListTableItem__details-overallProgress" "css_element"
    # Second row
    And I should see "Multiple section Activity" in the ".tui-dataTableRow:nth-child(2) .tui-performUserActivityListTableItem__title" "css_element"
    And I should see "##today##j F Y##" in the ".tui-dataTableRow:nth-child(2) .tui-performUserActivityListTableItem__overview-created" "css_element"
    And I should see "Appraisal" in the ".tui-dataTableRow:nth-child(2) .tui-performUserActivityListTableItem__overview-type" "css_element"
    And I should see "Not started" in the ".tui-dataTableRow:nth-child(2) .tui-performUserActivityListTableItem__progress-status" "css_element"
    And I should see "Activity is not started" in the ".tui-dataTableRow:nth-child(2) .tui-performUserActivityListTableItem__details-overallProgress" "css_element"
    # Third row
    And I should see "Anonymous responses - Multiple section Activity" in the ".tui-dataTableRow:nth-child(3) .tui-performUserActivityListTableItem__title" "css_element"
    And I should see "##today##j F Y##" in the ".tui-dataTableRow:nth-child(3) .tui-performUserActivityListTableItem__overview-created" "css_element"
    And I should see "Appraisal" in the ".tui-dataTableRow:nth-child(3) .tui-performUserActivityListTableItem__overview-type" "css_element"
    And I should see "Not started" in the ".tui-dataTableRow:nth-child(3) .tui-performUserActivityListTableItem__progress-status" "css_element"
    And I should see "Activity is in progress" in the ".tui-dataTableRow:nth-child(3) .tui-performUserActivityListTableItem__details-overallProgress" "css_element"

    When I click on "View details" "button" in the ".tui-dataTableRow:nth-child(3)" "css_element"
    Then I should see "Section anon 1" in the tui modal
    And I should see "Section anon 2" in the tui modal
    And I should see "Section anon 3" in the tui modal

    And I should see "1" rows in the tui datatable in the ".tui-progressTrackerNav__item:nth-child(3)" "css_element"

    And I should see "Subject" in the ".tui-progressTrackerNav__item:nth-child(3) .tui-dataTable:nth-child(1) .tui-dataTableRowHeader" "css_element"
    And I should see "You" in the ".tui-progressTrackerNav__item:nth-child(3) .tui-dataTable:nth-child(1) .tui-dataTableRow:nth-child(1) .tui-dataTableCell:nth-child(1)" "css_element"
    And I should see "Not started" in the ".tui-progressTrackerNav__item:nth-child(3) .tui-dataTable:nth-child(1) .tui-dataTableRow:nth-child(1) .tui-dataTableCell:nth-child(2)" "css_element"

    And "View section" "link" should not exist in the ".tui-progressTrackerNav__item:nth-child(1) .tui-progressTrackerNav__itemContent" "css_element"
    And "View section" "link" should not exist in the ".tui-progressTrackerNav__item:nth-child(2) .tui-progressTrackerNav__itemContent" "css_element"
    And I should see "Section anon 1" in the ".tui-progressTrackerNav__item:nth-child(1) .tui-progressTrackerNav__itemContent" "css_element"
    And I should see "Section anon 2" in the ".tui-progressTrackerNav__item:nth-child(2) .tui-progressTrackerNav__itemContent" "css_element"
    And "View section" "link" should exist in the ".tui-progressTrackerNav__item:nth-child(3) .tui-progressTrackerNav__itemContent" "css_element"

    And I should see "Other anonymous respondents (18)" in the ".tui-progressTrackerNav__item:nth-child(1) .tui-dataTable:nth-child(1) .tui-dataTableRowHeader" "css_element"
    And I should see "1 completed" in the ".tui-progressTrackerNav__item:nth-child(1) .tui-dataTable:nth-child(1) .tui-dataTableRow:nth-child(1) .tui-dataTableCell:nth-child(1)" "css_element"

    And I should see "Other anonymous respondents (9)" in the ".tui-progressTrackerNav__item:nth-child(2) .tui-dataTable:nth-child(1) .tui-dataTableRowHeader" "css_element"
    And I should see "0 completed" in the ".tui-progressTrackerNav__item:nth-child(2) .tui-dataTable:nth-child(1) .tui-dataTableRow:nth-child(1) .tui-dataTableCell:nth-child(1)" "css_element"

    And I should see "Other anonymous respondents (0)" in the ".tui-progressTrackerNav__item:nth-child(3) .tui-dataTable:nth-child(2) .tui-dataTableRowHeader" "css_element"
    And I should see "All complete" in the ".tui-progressTrackerNav__item:nth-child(3) .tui-dataTable:nth-child(2) .tui-dataTableRow:nth-child(1) .tui-dataTableCell:nth-child(1)" "css_element"

    When I click on "View section" "link" in the ".tui-progressTrackerNav__item:nth-child(3) .tui-progressTrackerNav__itemContent" "css_element"
    Then I should see "Anonymous responses - Multiple section Activity" in the ".tui-pageHeading__title" "css_element"
    Then I should see "Section anon 3" in the ".tui-participantContent__sectionHeading-title" "css_element"
    And I should see "Question 3"
    And I should see perform activity relationship to user "yourself"

  Scenario: List and complete sections as the manager-appraiser with multiple sections with anonymous answers
    Given I log in as "manager-appraiser1"

    When I navigate to the outstanding perform activities list page
    And I click on "As Appraiser" "link"
    # First row
    Then I should see "Multiple section Activity" in the ".tui-dataTableRow:nth-child(1) .tui-performUserActivityListTableItem__title" "css_element"
    And I should see "##today##j F Y##" in the ".tui-dataTableRow:nth-child(1) .tui-performUserActivityListTableItem__overview-created" "css_element"
    And I should see "John One" in the ".tui-dataTableRow:nth-child(1) .tui-performUserActivityListTableItem__overview-subject" "css_element"
    And I should see "Appraisal" in the ".tui-dataTableRow:nth-child(1) .tui-performUserActivityListTableItem__overview-type" "css_element"
    And I should see "Not started" in the ".tui-dataTableRow:nth-child(1) .tui-performUserActivityListTableItem__progress-status" "css_element"
    And I should see "Activity is not started" in the ".tui-dataTableRow:nth-child(1) .tui-performUserActivityListTableItem__details-overallProgress" "css_element"
    # Second row
    And I should see "Anonymous responses - Multiple section Activity" in the ".tui-dataTableRow:nth-child(2) .tui-performUserActivityListTableItem__title" "css_element"
    And I should see "##today##j F Y##" in the ".tui-dataTableRow:nth-child(2) .tui-performUserActivityListTableItem__overview-created" "css_element"
    And I should see "John One" in the ".tui-dataTableRow:nth-child(2) .tui-performUserActivityListTableItem__overview-subject" "css_element"
    And I should see "Appraisal" in the ".tui-dataTableRow:nth-child(2) .tui-performUserActivityListTableItem__overview-type" "css_element"
    And I should see "Not started" in the ".tui-dataTableRow:nth-child(2) .tui-performUserActivityListTableItem__progress-status" "css_element"
    And I should see "Activity is not started" in the ".tui-dataTableRow:nth-child(2) .tui-performUserActivityListTableItem__details-overallProgress" "css_element"

    When I click on "View details" "button" in the ".tui-dataTableRow:nth-child(2)" "css_element"
    Then I should see "Section anon 1" in the tui modal
    And I should see "Section anon 2" in the tui modal
    And I should see "Section anon 3" in the tui modal

    And I should see "Manager" in the ".tui-progressTrackerNav__item:nth-child(1) .tui-dataTable:nth-child(1) .tui-dataTableRowHeader" "css_element"
    And I should see "You" in the ".tui-progressTrackerNav__item:nth-child(1) .tui-dataTable:nth-child(1) .tui-dataTableRow:nth-child(1) .tui-dataTableCell:nth-child(1)" "css_element"
    And I should see "Not started" in the ".tui-progressTrackerNav__item:nth-child(1) .tui-dataTable:nth-child(1) .tui-dataTableRow:nth-child(1) .tui-dataTableCell:nth-child(2)" "css_element"

    And I should see "Appraiser" in the ".tui-progressTrackerNav__item:nth-child(1) .tui-dataTable:nth-child(2) .tui-dataTableRowHeader" "css_element"
    And I should see "You" in the ".tui-progressTrackerNav__item:nth-child(1) .tui-dataTable:nth-child(2) .tui-dataTableRow:nth-child(1) .tui-dataTableCell:nth-child(1)" "css_element"
    And I should see "Not started" in the ".tui-progressTrackerNav__item:nth-child(1) .tui-dataTable:nth-child(2) .tui-dataTableRow:nth-child(1) .tui-dataTableCell:nth-child(2)" "css_element"

    And I should see "Manager" in the ".tui-progressTrackerNav__item:nth-child(2) .tui-dataTable:nth-child(1) .tui-dataTableRowHeader" "css_element"
    And I should see "You" in the ".tui-progressTrackerNav__item:nth-child(2) .tui-dataTable:nth-child(1) .tui-dataTableRow:nth-child(1) .tui-dataTableCell:nth-child(1)" "css_element"
    And I should see "Not started" in the ".tui-progressTrackerNav__item:nth-child(2) .tui-dataTable:nth-child(1) .tui-dataTableRow:nth-child(1) .tui-dataTableCell:nth-child(2)" "css_element"

    And I should see "Other anonymous respondents (16)" in the ".tui-progressTrackerNav__item:nth-child(1) .tui-dataTable:nth-child(3) .tui-dataTableRowHeader" "css_element"
    And I should see "0 completed" in the ".tui-progressTrackerNav__item:nth-child(1) .tui-dataTable:nth-child(3) .tui-dataTableRow:nth-child(1) .tui-dataTableCell:nth-child(1)" "css_element"

    And I should see "Other anonymous respondents (8)" in the ".tui-progressTrackerNav__item:nth-child(2) .tui-dataTable:nth-child(2) .tui-dataTableRowHeader" "css_element"
    And I should see "0 completed" in the ".tui-progressTrackerNav__item:nth-child(2) .tui-dataTable:nth-child(2) .tui-dataTableRow:nth-child(1) .tui-dataTableCell:nth-child(1)" "css_element"

    And I should see "Other anonymous respondents (1)" in the ".tui-progressTrackerNav__item:nth-child(3) .tui-dataTable:nth-child(1) .tui-dataTableRowHeader" "css_element"
    And I should see "0 completed" in the ".tui-progressTrackerNav__item:nth-child(3) .tui-dataTable:nth-child(1) .tui-dataTableRow:nth-child(1) .tui-dataTableCell:nth-child(1)" "css_element"

    And ".tui-progressTrackerNav__item:nth-child(3) .tui-performUserActivityListSection__data" "css_element" should not exist
    And "View section" "link" should exist in the ".tui-progressTrackerNav__item:nth-child(1) .tui-progressTrackerNav__itemContent" "css_element"
    And "View section" "link" should not exist in the ".tui-progressTrackerNav__item:nth-child(2) .tui-progressTrackerNav__itemContent" "css_element"
    And "View section" "link" should not exist in the ".tui-progressTrackerNav__item:nth-child(3) .tui-progressTrackerNav__itemContent" "css_element"
    And I should see "Section anon 3" in the ".tui-performUserActivityListSections" "css_element"

    When I click on "View section" "link" in the ".tui-progressTrackerNav__item:nth-child(1) .tui-progressTrackerNav__itemContent" "css_element"
    Then I should see "Anonymous responses - Multiple section Activity" in the ".tui-pageHeading__title" "css_element"
    And I should see "Section anon 1" in the ".tui-participantContent__sectionHeading-title" "css_element"
    And I should see "Question 1"
    And I should see perform activity relationship to user "Appraiser"
    When I click on "Performance activities" "link"
    And I click on "As Appraiser" "link"
    # First row
    Then I should see "Multiple section Activity" in the ".tui-dataTableRow:nth-child(1) .tui-performUserActivityListTableItem__title" "css_element"
    And I should see "##today##j F Y##" in the ".tui-dataTableRow:nth-child(1) .tui-performUserActivityListTableItem__overview-created" "css_element"
    And I should see "John One" in the ".tui-dataTableRow:nth-child(1) .tui-performUserActivityListTableItem__overview-subject" "css_element"
    And I should see "Appraisal" in the ".tui-dataTableRow:nth-child(1) .tui-performUserActivityListTableItem__overview-type" "css_element"
    And I should see "Not started" in the ".tui-dataTableRow:nth-child(1) .tui-performUserActivityListTableItem__progress-status" "css_element"
    And I should see "Activity is not started" in the ".tui-dataTableRow:nth-child(1) .tui-performUserActivityListTableItem__details-overallProgress" "css_element"
    # Second row
    And I should see "Anonymous responses - Multiple section Activity" in the ".tui-dataTableRow:nth-child(2) .tui-performUserActivityListTableItem__title" "css_element"
    And I should see "##today##j F Y##" in the ".tui-dataTableRow:nth-child(2) .tui-performUserActivityListTableItem__overview-created" "css_element"
    And I should see "John One" in the ".tui-dataTableRow:nth-child(2) .tui-performUserActivityListTableItem__overview-subject" "css_element"
    And I should see "Appraisal" in the ".tui-dataTableRow:nth-child(2) .tui-performUserActivityListTableItem__overview-type" "css_element"
    And I should see "In progress" in the ".tui-dataTableRow:nth-child(2) .tui-performUserActivityListTableItem__progress-status" "css_element"
    And I should see "Activity is in progress" in the ".tui-dataTableRow:nth-child(2) .tui-performUserActivityListTableItem__details-overallProgress" "css_element"

  Scenario: List and complete sections as the manager
    Given I log in as "manager-appraiser1"
    When I navigate to the outstanding perform activities list page
    And I click on "As Appraiser" "link"

    # First row
    Then I should see "Multiple section Activity" in the ".tui-dataTableRow:nth-child(1) .tui-performUserActivityListTableItem__title" "css_element"
    And I should see "##today##j F Y##" in the ".tui-dataTableRow:nth-child(1) .tui-performUserActivityListTableItem__overview-created" "css_element"
    And I should see "John One" in the ".tui-dataTableRow:nth-child(1) .tui-performUserActivityListTableItem__overview-subject" "css_element"
    And I should see "Appraisal" in the ".tui-dataTableRow:nth-child(1) .tui-performUserActivityListTableItem__overview-type" "css_element"
    And I should see "Not started" in the ".tui-dataTableRow:nth-child(1) .tui-performUserActivityListTableItem__progress-status" "css_element"
    And I should see "Activity is not started" in the ".tui-dataTableRow:nth-child(1) .tui-performUserActivityListTableItem__details-overallProgress" "css_element"
    # Second row
    And I should see "Anonymous responses - Multiple section Activity" in the ".tui-dataTableRow:nth-child(2) .tui-performUserActivityListTableItem__title" "css_element"
    And I should see "##today##j F Y##" in the ".tui-dataTableRow:nth-child(2) .tui-performUserActivityListTableItem__overview-created" "css_element"
    And I should see "John One" in the ".tui-dataTableRow:nth-child(2) .tui-performUserActivityListTableItem__overview-subject" "css_element"
    And I should see "Appraisal" in the ".tui-dataTableRow:nth-child(2) .tui-performUserActivityListTableItem__overview-type" "css_element"
    And I should see "Not started" in the ".tui-dataTableRow:nth-child(2) .tui-performUserActivityListTableItem__progress-status" "css_element"
    And I should see "Activity is not started" in the ".tui-dataTableRow:nth-child(2) .tui-performUserActivityListTableItem__details-overallProgress" "css_element"

    When I click on "View details" "button" in the ".tui-dataTableRow:nth-child(1)" "css_element"
    Then I should see "Section 1" in the tui modal
    And I should see "Section 2" in the tui modal
    And I should see "Section 3" in the tui modal

    Then I should see "Subject" in the ".tui-progressTrackerNav__item:nth-child(1) .tui-dataTable:nth-child(1) .tui-dataTableRowHeader" "css_element"
    And I should see "John One" in the ".tui-progressTrackerNav__item:nth-child(1) .tui-dataTable:nth-child(1) .tui-dataTableRow:nth-child(1) .tui-dataTableCell:nth-child(1)" "css_element"
    And I should see "Not started" in the ".tui-progressTrackerNav__item:nth-child(1) .tui-dataTable:nth-child(1) .tui-dataTableRow:nth-child(1) .tui-dataTableCell:nth-child(2)" "css_element"

    And I should see "Manager" in the ".tui-progressTrackerNav__item:nth-child(1) .tui-dataTable:nth-child(2) .tui-dataTableRowHeader" "css_element"
    And I should see "David Two" in the ".tui-progressTrackerNav__item:nth-child(1) .tui-dataTable:nth-child(2) .tui-dataTableRow:nth-child(1) .tui-dataTableCell:nth-child(1)" "css_element"
    And I should see "Not started" in the ".tui-progressTrackerNav__item:nth-child(1) .tui-dataTable:nth-child(2) .tui-dataTableRow:nth-child(1) .tui-dataTableCell:nth-child(2)" "css_element"
    And I should see "You" in the ".tui-progressTrackerNav__item:nth-child(1) .tui-dataTable:nth-child(2) .tui-dataTableRow:nth-child(2) .tui-dataTableCell:nth-child(1)" "css_element"
    And I should see "Not started" in the ".tui-progressTrackerNav__item:nth-child(1) .tui-dataTable:nth-child(2) .tui-dataTableRow:nth-child(2) .tui-dataTableCell:nth-child(2)" "css_element"

    And I should see "Appraiser" in the ".tui-progressTrackerNav__item:nth-child(1) .tui-dataTable:nth-child(4) .tui-dataTableRowHeader" "css_element"
    And I should see "Harry Three" in the ".tui-progressTrackerNav__item:nth-child(1) .tui-dataTable:nth-child(4) .tui-dataTableRow:nth-child(1) .tui-dataTableCell:nth-child(1)" "css_element"
    And I should see "Not started" in the ".tui-progressTrackerNav__item:nth-child(1) .tui-dataTable:nth-child(4) .tui-dataTableRow:nth-child(1) .tui-dataTableCell:nth-child(2)" "css_element"
    And I should see "You" in the ".tui-progressTrackerNav__item:nth-child(1) .tui-dataTable:nth-child(4) .tui-dataTableRow:nth-child(2) .tui-dataTableCell:nth-child(1)" "css_element"
    And I should see "Not started" in the ".tui-progressTrackerNav__item:nth-child(1) .tui-dataTable:nth-child(4) .tui-dataTableRow:nth-child(2) .tui-dataTableCell:nth-child(2)" "css_element"

    And I should see "Manager" in the ".tui-progressTrackerNav__item:nth-child(2) .tui-dataTable:nth-child(1) .tui-dataTableRowHeader" "css_element"
    And I should see "David Two" in the ".tui-progressTrackerNav__item:nth-child(2) .tui-dataTable:nth-child(1) .tui-dataTableRow:nth-child(1) .tui-dataTableCell:nth-child(1)" "css_element"
    And I should see "Not started" in the ".tui-progressTrackerNav__item:nth-child(2) .tui-dataTable:nth-child(1) .tui-dataTableRow:nth-child(1) .tui-dataTableCell:nth-child(2)" "css_element"
    And I should see "You" in the ".tui-progressTrackerNav__item:nth-child(2) .tui-dataTable:nth-child(1) .tui-dataTableRow:nth-child(2) .tui-dataTableCell:nth-child(1)" "css_element"
    And I should see "Not started" in the ".tui-progressTrackerNav__item:nth-child(2) .tui-dataTable:nth-child(1) .tui-dataTableRow:nth-child(2) .tui-dataTableCell:nth-child(2)" "css_element"

    And I should see "Subject" in the ".tui-progressTrackerNav__item:nth-child(3) .tui-dataTable:nth-child(1) .tui-dataTableRowHeader" "css_element"
    And I should see "John One" in the ".tui-progressTrackerNav__item:nth-child(3) .tui-dataTable:nth-child(1) .tui-dataTableRow:nth-child(1) .tui-dataTableCell:nth-child(1)" "css_element"
    And I should see "Not started" in the ".tui-progressTrackerNav__item:nth-child(3) .tui-dataTable:nth-child(1) .tui-dataTableRow:nth-child(1) .tui-dataTableCell:nth-child(2)" "css_element"

    And "View section" "link" should exist in the ".tui-progressTrackerNav__item:nth-child(1) .tui-progressTrackerNav__itemContent" "css_element"
    And "View section" "link" should not exist in the ".tui-progressTrackerNav__item:nth-child(2) .tui-progressTrackerNav__itemContent" "css_element"
    And "View section" "link" should not exist in the ".tui-progressTrackerNav__item:nth-child(3) .tui-progressTrackerNav__itemContent" "css_element"
    And I should see "Section 3" in the ".tui-progressTrackerNav__item:nth-child(3) .tui-progressTrackerNav__itemContent" "css_element"
    When I click on "View section" "link" in the ".tui-progressTrackerNav__item:nth-child(1) .tui-progressTrackerNav__itemContent" "css_element"
    Then I should see "Multiple section Activity" in the ".tui-pageHeading__title" "css_element"
    And I should see "Section 1" in the ".tui-participantContent__sectionHeading-title" "css_element"
    And I should see "Question 1"
    And I should see perform activity relationship to user "Appraiser"
    When I click on "Performance activities" "link"
    And I click on "As Manager" "link"

    # First row
    Then I should see "Single section Activity" in the ".tui-dataTableRow:nth-child(1) .tui-performUserActivityListTableItem__title" "css_element"
    And I should see "##today##j F Y##" in the ".tui-dataTableRow:nth-child(1) .tui-performUserActivityListTableItem__overview-created" "css_element"
    And I should see "John One" in the ".tui-dataTableRow:nth-child(1) .tui-performUserActivityListTableItem__overview-subject" "css_element"
    And I should see "Feedback" in the ".tui-dataTableRow:nth-child(1) .tui-performUserActivityListTableItem__overview-type" "css_element"
    And I should see "Not started" in the ".tui-dataTableRow:nth-child(1) .tui-performUserActivityListTableItem__progress-status" "css_element"
    And I should see "Activity is not started" in the ".tui-dataTableRow:nth-child(1) .tui-performUserActivityListTableItem__details-overallProgress" "css_element"
    # Second row
    And I should see "Multiple section Activity" in the ".tui-dataTableRow:nth-child(2) .tui-performUserActivityListTableItem__title" "css_element"
    And I should see "##today##j F Y##" in the ".tui-dataTableRow:nth-child(2) .tui-performUserActivityListTableItem__overview-created" "css_element"
    And I should see "John One" in the ".tui-dataTableRow:nth-child(2) .tui-performUserActivityListTableItem__overview-subject" "css_element"
    And I should see "Appraisal" in the ".tui-dataTableRow:nth-child(2) .tui-performUserActivityListTableItem__overview-type" "css_element"
    And I should see "Not started" in the ".tui-dataTableRow:nth-child(2) .tui-performUserActivityListTableItem__progress-status" "css_element"
    And I should see "Activity is in progress" in the ".tui-dataTableRow:nth-child(2) .tui-performUserActivityListTableItem__details-overallProgress" "css_element"
    # Third row
    And I should see "Anonymous responses - Multiple section Activity" in the ".tui-dataTableRow:nth-child(3) .tui-performUserActivityListTableItem__title" "css_element"
    And I should see "##today##j F Y##" in the ".tui-dataTableRow:nth-child(3) .tui-performUserActivityListTableItem__overview-created" "css_element"
    And I should see "John One" in the ".tui-dataTableRow:nth-child(3) .tui-performUserActivityListTableItem__overview-subject" "css_element"
    And I should see "Appraisal" in the ".tui-dataTableRow:nth-child(3) .tui-performUserActivityListTableItem__overview-type" "css_element"
    And I should see "Not started" in the ".tui-dataTableRow:nth-child(3) .tui-performUserActivityListTableItem__progress-status" "css_element"
    And I should see "Activity is not started" in the ".tui-dataTableRow:nth-child(3) .tui-performUserActivityListTableItem__details-overallProgress" "css_element"

    When I click on "View details" "button" in the ".tui-dataTableRow:nth-child(2)" "css_element"

    Then I should see "Subject" in the ".tui-progressTrackerNav__item:nth-child(1) .tui-dataTable:nth-child(1) .tui-dataTableRowHeader" "css_element"
    And I should see "John One" in the ".tui-progressTrackerNav__item:nth-child(1) .tui-dataTable:nth-child(1) .tui-dataTableRow:nth-child(1) .tui-dataTableCell:nth-child(1)" "css_element"
    And I should see "Not started" in the ".tui-progressTrackerNav__item:nth-child(1) .tui-dataTable:nth-child(1) .tui-dataTableRow:nth-child(1) .tui-dataTableCell:nth-child(2)" "css_element"

    And I should see "Manager" in the ".tui-progressTrackerNav__item:nth-child(1) .tui-dataTable:nth-child(2) .tui-dataTableRowHeader" "css_element"
    And I should see "David Two" in the ".tui-progressTrackerNav__item:nth-child(1) .tui-dataTable:nth-child(2) .tui-dataTableRow:nth-child(1) .tui-dataTableCell:nth-child(1)" "css_element"
    And I should see "Not started" in the ".tui-progressTrackerNav__item:nth-child(1) .tui-dataTable:nth-child(2) .tui-dataTableRow:nth-child(1) .tui-dataTableCell:nth-child(2)" "css_element"
    And I should see "You" in the ".tui-progressTrackerNav__item:nth-child(1) .tui-dataTable:nth-child(2) .tui-dataTableRow:nth-child(2) .tui-dataTableCell:nth-child(1)" "css_element"
    And I should see "Not started" in the ".tui-progressTrackerNav__item:nth-child(1) .tui-dataTable:nth-child(2) .tui-dataTableRow:nth-child(2) .tui-dataTableCell:nth-child(2)" "css_element"

    And I should see "Appraiser" in the ".tui-progressTrackerNav__item:nth-child(1) .tui-dataTable:nth-child(4) .tui-dataTableRowHeader" "css_element"
    And I should see "Harry Three" in the ".tui-progressTrackerNav__item:nth-child(1) .tui-dataTable:nth-child(4) .tui-dataTableRow:nth-child(1) .tui-dataTableCell:nth-child(1)" "css_element"
    And I should see "Not started" in the ".tui-progressTrackerNav__item:nth-child(1) .tui-dataTable:nth-child(4) .tui-dataTableRow:nth-child(1) .tui-dataTableCell:nth-child(2)" "css_element"
    And I should see "You" in the ".tui-progressTrackerNav__item:nth-child(1) .tui-dataTable:nth-child(4) .tui-dataTableRow:nth-child(2) .tui-dataTableCell:nth-child(1)" "css_element"
    And I should see "In progress" in the ".tui-progressTrackerNav__item:nth-child(1) .tui-dataTable:nth-child(4) .tui-dataTableRow:nth-child(2) .tui-dataTableCell:nth-child(2)" "css_element"

    And I should see "Manager" in the ".tui-progressTrackerNav__item:nth-child(2) .tui-dataTable:nth-child(1) .tui-dataTableRowHeader" "css_element"
    And I should see "David Two" in the ".tui-progressTrackerNav__item:nth-child(2) .tui-dataTable:nth-child(1) .tui-dataTableRow:nth-child(1) .tui-dataTableCell:nth-child(1)" "css_element"
    And I should see "Not started" in the ".tui-progressTrackerNav__item:nth-child(2) .tui-dataTable:nth-child(1) .tui-dataTableRow:nth-child(1) .tui-dataTableCell:nth-child(2)" "css_element"
    And I should see "You" in the ".tui-progressTrackerNav__item:nth-child(2) .tui-dataTable:nth-child(1) .tui-dataTableRow:nth-child(2) .tui-dataTableCell:nth-child(1)" "css_element"
    And I should see "Not started" in the ".tui-progressTrackerNav__item:nth-child(2) .tui-dataTable:nth-child(1) .tui-dataTableRow:nth-child(2) .tui-dataTableCell:nth-child(2)" "css_element"

    When I click on "View section" "link" in the ".tui-progressTrackerNav__item:nth-child(2) .tui-progressTrackerNav__itemContent" "css_element"
    Then I should see "Multiple section Activity" in the ".tui-pageHeading__title" "css_element"
    And I should see "Section 2" in the ".tui-participantContent__sectionHeading-title" "css_element"
    And I should see "Question 2"
    And I should see perform activity relationship to user "Manager"
    When I click on "Complete section" "button"
    And I confirm the tui confirmation modal
    And I close the tui notification toast
    And I click on "Performance activities" "link"
    Then I click on "As Manager" "link"
    # First row
    Then I should see "Single section Activity" in the ".tui-dataTableRow:nth-child(1) .tui-performUserActivityListTableItem__title" "css_element"
    And I should see "##today##j F Y##" in the ".tui-dataTableRow:nth-child(1) .tui-performUserActivityListTableItem__overview-created" "css_element"
    And I should see "John One" in the ".tui-dataTableRow:nth-child(1) .tui-performUserActivityListTableItem__overview-subject" "css_element"
    And I should see "Feedback" in the ".tui-dataTableRow:nth-child(1) .tui-performUserActivityListTableItem__overview-type" "css_element"
    And I should see "Not started" in the ".tui-dataTableRow:nth-child(1) .tui-performUserActivityListTableItem__progress-status" "css_element"
    And I should see "Activity is not started" in the ".tui-dataTableRow:nth-child(1) .tui-performUserActivityListTableItem__details-overallProgress" "css_element"
    # Second row
    And I should see "Multiple section Activity" in the ".tui-dataTableRow:nth-child(2) .tui-performUserActivityListTableItem__title" "css_element"
    And I should see "##today##j F Y##" in the ".tui-dataTableRow:nth-child(2) .tui-performUserActivityListTableItem__overview-created" "css_element"
    And I should see "John One" in the ".tui-dataTableRow:nth-child(2) .tui-performUserActivityListTableItem__overview-subject" "css_element"
    And I should see "Appraisal" in the ".tui-dataTableRow:nth-child(2) .tui-performUserActivityListTableItem__overview-type" "css_element"
    And I should see "In progress" in the ".tui-dataTableRow:nth-child(2) .tui-performUserActivityListTableItem__progress-status" "css_element"
    And I should see "Activity is in progress" in the ".tui-dataTableRow:nth-child(2) .tui-performUserActivityListTableItem__details-overallProgress" "css_element"
    # Third row
    And I should see "Anonymous responses - Multiple section Activity" in the ".tui-dataTableRow:nth-child(3) .tui-performUserActivityListTableItem__title" "css_element"
    And I should see "##today##j F Y##" in the ".tui-dataTableRow:nth-child(3) .tui-performUserActivityListTableItem__overview-created" "css_element"
    And I should see "John One" in the ".tui-dataTableRow:nth-child(3) .tui-performUserActivityListTableItem__overview-subject" "css_element"
    And I should see "Appraisal" in the ".tui-dataTableRow:nth-child(3) .tui-performUserActivityListTableItem__overview-type" "css_element"
    And I should see "Not started" in the ".tui-dataTableRow:nth-child(3) .tui-performUserActivityListTableItem__progress-status" "css_element"
    And I should see "Activity is not started" in the ".tui-dataTableRow:nth-child(3) .tui-performUserActivityListTableItem__details-overallProgress" "css_element"

    When I click on "View details" "button" in the ".tui-dataTableRow:nth-child(2)" "css_element"

    Then I should see "Subject" in the ".tui-progressTrackerNav__item:nth-child(1) .tui-dataTable:nth-child(1) .tui-dataTableRowHeader" "css_element"
    And I should see "John One" in the ".tui-progressTrackerNav__item:nth-child(1) .tui-dataTable:nth-child(1) .tui-dataTableRow:nth-child(1) .tui-dataTableCell:nth-child(1)" "css_element"
    And I should see "Not started" in the ".tui-progressTrackerNav__item:nth-child(1) .tui-dataTable:nth-child(1) .tui-dataTableRow:nth-child(1) .tui-dataTableCell:nth-child(2)" "css_element"

    And I should see "Manager" in the ".tui-progressTrackerNav__item:nth-child(1) .tui-dataTable:nth-child(2) .tui-dataTableRowHeader" "css_element"
    And I should see "David Two" in the ".tui-progressTrackerNav__item:nth-child(1) .tui-dataTable:nth-child(2) .tui-dataTableRow:nth-child(1) .tui-dataTableCell:nth-child(1)" "css_element"
    And I should see "Not started" in the ".tui-progressTrackerNav__item:nth-child(1) .tui-dataTable:nth-child(2) .tui-dataTableRow:nth-child(1) .tui-dataTableCell:nth-child(2)" "css_element"
    And I should see "You" in the ".tui-progressTrackerNav__item:nth-child(1) .tui-dataTable:nth-child(2) .tui-dataTableRow:nth-child(2) .tui-dataTableCell:nth-child(1)" "css_element"
    And I should see "Not started" in the ".tui-progressTrackerNav__item:nth-child(1) .tui-dataTable:nth-child(2) .tui-dataTableRow:nth-child(2) .tui-dataTableCell:nth-child(2)" "css_element"

    And I should see "Appraiser" in the ".tui-progressTrackerNav__item:nth-child(1) .tui-dataTable:nth-child(4) .tui-dataTableRowHeader" "css_element"
    And I should see "Harry Three" in the ".tui-progressTrackerNav__item:nth-child(1) .tui-dataTable:nth-child(4) .tui-dataTableRow:nth-child(1) .tui-dataTableCell:nth-child(1)" "css_element"
    And I should see "Not started" in the ".tui-progressTrackerNav__item:nth-child(1) .tui-dataTable:nth-child(4) .tui-dataTableRow:nth-child(1) .tui-dataTableCell:nth-child(2)" "css_element"
    And I should see "You" in the ".tui-progressTrackerNav__item:nth-child(1) .tui-dataTable:nth-child(4) .tui-dataTableRow:nth-child(2) .tui-dataTableCell:nth-child(1)" "css_element"
    And I should see "In progress" in the ".tui-progressTrackerNav__item:nth-child(1) .tui-dataTable:nth-child(4) .tui-dataTableRow:nth-child(2) .tui-dataTableCell:nth-child(2)" "css_element"

    And I should see "Manager" in the ".tui-progressTrackerNav__item:nth-child(2) .tui-dataTable:nth-child(1) .tui-dataTableRowHeader" "css_element"
    And I should see "David Two" in the ".tui-progressTrackerNav__item:nth-child(2) .tui-dataTable:nth-child(1) .tui-dataTableRow:nth-child(1) .tui-dataTableCell:nth-child(1)" "css_element"
    And I should see "Not started" in the ".tui-progressTrackerNav__item:nth-child(2) .tui-dataTable:nth-child(1) .tui-dataTableRow:nth-child(1) .tui-dataTableCell:nth-child(2)" "css_element"
    And I should see "You" in the ".tui-progressTrackerNav__item:nth-child(2) .tui-dataTable:nth-child(1) .tui-dataTableRow:nth-child(2) .tui-dataTableCell:nth-child(1)" "css_element"
    And I should see "Complete" in the ".tui-progressTrackerNav__item:nth-child(2) .tui-dataTable:nth-child(1) .tui-dataTableRow:nth-child(2) .tui-dataTableCell:nth-child(2)" "css_element"
    And ".tui-performUserActivityListSection__progressIcon-complete" "css_element" should exist in the ".tui-progressTrackerNav__item:nth-child(2) .tui-dataTable:nth-child(1) .tui-dataTableRow:nth-child(2) .tui-dataTableCell:nth-child(2)" "css_element"
    And I should see "Closed" in the ".tui-progressTrackerNav__item:nth-child(2) .tui-dataTable:nth-child(1) .tui-dataTableRow:nth-child(2) .tui-dataTableCell:nth-child(2) .tui-performUserActivityListSection__closed" "css_element"

    And I should see "Subject" in the ".tui-progressTrackerNav__item:nth-child(3) .tui-dataTable:nth-child(1) .tui-dataTableRowHeader" "css_element"
    And I should see "John One" in the ".tui-progressTrackerNav__item:nth-child(3) .tui-dataTable:nth-child(1) .tui-dataTableRow:nth-child(1) .tui-dataTableCell:nth-child(1)" "css_element"
    And I should see "Not started" in the ".tui-progressTrackerNav__item:nth-child(3) .tui-dataTable:nth-child(1) .tui-dataTableRow:nth-child(1) .tui-dataTableCell:nth-child(2)" "css_element"

  Scenario: Show subject instance created date, overdue and closed icon on user activity list
    Given I log in as "john"
    When I navigate to the outstanding perform activities list page

    # First row
    Then I should see "Single section Activity" in the ".tui-dataTableRow:nth-child(1) .tui-performUserActivityListTableItem__title" "css_element"
    And I should see "##today##j F Y##" in the ".tui-dataTableRow:nth-child(1) .tui-performUserActivityListTableItem__overview-created" "css_element"
    And I should see "Feedback" in the ".tui-dataTableRow:nth-child(1) .tui-performUserActivityListTableItem__overview-type" "css_element"
    And I should see "Not started" in the ".tui-dataTableRow:nth-child(1) .tui-performUserActivityListTableItem__progress-status" "css_element"
    And I should see "Activity is not started" in the ".tui-dataTableRow:nth-child(1) .tui-performUserActivityListTableItem__details-overallProgress" "css_element"
    # Second row
    And I should see "Multiple section Activity" in the ".tui-dataTableRow:nth-child(2) .tui-performUserActivityListTableItem__title" "css_element"
    And I should see "##today##j F Y##" in the ".tui-dataTableRow:nth-child(2) .tui-performUserActivityListTableItem__overview-created" "css_element"
    And I should see "Appraisal" in the ".tui-dataTableRow:nth-child(2) .tui-performUserActivityListTableItem__overview-type" "css_element"
    And I should see "Not started" in the ".tui-dataTableRow:nth-child(2) .tui-performUserActivityListTableItem__progress-status" "css_element"
    And I should see "Activity is not started" in the ".tui-dataTableRow:nth-child(2) .tui-performUserActivityListTableItem__details-overallProgress" "css_element"
    # Third row
    And I should see "Anonymous responses - Multiple section Activity" in the ".tui-dataTableRow:nth-child(3) .tui-performUserActivityListTableItem__title" "css_element"
    And I should see "##today##j F Y##" in the ".tui-dataTableRow:nth-child(3) .tui-performUserActivityListTableItem__overview-created" "css_element"
    And I should see "Appraisal" in the ".tui-dataTableRow:nth-child(3) .tui-performUserActivityListTableItem__overview-type" "css_element"
    And I should see "Not started" in the ".tui-dataTableRow:nth-child(3) .tui-performUserActivityListTableItem__progress-status" "css_element"
    And I should see "Activity is not started" in the ".tui-dataTableRow:nth-child(3) .tui-performUserActivityListTableItem__details-overallProgress" "css_element"

    When I click on "View details" "button" in the ".tui-dataTableRow:nth-child(1)" "css_element"
    # Display subject instance created date
    Then I should see "##today##j F Y##" in the ".tui-performUserActivityListSectionsModal__overview-created" "css_element"
    And I close the tui modal

    When I click on "View details" "button" in the ".tui-dataTableRow:nth-child(2)" "css_element"
    # Subject instance created date and due date display.
    Then I should see "##today##j F Y##" in the ".tui-performUserActivityListSectionsModal__overview-created" "css_element"
    And I close the tui modal

    # Complete Single section activity
    And I click on "Single section Activity" "link"
    And I click on "Submit" "button"
    And I confirm the tui confirmation modal
    And I should see "Section submitted" in the tui success notification toast

    # Section 2 in progress
    When I click on "Performance activities" "link"
    And I click on "View details" "button" in the ".tui-dataTableRow:nth-child(2)" "css_element"
    And I click on "View section" "link" in the ".tui-progressTrackerNav__item:nth-child(1) .tui-progressTrackerNav__itemContent" "css_element"
    And I click on "Performance activities" "link"

    # Complete Section 3 of multiple section activity
    When I click on "View details" "button" in the ".tui-dataTableRow:nth-child(2)" "css_element"
    And I click on "View section" "link" in the ".tui-progressTrackerNav__item:nth-child(3) .tui-progressTrackerNav__itemContent .tui-collapsible__header" "css_element"
    And I click on "Complete section" "button"
    And I confirm the tui confirmation modal
    And I close the tui notification toast
    And I click on "Performance activities" "link"

    # Overdue lozenge shows on due subject/participant instances
    And Subject instances for "track 1" track are due "##yesterday##"
    And Subject instances for "track 2" track are due "##yesterday##"
    And I reload the page

    # Closed participant instance shows the closed icon
    # First row
    Then I should see "Single section Activity" in the ".tui-dataTableRow:nth-child(1) .tui-performUserActivityListTableItem__title" "css_element"
    And I should see "##today##j F Y##" in the ".tui-dataTableRow:nth-child(1) .tui-performUserActivityListTableItem__overview-created" "css_element"
    And I should see "Feedback" in the ".tui-dataTableRow:nth-child(1) .tui-performUserActivityListTableItem__overview-type" "css_element"
    And I should see "Complete" in the ".tui-dataTableRow:nth-child(1) .tui-performUserActivityListTableItem__progress-status" "css_element"
    And I should see "Activity is overdue" in the ".tui-dataTableRow:nth-child(1) .tui-performUserActivityListTableItem__details-overallProgress" "css_element"
    # Second row
    And I should see "Multiple section Activity" in the ".tui-dataTableRow:nth-child(2) .tui-performUserActivityListTableItem__title" "css_element"
    And I should see "##today##j F Y##" in the ".tui-dataTableRow:nth-child(2) .tui-performUserActivityListTableItem__overview-created" "css_element"
    And I should see "Appraisal" in the ".tui-dataTableRow:nth-child(2) .tui-performUserActivityListTableItem__overview-type" "css_element"
    And I should see "Overdue" in the ".tui-dataTableRow:nth-child(2) .tui-performUserActivityListTableItem__progress-status" "css_element"
    And I should see "Activity is overdue" in the ".tui-dataTableRow:nth-child(2) .tui-performUserActivityListTableItem__details-overallProgress" "css_element"
    # Third row
    And I should see "Anonymous responses - Multiple section Activity" in the ".tui-dataTableRow:nth-child(3) .tui-performUserActivityListTableItem__title" "css_element"
    And I should see "##today##j F Y##" in the ".tui-dataTableRow:nth-child(3) .tui-performUserActivityListTableItem__overview-created" "css_element"
    And I should see "Appraisal" in the ".tui-dataTableRow:nth-child(3) .tui-performUserActivityListTableItem__overview-type" "css_element"
    And I should see "Not started" in the ".tui-dataTableRow:nth-child(3) .tui-performUserActivityListTableItem__progress-status" "css_element"
    And I should see "Activity is not started" in the ".tui-dataTableRow:nth-child(3) .tui-performUserActivityListTableItem__details-overallProgress" "css_element"

    # Overdue lozenge shows on non-completed participant sections
    When I click on "View details" "button" in the ".tui-dataTableRow:nth-child(1)" "css_element"

    Then I should see "Subject" in the ".tui-performUserActivityListSection:nth-child(1) .tui-dataTable:nth-child(1) .tui-dataTableRowHeader" "css_element"
    And I should see "You" in the ".tui-performUserActivityListSection:nth-child(1) .tui-dataTable:nth-child(1) .tui-dataTableRow:nth-child(1) .tui-dataTableCell:nth-child(1)" "css_element"
    And I should see "Complete" in the ".tui-performUserActivityListSection:nth-child(1) .tui-dataTable:nth-child(1) .tui-dataTableRow:nth-child(1) .tui-dataTableCell:nth-child(2)" "css_element"
    And ".tui-performUserActivityListSection__progressIcon-complete" "css_element" should exist in the ".tui-performUserActivityListSection:nth-child(1) .tui-dataTable:nth-child(1) .tui-dataTableRow:nth-child(1) .tui-dataTableCell:nth-child(2)" "css_element"

    And I should see "Manager" in the ".tui-performUserActivityListSection:nth-child(1) .tui-dataTable:nth-child(2) .tui-dataTableRowHeader" "css_element"
    And I should see "David Two" in the ".tui-performUserActivityListSection:nth-child(1) .tui-dataTable:nth-child(2) .tui-dataTableRow:nth-child(1) .tui-dataTableCell:nth-child(1)" "css_element"
    And I should see "Not started" in the ".tui-performUserActivityListSection:nth-child(1) .tui-dataTable:nth-child(2) .tui-dataTableRow:nth-child(1) .tui-dataTableCell:nth-child(2)" "css_element"
    And ".tui-performUserActivityListSection__progressIcon-overdue" "css_element" should exist in the ".tui-performUserActivityListSection:nth-child(1) .tui-dataTable:nth-child(2) .tui-dataTableRow:nth-child(1) .tui-dataTableCell:nth-child(2)" "css_element"
    And I should see "combined a" in the ".tui-performUserActivityListSection:nth-child(1) .tui-dataTable:nth-child(2) .tui-dataTableRow:nth-child(2) .tui-dataTableCell:nth-child(1)" "css_element"
    And I should see "Not started" in the ".tui-performUserActivityListSection:nth-child(1) .tui-dataTable:nth-child(2) .tui-dataTableRow:nth-child(2) .tui-dataTableCell:nth-child(2)" "css_element"
    And ".tui-performUserActivityListSection__progressIcon-overdue" "css_element" should exist in the ".tui-performUserActivityListSection:nth-child(1) .tui-dataTable:nth-child(2) .tui-dataTableRow:nth-child(2) .tui-dataTableCell:nth-child(2)" "css_element"

    And I close the tui modal

    # Uncompleted sections show overdue lozenge
    When I click on "View details" "button" in the ".tui-dataTableRow:nth-child(2)" "css_element"

    Then I should see "Subject" in the ".tui-progressTrackerNav__item:nth-child(1) .tui-dataTable:nth-child(1) .tui-dataTableRowHeader" "css_element"
    And I should see "You" in the ".tui-progressTrackerNav__item:nth-child(1) .tui-dataTable:nth-child(1) .tui-dataTableRow:nth-child(1) .tui-dataTableCell:nth-child(1)" "css_element"
    And I should see "In progress" in the ".tui-progressTrackerNav__item:nth-child(1) .tui-dataTable:nth-child(1) .tui-dataTableRow:nth-child(1) .tui-dataTableCell:nth-child(2)" "css_element"
    And ".tui-performUserActivityListSection__progressIcon-overdue" "css_element" should exist in the ".tui-progressTrackerNav__item:nth-child(1) .tui-dataTable:nth-child(1) .tui-dataTableRow:nth-child(1) .tui-dataTableCell:nth-child(2)" "css_element"

    And I should see "Manager" in the ".tui-progressTrackerNav__item:nth-child(1) .tui-dataTable:nth-child(2) .tui-dataTableRowHeader" "css_element"
    And I should see "David Two" in the ".tui-progressTrackerNav__item:nth-child(1) .tui-dataTable:nth-child(2) .tui-dataTableRow:nth-child(1) .tui-dataTableCell:nth-child(1)" "css_element"
    And I should see "Not started" in the ".tui-progressTrackerNav__item:nth-child(1) .tui-dataTable:nth-child(2) .tui-dataTableRow:nth-child(1) .tui-dataTableCell:nth-child(2)" "css_element"
    And ".tui-performUserActivityListSection__progressIcon-overdue" "css_element" should exist in the ".tui-progressTrackerNav__item:nth-child(1) .tui-dataTable:nth-child(2) .tui-dataTableRow:nth-child(1) .tui-dataTableCell:nth-child(2)" "css_element"
    And I should see "combined a" in the ".tui-progressTrackerNav__item:nth-child(1) .tui-dataTable:nth-child(2) .tui-dataTableRow:nth-child(2) .tui-dataTableCell:nth-child(1)" "css_element"
    And I should see "Not started" in the ".tui-progressTrackerNav__item:nth-child(1) .tui-dataTable:nth-child(2) .tui-dataTableRow:nth-child(2) .tui-dataTableCell:nth-child(2)" "css_element"
    And ".tui-performUserActivityListSection__progressIcon-overdue" "css_element" should exist in the ".tui-progressTrackerNav__item:nth-child(1) .tui-dataTable:nth-child(2) .tui-dataTableRow:nth-child(2) .tui-dataTableCell:nth-child(2)" "css_element"

    And I should see "Appraiser" in the ".tui-progressTrackerNav__item:nth-child(1) .tui-dataTable:nth-child(4) .tui-dataTableRowHeader" "css_element"
    And I should see "Harry Three" in the ".tui-progressTrackerNav__item:nth-child(1) .tui-dataTable:nth-child(4) .tui-dataTableRow:nth-child(1) .tui-dataTableCell:nth-child(1)" "css_element"
    And I should see "Not started" in the ".tui-progressTrackerNav__item:nth-child(1) .tui-dataTable:nth-child(4) .tui-dataTableRow:nth-child(1) .tui-dataTableCell:nth-child(2)" "css_element"
    And ".tui-performUserActivityListSection__progressIcon-overdue" "css_element" should exist in the ".tui-progressTrackerNav__item:nth-child(1) .tui-dataTable:nth-child(4) .tui-dataTableRow:nth-child(1) .tui-dataTableCell:nth-child(2)" "css_element"
    And I should see "combined a" in the ".tui-progressTrackerNav__item:nth-child(1) .tui-dataTable:nth-child(4) .tui-dataTableRow:nth-child(2) .tui-dataTableCell:nth-child(1)" "css_element"
    And I should see "Not started" in the ".tui-progressTrackerNav__item:nth-child(1) .tui-dataTable:nth-child(4) .tui-dataTableRow:nth-child(2) .tui-dataTableCell:nth-child(2)" "css_element"
    And ".tui-performUserActivityListSection__progressIcon-overdue" "css_element" should exist in the ".tui-progressTrackerNav__item:nth-child(1) .tui-dataTable:nth-child(4) .tui-dataTableRow:nth-child(2) .tui-dataTableCell:nth-child(2)" "css_element"

    And I should see "Manager" in the ".tui-progressTrackerNav__item:nth-child(2) .tui-dataTable:nth-child(1) .tui-dataTableRowHeader" "css_element"
    And I should see "David Two" in the ".tui-progressTrackerNav__item:nth-child(2) .tui-dataTable:nth-child(1) .tui-dataTableRow:nth-child(1) .tui-dataTableCell:nth-child(1)" "css_element"
    And I should see "Not started" in the ".tui-progressTrackerNav__item:nth-child(2) .tui-dataTable:nth-child(1) .tui-dataTableRow:nth-child(1) .tui-dataTableCell:nth-child(2)" "css_element"
    And ".tui-performUserActivityListSection__progressIcon-overdue" "css_element" should exist in the ".tui-progressTrackerNav__item:nth-child(2) .tui-dataTable:nth-child(1) .tui-dataTableRow:nth-child(1) .tui-dataTableCell:nth-child(2)" "css_element"
    And I should see "combined a" in the ".tui-progressTrackerNav__item:nth-child(2) .tui-dataTable:nth-child(1) .tui-dataTableRow:nth-child(2) .tui-dataTableCell:nth-child(1)" "css_element"
    And I should see "Not started" in the ".tui-progressTrackerNav__item:nth-child(2) .tui-dataTable:nth-child(1) .tui-dataTableRow:nth-child(2) .tui-dataTableCell:nth-child(2)" "css_element"
    And ".tui-performUserActivityListSection__progressIcon-overdue" "css_element" should exist in the ".tui-progressTrackerNav__item:nth-child(2) .tui-dataTable:nth-child(1) .tui-dataTableRow:nth-child(2) .tui-dataTableCell:nth-child(2)" "css_element"

    And I should see "Subject" in the ".tui-progressTrackerNav__item:nth-child(3) .tui-dataTable:nth-child(1) .tui-dataTableRowHeader" "css_element"
    And I should see "You" in the ".tui-progressTrackerNav__item:nth-child(3) .tui-dataTable:nth-child(1) .tui-dataTableRow:nth-child(1) .tui-dataTableCell:nth-child(1)" "css_element"
    And I should see "Complete" in the ".tui-progressTrackerNav__item:nth-child(3) .tui-dataTable:nth-child(1) .tui-dataTableRow:nth-child(1) .tui-dataTableCell:nth-child(2)" "css_element"
    And ".tui-performUserActivityListSection__progressIcon-complete" "css_element" should exist in the ".tui-progressTrackerNav__item:nth-child(3) .tui-dataTable:nth-child(1) .tui-dataTableRow:nth-child(1) .tui-dataTableCell:nth-child(2)" "css_element"

  Scenario: Collapse and expand sections
    Given I log in as "john"
    When I navigate to the outstanding perform activities list page

    When I click on "View details" "button" in the ".tui-dataTableRow:nth-child(2)" "css_element"

    Then I should see "Subject" in the ".tui-progressTrackerNav__item:nth-child(1) .tui-dataTable:nth-child(1) .tui-dataTableRowHeader" "css_element"
    And I should see "You" in the ".tui-progressTrackerNav__item:nth-child(1) .tui-dataTable:nth-child(1) .tui-dataTableRow:nth-child(1) .tui-dataTableCell:nth-child(1)" "css_element"
    And I should see "Not started" in the ".tui-progressTrackerNav__item:nth-child(1) .tui-dataTable:nth-child(1) .tui-dataTableRow:nth-child(1) .tui-dataTableCell:nth-child(2)" "css_element"

    And I should see "Manager" in the ".tui-progressTrackerNav__item:nth-child(1) .tui-dataTable:nth-child(2) .tui-dataTableRowHeader" "css_element"
    And I should see "David Two" in the ".tui-progressTrackerNav__item:nth-child(1) .tui-dataTable:nth-child(2) .tui-dataTableRow:nth-child(1) .tui-dataTableCell:nth-child(1)" "css_element"
    And I should see "Not started" in the ".tui-progressTrackerNav__item:nth-child(1) .tui-dataTable:nth-child(2) .tui-dataTableRow:nth-child(1) .tui-dataTableCell:nth-child(2)" "css_element"
    And I should see "combined a" in the ".tui-progressTrackerNav__item:nth-child(1) .tui-dataTable:nth-child(2) .tui-dataTableRow:nth-child(2) .tui-dataTableCell:nth-child(1)" "css_element"
    And I should see "Not started" in the ".tui-progressTrackerNav__item:nth-child(1) .tui-dataTable:nth-child(2) .tui-dataTableRow:nth-child(2) .tui-dataTableCell:nth-child(2)" "css_element"

    And I should see "Appraiser" in the ".tui-progressTrackerNav__item:nth-child(1) .tui-dataTable:nth-child(4) .tui-dataTableRowHeader" "css_element"
    And I should see "Harry Three" in the ".tui-progressTrackerNav__item:nth-child(1) .tui-dataTable:nth-child(4) .tui-dataTableRow:nth-child(1) .tui-dataTableCell:nth-child(1)" "css_element"
    And I should see "Not started" in the ".tui-progressTrackerNav__item:nth-child(1) .tui-dataTable:nth-child(4) .tui-dataTableRow:nth-child(1) .tui-dataTableCell:nth-child(2)" "css_element"
    And I should see "combined a" in the ".tui-progressTrackerNav__item:nth-child(1) .tui-dataTable:nth-child(4) .tui-dataTableRow:nth-child(2) .tui-dataTableCell:nth-child(1)" "css_element"
    And I should see "Not started" in the ".tui-progressTrackerNav__item:nth-child(1) .tui-dataTable:nth-child(4) .tui-dataTableRow:nth-child(2) .tui-dataTableCell:nth-child(2)" "css_element"

    # Collapse then expand
    And I click on "Section 1" "link_or_button" in the ".tui-progressTrackerNav__item:nth-child(1) .tui-progressTrackerNav__itemContent" "css_element"
    Then ".tui-progressTrackerNav__item:nth-child(1) .tui-dataTable" "css_element" should not exist
    And I click on "Section 1" "button" in the ".tui-progressTrackerNav__item:nth-child(1) .tui-progressTrackerNav__itemContent" "css_element"

    Then I should see "Subject" in the ".tui-progressTrackerNav__item:nth-child(1) .tui-dataTable:nth-child(1) .tui-dataTableRowHeader" "css_element"
    And I should see "You" in the ".tui-progressTrackerNav__item:nth-child(1) .tui-dataTable:nth-child(1) .tui-dataTableRow:nth-child(1) .tui-dataTableCell:nth-child(1)" "css_element"
    And I should see "Not started" in the ".tui-progressTrackerNav__item:nth-child(1) .tui-dataTable:nth-child(1) .tui-dataTableRow:nth-child(1) .tui-dataTableCell:nth-child(2)" "css_element"

    And I should see "Manager" in the ".tui-progressTrackerNav__item:nth-child(1) .tui-dataTable:nth-child(2) .tui-dataTableRowHeader" "css_element"
    And I should see "(9)" in the ".tui-progressTrackerNav__item:nth-child(1) .tui-dataTable:nth-child(2) .tui-dataTableRowHeader span" "css_element"
    And I should see "David Two" in the ".tui-progressTrackerNav__item:nth-child(1) .tui-dataTable:nth-child(2) .tui-dataTableRow:nth-child(1) .tui-dataTableCell:nth-child(1)" "css_element"
    And I should see "Not started" in the ".tui-progressTrackerNav__item:nth-child(1) .tui-dataTable:nth-child(2) .tui-dataTableRow:nth-child(1) .tui-dataTableCell:nth-child(2)" "css_element"
    And I should see "combined a" in the ".tui-progressTrackerNav__item:nth-child(1) .tui-dataTable:nth-child(2) .tui-dataTableRow:nth-child(2) .tui-dataTableCell:nth-child(1)" "css_element"
    And I should see "Not started" in the ".tui-progressTrackerNav__item:nth-child(1) .tui-dataTable:nth-child(2) .tui-dataTableRow:nth-child(2) .tui-dataTableCell:nth-child(2)" "css_element"

    And I should see "Appraiser" in the ".tui-progressTrackerNav__item:nth-child(1) .tui-dataTable:nth-child(4) .tui-dataTableRowHeader" "css_element"
    And I should see "(9)" in the ".tui-progressTrackerNav__item:nth-child(1) .tui-dataTable:nth-child(4) .tui-dataTableRowHeader span" "css_element"
    And I should see "Harry Three" in the ".tui-progressTrackerNav__item:nth-child(1) .tui-dataTable:nth-child(4) .tui-dataTableRow:nth-child(1) .tui-dataTableCell:nth-child(1)" "css_element"
    And I should see "Not started" in the ".tui-progressTrackerNav__item:nth-child(1) .tui-dataTable:nth-child(4) .tui-dataTableRow:nth-child(1) .tui-dataTableCell:nth-child(2)" "css_element"
    And I should see "combined a" in the ".tui-progressTrackerNav__item:nth-child(1) .tui-dataTable:nth-child(4) .tui-dataTableRow:nth-child(2) .tui-dataTableCell:nth-child(1)" "css_element"
    And I should see "Not started" in the ".tui-progressTrackerNav__item:nth-child(1) .tui-dataTable:nth-child(4) .tui-dataTableRow:nth-child(2) .tui-dataTableCell:nth-child(2)" "css_element"

    And I should see "Manager" in the ".tui-progressTrackerNav__item:nth-child(2) .tui-dataTable:nth-child(1) .tui-dataTableRowHeader" "css_element"
    And I should see "(9)" in the ".tui-progressTrackerNav__item:nth-child(2) .tui-dataTable:nth-child(1) .tui-dataTableRowHeader span" "css_element"
    And I should see "David Two" in the ".tui-progressTrackerNav__item:nth-child(2) .tui-dataTable:nth-child(1) .tui-dataTableRow:nth-child(1) .tui-dataTableCell:nth-child(1)" "css_element"
    And I should see "Not started" in the ".tui-progressTrackerNav__item:nth-child(2) .tui-dataTable:nth-child(1) .tui-dataTableRow:nth-child(1) .tui-dataTableCell:nth-child(2)" "css_element"
    And I should see "combined a" in the ".tui-progressTrackerNav__item:nth-child(2) .tui-dataTable:nth-child(1) .tui-dataTableRow:nth-child(2) .tui-dataTableCell:nth-child(1)" "css_element"
    And I should see "Not started" in the ".tui-progressTrackerNav__item:nth-child(2) .tui-dataTable:nth-child(1) .tui-dataTableRow:nth-child(2) .tui-dataTableCell:nth-child(2)" "css_element"

    # Collapse then expand
    And I click on "Section 2" "button" in the ".tui-progressTrackerNav__item:nth-child(2) .tui-progressTrackerNav__itemContent" "css_element"
    Then ".tui-progressTrackerNav__item:nth-child(2) .tui-dataTable" "css_element" should not exist
    And I click on "Section 2" "button" in the ".tui-progressTrackerNav__item:nth-child(2) .tui-progressTrackerNav__itemContent" "css_element"

    Then I should see "Manager" in the ".tui-progressTrackerNav__item:nth-child(2) .tui-dataTable:nth-child(1) .tui-dataTableRowHeader" "css_element"
    And I should see "(9)" in the ".tui-progressTrackerNav__item:nth-child(2) .tui-dataTable:nth-child(1) .tui-dataTableRowHeader span" "css_element"
    And I should see "David Two" in the ".tui-progressTrackerNav__item:nth-child(2) .tui-dataTable:nth-child(1) .tui-dataTableRow:nth-child(1) .tui-dataTableCell:nth-child(1)" "css_element"
    And I should see "Not started" in the ".tui-progressTrackerNav__item:nth-child(2) .tui-dataTable:nth-child(1) .tui-dataTableRow:nth-child(1) .tui-dataTableCell:nth-child(2)" "css_element"
    And I should see "combined a" in the ".tui-progressTrackerNav__item:nth-child(2) .tui-dataTable:nth-child(1) .tui-dataTableRow:nth-child(2) .tui-dataTableCell:nth-child(1)" "css_element"
    And I should see "Not started" in the ".tui-progressTrackerNav__item:nth-child(2) .tui-dataTable:nth-child(1) .tui-dataTableRow:nth-child(2) .tui-dataTableCell:nth-child(2)" "css_element"

    Then I should see "Subject" in the ".tui-progressTrackerNav__item:nth-child(3) .tui-dataTable:nth-child(1) .tui-dataTableRowHeader" "css_element"
    And I should see "You" in the ".tui-progressTrackerNav__item:nth-child(3) .tui-dataTable:nth-child(1) .tui-dataTableRow:nth-child(1) .tui-dataTableCell:nth-child(1)" "css_element"
    And I should see "Not started" in the ".tui-progressTrackerNav__item:nth-child(3) .tui-dataTable:nth-child(1) .tui-dataTableRow:nth-child(1) .tui-dataTableCell:nth-child(2)" "css_element"

    # Collapse then expand
    And I click on "Section 3" "button" in the ".tui-progressTrackerNav__item:nth-child(3) .tui-progressTrackerNav__itemContent" "css_element"
    Then ".tui-progressTrackerNav__item:nth-child(3) .tui-dataTable" "css_element" should not exist
    And I click on "Section 3" "button" in the ".tui-progressTrackerNav__item:nth-child(3) .tui-progressTrackerNav__itemContent" "css_element"

    Then I should see "Subject" in the ".tui-progressTrackerNav__item:nth-child(3) .tui-dataTable:nth-child(1) .tui-dataTableRowHeader" "css_element"
    And I should see "You" in the ".tui-progressTrackerNav__item:nth-child(3) .tui-dataTable:nth-child(1) .tui-dataTableRow:nth-child(1) .tui-dataTableCell:nth-child(1)" "css_element"
    And I should see "Not started" in the ".tui-progressTrackerNav__item:nth-child(3) .tui-dataTable:nth-child(1) .tui-dataTableRow:nth-child(1) .tui-dataTableCell:nth-child(2)" "css_element"

  Scenario: Activities sections view more
    Given I log in as "john"
    When I navigate to the outstanding perform activities list page

    When I click on "View details" "button" in the ".tui-dataTableRow:nth-child(2)" "css_element"

    And I should see "You" in the ".tui-progressTrackerNav__item:nth-child(1) .tui-dataTable:nth-child(1) .tui-dataTableRow:nth-child(1) .tui-dataTableCell:nth-child(1)" "css_element"
    And I should see "Not started" in the ".tui-progressTrackerNav__item:nth-child(1) .tui-dataTable:nth-child(1) .tui-dataTableRow:nth-child(1) .tui-dataTableCell:nth-child(2)" "css_element"

    # Section 1
    And I should see "Manager" in the ".tui-progressTrackerNav__item:nth-child(1) .tui-dataTable:nth-child(2) .tui-dataTableRowHeader" "css_element"
    And I should see "David Two" in the ".tui-progressTrackerNav__item:nth-child(1) .tui-dataTable:nth-child(2) .tui-dataTableRow:nth-child(1) .tui-dataTableCell:nth-child(1)" "css_element"
    And I should see "Not started" in the ".tui-progressTrackerNav__item:nth-child(1) .tui-dataTable:nth-child(2) .tui-dataTableRow:nth-child(1) .tui-dataTableCell:nth-child(2)" "css_element"
    And I should see "zcombined a" in the ".tui-progressTrackerNav__item:nth-child(1) .tui-dataTable:nth-child(2) .tui-dataTableRow:nth-child(2) .tui-dataTableCell:nth-child(1)" "css_element"
    And I should see "Not started" in the ".tui-progressTrackerNav__item:nth-child(1) .tui-dataTable:nth-child(2) .tui-dataTableRow:nth-child(2) .tui-dataTableCell:nth-child(2)" "css_element"
    And I should see "zcombined b" in the ".tui-progressTrackerNav__item:nth-child(1) .tui-dataTable:nth-child(2) .tui-dataTableRow:nth-child(3) .tui-dataTableCell:nth-child(1)" "css_element"
    And I should see "Not started" in the ".tui-progressTrackerNav__item:nth-child(1) .tui-dataTable:nth-child(2) .tui-dataTableRow:nth-child(3) .tui-dataTableCell:nth-child(2)" "css_element"

    Then I click on "View more" "button" in the ".tui-progressTrackerNav__item:nth-child(1)" "css_element"
    Then I click on "View more" "button" in the ".tui-progressTrackerNav__item:nth-child(1)" "css_element"

    And I should see "Manager" in the ".tui-progressTrackerNav__item:nth-child(1) .tui-dataTable:nth-child(2) .tui-dataTableRowHeader" "css_element"
    And I should see "David Two" in the ".tui-progressTrackerNav__item:nth-child(1) .tui-dataTable:nth-child(2) .tui-dataTableRow:nth-child(1) .tui-dataTableCell:nth-child(1)" "css_element"
    And I should see "Not started" in the ".tui-progressTrackerNav__item:nth-child(1) .tui-dataTable:nth-child(2) .tui-dataTableRow:nth-child(1) .tui-dataTableCell:nth-child(2)" "css_element"
    And I should see "zcombined a" in the ".tui-progressTrackerNav__item:nth-child(1) .tui-dataTable:nth-child(2) .tui-dataTableRow:nth-child(2) .tui-dataTableCell:nth-child(1)" "css_element"
    And I should see "Not started" in the ".tui-progressTrackerNav__item:nth-child(1) .tui-dataTable:nth-child(2) .tui-dataTableRow:nth-child(2) .tui-dataTableCell:nth-child(2)" "css_element"
    And I should see "zcombined b" in the ".tui-progressTrackerNav__item:nth-child(1) .tui-dataTable:nth-child(2) .tui-dataTableRow:nth-child(3) .tui-dataTableCell:nth-child(1)" "css_element"
    And I should see "Not started" in the ".tui-progressTrackerNav__item:nth-child(1) .tui-dataTable:nth-child(2) .tui-dataTableRow:nth-child(3) .tui-dataTableCell:nth-child(2)" "css_element"
    And I should see "zcombined c" in the ".tui-progressTrackerNav__item:nth-child(1) .tui-dataTable:nth-child(2) .tui-dataTableRow:nth-child(4) .tui-dataTableCell:nth-child(1)" "css_element"
    And I should see "Not started" in the ".tui-progressTrackerNav__item:nth-child(1) .tui-dataTable:nth-child(2) .tui-dataTableRow:nth-child(4) .tui-dataTableCell:nth-child(2)" "css_element"
    And I should see "zcombined d" in the ".tui-progressTrackerNav__item:nth-child(1) .tui-dataTable:nth-child(2) .tui-dataTableRow:nth-child(5) .tui-dataTableCell:nth-child(1)" "css_element"
    And I should see "Not started" in the ".tui-progressTrackerNav__item:nth-child(1) .tui-dataTable:nth-child(2) .tui-dataTableRow:nth-child(5) .tui-dataTableCell:nth-child(2)" "css_element"
    And I should see "zcombined e" in the ".tui-progressTrackerNav__item:nth-child(1) .tui-dataTable:nth-child(2) .tui-dataTableRow:nth-child(6) .tui-dataTableCell:nth-child(1)" "css_element"
    And I should see "Not started" in the ".tui-progressTrackerNav__item:nth-child(1) .tui-dataTable:nth-child(2) .tui-dataTableRow:nth-child(6) .tui-dataTableCell:nth-child(2)" "css_element"
    And I should see "zcombined f" in the ".tui-progressTrackerNav__item:nth-child(1) .tui-dataTable:nth-child(2) .tui-dataTableRow:nth-child(7) .tui-dataTableCell:nth-child(1)" "css_element"
    And I should see "Not started" in the ".tui-progressTrackerNav__item:nth-child(1) .tui-dataTable:nth-child(2) .tui-dataTableRow:nth-child(7) .tui-dataTableCell:nth-child(2)" "css_element"
    And I should see "zcombined g" in the ".tui-progressTrackerNav__item:nth-child(1) .tui-dataTable:nth-child(2) .tui-dataTableRow:nth-child(8) .tui-dataTableCell:nth-child(1)" "css_element"
    And I should see "Not started" in the ".tui-progressTrackerNav__item:nth-child(1) .tui-dataTable:nth-child(2) .tui-dataTableRow:nth-child(8) .tui-dataTableCell:nth-child(2)" "css_element"
    And I should see "zcombined h" in the ".tui-progressTrackerNav__item:nth-child(1) .tui-dataTable:nth-child(2) .tui-dataTableRow:nth-child(9) .tui-dataTableCell:nth-child(1)" "css_element"
    And I should see "Not started" in the ".tui-progressTrackerNav__item:nth-child(1) .tui-dataTable:nth-child(2) .tui-dataTableRow:nth-child(9) .tui-dataTableCell:nth-child(2)" "css_element"

    Then I should not see "View more" in the ".tui-progressTrackerNav__item:nth-child(1)" "css_element"

    # Section 2
    And I should see "Manager" in the ".tui-progressTrackerNav__item:nth-child(2) .tui-dataTable:nth-child(1) .tui-dataTableRowHeader" "css_element"
    And I should see "David Two" in the ".tui-progressTrackerNav__item:nth-child(2) .tui-dataTable:nth-child(1) .tui-dataTableRow:nth-child(1) .tui-dataTableCell:nth-child(1)" "css_element"
    And I should see "Not started" in the ".tui-progressTrackerNav__item:nth-child(2) .tui-dataTable:nth-child(1) .tui-dataTableRow:nth-child(1) .tui-dataTableCell:nth-child(2)" "css_element"
    And I should see "zcombined a" in the ".tui-progressTrackerNav__item:nth-child(2) .tui-dataTable:nth-child(1) .tui-dataTableRow:nth-child(2) .tui-dataTableCell:nth-child(1)" "css_element"
    And I should see "Not started" in the ".tui-progressTrackerNav__item:nth-child(2) .tui-dataTable:nth-child(1) .tui-dataTableRow:nth-child(2) .tui-dataTableCell:nth-child(2)" "css_element"
    And I should see "zcombined b" in the ".tui-progressTrackerNav__item:nth-child(2) .tui-dataTable:nth-child(1) .tui-dataTableRow:nth-child(3) .tui-dataTableCell:nth-child(1)" "css_element"
    And I should see "Not started" in the ".tui-progressTrackerNav__item:nth-child(2) .tui-dataTable:nth-child(1) .tui-dataTableRow:nth-child(3) .tui-dataTableCell:nth-child(2)" "css_element"

    Then I click on "View more" "button" in the ".tui-progressTrackerNav__item:nth-child(2)" "css_element"

    And I should see "Manager" in the ".tui-progressTrackerNav__item:nth-child(2) .tui-dataTable:nth-child(1) .tui-dataTableRowHeader" "css_element"
    And I should see "David Two" in the ".tui-progressTrackerNav__item:nth-child(2) .tui-dataTable:nth-child(1) .tui-dataTableRow:nth-child(1) .tui-dataTableCell:nth-child(1)" "css_element"
    And I should see "Not started" in the ".tui-progressTrackerNav__item:nth-child(2) .tui-dataTable:nth-child(1) .tui-dataTableRow:nth-child(1) .tui-dataTableCell:nth-child(2)" "css_element"
    And I should see "zcombined a" in the ".tui-progressTrackerNav__item:nth-child(2) .tui-dataTable:nth-child(1) .tui-dataTableRow:nth-child(2) .tui-dataTableCell:nth-child(1)" "css_element"
    And I should see "Not started" in the ".tui-progressTrackerNav__item:nth-child(2) .tui-dataTable:nth-child(1) .tui-dataTableRow:nth-child(2) .tui-dataTableCell:nth-child(2)" "css_element"
    And I should see "zcombined b" in the ".tui-progressTrackerNav__item:nth-child(2) .tui-dataTable:nth-child(1) .tui-dataTableRow:nth-child(3) .tui-dataTableCell:nth-child(1)" "css_element"
    And I should see "Not started" in the ".tui-progressTrackerNav__item:nth-child(2) .tui-dataTable:nth-child(1) .tui-dataTableRow:nth-child(3) .tui-dataTableCell:nth-child(2)" "css_element"
    And I should see "zcombined c" in the ".tui-progressTrackerNav__item:nth-child(2) .tui-dataTable:nth-child(1) .tui-dataTableRow:nth-child(4) .tui-dataTableCell:nth-child(1)" "css_element"
    And I should see "Not started" in the ".tui-progressTrackerNav__item:nth-child(2) .tui-dataTable:nth-child(1) .tui-dataTableRow:nth-child(4) .tui-dataTableCell:nth-child(2)" "css_element"
    And I should see "zcombined d" in the ".tui-progressTrackerNav__item:nth-child(2) .tui-dataTable:nth-child(1) .tui-dataTableRow:nth-child(5) .tui-dataTableCell:nth-child(1)" "css_element"
    And I should see "Not started" in the ".tui-progressTrackerNav__item:nth-child(2) .tui-dataTable:nth-child(1) .tui-dataTableRow:nth-child(5) .tui-dataTableCell:nth-child(2)" "css_element"
    And I should see "zcombined e" in the ".tui-progressTrackerNav__item:nth-child(2) .tui-dataTable:nth-child(1) .tui-dataTableRow:nth-child(6) .tui-dataTableCell:nth-child(1)" "css_element"
    And I should see "Not started" in the ".tui-progressTrackerNav__item:nth-child(2) .tui-dataTable:nth-child(1) .tui-dataTableRow:nth-child(6) .tui-dataTableCell:nth-child(2)" "css_element"
    And I should see "zcombined f" in the ".tui-progressTrackerNav__item:nth-child(2) .tui-dataTable:nth-child(1) .tui-dataTableRow:nth-child(7) .tui-dataTableCell:nth-child(1)" "css_element"
    And I should see "Not started" in the ".tui-progressTrackerNav__item:nth-child(2) .tui-dataTable:nth-child(1) .tui-dataTableRow:nth-child(7) .tui-dataTableCell:nth-child(2)" "css_element"
    And I should see "zcombined g" in the ".tui-progressTrackerNav__item:nth-child(2) .tui-dataTable:nth-child(1) .tui-dataTableRow:nth-child(8) .tui-dataTableCell:nth-child(1)" "css_element"
    And I should see "Not started" in the ".tui-progressTrackerNav__item:nth-child(2) .tui-dataTable:nth-child(1) .tui-dataTableRow:nth-child(8) .tui-dataTableCell:nth-child(2)" "css_element"
    And I should see "zcombined h" in the ".tui-progressTrackerNav__item:nth-child(2) .tui-dataTable:nth-child(1) .tui-dataTableRow:nth-child(9) .tui-dataTableCell:nth-child(1)" "css_element"
    And I should see "Not started" in the ".tui-progressTrackerNav__item:nth-child(2) .tui-dataTable:nth-child(1) .tui-dataTableRow:nth-child(9) .tui-dataTableCell:nth-child(2)" "css_element"

    Then I should not see "View more" in the ".tui-progressTrackerNav__item:nth-child(2)" "css_element"


  Scenario: Complete sections as the subject with multiple sections using manual close
    Given I log in as "john"
    When I navigate to the outstanding perform activities list page
    # Can see manual close activity
    Then I should see "Multiple section activity with manual closure" in the ".tui-dataTableRow:nth-child(4) .tui-performUserActivityListTableItem__title" "css_element"
    And I should see "##today##j F Y##" in the ".tui-dataTableRow:nth-child(4) .tui-performUserActivityListTableItem__overview-created" "css_element"
    And I should see "Appraisal" in the ".tui-dataTableRow:nth-child(4) .tui-performUserActivityListTableItem__overview-type" "css_element"
    And I should see "Not started" in the ".tui-dataTableRow:nth-child(4) .tui-performUserActivityListTableItem__progress-status" "css_element"
    And I should see "Activity is not started" in the ".tui-dataTableRow:nth-child(4) .tui-performUserActivityListTableItem__details-overallProgress" "css_element"

    # Viewing manual close activity
    When I click on "Multiple section activity with manual closure" "link"
    Then I should see "Sect 1"
    And I should see "Sect 2"
    And I should see "Sect 3"
    And I should see "Open" in the ".tui-progressTrackerNav__item:nth-child(1) .tui-activityProgressTrackerItem__availability" "css_element"
    And I should see "Open" in the ".tui-progressTrackerNav__item:nth-child(2) .tui-activityProgressTrackerItem__availability" "css_element"
    And I should see "Open" in the ".tui-progressTrackerNav__item:nth-child(3) .tui-activityProgressTrackerItem__availability" "css_element"
    And I should see "Complete activity" in the ".tui-participantContent__manualClose" "css_element"
    And "Submit section" "button" should exist

    # Check manual complete is blocked by required section
    And I should see "*" in the ".tui-progressTrackerNav__item:nth-child(2)" "css_element"
    And I should see "Required sections not submitted" in the ".tui-participantContent__manualClose-requirement" "css_element"
    And the "Complete activity" "button" should be disabled

    # Complete required section
    When I click on "Sect 2" "button" in the ".tui-progressTrackerNav__item:nth-child(2) .tui-progressTrackerNav__itemContent" "css_element"
    And I answer "short text" question "Question 2" with "text for required question"
    And I click on "Submit" "button"
    And I confirm the tui confirmation modal
    And I should see "Section submitted" in the tui success notification toast
    Then the "Complete activity" "button" should be enabled
    And ".tui-participantContent__manualClose-requirement" "css_element" should not exist

    # Manual close activity
    When I click on "Complete activity" "button"
    Then I should see "Confirm activity completion"
    And I should see "You will not be able to update your responses, and your activity progress will be marked as complete and closed."

    When I confirm the tui confirmation modal
    Then I should see "Activity completed and closed." in the tui success notification toast
    And I should see "Closed" in the ".tui-progressTrackerNav__item:nth-child(1) .tui-activityProgressTrackerItem__availability" "css_element"
    And I should see "Closed" in the ".tui-progressTrackerNav__item:nth-child(2) .tui-activityProgressTrackerItem__availability" "css_element"
    And I should see "Closed" in the ".tui-progressTrackerNav__item:nth-child(3) .tui-activityProgressTrackerItem__availability" "css_element"
    And "Submit section" "button" should not exist

  Scenario: Check single section activities with auto close has correct buttons
    Given I log in as "john"
    When I navigate to the outstanding perform activities list page
    # Viewing manual close activity
    When I click on "Single sect Activity with manual and auto close" "link"
    Then "Complete activity" "button" should exist
    And ".tui-participantContent__manualClose" "css_element" should not exist