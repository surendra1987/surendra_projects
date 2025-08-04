@core @core_admin @javascript
Feature: Allow unsafe markup in user menu
  In order to display custom markup in the user menu
  As an admin
  I need to enable allowunsafemarkup and add an item to the user menu

  Background:
    Given I am on a totara site
    And I log in as "admin"
    And I am on site homepage
    And I set the following administration settings values:
    | customusermenuitems | <script>const title = document.createElement('h2');title.id = 'identifiableIDHere';title.innerText = 'HIJACKED';document.body.appendChild(title);</script>\|url\|message |

  Scenario: The "allowunsafemarkup" feature is not in use (default value)
    Given I set the following administration settings values:
      | allowunsafemarkup | 0 |
    When I click on "Admin User" "text"
    Then I should see "<script>const title = document.createElement('h2');title.id = 'identifiableIDHere';title.innerText = 'HIJACKED';document.body.appendChild(title);</script>"

  Scenario: the "allowunsafemarkup" feature is in use
    Given I set the following administration settings values:
      | allowunsafemarkup | 1 |
    When I click on "Admin User" "text"
    Then "#identifiableIDHere" "css_element" should exist
