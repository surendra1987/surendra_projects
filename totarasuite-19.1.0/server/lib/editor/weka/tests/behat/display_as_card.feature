@totara @javascript @editor @editor_weka @vuejs
Feature: Test Weka display as card feature
  Background:
    Given I am on a totara site
    And the following "users" exist:
      | username | firstname | lastname | email           |
      | user_one | User      | One      | one@example.com |

  Scenario: Create resource
    And I log in as "admin"
    And I navigate to the "weka_basic" fixture in the "lib/editor/weka" plugin
    And I activate the weka editor with css ".tui-fixture-wekaBasic"
    And I click on the "Link" toolbar button in the weka editor
    And I set the field "URL" to "https://test.totaralms.com/course/view.php?id=2"
    And I set the field "Display text" to "mylink"
    And I click on "Done" "button" in the ".tui-modal" "css_element"
    And I click on "mylink" "link"
    And I click on "Display as card" "button"
    Then I should see "https://test.totaralms.com/course/view.php?id=2" in the ".tui-linkBlock__summary" "css_element"