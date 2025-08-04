@core @core_my @mod_perform @perform_overview @javascript
Feature: Perform overview page for activities

  Background:
    Given I am on a totara site
    And the following "users" exist:
      | username | firstname | lastname | email                   |
      | alice    | Alice     | Smith    | alice.smith@example.com |

  Scenario: Activities overview when no activities are assigned
    When I log in as "alice"
    And I navigate to my overview
    Then I should see "No activities are currently assigned for this period."

  Scenario: Activities overview for recently assigned activity without due date
    Given the following "subject instances" exist in "mod_perform" plugin:
      | activity_name      | subject_username | subject_is_participating |
      | Behat activity one | alice            | true                     |
    When I log in as "alice"
    And I navigate to my overview

    # Summary content
    Then I should see "Performance activities" in the activities overview header
    And I should see "1" in the activities overview total count
    And I should see "0 completed" in the activities overview graph legend
    And I should see "0 progressed" in the activities overview graph legend
    And I should see "0 not progressed" in the activities overview graph legend
    And I should see "1 not started" in the activities overview graph legend

    # 'Not started' section content
    And I should see the "not started" activities overview section
    And I should see "Not started" in the "not started" activities overview section
    And I should see "1" in the "not started" activities overview section header count
    And I should see the "assignment" date for activity "Behat activity one" for user "alice" in row "1" of the "not started" activities overview section
    And I should not see "Due" in the "not started" activities overview section
    And I should not see the "due soon" icon in row "1" of the "not started" activities overview section
    And I should not see the "overdue" icon in row "1" of the "not started" activities overview section
    And I should not see the "achieved" activities overview section
    And I should not see the "progressed" activities overview section
    And I should not see the "not progressed" activities overview section

  Scenario: Activities overview for activity with due date not due soon
    Given the following "subject instances" exist in "mod_perform" plugin:
      | activity_name      | subject_username | subject_is_participating | due_date_relative |
      | Behat activity one | alice            | true                     | +10 days          |
    When I log in as "alice"
    And I navigate to my overview

    # Summary content
    Then I should not see the activities overview due soon count

    # 'Not started' section content
    And I should see the "not started" activities overview section
    And I should see "1" in the "not started" activities overview section header count
    And I should see the "assignment" date for activity "Behat activity one" for user "alice" in row "1" of the "not started" activities overview section
    And I should see the "due" date for activity "Behat activity one" for user "alice" in row "1" of the "not started" activities overview section
    And I should not see the "due soon" icon in row "1" of the "not started" activities overview section
    And I should not see the "overdue" icon in row "1" of the "not started" activities overview section
    And I should not see the "achieved" activities overview section
    And I should not see the "progressed" activities overview section
    And I should not see the "not progressed" activities overview section

  Scenario: Activities overview for activity with due date due soon
    Given the following "subject instances" exist in "mod_perform" plugin:
      | activity_name      | subject_username | subject_is_participating | due_date_relative |
      | Behat activity one | alice            | true                     | +2 days           |
    When I log in as "alice"
    And I navigate to my overview

    # Summary content
    Then I should see "1" in the activities overview due soon count

    # 'Not started' section content
    And I should see the "not started" activities overview section
    And I should see "1" in the "not started" activities overview section header count
    And I should see the "assignment" date for activity "Behat activity one" for user "alice" in row "1" of the "not started" activities overview section
    And I should see the "due" date for activity "Behat activity one" for user "alice" in row "1" of the "not started" activities overview section
    And I should see the "due soon" icon in row "1" of the "not started" activities overview section
    And I should not see the "overdue" icon in row "1" of the "not started" activities overview section
    And I should not see the "achieved" activities overview section
    And I should not see the "progressed" activities overview section
    And I should not see the "not progressed" activities overview section

  Scenario: Activities overview for activity with due date overdue
    Given the following "subject instances" exist in "mod_perform" plugin:
      | activity_name      | subject_username | subject_is_participating | due_date_relative |
      | Behat activity one | alice            | true                     | -2 days           |
    When I log in as "alice"
    And I navigate to my overview

    # Summary content
    Then I should not see the activities overview due soon count

    # 'Not started' section content
    And I should see the "not started" activities overview section
    And I should see "1" in the "not started" activities overview section header count
    And I should see the "assignment" date for activity "Behat activity one" for user "alice" in row "1" of the "not started" activities overview section
    And I should see the "due" date for activity "Behat activity one" for user "alice" in row "1" of the "not started" activities overview section
    And I should not see the "due soon" icon in row "1" of the "not started" activities overview section
    And I should see the "overdue" icon in row "1" of the "not started" activities overview section
    And I should not see the "achieved" activities overview section
    And I should not see the "progressed" activities overview section
    And I should not see the "not progressed" activities overview section

  Scenario: Activities overview for more than two activities with sorting
    Given the following "subject instances" exist in "mod_perform" plugin:
      | activity_name        | subject_username | subject_is_participating | backdate |
      | Behat activity one   | alice            | true                     | -3 days  |
      | Behat activity two   | alice            | true                     | -4 days  |
      | Behat activity three | alice            | true                     | -2 days  |

    When I log in as "alice"
    And I navigate to my overview

    # Summary content
    Then I should see "3" in the activities overview total count
    And I should see "0 completed" in the activities overview graph legend
    And I should see "0 progressed" in the activities overview graph legend
    And I should see "0 not progressed" in the activities overview graph legend
    And I should see "3 not started" in the activities overview graph legend

    # 'Not started' section content
    And I should see the "not started" activities overview section
    And I should see "Behat activity three" in row "1" of the "not started" activities overview section
    And I should see "Behat activity one" in row "2" of the "not started" activities overview section
    And I should not see "Behat activity two" in the "not started" activities overview section
    And I should not see the "achieved" activities overview section
    And I should not see the "progressed" activities overview section
    And I should not see the "not progressed" activities overview section

  Scenario: Activities overview for progressed activities
    Given the following "subject instances" exist in "mod_perform" plugin:
      | activity_name      | subject_username | subject_is_participating | backdate |
      | Behat activity one | alice            | true                     | -6 days  |
      | Behat activity two | alice            | true                     | -5 days  |
    And the activity "Behat activity one" for user "alice" was progressed at date "-4 days"
    And the activity "Behat activity two" for user "alice" was progressed at date "-3 days"

    When I log in as "alice"
    And I navigate to my overview

    # Summary content
    And I should see "2" in the activities overview total count
    And I should see "0 completed" in the activities overview graph legend
    And I should see "2 progressed" in the activities overview graph legend
    And I should see "0 not progressed" in the activities overview graph legend
    And I should see "0 not started" in the activities overview graph legend

    # 'Progressed' section content
    And I should see the "progressed" activities overview section
    And I should see "Behat activity two" in row "1" of the "progressed" activities overview section
    And I should see "Behat activity one" in row "2" of the "progressed" activities overview section
    And I should see the "update" date for activity "Behat activity two" for user "alice" in row "1" of the "progressed" activities overview section
    And I should see the "update" date for activity "Behat activity one" for user "alice" in row "2" of the "progressed" activities overview section
    And I should not see the "achieved" activities overview section
    And I should not see the "not started" activities overview section
    And I should not see the "not progressed" activities overview section

    # Check the popover on the "Updated" string
    When I click on updated string in row "1" of the "progressed" activities overview section
    Then I should see "Activity was viewed."

    # Check the link for an activity
    When I close the tui popover
    And I follow "Behat activity two"
    Then I should see "You are participating in an activity about yourself"

  Scenario: Activities overview observation period filter and not progressed section
    Given the following "subject instances" exist in "mod_perform" plugin:
      | activity_name      | subject_username | subject_is_participating | backdate |
      | Behat activity one | alice            | true                     | -20 days |
      | Behat activity two | alice            | true                     | -20 days |
    And the activity "Behat activity one" for user "alice" was progressed at date "-4 days"
    And the activity "Behat activity two" for user "alice" was progressed at date "-18 days"

    When I log in as "alice"
    And I navigate to my overview

    # Summary content
    And I should see "2" in the activities overview total count
    And I should see "0 completed" in the activities overview graph legend
    And I should see "1 progressed" in the activities overview graph legend
    And I should see "1 not progressed" in the activities overview graph legend
    And I should see "0 not started" in the activities overview graph legend

    # 'Progressed' section content
    And I should see the "progressed" activities overview section
    And I should see "Behat activity one" in row "1" of the "progressed" activities overview section
    And I should not see "Behat activity two" in the "progressed" activities overview section
    And I should see the "update" date for activity "Behat activity one" for user "alice" in row "1" of the "progressed" activities overview section

    # 'Not progressed' section content
    And I should see the "not progressed" activities overview section
    And I should see "Behat activity two" in row "1" of the "not progressed" activities overview section
    And I should not see "Behat activity one" in the "not progressed" activities overview section
    And I should see the "update" date for activity "Behat activity two" for user "alice" in row "1" of the "not progressed" activities overview section

    And I should not see the "achieved" activities overview section
    And I should not see the "not started" activities overview section

    When I select "30" from the "Period" singleselect

    # Summary content
    Then I should see "2" in the activities overview total count
    And I should see "0 completed" in the activities overview graph legend
    And I should see "2 progressed" in the activities overview graph legend
    And I should see "0 not progressed" in the activities overview graph legend
    And I should see "0 not started" in the activities overview graph legend

    # 'Progressed' section content
    And I should see the "progressed" activities overview section
    And I should see "Behat activity one" in row "1" of the "progressed" activities overview section
    And I should see "Behat activity two" in row "2" of the "progressed" activities overview section

  Scenario: Activities overview observation period filter and achieved section
    Given the following "subject instances" exist in "mod_perform" plugin:
      | activity_name      | subject_username | subject_is_participating | backdate |
      | Behat activity one | alice            | true                     | -20 days |
      | Behat activity two | alice            | true                     | -20 days |
    And the activity "Behat activity two" for user "alice" was completed at date "-18 days"

    When I log in as "alice"
    And I navigate to my overview

    # Summary content
    Then I should see "1" in the activities overview total count
    And I should see "0 completed" in the activities overview graph legend
    And I should see "0 progressed" in the activities overview graph legend
    And I should see "0 not progressed" in the activities overview graph legend
    And I should see "1 not started" in the activities overview graph legend

    # 'Not started' section content
    And I should see the "not started" activities overview section
    And I should see "Behat activity one" in row "1" of the "not started" activities overview section
    And I should not see the "achieved" activities overview section
    And I should not see the "progressed" activities overview section
    And I should not see the "not progressed" activities overview section

    When I select "30" from the "Period" singleselect

    # Summary content
    Then I should see "2" in the activities overview total count
    And I should see "1 completed" in the activities overview graph legend
    And I should see "0 progressed" in the activities overview graph legend
    And I should see "0 not progressed" in the activities overview graph legend
    And I should see "1 not started" in the activities overview graph legend

    # 'Not started' section content
    And I should see the "not started" activities overview section
    And I should see "Behat activity one" in row "1" of the "not started" activities overview section

    # 'Achieved' section content
    And I should see the "completed" activities overview section
    And I should see "Behat activity two" in row "1" of the "completed" activities overview section
    And I should see the "completed" date for activity "Behat activity two" for user "alice" in row "1" of the "completed" activities overview section

  Scenario: Manager views activities overview for a direct report
    Given the following "users" exist:
      | username | firstname | lastname | email                 |
      | manager  | Manager   | McMan    | manager@example.com   |
    And the following job assignments exist:
      | user    | manager | fullname   |
      | alice   | manager | Subject JA |
    And the following "subject instances" exist in "mod_perform" plugin:
      | activity_name      | subject_username | subject_is_participating | other_participant_username | backdate |
      | Behat activity one | alice            | true                     | manager                    | -2 days  |
      | Behat activity two | alice            | true                     |                            | -3 days  |

    When I log in as "manager"
    And I navigate to my overview for user "alice"
    Then I should see "Behat activity one" in row "1" of the "not started" activities overview section
    And I should see "Behat activity two" in row "2" of the "not started" activities overview section
    And "Behat activity one" "link" should exist
    And "Behat activity two" "link" should not exist

    When I log out
    And I log in as "alice"
    And I navigate to my overview
    Then I should see "Behat activity one" in row "1" of the "not started" activities overview section
    And I should see "Behat activity two" in row "2" of the "not started" activities overview section
    And "Behat activity one" "link" should exist
    And "Behat activity two" "link" should exist
