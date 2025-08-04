@core @core_course @javascript @format_pathway
Feature: Enrolment workflow for a pathway format course
  In order to quickly enrol to the pathway course
  As a learner
  I can click enrol button to self enrol the course

  Background:
    Given I am on a totara site
    And the following "courses" exist:
      | fullname | shortname | format  |
      | Spring   | Spring    | pathway |
    And the following "users" exist:
      | username   | firstname | lastname |
      | user_one   | User      | One      |

  Scenario: Enrolment workflow for an admin
    Given I am on a totara site
    And I log in as "admin"
    And I am on "Spring" course homepage
    And I click on "Users" "button" in the ".tui-treeNode__trigger" "css_element"
    And I click on "Enrolment methods" "link"
    And I click on "Enable" "link" in the "Self enrolment (Learner)" "table_row"
    When I follow "Enrolment options"
    And I press "Enrol me"
    Then I should see "You've been enrolled successfully"

  Scenario: Check enrol button should be not shown
    Given I am on a totara site
    And I log in as "admin"
    When I am on "Spring" course homepage
    Then I should not see "Enrol"
    And I click on "Users" "button" in the ".tui-treeNode__trigger" "css_element"
    And I click on "Enrolment methods" "link"
    And I click on "Enable" "link" in the "Guest access" "table_row"
    And I log out

    And I log in as "user_one"
    When I am on "Spring" course homepage
    Then I should not see "Enrol"

  Scenario: Enrolment workflow for a site guest
    Given I am on a totara site
    And I log in as "admin"
    And I set the following administration settings values:
      | Guest login | Show |
    And I am on "Spring" course homepage
    And I click on "Users" "button" in the ".tui-treeNode__trigger" "css_element"
    And I click on "Enrolment methods" "link"
    And I click on "Enable" "link" in the "Self enrolment (Learner)" "table_row"
    And I click on "Edit" "link" in the "Guest access" "table_row"
    And I set the following fields to these values:
      | Allow guest access | Yes |
    And I press "Save changes"
    And I log out

    And I log in as "guest"
    When I am on "Spring" course homepage
    Then I should not see "Enrol"
    And I should see "Spring"

  Scenario: Enrolment workflow for a leaner
    Given I am on a totara site
    And I log in as "admin"
    And I am on "Spring" course homepage
    And I click on "Users" "button" in the ".tui-treeNode__trigger" "css_element"
    And I click on "Enrolment methods" "link"
    And I click on "Enable" "link" in the "Self enrolment (Learner)" "table_row"
    And I click on "Edit" "link" in the "Guest access" "table_row"
    And I set the following fields to these values:
      | Allow guest access | Yes   |
    And I press "Save changes"
    And I log out

    And I log in as "user_one"
    And I am on course index
    And I click on "div[title=\"Spring\"]" "css_element"
    When I follow "Enrolment options"
    And I press "Enrol me"
    Then I should see "You've been enrolled successfully"