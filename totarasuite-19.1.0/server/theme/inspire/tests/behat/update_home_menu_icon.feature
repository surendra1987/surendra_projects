@core @theme @theme_inspire @javascript @totara @totara_core @totara_core_menu
Feature: Update the Home icon from the Main Menu settings

  Background:
    Given I log in as "admin"
    And I am on site homepage
    And I toggle open the admin quick access menu
    And I follow "Navigation"
    And I follow "Main menu"
    And I follow "Edit"

  Scenario: Confirm default icon is shown
    Then I should see image with alt text "House Door"

  Scenario: Change default home icon
    Given I click on "Choose icon" "button"
    And I click on "img[title='Front']" "css_element" in the "#icon-selectable" "css_element"
    And I click on "OK" "button"
    And I click on "Save changes" "button"
    And I follow "Edit"
    Then "//*[local-name()='svg'][contains(@class, 'bi-front')]" "xpath_element" should exist
    And I should see image with alt text "Front"

  Scenario: Use Custom CSS Class field to change item style
    Given I set the field "Custom CSS class" to "geoff"
    When I click on "Save changes" "button"
    Then "li.geoff" "css_element" should exist