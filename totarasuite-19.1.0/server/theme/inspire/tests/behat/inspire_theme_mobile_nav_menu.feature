@core @theme @theme_inspire @totara_mobile @javascript
Feature: The Inspire theme nav menu is automatically collapsed in mobile view

  Background:
    Given I log in as "admin"
    And I change viewport size to "small"

  Scenario: Regardless of the desktop view, the mobile nav is collapsed
    Given I am on site homepage
    And I toggle open the admin quick access menu
    And I follow "Appearance"
    And I follow "Inspire"
    When the "Expanded" "radio" should be enabled
    Then "//nav[@class='tui-theme_inspire-navigation']" "xpath_element" should not exist

  Scenario: Expanded nav obscures information on the page
    Given I am on site homepage
    Then I should see "Front page"
    When I click on "//button[contains(@class, 'toggle--overlay')]" "xpath_element"
    Then I should see "Learn"
