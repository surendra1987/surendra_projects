@totara @perform @mod_perform @javascript @vuejs
Feature: Job assignment column on user activities list.
  Background:
    Given the following "users" exist:
      | username | firstname | lastname | email                    |
      | edward   | Edward    | Eiger    | edward.eiger@example.com |
      | mark     | Mark      | Moolman  | mark.moolman@example.com |
      | jake     | Jake      | Johnson  | jake.johnson@example.com |
      | susan    | Susan     | Steele   | susan.steele@example.com |
      | birthe   | Birthe    | Willis   | birthe.willis@example.com|
      | sine     | Sine      | Seagal   | sine.seagal@example.com  |
    And the following job assignments exist:
      | user   | manager | appraiser | fullname | idnumber |
      | edward | mark    | jake      | Mark man | Y1k      |
      | edward | susan   | mark      |          | Y2k      |
      | birthe | mark    |           | Mark man | Y3k      |
    And the following "activities" exist in "mod_perform" plugin:
      | activity_name              | activity_type | create_section | create_track | activity_status |
      | All job assignments        | appraisal     | false          | false        | Active          |
      | Specific job assignments   | appraisal     | false          | false        | Active          |
      | Subject assignment         | appraisal     | false          | false        | Active          |
      | Another Subject assignment | appraisal     | false          | false        | Active          |

    And the following "activity sections" exist in "mod_perform" plugin:
      | activity_name              | section_name            |
      | All job assignments        | All job section         |
      | Specific job assignments   | specific section        |
      | Subject assignment         | subject section         |
      | Another Subject assignment | another subject section |

    And the following "cohorts" exist:
      | name | idnumber | description | contextlevel | reference | cohorttype |
      | aud1 | aud1     | Audience 1  | System       | 0         | 1          |
      | aud2 | aud2     | Audience 2  | System       | 0         | 1          |
    And the following "cohort members" exist:
      | user   | cohort |
      | edward | aud1   |
      | edward | aud2   |
      | susan  | aud2   |
      | mark   | aud2   |
      | birthe | aud1   |
      | sine   | aud1   |

    And the following "activity tracks" exist in "mod_perform" plugin:
      | activity_name              | track_description     | subject_instance_generation |
      | All job assignments        | All job track         | ONE_PER_SUBJECT             |
      | Specific job assignments   | specific track        | ONE_PER_JOB                 |
      | Subject assignment         | subject track         | ONE_PER_SUBJECT             |
      | Another Subject assignment | another subject track | ONE_PER_JOB                 |
    And the following "track assignments" exist in "mod_perform" plugin:
      | track_description     | assignment_type | assignment_name |
      | All job track         | cohort          | aud1            |
      | specific track        | cohort          | aud1            |
      | subject track         | cohort          | aud2            |
      | another subject track | cohort          | aud2            |
    And the following "section relationships" exist in "mod_perform" plugin:
      | section_name            | relationship |
      | All job section         | manager      |
      | All job section         | subject      |
      | All job section         | appraiser    |
      | specific section        | subject      |
      | specific section        | appraiser    |
      | subject section         | subject      |
      | another subject section | subject      |
      | another subject section | manager      |
    And the following "section elements" exist in "mod_perform" plugin:
      | section_name            | element_name | title      |
      | All job section         | short_text   | Question 1 |
      | specific section        | short_text   | Question 2 |
      | subject section         | short_text   | Question 3 |
      | another subject section | short_text   | Question 3 |

    And I run the scheduled task "mod_perform\task\expand_assignments_task"
    And I run the scheduled task "mod_perform\task\create_subject_instance_task"

  Scenario: Job assignment column shows when a subject instance with a specific job assignment is listed.
    Given I log in as "mark"
    And I navigate to the outstanding perform activities list page

    # Tests job assignment column isn't shown when all the subject instances are not created per job assignment
    # First row
    Then I should see "Another Subject assignment" in the ".tui-dataTableRow:nth-child(1) .tui-performUserActivityListTableItem__title" "css_element"
    And I should see "##today##j F Y##" in the ".tui-dataTableRow:nth-child(1) .tui-performUserActivityListTableItem__overview-created" "css_element"
    And I should see "Appraisal" in the ".tui-dataTableRow:nth-child(1) .tui-performUserActivityListTableItem__overview-type" "css_element"
    And I should see "Not started" in the ".tui-dataTableRow:nth-child(1) .tui-performUserActivityListTableItem__progress-status" "css_element"
    And I should see "Activity is not started" in the ".tui-dataTableRow:nth-child(1) .tui-performUserActivityListTableItem__details-overallProgress" "css_element"
    # Second row
    And I should see "Subject assignment" in the ".tui-dataTableRow:nth-child(2) .tui-performUserActivityListTableItem__title" "css_element"
    And I should see "##today##j F Y##" in the ".tui-dataTableRow:nth-child(2) .tui-performUserActivityListTableItem__overview-created" "css_element"
    And I should see "Appraisal" in the ".tui-dataTableRow:nth-child(2) .tui-performUserActivityListTableItem__overview-type" "css_element"
    And I should see "Not started" in the ".tui-dataTableRow:nth-child(2) .tui-performUserActivityListTableItem__progress-status" "css_element"
    And I should see "Activity is not started" in the ".tui-dataTableRow:nth-child(2) .tui-performUserActivityListTableItem__details-overallProgress" "css_element"

    When I click on "As Manager" "link"

    # Tests job assignment column is shown when any of the subject instances are created per job assignment
    # First row
    Then I should see "Another Subject assignment" in the ".tui-dataTableRow:nth-child(1) .tui-performUserActivityListTableItem__title" "css_element"
    And I should see "##today##j F Y##" in the ".tui-dataTableRow:nth-child(1) .tui-performUserActivityListTableItem__overview-created" "css_element"
    And I should see "Edward Eiger" in the ".tui-dataTableRow:nth-child(1) .tui-performUserActivityListTableItem__overview-subject" "css_element"
    And I should see "Appraisal" in the ".tui-dataTableRow:nth-child(1) .tui-performUserActivityListTableItem__overview-type" "css_element"
    And I should see "Mark man" in the ".tui-dataTableRow:nth-child(1) .tui-performUserActivityListTableItem__details-jobAssignment" "css_element"
    And I should see "Not started" in the ".tui-dataTableRow:nth-child(1) .tui-performUserActivityListTableItem__progress-status" "css_element"
    And I should see "Activity is not started" in the ".tui-dataTableRow:nth-child(1) .tui-performUserActivityListTableItem__details-overallProgress" "css_element"
    # Second row
    And I should see "All job assignments" in the ".tui-dataTableRow:nth-child(2) .tui-performUserActivityListTableItem__title" "css_element"
    And I should see "##today##j F Y##" in the ".tui-dataTableRow:nth-child(2) .tui-performUserActivityListTableItem__overview-created" "css_element"
    And I should see "Birthe Willis" in the ".tui-dataTableRow:nth-child(2) .tui-performUserActivityListTableItem__overview-subject" "css_element"
    And I should see "Appraisal" in the ".tui-dataTableRow:nth-child(2) .tui-performUserActivityListTableItem__overview-type" "css_element"
    And I should see "Not started" in the ".tui-dataTableRow:nth-child(2) .tui-performUserActivityListTableItem__progress-status" "css_element"
    And I should see "Activity is not started" in the ".tui-dataTableRow:nth-child(2) .tui-performUserActivityListTableItem__details-overallProgress" "css_element"
    # Third row
    And I should see "All job assignments" in the ".tui-dataTableRow:nth-child(3) .tui-performUserActivityListTableItem__title" "css_element"
    And I should see "##today##j F Y##" in the ".tui-dataTableRow:nth-child(3) .tui-performUserActivityListTableItem__overview-created" "css_element"
    And I should see "Edward Eiger" in the ".tui-dataTableRow:nth-child(3) .tui-performUserActivityListTableItem__overview-subject" "css_element"
    And I should see "Appraisal" in the ".tui-dataTableRow:nth-child(3) .tui-performUserActivityListTableItem__overview-type" "css_element"
    And I should see "Not started" in the ".tui-dataTableRow:nth-child(3) .tui-performUserActivityListTableItem__progress-status" "css_element"
    And I should see "Activity is not started" in the ".tui-dataTableRow:nth-child(3) .tui-performUserActivityListTableItem__details-overallProgress" "css_element"

    When I click on "As Appraiser" "link"

    # Tests job assignment column is shown when any of the subject instances are created per job assignment
    # First row
    Then I should see "Specific job assignments" in the ".tui-dataTableRow:nth-child(1) .tui-performUserActivityListTableItem__title" "css_element"
    And I should see "##today##j F Y##" in the ".tui-dataTableRow:nth-child(1) .tui-performUserActivityListTableItem__overview-created" "css_element"
    And I should see "Edward Eiger" in the ".tui-dataTableRow:nth-child(1) .tui-performUserActivityListTableItem__overview-subject" "css_element"
    And I should see "Appraisal" in the ".tui-dataTableRow:nth-child(1) .tui-performUserActivityListTableItem__overview-type" "css_element"
    And I should see "Unnamed job assignment (ID: Y2k)" in the ".tui-dataTableRow:nth-child(1) .tui-performUserActivityListTableItem__details-jobAssignment" "css_element"
    And I should see "Not started" in the ".tui-dataTableRow:nth-child(1) .tui-performUserActivityListTableItem__progress-status" "css_element"
    And I should see "Activity is not started" in the ".tui-dataTableRow:nth-child(1) .tui-performUserActivityListTableItem__details-overallProgress" "css_element"
    # Second row
    And I should see "All job assignments" in the ".tui-dataTableRow:nth-child(2) .tui-performUserActivityListTableItem__title" "css_element"
    And I should see "##today##j F Y##" in the ".tui-dataTableRow:nth-child(2) .tui-performUserActivityListTableItem__overview-created" "css_element"
    And I should see "Edward Eiger" in the ".tui-dataTableRow:nth-child(2) .tui-performUserActivityListTableItem__overview-subject" "css_element"
    And I should see "Appraisal" in the ".tui-dataTableRow:nth-child(2) .tui-performUserActivityListTableItem__overview-type" "css_element"
    And I should see "Not started" in the ".tui-dataTableRow:nth-child(2) .tui-performUserActivityListTableItem__progress-status" "css_element"
    And I should see "Activity is not started" in the ".tui-dataTableRow:nth-child(2) .tui-performUserActivityListTableItem__details-overallProgress" "css_element"

  Scenario: Change Manager Job assignment from one manager to another.
    Given I log in as "birthe"
    And I navigate to the outstanding perform activities list page
    And I click on "Specific job assignments" "link"
    And I should see "Mark man" in the ".tui-jobAssignment__jobAssignmentDetails" "css_element"
    And I should see "Mark Moolman's team" in the ".tui-jobAssignment__jobAssignmentDetails" "css_element"
    And I log out

    And I log in as "admin"
    And I navigate to "Manage users" node in "Site administration > Users"
    And I click on "Birthe Willis" "link" in the "Birthe Willis" "table_row"
    And I click on "Mark man" "link"
    And I click on "Delete" "link"
    And I press "Choose manager"
    And I click on "Search" "link" in the "Choose manager" "totaradialogue"
    And I search for "Sine Seagal" in the "Choose manager" totara dialogue
    When I click on "Sine Seagal (sine.seagal@example.com) - create empty job assignment" "link" in the "#search-tab" "css_element"
    And I click on "OK" "button" in the "Choose manager" "totaradialogue"
    And I click on "Update job assignment" "button"
    And I log out

    And I log in as "birthe"
    And I navigate to the outstanding perform activities list page
    And I click on "Specific job assignments" "link"
    And I should see "Mark man" in the ".tui-jobAssignment__jobAssignmentDetails" "css_element"
    And I should see "Mark Moolman's team (former) "
    And I should see "Mark man" in the ".tui-jobAssignment__jobAssignmentDetails" "css_element"
    And I should see "Sine Seagal's team "
    And I log out

    And I log in as "admin"
    And I navigate to "Manage users" node in "Site administration > Users"
    And I click on "Birthe Willis" "link" in the "Birthe Willis" "table_row"
    And I click on "Mark man" "link"
    And I click on "Delete" "link"
    And I click on "Update job assignment" "button"
    And I log out

    And I log in as "birthe"
    And I navigate to the outstanding perform activities list page
    And I click on "Specific job assignments" "link"
    And I should see "Mark man" in the ".tui-jobAssignment__jobAssignmentDetails" "css_element"
    And I should see "Sine Seagal's team (former) "
    And I should see "Mark Moolman's team (former) "
    And I log out

    And I log in as "admin"
    And I navigate to "Manage users" node in "Site administration > Users"
    And I click on "Birthe Willis" "link" in the "Birthe Willis" "table_row"
    And I click on "Mark man" "link"
    And I press "Choose manager"
    And I click on "Search" "link" in the "Choose manager" "totaradialogue"
    And I search for "Mark Moolman" in the "Choose manager" totara dialogue
    When I click on "Mark Moolman (mark.moolman@example.com) - Unnamed job assignment (ID: 1)" "link" in the "#search-tab" "css_element"
    And I click on "OK" "button" in the "Choose manager" "totaradialogue"
    And I click on "Update job assignment" "button"
    And I log out

    And I log in as "birthe"
    And I navigate to the outstanding perform activities list page
    And I click on "Specific job assignments" "link"
    And I should see "Mark man" in the ".tui-jobAssignment__jobAssignmentDetails" "css_element"
    And I should see "Mark Moolman's team "
    And I should see "Sine Seagal's team (former) "
    And I should not see "Mark Moolman's team (former) "
