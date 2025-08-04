@totara @totara_catalog @javascript @core @core_course
Feature: Catalog item can be excluded
  Background:
    Given I am on a totara site
    And the following "courses" exist:
      | fullname | shortname | summary | format |
      | Course 1 | C1 | <p>Course summary</p> | topics |
      | Course 2 | C2 | <p>Course summary</p> | topics |

  Scenario: Admin can not see the excluded course
    Given I log in as "admin"
    And I am on totara catalog page
    Then I should see "Course 2"
    And I should see "Course 1"
    And I am on "Course 1" course homepage
    And I navigate to "Edit settings" in current page administration
    And I set the following fields to these values:
      | Exclude from catalogue | 1 |
    And I press "Save and display"
    And I am on homepage
    When I am on totara catalog page
    Then I should not see "Course 1"
    And I should see "Course 2"

  Scenario: Admin can not see the course when audience based course visibility is enabled
    Given I log in as "admin"
    And I set the following administration settings values:
      | Enable audience-based visibility | 1 |
    When I am on totara catalog page
    Then I should see "Course 2"
    And I should see "Course 1"
    And I am on "Course 1" course homepage
    And I navigate to "Edit settings" in current page administration
    And I set the following fields to these values:
      | Exclude from catalogue | 1 |
    And I press "Save and display"
    And I am on homepage
    When I am on totara catalog page
    Then I should not see "Course 1"
    And I should see "Course 2"