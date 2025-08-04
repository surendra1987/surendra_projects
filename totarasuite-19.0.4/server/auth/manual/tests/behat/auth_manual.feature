@auth @auth_manual @javascript
Feature: Test manual authentication works.
  In order to check manual authentication
  As a teacher
  I need to go on login page and enter username and password.

  Background:
    Given the following "users" exist:
      | username | firstname | lastname |
      | teacher1 | Bilbo     | Baggins  |

  Scenario: Check login works with javascript.
    Given I am on homepage
    And I expand navigation bar
    And I click on "Sign in" "link" in the ".login" "css_element"
    When I set the field "Username" to "teacher1"
    And I set the field "Password" to "teacher1"
    When I press "Sign in"
    Then I should see "Bilbo Baggins"
