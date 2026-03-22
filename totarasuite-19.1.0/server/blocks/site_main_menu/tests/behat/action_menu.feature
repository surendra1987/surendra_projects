@block @block_main_menu @javascript
Feature: Use the action menu in the main menu block
  In order to use main menu block
  As an admin
  I need to add and edit activities there

  Background:
    Given I log in as "admin"
    And I am on site homepage
    And I navigate to "Turn editing on" node in "Front page settings"
    And I add the "Main menu" block
    And I add a "Forum" to section "0" and I fill the form with:
      | Forum name | My forum name |

  Scenario: Indent activities in site main menu block
    Given I click on "Edit" "link" in the "//*[contains(@class,'block_site_main_menu')]//li[contains(.,'My forum name')]//*[@class='buttons']" "xpath_element"
    When I click on "Move right" "link" in the "//*[contains(@class,'block_site_main_menu')]//li[contains(.,'My forum name')]//*[@class='buttons']" "xpath_element"
    Then ".block_site_main_menu .mod-indent-1" "css_element" should exist

    Given I click on "Edit" "link" in the "//*[contains(@class,'block_site_main_menu')]//li[contains(.,'My forum name')]//*[@class='buttons']" "xpath_element"
    When I click on "Move left" "link" in the "//*[contains(@class,'block_site_main_menu')]//li[contains(.,'My forum name')]//*[@class='buttons']" "xpath_element"
    Then ".block_site_main_menu .mod-indent" "css_element" should not exist

  Scenario: Hide activities in site main menu block
    Given I click on "Edit" "link" in the "//*[contains(@class,'block_site_main_menu')]//li[contains(.,'My forum name')]//*[@class='buttons']" "xpath_element"
    When I click on "Hide" "link" in the "//*[contains(@class,'block_site_main_menu')]//li[contains(.,'My forum name')]//*[@class='buttons']" "xpath_element"
    Then "//*[contains(@class,'block_site_main_menu')]//li//*[contains(@class, 'dimmed_text') and contains(.,'My forum name')]" "xpath_element" should exist

    Given I click on "Edit" "link" in the "//*[contains(@class,'block_site_main_menu')]//li[contains(.,'My forum name')]//*[@class='buttons']" "xpath_element"
    When I click on "Show" "link" in the "//*[contains(@class,'block_site_main_menu')]//li[contains(.,'My forum name')]//*[@class='buttons']" "xpath_element"
    Then "//*[contains(@class,'block_site_main_menu')]//li//div[contains(@class, 'dimmed_text') and contains(.,'My forum name')]" "xpath_element" should not exist

  Scenario: Duplicate activities in site main menu block
    Given I click on "Edit" "link" in the "//*[contains(@class,'block_site_main_menu')]//li[contains(.,'My forum name')]//*[@class='buttons']" "xpath_element"
    When I click on "Duplicate" "link" in the "//*[contains(@class,'block_site_main_menu')]//li[contains(.,'My forum name')]//*[@class='buttons']" "xpath_element"
    Then "(//*[contains(@class,'block_site_main_menu')]//li//div[contains(@class, 'activity') and contains(.,'My forum name')])[1]" "xpath_element" should exist
    And "(//*[contains(@class,'block_site_main_menu')]//li//div[contains(@class, 'activity') and contains(.,'My forum name')])[2]" "xpath_element" should exist

  Scenario: Delete activities in site main menu block
    Given I click on "Edit" "link" in the "//*[contains(@class,'block_site_main_menu')]//li[contains(.,'My forum name')]//*[@class='buttons']" "xpath_element"
    When I click on "Delete" "link" in the "//*[contains(@class,'block_site_main_menu')]//li[contains(.,'My forum name')]//*[@class='buttons']" "xpath_element"
    And I click on "Yes" "button"
    Then I should not see "My forum name" in the "Main menu" "block"
