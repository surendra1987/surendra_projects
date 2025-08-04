@totara @totara_mobile @javascript
Feature: Confirm that logging in via the browser method redirects back to app registration.

  Background:
    Given I am on a totara site
    And the following "users" exist:
      | username | firstname | lastname | email                |
      | student1 | Student   | 1        | student1@example.com |
    When I log in as "admin"
    And I navigate to "Plugins > Mobile > Mobile settings" in site administration
    And I set the following fields to these values:
      | Enable mobile app | 1 |
    And I click on "Save changes" "button"
    And I navigate to "Plugins > Mobile > Mobile authentication" in site administration
    And I set the following fields to these values:
      | Type of login | browser |
    And I click on "Save changes" "button"
    And I log out

  Scenario: Check that when I log in via the mobile browser login page I load the app installer.
    When I log in as "student1" via the mobile browser page
    Then I am at the totara mobile app installer

  Scenario: Check that without the capability I load the regular login page via mobile browser login.
    When I log in as "admin"
    And I set the following system permissions of "Learner" role:
      | capability        | permission |
      | totara/mobile:use | Prevent    |
    And I log out
    And I log in as "student1"
    Then I should see "My Learning"
