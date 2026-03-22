@core @theme @theme_inspire @javascript
Feature: Update inspire theme settings
  In order to change theme colour
  As a user
  I need to confirm that updating theme settings works as expected

  Background:
    Given I log in as "admin"
    And I am on site homepage
    And I navigate to "Inspire" node in "Site administration > Appearance > Themes"

  Scenario: Confirm inpsire default colours apply
    Then element ":root" should have a css property "--color-state" with a value of "#0074be"
    And element ":root" should have a css property "--color-primary" with a value of "#455465"

  Scenario: Navigate to inspire theme settings and update theme colours
    When I click on "UI colours" "link"
    And I set the field "Primary" to "#FF000B"
    And I set the field "Accent" to "#00FFE6"
    And I click on "Save Colours Settings" "button"
    And I reload the page
    Then element ":root" should have a css property "--color-state" with a value of "#FF000B"
    And element ":root" should have a css property "--color-primary" with a value of "#00FFE6"