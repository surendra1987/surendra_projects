@core @core_my @javascript @perform_goal @perform_overview
Feature: Perform overview page for goals

  Background:
    Given I am on a totara site
    And the following "users" exist:
      | username | firstname | lastname | email                   |
      | alice    | Alice     | Smith    | alice.smith@example.com |

  Scenario: Goals overview when no goals are assigned
    When I log in as "alice"
    And I navigate to my overview
    Then I should see "No goals are currently assigned for this period."

  Scenario: Goals overview for goal with target date not due soon
    Given the following "goals" exist in "perform_goal" plugin:
      | name                 | id_number                   | username | owner | created_at | target_date |
      | Alice Perform goal 1 | test-goal-behat-id-number_2 | alice    | admin | -2 days    | +10 days    |
    When I log in as "alice"
    And I navigate to my overview

    # Summary content
    Then I should not see the goals overview due soon count

    # 'Not started' section content
    And I should see the "not started" goals overview section
    And I should see "1" in the "not started" goals overview section header count
    And I should see the "assignment" date for goal "Alice Perform goal 1" for user "alice" in row "1" of the "not started" goals overview section
    And I should see the "due" date for goal "Alice Perform goal 1" for user "alice" in row "1" of the "not started" goals overview section
    And I should not see the "due soon" icon in row "1" of the "not started" goals overview section
    And I should not see the "overdue" icon in row "1" of the "not started" goals overview section
    And I should not see the "achieved" goals overview section
    And I should not see the "progressed" goals overview section
    And I should not see the "not progressed" goals overview section

  Scenario: Goals overview for goal with target date due soon
    Given the following "goals" exist in "perform_goal" plugin:
      | name                 | id_number                   | username | owner | created_at | target_date |
      | Alice Perform goal 2 | test-goal-behat-id-number_2 | alice    | admin | -2 days    | +2 days    |
    When I log in as "alice"
    And I navigate to my overview

    # Summary content
    Then I should see "1" in the goals overview due soon count

    # 'Not started' section content
    And I should see the "not started" goals overview section
    And I should see "1" in the "not started" goals overview section header count
    And I should see the "assignment" date for goal "Alice Perform goal 2" for user "alice" in row "1" of the "not started" goals overview section
    And I should see the "due" date for goal "Alice Perform goal 2" for user "alice" in row "1" of the "not started" goals overview section
    And I should see the "due soon" icon in row "1" of the "not started" goals overview section
    And I should not see the "overdue" icon in row "1" of the "not started" goals overview section
    And I should not see the "achieved" goals overview section
    And I should not see the "progressed" goals overview section
    And I should not see the "not progressed" goals overview section

  Scenario: Goals overview for goal with target date overdue
    Given the following "goals" exist in "perform_goal" plugin:
    | name                 | id_number                   | username | owner | created_at | target_date |
    | Alice Perform goal 3 | test-goal-behat-id-number_3 | alice    | admin | -2 days    | -2 days     |
    When I log in as "alice"
    And I navigate to my overview

    # Summary content
    Then I should not see the goals overview due soon count

    # 'Not started' section content
    And I should see the "not started" goals overview section
    And I should see "1" in the "not started" goals overview section header count
    And I should see the "assignment" date for goal "Alice Perform goal 3" for user "alice" in row "1" of the "not started" goals overview section
    And I should see the "due" date for goal "Alice Perform goal 3" for user "alice" in row "1" of the "not started" goals overview section
    And I should not see the "due soon" icon in row "1" of the "not started" goals overview section
    And I should see the "overdue" icon in row "1" of the "not started" goals overview section
    And I should not see the "achieved" goals overview section
    And I should not see the "progressed" goals overview section
    And I should not see the "not progressed" goals overview section

  Scenario: Goals overview for progressed goals
    Given the following "goals" exist in "perform_goal" plugin:
      | name                 | id_number                   | username | owner | created_at | target_date | status      |
      | Alice Perform goal 4 | test-goal-behat-id-number_4 | alice    | admin | -5 days    | +10 days    | in_progress |
      | Alice Perform goal 5 | test-goal-behat-id-number_5 | alice    | admin | -6 days    | +10 days    | in_progress |
    When I log in as "alice"
    And I navigate to my overview

    # Summary content
    And I should see "2" in the goals overview total count
    And I should see "0 achieved" in the goals overview graph legend
    And I should see "2 progressed" in the goals overview graph legend
    And I should see "0 not progressed" in the goals overview graph legend
    And I should see "0 not started" in the goals overview graph legend

    # 'Progressed' section content
    And I should see the "progressed" goals overview section
    And I should see "Alice Perform goal 4" in row "1" of the "progressed" goals overview section
    And I should see "Alice Perform goal 5" in row "2" of the "progressed" goals overview section
    And I should see the "update" date for goal "Alice Perform goal 4" for user "alice" in row "1" of the "progressed" goals overview section
    And I should see the "update" date for goal "Alice Perform goal 5" for user "alice" in row "2" of the "progressed" goals overview section
    And I should not see the "achieved" goals overview section
    And I should not see the "not started" goals overview section
    And I should not see the "not progressed" goals overview section

    # Check the popover on the "Updated" string
    When I click on updated string in row "1" of the "progressed" goals overview section
    Then I should see "Goal progress was updated to 'In progress'"

    # Check the link for a company goal
    When I close the tui popover
    And I follow "Alice Perform goal 4"
    And I wait "2" seconds
    Then I should see "Alice Perform goal 4"

  Scenario: Goals overview observation period filter and not progressed section
    Given the following "goals" exist in "perform_goal" plugin:
      | name                 | id_number                   | username | owner | created_at | target_date |
      | Alice Perform goal 6 | test-goal-behat-id-number_6 | alice    | admin | -4 days    | +10 days    |
    Given the following "goals" exist in "perform_goal" plugin:
      | name                  | id_number                     | username | owner | created_at | target_date | description | current_value_updated_at | updated_at | status_updated_at | status      |
      | Alice Perform goal 7  | test-goal-behat-id-number_b7  | alice    | admin | -20 days   | +12 days    | descr 7     | -20 days                 | -20 days   | -28 days          | in_progress |
    And the goal "Alice Perform goal 6" for user "alice" was set to status "in_progress" and current value "25.0"

    When I log in as "alice"
    And I navigate to my overview

    # Summary content
    And I should see "2" in the goals overview total count
    And I should see "0 achieved" in the goals overview graph legend
    And I should see "1 progressed" in the goals overview graph legend
    And I should see "1 not progressed" in the goals overview graph legend
    And I should see "0 not started" in the goals overview graph legend

    # 'Progressed' section content
    And I should see the "progressed" goals overview section
    And I should see "Alice Perform goal 6" in row "1" of the "progressed" goals overview section
    And I should not see "Alice Perform goal 7" in the "progressed" goals overview section
    And I should see the "update" date for goal "Alice Perform goal 6" for user "alice" in row "1" of the "progressed" goals overview section

    # 'Not progressed' section content
    And I should see the "not progressed" goals overview section
    And I should see "Alice Perform goal 7" in row "1" of the "not progressed" goals overview section
    And I should see the "update" date for goal "Alice Perform goal 7" for user "alice" in row "1" of the "not progressed" goals overview section

    And I should not see the "achieved" goals overview section

    When I select "30" from the "Period" singleselect

    # Summary content
    Then I should see "2" in the goals overview total count
    And I should see "0 achieved" in the goals overview graph legend
    And I should see "2 progressed" in the goals overview graph legend
    And I should see "0 not progressed" in the goals overview graph legend
    And I should see "0 not started" in the goals overview graph legend

    # 'Progressed' section content
    And I should see the "progressed" goals overview section
    And I should see "Alice Perform goal 6" in row "2" of the "progressed" goals overview section

    # Check the link for a personal goal
    When I follow "Alice Perform goal 6"
    And I wait "2" seconds
    Then I should see "Alice Perform goal 6"

  Scenario: Goals overview observation period filter and achieved section
    Given the following "goals" exist in "perform_goal" plugin:
      | name                 | id_number                   | username | owner | created_at | target_date |
      | Alice Perform goal 8 | test-goal-behat-id-number_8 | alice    | admin | -20 days   | +10 days    |
      | Alice Perform goal 9 | test-goal-behat-id-number_9 | alice    | admin | -20 days   | +10 days    |
    And the goal "Alice Perform goal 8" for user "alice" was set to status "completed" and current value "25.0"

    When I log in as "alice"
    And I navigate to my overview

    # Summary content
    Then I should see "2" in the goals overview total count
    And I should see "1 achieved" in the goals overview graph legend
    And I should see "0 progressed" in the goals overview graph legend
    And I should see "0 not progressed" in the goals overview graph legend
    And I should see "1 not started" in the goals overview graph legend

    # 'Not started' section content
    And I should see the "not started" goals overview section
    And I should see "Alice Perform goal 9" in row "1" of the "not started" goals overview section
    And I should see the "achieved" goals overview section
    And I should not see the "progressed" goals overview section
    And I should not see the "not progressed" goals overview section

    When I select "30" from the "Period" singleselect

    # Summary content
    Then I should see "2" in the goals overview total count
    And I should see "1 achieved" in the goals overview graph legend
    And I should see "0 progressed" in the goals overview graph legend
    And I should see "0 not progressed" in the goals overview graph legend
    And I should see "1 not started" in the goals overview graph legend

    # 'Not started' section content
    And I should see the "not started" goals overview section
    And I should see "Alice Perform goal 9" in row "1" of the "not started" goals overview section

    # 'Achieved' section content
    And I should see the "achieved" goals overview section
    And I should see "Alice Perform goal 8" in row "1" of the "achieved" goals overview section
    And I should see the "achieved" date for goal "Alice Perform goal 8" for user "alice" in row "1" of the "achieved" goals overview section

  # Bugfix regression test
  Scenario: Check if a user can view goals belonging to someone else.
    Given the following "goals" exist in "perform_goal" plugin:
      | name                  | id_number                    | username | owner | created_at | target_date |
      | Alice Perform goal 10 | test-goal-behat-id-number_10 | alice    | admin | -20 days   | +10 days    |
    And the following "users" exist:
      | username | firstname | lastname | email                  |
      | bob      | Bob       | Miller   | bob.miller@example.com |
    When I log in as "bob"
    And I navigate to my overview
    Then I should see "No goals are currently assigned for this period."
