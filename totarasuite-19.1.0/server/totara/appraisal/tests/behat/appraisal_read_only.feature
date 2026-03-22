@totara @totara_appraisal @javascript
Feature: Check if the page became read only when read only switch for legacy appraisals is turned on
  Background:
    Given I am on a totara site
    And I enable the "appraisals" advanced feature
    And the following "users" exist:
      | username | firstname | lastname | email                |
      | learner1 | Learner   | One      | learner1@example.com |
      | manager1 | Manager   | One      | manager1@example.com |
    And the following "cohorts" exist:
      | name       | idnumber |
      | Audience 1 | A1       |
    And the following "cohort members" exist:
      | user     | cohort |
      | learner1 | A1     |
    And the following job assignments exist:
      | user     | fullname         | idnumber | manager  |
      | learner1 | Learner1 One     | l1ja     | manager1 |
    And the following "appraisals" exist in "totara_appraisal" plugin:
      | name            |
      |  Appraisal Test |
    And the following "stages" exist in "totara_appraisal" plugin:
      | appraisal       | name   | timedue                 |
      |  Appraisal Test | Stage1 | 1 January +2 years 23:59:59 |
      |  Appraisal Test | Stage2 | 1 January +5 years 23:59:59 |
      |  Appraisal Test | Stage3 | 1 January +8 years 23:59:59 |
    And the following "pages" exist in "totara_appraisal" plugin:
      | appraisal       | stage  | name                    |
      |  Appraisal Test | Stage1 | Stage1-Text             |
      |  Appraisal Test | Stage2 | Stage2-Ratings          |
      |  Appraisal Test | Stage3 | Stage3-Aggregates       |
    And the following "questions" exist in "totara_appraisal" plugin:
      | appraisal       | stage  | page                    | name              | type          | default | ExtraInfo                          |
      |  Appraisal Test | Stage1 | Stage1-Text             | S1-shorttext      | text          |         |                                    |
      |  Appraisal Test | Stage2 | Stage2-Ratings          | S2-Rating_Numeric | ratingnumeric | 5       | Range:1-10,Display:slider          |
      |  Appraisal Test | Stage2 | Stage2-Ratings          | S2-Rating_Custom  | ratingcustom  | choice1 |                                    |
      |  Appraisal Test | Stage3 | Stage3-Aggregates       | S3-Aggregate      | aggregate     |         | S2-Rating_Numeric,S2-Rating_Custom |
    And the following "assignments" exist in "totara_appraisal" plugin:
      | appraisal       | type     | id     |
      |  Appraisal Test | audience | A1     |

  Scenario: Check read only on manage legacy appraisal page.
    When I log in as "admin"
    And I navigate to "Manage Appraisals (legacy)" node in "Site administration > Legacy features"
    Then ".singlebutton" "css_element" should not exist in the "#region-main" "css_element"
    And I should see "Draft" in the "Appraisal Test" "table_row"
    Then I should not see "Activate" in the "Appraisal Test" "table_row"

  Scenario: Check read only on individual appraisal page.
    When I log in as "admin"
    And I navigate to "Manage Appraisals (legacy)" node in "Site administration > Legacy features"
    Then I should see "This legacy Performance Management feature is surpassed by new features which were introduced in a recent site upgrade"
    And I should see "Legacy appraisals have been made read-only. Active appraisals have been closed, and draft appraisals cannot be edited or activated"

    When I click on "Appraisal Test" "link" in the "Appraisal Test" "table_row"
    Then I should see "Legacy appraisals have been made read-only. Active appraisals have been closed, and draft appraisals cannot be edited or activated"
    And I should not see "Activate now"
    And "#id_submitbutton" "css_element" should not exist in the ".mform" "css_element"
    Then I click on "Content" "link" in the ".tabtree" "css_element"
    And I should see "Legacy appraisals have been made read-only. Active appraisals have been closed, and draft appraisals cannot be edited or activated"
    And ".addstage" "css_element" should not exist in the "#region-main" "css_element"
    And I should not see "Settings" in the "Stage1" "table_row"
    And "#appraisal-add-page" "css_element" should not exist in the ".appraisal-page-pane" "css_element"
    And "#fgroup_id_addquestgroup" "css_element" should not exist in the ".appraisal-content" "css_element"
    Then I click on "Assignments" "link" in the ".tabtree" "css_element"
    And I should see "Legacy appraisals have been made read-only. Active appraisals have been closed, and draft appraisals cannot be edited or activated"
    And ".custom-select" "css_element" should not exist in the "#region-main" "css_element"
    Then I click on "Messages" "link" in the ".tabtree" "css_element"
    And I should see "Legacy appraisals have been made read-only. Active appraisals have been closed, and draft appraisals cannot be edited or activated"
    And ".createmessage" "css_element" should not exist in the "#region-main" "css_element"

  Scenario: check read only user facing pages of the legacy appraisal
    When I activate the "Appraisal Test" appraisal
    And I log in as "learner1"
    And I am on "All Appraisals" page
    Then I should see "Appraisal Test"
    And I should see "Legacy appraisals have been made read-only. Active appraisals have been closed"

    When I click on "Appraisal Test" "link" in the "Appraisal Test" "table_row"
    Then I should see "This legacy appraisal has been closed and is read-only"

    # IMPT: This probably needs to change to "View" when TL-35911 and TL-35910 are merged
    When I press "Start"
    Then I should see "This legacy appraisal has been closed and is read-only"
