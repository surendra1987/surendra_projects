@core @core_my @mod_perform @perform_overview @javascript
Feature: Perform overview page for activities modal view

  Background:
    Given I am on a totara site
    And the following "users" exist:
      | username | firstname | lastname | email                    |
      | alice    | Alice     | Smith    | alice.smith@example.com  |
      | bill     | Bill      | Orange   | bill.orange@example.com  |
      | jake     | Jake      | Johnson  | jake.johnson@example.com |

    And the following job assignments exist:
      | user  | manager | fullname           | idnumber |
      | alice | bill    | QA TESTER          | Y1       |
      | alice | jake    | Instruction writer | Y2       |

    And the following "cohorts" exist:
      | name | idnumber | description | contextlevel | reference | cohorttype |
      | aud1 | aud1     | Audience 1  | System       | 0         | 1          |

    And the following "cohort members" exist:
      | user  | cohort |
      | alice | aud1   |

  Scenario: activity overview modal should not exist when status section has no more than two activities
    Given the following "subject instances" exist in "mod_perform" plugin:
      | activity_name        | subject_username | subject_is_participating | backdate |
      | Behat activity one   | alice            | true                     | -13 days |
      | Behat activity two   | alice            | true                     | -14 days |
      | Behat activity three | alice            | true                     | -15 days |
      | Behat activity four  | alice            | true                     | -16 days |
      | Behat activity five  | alice            | true                     | -17 days |
      | Behat activity six   | alice            | true                     | -18 days |
      | Behat activity seven | alice            | true                     | -19 days |
      | Behat activity eight | alice            | true                     | -20 days |
    #Progressed
    And the activity "Behat activity three" for user "alice" was progressed at date "-5 days"
    And the activity "Behat activity four" for user "alice" was progressed at date "-6 days"
    # Not progressed
    And the activity "Behat activity five" for user "alice" was progressed at date "-15 days"
    And the activity "Behat activity six" for user "alice" was progressed at date "-16 days"
    # Completed
    And the activity "Behat activity seven" for user "alice" was completed at date "-7 days"
    And the activity "Behat activity eight" for user "alice" was completed at date "-8 days"

    When I log in as "alice"
    And I navigate to my overview
    And ".tui-overviewStatusTable__header-countButton" "css_element" should not exist in the ".tui-overviewActivitiesSection__content-notStarted .tui-overviewStatusTable__header" "css_element"
    And ".tui-overviewStatusTable__header-countButton" "css_element" should not exist in the ".tui-overviewActivitiesSection__content-progressed .tui-overviewStatusTable__header" "css_element"
    And ".tui-overviewStatusTable__header-countButton" "css_element" should not exist in the ".tui-overviewActivitiesSection__content-notProgressed .tui-overviewStatusTable__header" "css_element"
    And ".tui-overviewStatusTable__header-countButton" "css_element" should not exist in the ".tui-overviewActivitiesSection__content-completed .tui-overviewStatusTable__header" "css_element"

  Scenario: not started activity overview modal should exist when there are more than two activities in that state

    Given the following "activities" exist in "mod_perform" plugin:
      | activity_name                      | activity_type | create_section | create_track | activity_status |
      | Behat activity with job assignment | appraisal     | false          | false        | Active          |

    And the following "activity tracks" exist in "mod_perform" plugin:
      | activity_name                      | track_description    | subject_instance_generation |
      | Behat activity with job assignment | job assignment track | ONE_PER_JOB                 |

    And the following "track assignments" exist in "mod_perform" plugin:
      | track_description    | assignment_type | assignment_name |
      | job assignment track | cohort          | aud1            |

    And the following "activity sections" exist in "mod_perform" plugin:
      | activity_name                      | section_name |
      | Behat activity with job assignment | section uno  |

    And the following "section relationships" exist in "mod_perform" plugin:
      | section_name | relationship |
      | section uno  | manager      |
      | section uno  | subject      |

    And the following "section elements" exist in "mod_perform" plugin:
      | section_name | element_name | title      |
      | section uno  | short_text   | Question 1 |

    And I run the scheduled task "mod_perform\task\expand_assignments_task"
    And I run the scheduled task "mod_perform\task\create_subject_instance_task"

    Given the following "subject instances" exist in "mod_perform" plugin:
      | activity_name             | description | subject_username | subject_is_participating | backdate | due_date_relative |
      | Behat activity one        | first desc  | alice            | true                     | -13 days | -2 days           |
      | Behat activity two        |             | alice            | true                     | -14 days | +4 days           |
      | Behat activity three      | third desc  | alice            | true                     | -15 days | +14 days          |
      | Behat activity four       |             | alice            | true                     | -16 days |                   |
      | Behat activity five       |             | alice            | true                     | -17 days |                   |
      | Behat activity six        |             | alice            | true                     | -18 days |                   |
      | Behat activity seven      |             | alice            | true                     | -19 days |                   |
      | Behat activity eight      |             | alice            | true                     | -20 days |                   |
      | Behat activity nine       |             | alice            | true                     | -21 days |                   |
      | Behat activity ten        |             | alice            | true                     | -22 days |                   |
      | Behat activity eleven     |             | alice            | true                     | -23 days |                   |
      | Behat activity twelve     |             | alice            | true                     | -24 days |                   |
      | Behat activity thirteen   |             | alice            | true                     | -25 days |                   |
      | Behat activity fourteen   |             | alice            | true                     | -26 days |                   |
      | Behat activity fifteen    |             | alice            | true                     | -27 days |                   |
      | Behat activity sixteen    |             | alice            | true                     | -28 days |                   |
      | Behat activity seventeen  |             | alice            | true                     | -29 days |                   |
      | Behat activity eighteen   |             | alice            | true                     | -30 days |                   |
      | Behat activity nineteen   |             | alice            | true                     | -31 days |                   |
      | Behat activity twenty     |             | alice            | true                     | -32 days |                   |
      | Behat activity twenty one |             | alice            | true                     | -33 days |                   |
      | Behat activity twenty two |             | alice            | true                     | -34 days |                   |

    When I log in as "alice"
    And I navigate to my overview
    Then I should see "24" in the ".tui-overviewActivitiesSection__content-notStarted .tui-overviewStatusTable__header" "css_element"

    # Modal content
    When I click on "not started" header count for activities overview group
    Then I should see "24 activities not started"

    And I should see "QA TESTER" in the ".tui-overviewActivitiesSection__content-viewAll" "css_element"
    And I should see "Instruction writer" in the ".tui-overviewActivitiesSection__content-viewAll" "css_element"

    And I should see "Behat activity one" in row "3" of the "activities" view all modal
    And I should see the "assignment" date for activity "Behat activity one" for user "alice" in row "3" of the "not started" activities overview modal
    And I should see "Due" in row "3" of the "activities" view all modal
    And I should see the "overdue" icon in row "3" of the activities overview modal
    And I should see "Description" in row "3" of the "activities" view all modal
    Then I click on description in row "3" of the "activities" view all modal
    And I should see "first desc"
    Then I click on description in row "3" of the "activities" view all modal
    And I should not see "first desc"

    And I should see the "assignment" date for activity "Behat activity two" for user "alice" in row "4" of the "not started" activities overview modal
    And I should see "Due" in row "4" of the "activities" view all modal
    And I should see the "due soon" icon in row "4" of the activities overview modal
    And I should not see "Description" in row "4" of the "activities" view all modal

    And I should see the "assignment" date for activity "Behat activity three" for user "alice" in row "5" of the "not started" activities overview modal
    And I should see "Due" in row "5" of the "activities" view all modal
    And I should not see the "due soon" icon in row "5" of the activities overview modal
    And I should see "Description" in row "5" of the "activities" view all modal
    Then I click on description in row "5" of the "activities" view all modal
    And I should see "third desc"
    Then I click on description in row "5" of the "activities" view all modal
    And I should not see "third desc"

    And I should see the "assignment" date for activity "Behat activity four" for user "alice" in row "6" of the "not started" activities overview modal
    And I should not see "Due" in row "6" of the "activities" view all modal
    And I should not see "Description" in row "6" of the "activities" view all modal

    Then I should see "Load more"
    And I should not see "Behat activity nine"
    And I should not see "Behat activity fourteen"
    And I should not see "Behat activity nineteen"
    And I should not see "Behat activity twenty one"
    Then I click on "Load more" "button"
    And I should see "Behat activity nine"
    And I should see "Behat activity fourteen"
    And I should see "Load more"
    And I should not see "Behat activity nineteen"
    And I should not see "Behat activity twenty one"
    Then I click on "Load more" "button"
    And I should see "Behat activity nineteen"
    And I should see "Behat activity twenty one"
    And I should not see "Load more"

  Scenario: progressed activity overview modal should exist when there are more than two activities in that state
    Given the following "subject instances" exist in "mod_perform" plugin:
      | activity_name             | description | subject_username | subject_is_participating | backdate | due_date_relative |
      | Behat activity one        | first desc  | alice            | true                     | -13 days | -2 days           |
      | Behat activity two        |             | alice            | true                     | -14 days | +4 days           |
      | Behat activity three      | third desc  | alice            | true                     | -15 days | +14 days          |
      | Behat activity four       |             | alice            | true                     | -16 days |                   |
      | Behat activity five       |             | alice            | true                     | -17 days |                   |
      | Behat activity six        |             | alice            | true                     | -18 days |                   |
      | Behat activity seven      |             | alice            | true                     | -19 days |                   |
      | Behat activity eight      |             | alice            | true                     | -20 days |                   |
      | Behat activity nine       |             | alice            | true                     | -21 days |                   |
      | Behat activity ten        |             | alice            | true                     | -22 days |                   |
      | Behat activity eleven     |             | alice            | true                     | -23 days |                   |
      | Behat activity twelve     |             | alice            | true                     | -24 days |                   |
      | Behat activity thirteen   |             | alice            | true                     | -25 days |                   |
      | Behat activity fourteen   |             | alice            | true                     | -26 days |                   |
      | Behat activity fifteen    |             | alice            | true                     | -27 days |                   |
      | Behat activity sixteen    |             | alice            | true                     | -28 days |                   |
      | Behat activity seventeen  |             | alice            | true                     | -29 days |                   |
      | Behat activity eighteen   |             | alice            | true                     | -30 days |                   |
      | Behat activity nineteen   |             | alice            | true                     | -31 days |                   |
      | Behat activity twenty     |             | alice            | true                     | -32 days |                   |
      | Behat activity twenty one |             | alice            | true                     | -33 days |                   |
      | Behat activity twenty two |             | alice            | true                     | -34 days |                   |

    And the activity "Behat activity one" for user "alice" was progressed at date "-2 days"
    And the activity "Behat activity two" for user "alice" was progressed at date "-3 days"
    And the activity "Behat activity three" for user "alice" was progressed at date "-4 days"
    And the activity "Behat activity four" for user "alice" was progressed at date "-5 days"
    And the activity "Behat activity five" for user "alice" was progressed at date "-6 days"
    And the activity "Behat activity six" for user "alice" was progressed at date "-7 days"
    And the activity "Behat activity seven" for user "alice" was progressed at date "-8 days"
    And the activity "Behat activity eight" for user "alice" was progressed at date "-9 days"
    And the activity "Behat activity nine" for user "alice" was progressed at date "-10 days"
    And the activity "Behat activity ten" for user "alice" was progressed at date "-11 days"
    And the activity "Behat activity eleven" for user "alice" was progressed at date "-12 days"
    And the activity "Behat activity twelve" for user "alice" was progressed at date "-12 days"
    And the activity "Behat activity thirteen" for user "alice" was progressed at date "-12 days"
    And the activity "Behat activity fourteen" for user "alice" was progressed at date "-12 days"
    And the activity "Behat activity fifteen" for user "alice" was progressed at date "-12 days"
    And the activity "Behat activity sixteen" for user "alice" was progressed at date "-12 days"
    And the activity "Behat activity seventeen" for user "alice" was progressed at date "-12 days"
    And the activity "Behat activity eighteen" for user "alice" was progressed at date "-12 days"
    And the activity "Behat activity nineteen" for user "alice" was progressed at date "-12 days"
    And the activity "Behat activity twenty" for user "alice" was progressed at date "-12 days"
    And the activity "Behat activity twenty one" for user "alice" was progressed at date "-13 days"
    And the activity "Behat activity twenty two" for user "alice" was progressed at date "-13 days"

    When I log in as "alice"
    And I navigate to my overview
    Then I should see "22" in the ".tui-overviewActivitiesSection__content-progressed .tui-overviewStatusTable__header" "css_element"

    # Modal content
    When I click on "progressed" header count for activities overview group
    Then I should see "22 activities progressed"

    And I should see "Behat activity one" in row "1" of the "activities" view all modal
    And I should see the "update" date for activity "Behat activity one" for user "alice" in row "1" of the "not started" activities overview modal
    And I should see "Due" in row "1" of the "activities" view all modal
    And I should see the "overdue" icon in row "1" of the activities overview modal
    And I should see "Description" in row "1" of the "activities" view all modal
    Then I click on description in row "1" of the "activities" view all modal
    And I should see "first desc"
    Then I click on description in row "1" of the "activities" view all modal
    And I should not see "first desc"

    And I should see the "update" date for activity "Behat activity two" for user "alice" in row "2" of the "not started" activities overview modal
    And I should see "Due" in row "2" of the "activities" view all modal
    And I should see the "due soon" icon in row "2" of the activities overview modal
    And I should not see "Description" in row "2" of the "activities" view all modal

    And I should see the "update" date for activity "Behat activity three" for user "alice" in row "3" of the "not started" activities overview modal
    And I should see "Due" in row "3" of the "activities" view all modal
    And I should not see the "due soon" icon in row "3" of the activities overview modal
    And I should see "Description" in row "3" of the "activities" view all modal
    Then I click on description in row "3" of the "activities" view all modal
    And I should see "third desc"
    Then I click on description in row "3" of the "activities" view all modal
    And I should not see "third desc"

    And I should see the "update" date for activity "Behat activity four" for user "alice" in row "4" of the "not started" activities overview modal
    And I should not see "Due" in row "4" of the "activities" view all modal
    And I should not see "Description" in row "4" of the "activities" view all modal

    Then I should see "Load more"
    And I should not see "Behat activity eleven"
    And I should not see "Behat activity fourteen"
    And I should not see "Behat activity twenty two"
    And I should not see "Behat activity twenty one"
    Then I click on "Load more" "button"
    And I should see "Behat activity eleven"
    And I should see "Behat activity fourteen"
    And I should see "Load more"
    And I should not see "Behat activity twenty one"
    And I should not see "Behat activity twenty two"
    Then I click on "Load more" "button"
    And I should see "Behat activity twenty one"
    And I should see "Behat activity twenty two"
    And I should not see "Load more"



  Scenario: not progressed activity overview modal should exist when there are more than two activities in that state
    Given the following "subject instances" exist in "mod_perform" plugin:
      | activity_name             | description | subject_username | subject_is_participating | backdate | due_date_relative |
      | Behat activity one        | first desc  | alice            | true                     | -13 days | -2 days           |
      | Behat activity two        |             | alice            | true                     | -14 days | +4 days           |
      | Behat activity three      | third desc  | alice            | true                     | -15 days | +14 days          |
      | Behat activity four       |             | alice            | true                     | -16 days |                   |
      | Behat activity five       |             | alice            | true                     | -17 days |                   |
      | Behat activity six        |             | alice            | true                     | -18 days |                   |
      | Behat activity seven      |             | alice            | true                     | -19 days |                   |
      | Behat activity eight      |             | alice            | true                     | -20 days |                   |
      | Behat activity nine       |             | alice            | true                     | -21 days |                   |
      | Behat activity ten        |             | alice            | true                     | -22 days |                   |
      | Behat activity eleven     |             | alice            | true                     | -23 days |                   |
      | Behat activity twelve     |             | alice            | true                     | -24 days |                   |
      | Behat activity thirteen   |             | alice            | true                     | -25 days |                   |
      | Behat activity fourteen   |             | alice            | true                     | -26 days |                   |
      | Behat activity fifteen    |             | alice            | true                     | -27 days |                   |
      | Behat activity sixteen    |             | alice            | true                     | -28 days |                   |
      | Behat activity seventeen  |             | alice            | true                     | -29 days |                   |
      | Behat activity eighteen   |             | alice            | true                     | -30 days |                   |
      | Behat activity nineteen   |             | alice            | true                     | -31 days |                   |
      | Behat activity twenty     |             | alice            | true                     | -32 days |                   |
      | Behat activity twenty one |             | alice            | true                     | -33 days |                   |
      | Behat activity twenty two |             | alice            | true                     | -34 days |                   |

    And the activity "Behat activity one" for user "alice" was progressed at date "-14 days"
    And the activity "Behat activity two" for user "alice" was progressed at date "-15 days"
    And the activity "Behat activity three" for user "alice" was progressed at date "-16 days"
    And the activity "Behat activity four" for user "alice" was progressed at date "-17 days"
    And the activity "Behat activity five" for user "alice" was progressed at date "-18 days"
    And the activity "Behat activity six" for user "alice" was progressed at date "-19 days"
    And the activity "Behat activity seven" for user "alice" was progressed at date "-20 days"
    And the activity "Behat activity eight" for user "alice" was progressed at date "-21 days"
    And the activity "Behat activity nine" for user "alice" was progressed at date "-22 days"
    And the activity "Behat activity ten" for user "alice" was progressed at date "-23 days"
    And the activity "Behat activity eleven" for user "alice" was progressed at date "-24 days"
    And the activity "Behat activity twelve" for user "alice" was progressed at date "-25 days"
    And the activity "Behat activity thirteen" for user "alice" was progressed at date "-26 days"
    And the activity "Behat activity fourteen" for user "alice" was progressed at date "-27 days"
    And the activity "Behat activity fifteen" for user "alice" was progressed at date "-28 days"
    And the activity "Behat activity sixteen" for user "alice" was progressed at date "-29 days"
    And the activity "Behat activity seventeen" for user "alice" was progressed at date "-30 days"
    And the activity "Behat activity eighteen" for user "alice" was progressed at date "-31 days"
    And the activity "Behat activity nineteen" for user "alice" was progressed at date "-32 days"
    And the activity "Behat activity twenty" for user "alice" was progressed at date "-33 days"
    And the activity "Behat activity twenty one" for user "alice" was progressed at date "-34 days"
    And the activity "Behat activity twenty two" for user "alice" was progressed at date "-35 days"

    When I log in as "alice"
    And I navigate to my overview
    Then I should see "22" in the ".tui-overviewActivitiesSection__content-notProgressed .tui-overviewStatusTable__header" "css_element"

    # Modal content
    When I click on "not progressed" header count for activities overview group
    Then I should see "22 activities not progressed"

    And I should see "Behat activity one" in row "1" of the "activities" view all modal
    And I should see the "update" date for activity "Behat activity one" for user "alice" in row "1" of the "not started" activities overview modal
    And I should see "Due" in row "1" of the "activities" view all modal
    And I should see the "overdue" icon in row "1" of the activities overview modal
    And I should see "Description" in row "1" of the "activities" view all modal
    Then I click on description in row "1" of the "activities" view all modal
    And I should see "first desc"
    Then I click on description in row "1" of the "activities" view all modal
    And I should not see "first desc"

    And I should see the "update" date for activity "Behat activity two" for user "alice" in row "2" of the "not started" activities overview modal
    And I should see "Due" in row "2" of the "activities" view all modal
    And I should see the "due soon" icon in row "2" of the activities overview modal
    And I should not see "Description" in row "2" of the "activities" view all modal

    And I should see the "update" date for activity "Behat activity three" for user "alice" in row "3" of the "not started" activities overview modal
    And I should see "Due" in row "3" of the "activities" view all modal
    And I should not see the "due soon" icon in row "3" of the activities overview modal
    And I should see "Description" in row "3" of the "activities" view all modal
    Then I click on description in row "3" of the "activities" view all modal
    And I should see "third desc"
    Then I click on description in row "3" of the "activities" view all modal
    And I should not see "third desc"

    And I should see the "update" date for activity "Behat activity four" for user "alice" in row "4" of the "not started" activities overview modal
    And I should not see "Due" in row "4" of the "activities" view all modal
    And I should not see "Description" in row "4" of the "activities" view all modal

    Then I should see "Load more"
    And I should not see "Behat activity eleven"
    And I should not see "Behat activity fourteen"
    And I should not see "Behat activity twenty two"
    And I should not see "Behat activity twenty one"
    Then I click on "Load more" "button"
    And I should see "Behat activity eleven"
    And I should see "Behat activity fourteen"
    And I should see "Load more"
    And I should not see "Behat activity twenty one"
    And I should not see "Behat activity twenty two"
    Then I click on "Load more" "button"
    And I should see "Behat activity twenty one"
    And I should see "Behat activity twenty two"
    And I should not see "Load more"


  Scenario: completed activity overview modal should exist when there are more than two activities in that state
    Given the following "subject instances" exist in "mod_perform" plugin:
      | activity_name               | description | subject_username | subject_is_participating | backdate | due_date_relative |
      | Behat activity one          | first desc  | alice            | true                     | -13 days | -2 days           |
      | Behat activity two          |             | alice            | true                     | -14 days | +4 days           |
      | Behat activity three        | third desc  | alice            | true                     | -15 days | +14 days          |
      | Behat activity four         |             | alice            | true                     | -16 days |                   |
      | Behat activity five         |             | alice            | true                     | -17 days |                   |
      | Behat activity six          |             | alice            | true                     | -18 days |                   |
      | Behat activity seven        |             | alice            | true                     | -19 days |                   |
      | Behat activity eight        |             | alice            | true                     | -20 days |                   |
      | Behat activity nine         |             | alice            | true                     | -21 days |                   |
      | Behat activity ten          |             | alice            | true                     | -22 days |                   |
      | Behat activity eleven       |             | alice            | true                     | -23 days |                   |
      | Behat activity twelve       |             | alice            | true                     | -24 days |                   |
      | Behat activity thirteen     |             | alice            | true                     | -25 days |                   |
      | Behat activity fourteen     |             | alice            | true                     | -26 days |                   |
      | Behat activity fifteen      |             | alice            | true                     | -27 days |                   |
      | Behat activity sixteen      |             | alice            | true                     | -28 days |                   |
      | Behat activity seventeen    |             | alice            | true                     | -29 days |                   |
      | Behat activity eighteen     |             | alice            | true                     | -30 days |                   |
      | Behat activity nineteen     |             | alice            | true                     | -31 days |                   |
      | Behat activity twenty       |             | alice            | true                     | -32 days |                   |
      | Behat activity twenty one   |             | alice            | true                     | -33 days |                   |
      | Behat activity twenty two   |             | alice            | true                     | -34 days |                   |
      | Behat activity twenty three |             | alice            | true                     | -34 days |                   |

    And the activity "Behat activity one" for user "alice" was completed at date "-2 days"
    And the activity "Behat activity two" for user "alice" was completed at date "-3 days"
    And the activity "Behat activity three" for user "alice" was completed at date "-4 days"
    And the activity "Behat activity four" for user "alice" was completed at date "-5 days"
    And the activity "Behat activity five" for user "alice" was completed at date "-6 days"
    And the activity "Behat activity six" for user "alice" was completed at date "-7 days"
    And the activity "Behat activity seven" for user "alice" was completed at date "-8 days"
    And the activity "Behat activity eight" for user "alice" was completed at date "-9 days"
    And the activity "Behat activity nine" for user "alice" was completed at date "-10 days"
    And the activity "Behat activity ten" for user "alice" was completed at date "-11 days"
    And the activity "Behat activity eleven" for user "alice" was completed at date "-12 days"
    And the activity "Behat activity twelve" for user "alice" was completed at date "-12 days"
    And the activity "Behat activity thirteen" for user "alice" was completed at date "-12 days"
    And the activity "Behat activity fourteen" for user "alice" was completed at date "-12 days"
    And the activity "Behat activity fifteen" for user "alice" was completed at date "-12 days"
    And the activity "Behat activity sixteen" for user "alice" was completed at date "-12 days"
    And the activity "Behat activity seventeen" for user "alice" was completed at date "-12 days"
    And the activity "Behat activity eighteen" for user "alice" was completed at date "-12 days"
    And the activity "Behat activity nineteen" for user "alice" was completed at date "-12 days"
    And the activity "Behat activity twenty" for user "alice" was completed at date "-12 days"
    And the activity "Behat activity twenty one" for user "alice" was completed at date "-13 days"
    And the activity "Behat activity twenty two" for user "alice" was completed at date "-13 days"
    And the activity "Behat activity twenty three" for user "alice" was completed at date "-15 days"


    When I log in as "alice"
    And I navigate to my overview
    Then I should see "22" in the ".tui-overviewActivitiesSection__content-completed .tui-overviewStatusTable__header" "css_element"

    # Modal content
    When I click on "completed" header count for activities overview group
    Then I should see "22 activities completed"

    And I should see "Behat activity one" in row "1" of the "activities" view all modal
    And I should see the "completed" date for activity "Behat activity one" for user "alice" in row "1" of the "achieved" activities overview modal
    And I should see "Description" in row "1" of the "activities" view all modal
    Then I click on description in row "1" of the "activities" view all modal
    And I should see "first desc"
    Then I click on description in row "1" of the "activities" view all modal
    And I should not see "first desc"

    And I should see the "completed" date for activity "Behat activity two" for user "alice" in row "2" of the "achieved" activities overview modal
    And I should not see "Description" in row "2" of the "activities" view all modal

    And I should see the "completed" date for activity "Behat activity three" for user "alice" in row "3" of the "achieved" activities overview modal
    And I should see "Description" in row "3" of the "activities" view all modal
    Then I click on description in row "3" of the "activities" view all modal
    And I should see "third desc"
    Then I click on description in row "3" of the "activities" view all modal
    And I should not see "third desc"

    And I should see the "completed" date for activity "Behat activity four" for user "alice" in row "4" of the "achieved" activities overview modal
    And I should not see "Description" in row "4" of the "activities" view all modal

    Then I should see "Load more"
    And I should not see "Behat activity eleven"
    And I should not see "Behat activity fourteen"
    And I should not see "Behat activity twenty two"
    And I should not see "Behat activity twenty one"
    Then I click on "Load more" "button"
    And I should see "Behat activity eleven"
    And I should see "Behat activity fourteen"
    And I should see "Load more"
    And I should not see "Behat activity twenty one"
    And I should not see "Behat activity twenty two"
    Then I click on "Load more" "button"
    And I should see "Behat activity twenty one"
    And I should see "Behat activity twenty two"
    And I should not see "Load more"
    And I click on "Close" "button"

    When I select "30" from the "Period" singleselect
    Then I should see "23" in the ".tui-overviewActivitiesSection__content-completed .tui-overviewStatusTable__header" "css_element"
    # Modal content
    When I click on "completed" header count for activities overview group
    Then I should see "23 activities completed"
