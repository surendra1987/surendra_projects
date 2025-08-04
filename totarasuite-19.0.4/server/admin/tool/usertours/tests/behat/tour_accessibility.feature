@tool @tool_usertours
Feature: Apply accessibility to a tour

  Background:
    Given I log in as "admin"
    And I add a new user tour with:
      | Name                | First tour |
      | Description         | My first tour |
      | Apply to URL match  | FRONTPAGE |
      | Tour is enabled     | 1 |
      | Show with backdrop  | 1 |
    And I add steps to the "First tour" tour:
      | targettype                  | Title             | Content |
      | Display in middle of page   | Welcome           | Welcome tour. |
    And I add steps to the tour:
      | targettype | targetvalue_selector | Title       | Content   |
      | Selector   | .usermenu            | User menu   | Next page |
      | Selector   | input,button         | Page 2      | Next page |
    And I add steps to the tour:
      | targettype                  | Title   | Content     |
      | Display in middle of page   | Page 3  | Final page. |

  @javascript
  Scenario: Check tabbing working correctly.
    Given I am on site homepage
    And I should see "Welcome"
    # First dialogue of the tour, "Welcome". It has Close, Next and End buttons.
    # Nothing highlighted on the page. Initially whole dialogue focused.
    When I press tab key in "//span[contains(@id, 'tour-step-tool_usertours')]" "xpath_element"
    And I wait until "//button[contains(@class, 'close')]" "xpath_element" exists
    Then the focused element is "//button[contains(@class, 'close')]" "xpath_element"
    When I press tab key in "//button[contains(@class, 'close')]" "xpath_element"
    Then the focused element is "//button[contains(@data-role, 'next')]" "xpath_element"
    When I press tab key in "//button[contains(@data-role, 'next')]" "xpath_element"
    Then the focused element is "//button[contains(text(),'End tour')]" "xpath_element"
    When I press tab key in "//button[contains(text(), 'End tour')]" "xpath_element"

    # Here the focus loops round to the whole dialogue again.
    Then the focused element is "//span[contains(@id, 'tour-step-tool_usertours')]" "xpath_element"
    When I press tab key in "//span[contains(@id, 'tour-step-tool_usertours')]" "xpath_element"
    Then the focused element is "//button[contains(@class, 'close')]" "xpath_element"
    # Check looping works properly going backwards too.
    When I press shift tab key in "//button[contains(@aria-label, 'Close')]" "xpath_element"
    And I press shift tab key in "//span[contains(@id, 'tour-step-tool_usertours')]" "xpath_element"
    Then the focused element is "//button[contains(text(), 'End tour')]" "xpath_element"

    When I press "Next"
    # Now we are on the "User menu" step, so Previous is also enabled.
    # Also, the user menu section in the page is highlighted, and this
    # section contain a hyperlink so the focus have to go though and back to the dialogue.

    And I click on "//span[contains(@id, 'tour-step-tool_usertours')]" "xpath_element"
    When I press tab key in "//span[contains(@id, 'tour-step-tool_usertours')]" "xpath_element"
    Then the focused element is "//button[contains(@class, 'close') and @aria-label='Close']" "xpath_element"

    When I press tab key in "//button[contains(@class, 'close') and @aria-label='Close']" "xpath_element"
    Then the focused element is "//button[@data-role='previous']" "xpath_element"

    When I press tab key in "//button[@data-role='previous']" "xpath_element"
    Then the focused element is "//button[contains(@data-role, 'next')]" "xpath_element"

    When I press tab key in "//button[contains(@data-role, 'next')]" "xpath_element"
    Then the focused element is "//button[contains(text(),'End tour')]" "xpath_element"

    # We tab 2 times from "End Tour" button to header container.
    When I press tab key in "//button[contains(text(),'End tour')]" "xpath_element"
    Then the focused element is ".usermenu" "css_element"
    When I press tab key in ".usermenu" "css_element"
    Then the focused element is "Admin User" "link" in the ".usermenu" "css_element"

  @javascript
  Scenario: Aria tags should not exist
    And I am on site homepage
    When I click on "Next" "button"
    And I click on "Next" "button"
    Then "input[aria-describedby^='tour-step-tool_usertours'],button[aria-describedby^='tour-step-tool_usertours']" "css_element" should exist
    And "input[tabindex],button[tabindex]" "css_element" should exist
    When I click on "Next" "button"
    Then "input[aria-describedby^='tour-step-tool_usertours'],button[aria-describedby^='tour-step-tool_usertours']" "css_element" should not exist
    And "input[tabindex],button[tabindex]" "css_element" should not exist
    When I click on "Previous" "button"
    Then "input[aria-describedby^='tour-step-tool_usertours'],button[aria-describedby^='tour-step-tool_usertours']" "css_element" should exist
    And "input[tabindex],button[tabindex]" "css_element" should exist
    When I click on "End tour" "button"
    Then "input[aria-describedby^='tour-step-tool_usertours'],button[aria-describedby^='tour-step-tool_usertours']" "css_element" should not exist
    And "input[tabindex],button[tabindex]" "css_element" should not exist
