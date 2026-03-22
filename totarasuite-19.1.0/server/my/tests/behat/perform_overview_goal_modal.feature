@core @core_my @totara @javascript @perform_goal @perform_overview
Feature: Goal modal in the perform overview page

  Background:
    Given I am on a totara site
    And the following "users" exist:
      | username | firstname | lastname | email                   |
      | alice    | Alice     | Smith    | alice.smith@example.com |

  Scenario: Goals overview modal should not exist when status section has no more than two goals
    Given the following "goals" exist in "perform_goal" plugin:
      | name                  | id_number                    | username | owner | created_at | target_date |
      | Alice Perform goal B1 | test-goal-behat-id-number_b1 | alice    | admin | 0 days     | +10 days    |
      | Alice Perform goal B2 | test-goal-behat-id-number_b2 | alice    | admin | 0 days     | +10 days    |
      | Alice Perform goal B3 | test-goal-behat-id-number_b3 | alice    | admin | -5 days    | +10 days    |
      | Alice Perform goal B4 | test-goal-behat-id-number_b4 | alice    | admin | -30 days   | +10 days    |
    And the goal "Alice Perform goal B3" for user "alice" was set to status "in_progress" and current value "25.0"
    And the goal "Alice Perform goal B4" for user "alice" was set to status "in_progress" and current value "25.0"

    When I log in as "alice"
    Then I navigate to my overview
    And ".tui-overviewStatusTable__header-countButton" "css_element" should not exist

  Scenario: Goals overview modal should exist when more than two goals are assigned
    # Not started goals. The sorting is by "updated_at", so give them distinct values to make the order predictable.
    Given the following "goals" exist in "perform_goal" plugin:
      | name                   | id_number                       | username | owner | created_at | target_date | description | updated_at |
      | Alice Perform goal B5  | test-goal-behat-id-number_b5    | alice    | admin | -1 days    | +1 days     | descr b5    | -2 days    |
      | Alice Perform goal B9  | test-goal-behat-id-number_b9    | alice    | admin | +2 days    | -5 days     | descr b9    | -3 days    |
      | Alice Perform goal B10 | test-goal-behat-id-number_b10   | alice    | admin | -2 days    | -7 days     | descr b10   | -1 days    |

    # In progress goals.
    And the following "goals" exist in "perform_goal" plugin:
      | name                   | id_number                       | username | owner | created_at | target_date | description | current_value_updated_at | updated_at | status_updated_at | status      |
      | Alice Perform goal B6  | test-goal-behat-id-number_b6    | alice    | admin | -1 days    | +2 days     | descr b6    | -5 days                  | -5 days    | -5 days           | in_progress |
      | Alice Perform goal B7  | test-goal-behat-id-number_b7    | alice    | admin | -3 days    | -2 days     | descr b7    | -6 days                  | -6 days    | -6 days           | in_progress |
      | Alice Perform goal B12 | test-goal-behat-id-number_b12   | alice    | admin | +2 days    | -17 days    | descr b12   | -1 days                  | -1 days    | -1 days           | in_progress |
      | Alice Perform goal B13 | test-goal-behat-id-number_b13   | alice    | admin | -2 days    | -20 days    | descr b13   | -2 days                  | -2 days    | -2 days           | in_progress |
      | Alice Perform goal B14 | test-goal-behat-id-number_b14   | alice    | admin | +2 days    | -6 days     | descr b14   | -3 days                  | -3 days    | -3 days           | in_progress |
      | Alice Perform goal B15 | test-goal-behat-id-number_b15   | alice    | admin | -2 days    | -7 days     | descr b15   | -4 days                  | -4 days    | -4 days           | in_progress |

    # Completed goals.
    And the following "goals" exist in "perform_goal" plugin:
      | name                   | id_number                       | username | owner | created_at | target_date | description | current_value_updated_at | updated_at | closed_at | status_updated_at | status    | current_value |
      | Alice Perform goal B18 | test-goal-behat-id-number_b18   | alice    | admin | -22 days   | -11 days    | descr b18   | -1 days                  | -1 days    | -1 days   | -1 days           | completed | 100.0         |
      | Alice Perform goal B19 | test-goal-behat-id-number_b19   | alice    | admin | -21 days   | -11 days    | descr b19   | -2 days                  | -2 days    | -2 days   | -2 days           | completed | 100.0         |
      | Alice Perform goal B20 | test-goal-behat-id-number_b20   | alice    | admin | -20 days   | -11 days    | descr b20   | -3 days                  | -3 days    | -3 days   | -3 days           | completed | 100.0         |

    # Not progressed goals.
    And the following "goals" exist in "perform_goal" plugin:
      | name                    | id_number                       | username | owner | created_at | target_date | description | current_value_updated_at | updated_at | status_updated_at | status      |
      | Alice Perform goal B16  | test-goal-behat-id-number_b16   | alice    | admin | -28 days   | +12 days    | descr b16   | -28 days                 | -28 days   | -28 days          | in_progress |
      | Alice Perform goal B17  | test-goal-behat-id-number_b17   | alice    | admin | -29 days   | +0 days     | descr b17   | -29 days                 | -29 days   | -29 days          | in_progress |
      | Alice Perform goal B17b | test-goal-behat-id-number_b17b  | alice    | admin | -30 days   | -1 days     | descr b17b  | -30 days                 | -30 days   | -30 days          | in_progress |

    When I log in as "alice"
    Then I navigate to my overview

    # Testing modal for not started goals
    Then I click on "not started" header count for goals overview group
    And I wait "2" seconds
    And I should see "3 goals not started"
    And I should see "Alice Perform goal B10" in row "1" of the "goals" view all modal
    And I should see the "assignment" date for goal "Alice Perform goal B10" for user "alice" in row "1" of the "not started" goals overview modal
    # Check description of first goal
    And I should see "Description" in row "1" of the "goals" view all modal
    # Check the toggle button and the content of the description
    Then I click on description in row "1" of the "goals" view all modal
    And I wait until "descr b10" "text" exists
    Then I click on description in row "1" of the "goals" view all modal
    Then I should not see "descr b10"
    # Check Due day of first goal
    And I should see "Due" in row "1" of the "goals" view all modal
    # Check second goal
    And I wait until "Alice Perform goal B5" "text" exists
    And I should see "Alice Perform goal B5" in row "2" of the "goals" view all modal
    And I should see the "assignment" date for goal "Alice Perform goal B5" for user "alice" in row "2" of the "not started" goals overview modal
    And I should see "Due" in row "2" of the "goals" view all modal
    And I should see the "due soon" icon in row "2" of the goals overview modal
    Then I click on description in row "2" of the "goals" view all modal
    And I wait until "descr b5" "text" exists
    Then I click on description in row "2" of the "goals" view all modal
    Then I should not see "descr b5"
    # Check the third goal
    And I should see "Alice Perform goal B9" in row "3" of the "goals" view all modal
    And I should see the "assignment" date for goal "Alice Perform goal B9" for user "alice" in row "3" of the "not started" goals overview modal
    And I should see "Due" in row "3" of the "goals" view all modal
    And I should see the "overdue" icon in row "3" of the goals overview modal
    Then I click on description in row "3" of the "goals" view all modal
    And I wait until "descr b9" "text" exists
    Then I click on description in row "3" of the "goals" view all modal
    Then I should not see "descr b9"
    # Check load more button is hide
    And I should not see "Load more"
    Then I close the tui modal

    # Testing modal for progressing goals
    Then I click on "progressed" header count for goals overview group
    And I wait "2" seconds
    And I should see "6 goals progressed"
    And I wait until "Alice Perform goal B12" "text" exists
    And I should see "Alice Perform goal B12" in row "1" of the "goals" view all modal
    And I should see the "update" date for goal "Alice Perform goal B12" for user "alice" in row "1" of the "progressed" goals overview modal
    # Check description of first goal in the modal
    And I should see "Description" in row "1" of the "goals" view all modal
    # Check the toggle button and the content of the description
    Then I click on description in row "1" of the "goals" view all modal
    And I wait until "descr b12" "text" exists
    # Check Due day of first goal in the modal
    And I should see "Due" in row "1" of the "goals" view all modal
    # Check second goal in the modal
    And I should see "Alice Perform goal B13" in row "2" of the "goals" view all modal
    And I should see the "update" date for goal "Alice Perform goal B13" for user "alice" in row "2" of the "progressed" goals overview modal
    And I should see "Due" in row "2" of the "goals" view all modal
    And I should see the "overdue" icon in row "2" of the goals overview modal
    Then I click on description in row "2" of the "goals" view all modal
    And I wait until "descr b13" "text" exists
    # Check the third goal in the modal
    And I should see "Alice Perform goal B14" in row "3" of the "goals" view all modal
    And I should see the "update" date for goal "Alice Perform goal B14" for user "alice" in row "3" of the "progressed" goals overview modal
    And I should see "Due" in row "3" of the "goals" view all modal
    And I should see the "overdue" icon in row "3" of the goals overview modal
    Then I click on description in row "3" of the "goals" view all modal
    And I wait until "descr b14" "text" exists
    # Check load more button is hide
    And I should not see "Load more"
    Then I close the tui modal

    # Testing modal for not progressing goals - CAN'T DO!
    Then I click on "not progressed" header count for goals overview group
    And I wait "2" seconds
    And I should see "3 goals not progressed"
    And I should see "Alice Perform goal B16" in row "1" of the "goals" view all modal
    And I should see the "update" date for goal "Alice Perform goal B16" for user "alice" in row "1" of the "not progressed" goals overview modal
    # Check description of first goal in the modal
    And I should see "Description" in row "1" of the "goals" view all modal
    # Check the toggle button and the content of the description
    Then I click on description in row "1" of the "goals" view all modal
    And I wait until "descr b16" "text" exists
    Then I click on description in row "1" of the "goals" view all modal
    And I should not see "descr b16"
    # Check Due day of first goal in the modal
    And I should see "Due" in row "1" of the "goals" view all modal
    # Check second goal in the modal
    And I should see "Alice Perform goal B17" in row "2" of the "goals" view all modal
    And I should see the "update" date for goal "Alice Perform goal B17" for user "alice" in row "2" of the "not progressed" goals overview modal
    And I should see "Due" in row "2" of the "goals" view all modal
    And I should see the "due soon" icon in row "2" of the goals overview modal
    Then I click on description in row "2" of the "goals" view all modal
    And I wait until "descr b17" "text" exists
    # Check the third goal in the modal
    And I should see "Alice Perform goal B17b" in row "3" of the "goals" view all modal
    And I should see the "update" date for goal "Alice Perform goal B17b" for user "alice" in row "3" of the "not progressed" goals overview modal
    And I should see "Due" in row "3" of the "goals" view all modal
    And I should see the "overdue" icon in row "3" of the goals overview modal
    Then I click on description in row "3" of the "goals" view all modal
    And I wait until "descr b17b" "text" exists
    # Check load more button is hide
    And I should not see "Load more"
    Then I click on "Close" "button"

    # Testing modal for complete goals
    Then I click on "achieved" header count for goals overview group
    And I wait "2" seconds
    And I should see "3 goals achieved"
    And I wait until "Alice Perform goal B18" "text" exists
    And I should see "Alice Perform goal B18" in row "1" of the "goals" view all modal
    And I should see the "achieved" date for goal "Alice Perform goal B18" for user "alice" in row "1" of the "achieved" goals overview modal
    # Check description of first goal in the modal
    And I should see "Description" in row "1" of the "goals" view all modal
    # Check the toggle button and the content of the description
    Then I click on description in row "1" of the "goals" view all modal
    And I wait until "descr b18" "text" exists
    # Check Due day of first goal in the modal
    And I should not see "Due" in row "1" of the "goals" view all modal
    # Check second goal in the modal
    And I should see "Alice Perform goal B19" in row "2" of the "goals" view all modal
    And I should see the "achieved" date for goal "Alice Perform goal B19" for user "alice" in row "2" of the "achieved" goals overview modal
    And I should not see the "due soon" icon in row "2" of the goals overview modal
    Then I click on description in row "2" of the "goals" view all modal
    And I wait until "descr b19" "text" exists
    # Check the third goad in the modal
    And I should see "Alice Perform goal B20" in row "3" of the "goals" view all modal
    And I should see the "achieved" date for goal "Alice Perform goal B20" for user "alice" in row "3" of the "achieved" goals overview modal
    And I should not see the "overdue" icon in row "3" of the goals overview modal
    Then I click on description in row "3" of the "goals" view all modal
    And I wait until "descr b20" "text" exists
    # Check load more button is hide
    And I should not see "Load more"

  Scenario: Load more should exist in goals overview modal when more than ten goals are assigned
    Given the following "goals" exist in "perform_goal" plugin:
      | name                   | id_number                       | username | owner | created_at | target_date | current_value_updated_at | updated_at |
      | Alice Perform goal C01 | test-goal-behat-id-number_C01   | alice    | admin | 0 days     | +7 days     | 0 days                   | 0 days     |
      | Alice Perform goal C02 | test-goal-behat-id-number_C02   | alice    | admin | -1 days    | +7 days     | -1 days                  | -1 days    |
      | Alice Perform goal C03 | test-goal-behat-id-number_C03   | alice    | admin | -2 days    | +7 days     | -2 days                  | -2 days    |
      | Alice Perform goal C04 | test-goal-behat-id-number_C04   | alice    | admin | -3 days    | +7 days     | -3 days                  | -3 days    |
      | Alice Perform goal C05 | test-goal-behat-id-number_C05   | alice    | admin | -4 days    | +7 days     | -4 days                  | -4 days    |
      | Alice Perform goal C06 | test-goal-behat-id-number_C06   | alice    | admin | -5 days    | +7 days     | -5 days                  | -5 days    |
      | Alice Perform goal C07 | test-goal-behat-id-number_C07   | alice    | admin | -6 days    | +7 days     | -6 days                  | -6 days    |
      | Alice Perform goal C08 | test-goal-behat-id-number_C08   | alice    | admin | -7 days    | +7 days     | -7 days                  | -7 days    |
      | Alice Perform goal C09 | test-goal-behat-id-number_C09   | alice    | admin | -8 days    | +7 days     | -8 days                  | -8 days    |
      | Alice Perform goal C10 | test-goal-behat-id-number_C10   | alice    | admin | -9 days    | +7 days     | -9 days                  | -9 days    |
      | Alice Perform goal C11 | test-goal-behat-id-number_C11   | alice    | admin | -10 days   | +7 days     | -10 days                 | -10 days   |
      | Alice Perform goal C12 | test-goal-behat-id-number_C12   | alice    | admin | -11 days   | +7 days     | -11 days                 | -11 days   |

    When I log in as "alice"
    And I navigate to my overview
    And I click on ".tui-overviewStatusTable__header-countButton" "css_element"
    Then I should see "12 goals not started"
    And I should not see "Alice Perform goal C11"
    And I should not see "Alice Perform goal C12"
    And I should see "Load more"
    Then I click on "Load more" "button"
    And I wait "2" seconds
    And I should see "Alice Perform goal C11"
    And I should see "Alice Perform goal C12"
