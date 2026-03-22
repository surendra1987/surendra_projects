@totara @block @block_totara_recommendations @ml_recommender @ml_service @engage @totara_engage @javascript @container_workspace @container_course @engage_article
Feature: Test Recommendations Block

  Background:
    Given I am on a totara site
    And I enable ml_service
    And the following "users" exist:
      | username | firstname | lastname | email             |
      | user1    | user1     | user1    | user1@example.com |
    And the following "topics" exist in "totara_topic" plugin:
      | name    |
      | Topic 1 |
    And the following "categories" exist:
      | name       | category | idnumber |
      | Category 1 | 0        | CAT1     |
    And the following "articles" exist in "engage_article" plugin:
      | name      | username | content | format       | timeview | access  | topics  |
      | Article 1 | admin    | Test    | FORMAT_PLAIN | 1        | PUBLIC  | Topic 1 |
      | Article 2 | admin    | Test    | FORMAT_PLAIN | 1        | PUBLIC  | Topic 1 |
      | Article 3 | admin    | Test    | FORMAT_PLAIN | 2        | PUBLIC  | Topic 1 |
      | Article 4 | admin    | Test    | FORMAT_PLAIN | 3        | PUBLIC  | Topic 1 |
      | Article 5 | admin    | Test    | FORMAT_PLAIN | 1        | PRIVATE | Topic 1 |
    And the following "courses" exist:
      | fullname | shortname | category |
      | Course 1 | c1        | 0        |
      | Course 2 | c2        | 0        |
      | Course 3 | c3        | 0        |
    And the following "workspaces" exist in "container_workspace" plugin:
      | name        | owner | summary    | private | hidden |
      | Workspace 1 | admin | Workpace 1 | 0       | 0      |
      | Workspace 2 | admin | Workpace 2 | 0       | 0      |
      | Workspace 3 | admin | Workpace 3 | 1       | 0      |
      | Workspace 4 | admin | Workpace 4 | 1       | 1      |
    And the following "user recommendations" exist in "ml_recommender" plugin:
      | username | name        | component            |
      | user1    | Article 1   | engage_microlearning |
      | user1    | Article 3   | engage_microlearning |
      | user1    | Article 4   | engage_microlearning |
      | user1    | Article 5   | engage_microlearning |
      | user1    | c1          | container_course     |
      | user1    | c3          | container_course     |
      | user1    | Workspace 1 | container_workspace  |
      | user1    | Workspace 3 | container_workspace  |
      | user1    | Workspace 4 | container_workspace  |
    And I log in as "admin"
    And I am on "Course 1" course homepage
    And I add "Self enrolment" enrolment method with:
      | Custom instance name | Test student enrolment |
    And I am on "Course 2" course homepage
    And I add "Self enrolment" enrolment method with:
      | Custom instance name | Test student enrolment |
    And I log out

  Scenario: Each user can see their own individual recommendations
    Given I log in as "user1"
    And I am on "Dashboard" page
    And I press "Customise this page"
    And I add the "Recommended for you" block to the "main" region

    # Article 1 should be visible (micro-learning)
    # Article 2 was not recommended
    # Articles 3 & 4 are recommended but are not micro-learning
    # Articles 5 is private
    When I configure the "Recommended for you" block
    And I set the following fields to these values:
      | Recommendation type | Micro-learning |
    And I press "Save changes"
    And I press "Stop customising this page"
    Then I should see "Article 1" in the "Recommended for you" "block"
    And I should not see "Article 2" in the "Recommended for you" "block"
    And I should not see "Article 3" in the "Recommended for you" "block"
    And I should not see "Article 4" in the "Recommended for you" "block"
    And I should not see "Article 5" in the "Recommended for you" "block"

    # Course 1 should be visible, 2 should not be (not recommended)
    # Course 3 does not have self-enrolment enabled so should not be visible
    When I press "Customise this page"
    And I configure the "Recommended for you" block
    And I set the following fields to these values:
      | Recommendation type | Courses |
    And I press "Save changes"
    And I press "Stop customising this page"
    Then I should see "Course 1" in the "Recommended for you" "block"
    And I should not see "Course 2" in the "Recommended for you" "block"
    And I should not see "Course 3" in the "Recommended for you" "block"

    # Workspace 1 should be visible, 2 should not be (not recommended)
    # Workspace 3 & 4 are private/hidden so shouldn't show
    When I press "Customise this page"
    And I configure the "Recommended for you" block
    And I set the following fields to these values:
      | Recommendation type | Workspaces |
    And I press "Save changes"
    And I press "Stop customising this page"
    Then I should see "Workspace 1" in the "Recommended for you" "block"
    And I should not see "Workspace 2" in the "Recommended for you" "block"
    And I should not see "Workspace 3" in the "Recommended for you" "block"
    And I should not see "Workspace 4" in the "Recommended for you" "block"