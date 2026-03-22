@core @core_my @javascript @mod_perform @perform_overview
Feature: Performance about others overview page block access

  Background:
    Given I am on a totara site
    And the following "users" exist:
      | username | firstname | lastname | email                   |
      | alice    | Alice     | Smith    | alice.smith@example.com |
      | john     | John      | One      | john.one@example.com    |
      | david    | David     | Two      | david.two@example.com   |
      | harry    | Harry     | Three    | harry.three@example.com |
      | tom      | Tom       | four     | tom.four@example.com    |
      | jerry    | Jerry     | five     | jerry.five@example.com  |
    And the following job assignments exist:
      | user  | manager |
      | john  | alice   |
      | david | alice   |
      | harry | alice   |

  Scenario: The manager(alice) of users(john, david, harry) has NOT been assigned an activity about them
    Given I am on a totara site
    And the following "subject instances" exist in "mod_perform" plugin:
      | activity_name           | activity_type | subject_username | subject_is_participating | number_repeated_instances | track   |
      | johns example activity  | check-in      | john             | true                     | 1                         | track 1 |
      | davids example activity | appraisal     | john             | true                     | 1                         | track 2 |
      | herrys example activity | appraisal     | john             | true                     | 1                         | track 3 |
    And I log in as "alice"
    When I navigate to my overview
    Then ".tui-myPerformOverviewRequiredAction" "css_element" should not exist
    And I log out
    When I log in as "admin"
    And I navigate to my overview for user "alice"
    Then ".tui-myPerformOverviewRequiredAction" "css_element" should not exist
    And I log out

  Scenario: The manager(alice) of users(john, david, harry) has been assigned an activity about them and the manager has completed the activity for all users
    Given the following "subject instances" exist in "mod_perform" plugin:
      | activity_name           | activity_type | subject_username | subject_is_participating | other_participant_username | number_repeated_instances | track   | backdate |
      | johns example activity  | feedback      | john             | true                     | alice                      | 1                         | track 1 | -5 days  |
      | davids example activity | feedback      | david            | true                     | alice                      | 1                         | track 2 | -5 days  |
      | harrys example activity | feedback      | harry            | true                     | alice                      | 1                         | track 3 | -5 days  |
    And I log in as "alice"
    When I navigate to my overview
    Then I should see "There are 3 activities about others that require you to respond." in the ".tui-myPerformOverviewRequiredAction__activitiesAboutOthers" "css_element"
    And I should see "View" in the ".tui-myPerformOverviewRequiredAction__activitiesAboutOthers .tui-btn" "css_element"
    When I click on "View" "link" in the ".tui-myPerformOverviewRequiredAction__activitiesAboutOthers" "css_element"
    Then I should see "Performance activities"
    And I log out

    And I log in as "admin"
    When I navigate to my overview for user "alice"
    Then I should see "Activities about others" in the ".tui-myPerformOverviewRequiredAction" "css_element"
    And I should see "There are 3 activities that require Alice Smith to respond." in the ".tui-myPerformOverviewRequiredAction__activitiesAboutOthers" "css_element"
    And I should not see "View" in the ".tui-myPerformOverviewRequiredAction__activitiesAboutOthers" "css_element"
    And I log out

    And I log in as "alice"
    And the activity "johns example activity" for user "john" was completed at date "-4 days"
    And the activity "davids example activity" for user "david" was completed at date "-4 days"
    And the activity "harrys example activity" for user "harry" was completed at date "-4 days"
    When I navigate to my overview
    Then ".tui-myPerformOverviewRequiredAction" "css_element" should not exist
    And I log out

  Scenario: The manager(alice) of users(john, david, harry) has been assigned an activity about them and
    the manager has completed the activity for User john, is in progress for User david and hasn't started for User harry
    Given the following "subject instances" exist in "mod_perform" plugin:
      | activity_name           | activity_type | subject_username | subject_is_participating | other_participant_username | number_repeated_instances | track   | backdate |
      | johns example activity  | feedback      | john             | true                     | alice                      | 1                         | track 1 | -5 days  |
      | davids example activity | feedback      | david            | true                     | alice                      | 1                         | track 2 | -5 days  |
      | harrys example activity | feedback      | harry            | true                     | alice                      | 1                         | track 3 | -5 days  |
    And I log in as "alice"
    When I navigate to my overview
    Then I should see "Activities about others" in the ".tui-myPerformOverviewRequiredAction" "css_element"
    And I should see "There are 3 activities about others that require you to respond." in the ".tui-myPerformOverviewRequiredAction__activitiesAboutOthers" "css_element"
    And I should see "View" in the ".tui-myPerformOverviewRequiredAction__activitiesAboutOthers .tui-btn" "css_element"
    When I click on "View" "link" in the ".tui-myPerformOverviewRequiredAction__activitiesAboutOthers" "css_element"
    Then I should see "Performance activities"
    And I log out

    And I log in as "admin"
    When I navigate to my overview for user "alice"
    Then I should see "Activities about others" in the ".tui-myPerformOverviewRequiredAction" "css_element"
    And I should see "There are 3 activities that require Alice Smith to respond." in the ".tui-myPerformOverviewRequiredAction__activitiesAboutOthers" "css_element"
    And I should not see "View" in the ".tui-myPerformOverviewRequiredAction__activitiesAboutOthers" "css_element"
    And I log out

    And I log in as "alice"
    And the activity "johns example activity" for user "john" was completed at date "-4 days"
    And the activity "davids example activity" for user "david" was progressed at date "-4 days"
    When I navigate to my overview
    Then I should see "Activities about others" in the ".tui-myPerformOverviewRequiredAction" "css_element"
    And I should see "There are 2 activities about others that require you to respond." in the ".tui-myPerformOverviewRequiredAction__activitiesAboutOthers" "css_element"
    And I log out

    And I log in as "admin"
    When I navigate to my overview for user "alice"
    Then I should see "Activities about others" in the ".tui-myPerformOverviewRequiredAction" "css_element"
    And I should see "There are 2 activities that require Alice Smith to respond." in the ".tui-myPerformOverviewRequiredAction__activitiesAboutOthers" "css_element"
    And I should not see "View" in the ".tui-myPerformOverviewRequiredAction__activitiesAboutOthers" "css_element"
    And I log out

  Scenario: The manager(alice) of users(john, david, harry, tom, jerry) has been assigned an activity about them and
    the manager has completed the activity for User john, is in progress for User david and hasn't started for User harry, tom and jerry activities are overdue
    Given the following "subject instances" exist in "mod_perform" plugin:
      | activity_name           | activity_type | subject_username | subject_is_participating | other_participant_username | number_repeated_instances | track   | backdate | due_date_relative |
      | johns example activity  | feedback      | john             | true                     | alice                      | 1                         | track 1 | -5 days  |                   |
      | davids example activity | feedback      | david            | true                     | alice                      | 1                         | track 2 | -5 days  |                   |
      | harrys example activity | feedback      | harry            | true                     | alice                      | 1                         | track 3 | -5 days  |                   |
      | toms example activity   | check-in      | tom              | true                     | alice                      | 1                         | track 4 | -5 days  | -2 days           |
      | jerrys example activity | check-in      | jerry            | true                     | alice                      | 1                         | track 5 | -5 days  | -2 days           |
    And I log in as "alice"
    When I navigate to my overview
    Then I should see "Activities about others" in the ".tui-myPerformOverviewRequiredAction" "css_element"
    And I should see "There are 5 activities about others that require you to respond. 2 of them are overdue." in the ".tui-myPerformOverviewRequiredAction__activitiesAboutOthers" "css_element"
    And I should see "View" in the ".tui-myPerformOverviewRequiredAction__activitiesAboutOthers .tui-btn" "css_element"
    When I click on "View" "link" in the ".tui-myPerformOverviewRequiredAction__activitiesAboutOthers" "css_element"
    Then I should see "Performance activities"
    And I log out

    And I log in as "admin"
    When I navigate to my overview for user "alice"
    Then I should see "Activities about others" in the ".tui-myPerformOverviewRequiredAction" "css_element"
    And I should see "There are 5 activities about others that require Alice Smith to respond. 2 of them are overdue." in the ".tui-myPerformOverviewRequiredAction__activitiesAboutOthers" "css_element"
    And I should not see "View" in the ".tui-myPerformOverviewRequiredAction__activitiesAboutOthers" "css_element"
    And I log out

    And I log in as "alice"
    And the activity "johns example activity" for user "john" was completed at date "-4 days"
    And the activity "davids example activity" for user "david" was progressed at date "-4 days"
    When I navigate to my overview
    Then I should see "Activities about others" in the ".tui-myPerformOverviewRequiredAction" "css_element"
    And I should see "There are 4 activities about others that require you to respond. 2 of them are overdue." in the ".tui-myPerformOverviewRequiredAction__activitiesAboutOthers" "css_element"
    And I log out

    And I log in as "admin"
    When I navigate to my overview for user "alice"
    Then I should see "Activities about others" in the ".tui-myPerformOverviewRequiredAction" "css_element"
    And I should see "There are 4 activities about others that require Alice Smith to respond. 2 of them are overdue." in the ".tui-myPerformOverviewRequiredAction__activitiesAboutOthers" "css_element"
    And I should not see "View" in the ".tui-myPerformOverviewRequiredAction__activitiesAboutOthers" "css_element"
    And I log out

  Scenario: 2 years back. The manager(alice) of users(john, david, harry) has been assigned an activity about them and
    the manager has completed the activity for User john, is in progress for User david and hasn't started for User harry
    Given the following "subject instances" exist in "mod_perform" plugin:
      | activity_name             | activity_type | subject_username | subject_is_participating | other_participant_username | number_repeated_instances | track   | backdate  |
      | johns example activity 1  | feedback      | john             | true                     | alice                      | 1                         | track 1 | -760 days |
      | davids example activity 1 | feedback      | david            | true                     | alice                      | 1                         | track 2 | -760 days |
      | harrys example activity 1 | feedback      | harry            | true                     | alice                      | 1                         | track 3 | -760 days |
    And I log in as "alice"
    When I navigate to my overview
    Then ".tui-myPerformOverviewRequiredAction" "css_element" should not exist
    And I log out

    And I log in as "admin"
    When I navigate to my overview for user "alice"
    Then ".tui-myPerformOverviewRequiredAction" "css_element" should not exist
    And I log out

    And I log in as "alice"
    And the activity "johns example activity 1" for user "john" was completed at date "-758 days"
    And the activity "davids example activity 1" for user "david" was progressed at date "-756 days"
    When I navigate to my overview
    Then ".tui-myPerformOverviewRequiredAction" "css_element" should not exist
    And I log out

    And I log in as "admin"
    When I navigate to my overview for user "alice"
    Then ".tui-myPerformOverviewRequiredAction" "css_element" should not exist
    And I log out

  Scenario: Check the activities about others message excludes items where a participant's access has been removed
    Given the following "subject instances" exist in "mod_perform" plugin:
      | activity_name           | activity_type | subject_username | subject_is_participating | other_participant_username | number_repeated_instances | track   | backdate | due_date_relative |
      | johns example activity  | feedback      | john             | true                     | alice                      | 1                         | track 1 | -5 days  |                   |
      | davids example activity | feedback      | david            | true                     | alice                      | 1                         | track 2 | -5 days  |                   |
      | harrys example activity | feedback      | harry            | true                     | alice                      | 1                         | track 3 | -5 days  |                   |
      | toms example activity   | check-in      | tom              | true                     | alice                      | 1                         | track 4 | -5 days  | -2 days           |
      | jerrys example activity | check-in      | jerry            | true                     | alice                      | 1                         | track 5 | -5 days  | -2 days           |
    And I log in as "alice"
    When I navigate to my overview
    Then I should see "Activities about others" in the ".tui-myPerformOverviewRequiredAction" "css_element"
    And I should see "There are 5 activities about others that require you to respond. 2 of them are overdue." in the ".tui-myPerformOverviewRequiredAction__activitiesAboutOthers" "css_element"
    And I log out

    When I log in as "admin"
    And I remove access for participant "alice" in the activity "harrys example activity" where "harry" is the subject
    And I remove access for participant "alice" in the activity "toms example activity" where "tom" is the subject
    And I log out

    And I log in as "alice"
    When I navigate to my overview
    Then I should see "Activities about others" in the ".tui-myPerformOverviewRequiredAction" "css_element"
    # The 2 numbers should now exclude items where the participant had their access removed.
    And I should see "There are 3 activities about others that require you to respond. 1 of them is overdue." in the ".tui-myPerformOverviewRequiredAction__activitiesAboutOthers" "css_element"
    And I log out
