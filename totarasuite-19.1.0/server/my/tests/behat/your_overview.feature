@core @core_my @perform_overview @javascript
Feature: Performance overview page access

  Background:
    Given I am on a totara site
    And the following "users" exist:
      | username | firstname | lastname | email                   |
      | alice    | Alice     | Smith    | alice.smith@example.com |
    And flavour "perform" is active
    And I disable the "goals" advanced feature

  Scenario: As admin test the default overview page setting
    When I log in as "admin"
    Then I should see "Develop > Performance overview" in the totara menu
    And I log out

  Scenario: Login as a user and check overview page
    Given I log in as "alice"
    And I should see "Develop > Performance overview" in the totara menu
    And I navigate to my overview
    # Fix it when to page is done
    Then I should see "Performance overview"
    And I should not see "Require action"

  Scenario: Different users access and check overview page
    Given the following "users" exist:
      | username | firstname | lastname | email                 |
      | manager  | Stu       | Den      | manager@example.com   |
      | bolobala | Bolo      | Bala     | bolo.bala@example.com |
      | teacher  | Tea       | Cher     | teacher@example.com   |
    And the following job assignments exist:
      | user     | manager |
      | alice    | manager |
      | bolobala | teacher |
    And I log in as "manager"
    And I navigate to my overview for user "alice"
    # Fix it when to page is done
    And I should see "Performance overview"
    And I log out

    And I log in as "teacher"
    And I navigate to my overview for user "bolobala"
    # Fix it when to page is done
    And I should see "Performance overview"
    And I log out

  Scenario: Disable an advanced feature and check overview page
    Given I log in as "admin"
    And I navigate to "Main menu" node in "Site administration > Navigation"
    And I disable the "competencies" advanced feature
    And I log out

    And I log in as "alice"
    And I should see "Develop > Performance overview" in the totara menu
    And I log out

    And I log in as "admin"
    And I enable the "competencies" advanced feature
    And I disable the "competency_assignment" advanced feature
    And I log out

    And I log in as "alice"
    And I should see "Develop > Performance overview" in the totara menu
    And I log out

    And I log in as "admin"
    And I disable the "performance_activities" advanced feature
    And I disable the "perform_goals" advanced feature
    And I log out

    And I log in as "alice"
    And I should not see "Develop > Performance overview" in the totara menu
    And I log out

  Scenario: I can view available sections on the overview page
    Given I log in as "admin"
    And I navigate to my overview
    And I should see "Goals"
    And I should see "Competencies"
    And I should see "Performance activities"

  Scenario: I can view another users overview page and the UI responds accordingly
    Given the following "users" exist:
      | username | firstname | lastname | email                 |
      | manager  | Stu       | Den      | manager@example.com   |
      | bolobala | Bolo      | Bala     | bolo.bala@example.com |
      | teacher  | Tea       | Cher     | teacher@example.com   |
    And the following job assignments exist:
      | user     | manager |
      | alice    | manager |
      | bolobala | teacher |
    And I log in as "manager"
    And I navigate to my overview for user "alice"
    And ".tui-miniProfileCard" "css_element" should exist
    And I should see "Alice Smith" in the ".tui-miniProfileCard" "css_element"

  Scenario: I can see the banner for scheduled updates to competencies
    Given I log in as "admin"
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
    And I navigate to the competency self assignment page
    And I click on ".tui-checkbox__label" "css_element" in the tui datatable row with "Competency 1" "Competency"
    And I click on "Assign competencies" "button"
    And I confirm the tui confirmation modal
    And I navigate to my overview
    Then I should see "The competencies on this page are currently being updated, and changes may take some time to appear." in the tui info notification banner
    And I trigger cron
    Then I should not see "The competencies on this page are currently being updated, and changes may take some time to appear."

  Scenario: I can view pending activity participation in the overview require selection content
    Given I log in as "admin"
    And I navigate to my overview
    And I should not see "Require action"
    And I log out
    And the following "users" exist:
      | username | firstname | lastname | email             |
      | jared    | Jared     | Stanton  | jared@example.com |
      | tom      | Tom       | Johnson  | tom@example.com   |
    And the following job assignments exist:
      | user  | manager |
      | jared | tom     |
    And the following "cohorts" exist:
      | name | idnumber |
      | aud1 | aud1     |
    And the following "cohort members" exist:
      | user  | cohort |
      | jared | aud1   |
    And the following "activities" exist in "mod_perform" plugin:
      | activity_name                  | description                  | activity_type | create_track | create_section | activity_status |
      | Activity 1 with peer selection | Subject needs to select peer | check-in      | false        | false          | Active          |
      | Activity 2 with peer selection | Subject needs to select peer | check-in      | false        | false          | Active          |
    And the following "activity settings" exist in "mod_perform" plugin:
      | activity_name                  | close_on_completion | multisection |
      | Activity 1 with peer selection | yes                 | yes          |
      | Activity 2 with peer selection | yes                 | yes          |
    And the following "activity tracks" exist in "mod_perform" plugin:
      | activity_name                  | track_description |
      | Activity 1 with peer selection | track 1           |
      | Activity 2 with peer selection | track 2           |
    And the following "track assignments" exist in "mod_perform" plugin:
      | track_description | assignment_type | assignment_name |
      | track 1           | cohort          | aud1            |
      | track 2           | cohort          | aud1            |
    And the following "activity sections" exist in "mod_perform" plugin:
      | activity_name                  | section_name           |
      | Activity 1 with peer selection | Source (1) section one |
      | Activity 1 with peer selection | Source (1) section two |
      | Activity 2 with peer selection | Source (2) section one |
      | Activity 2 with peer selection | Source (2) section two |
    And the following "section relationships" exist in "mod_perform" plugin:
      | section_name           | relationship |
      | Source (1) section one | subject      |
      | Source (1) section one | manager      |
      | Source (1) section one | peer         |
      | Source (1) section two | peer         |
      | Source (2) section one | peer         |
    And the following "section elements" exist in "mod_perform" plugin:
      | section_name           | element_name         | title               | data                                                                                                                                   |
      | Source (1) section one | numeric_rating_scale | On a scale of 1 - 5 | {"defaultValue":"1", "highValue":"5", "lowValue":"3"}                                                                                  |
      | Source (1) section two | custom_rating_scale  | Zero or a hundy     | {"options": [{"name":"option_1","value": {"text":"A hundy","score":"100"}}, {"name":"option_2","value": {"text":"Zero","score":"0"}}]} |
      | Source (2) section one | numeric_rating_scale | On a scale of 1 - 5 | {"defaultValue":"1", "highValue":"5", "lowValue":"3"}                                                                                  |
    And I run the scheduled task "mod_perform\task\expand_assignments_task"
    And I run the scheduled task "mod_perform\task\create_subject_instance_task"
    And I run the scheduled task "mod_perform\task\create_manual_participant_progress_task"

    # Check that disabling performance activities hides the "require action" section
    When I disable the "performance_activities" advanced feature
    And I log in as "jared"
    And I navigate to my overview
    And I should not see "Require action"
    And I enable the "performance_activities" advanced feature

    And I log out
    When I log in as "jared"
    And I navigate to my overview
    And I should see "Require action"
    Then I should see "Activities awaiting participant selection" in the ".tui-myPerformOverviewRequiredAction" "css_element"
    And I should see "There are 2 activities that require you to select participants." in the ".tui-myPerformOverviewRequiredAction__participantsSelection" "css_element"
    And I should see "Select" in the ".tui-myPerformOverviewRequiredAction__participantsSelection .tui-btn" "css_element"
    When I click on "Select" "link" in the ".tui-myPerformOverviewRequiredAction__participantsSelection" "css_element"
    Then I should see "Select participants"
    And I log out

    When I log in as "tom"
    And I navigate to my overview for user "jared"
    Then I should see "Activities awaiting participant selection" in the ".tui-myPerformOverviewRequiredAction" "css_element"
    And I should see "There are 2 activities that require Jared Stanton to select participants." in the ".tui-myPerformOverviewRequiredAction__participantsSelection" "css_element"
    And I should not see "Select" in the ".tui-myPerformOverviewRequiredAction__participantsSelection" "css_element"

    When I navigate to my overview
    Then ".tui-myPerformOverviewRequiredAction" "css_element" should not exist
