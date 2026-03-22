@core @theme @theme_inspire @_file_upload @javascript
Feature: Update navigation menu from the Inspire Theme Settings

  Background:
    Given I log in as "admin"
    And I am on site homepage
    And I toggle open the admin quick access menu
    And I follow "Appearance"
    And I follow "Inspire"

  Scenario: Change logo and logomark
    Given "//img[contains(@class, 'headingLogo') and contains(@src, 'pluginfile.php') and contains(@src, 'test')]" "xpath_element" should not exist
    And I click on "Collapse navigation" "button"
    And "//img[contains(@class, 'headingLogo') and contains(@src, 'pluginfile.php') and contains(@src, 'leaves-green')]" "xpath_element" should not exist
    And I click on "Expand navigation" "button"
    When I upload "theme/inspire/tests/fixtures/test.jpg" to the "Logo" tui image uploader
    And I upload "theme/inspire/tests/fixtures/leaves-green.png" to the "Logomark" tui image uploader
    And I set the field "Enabled" to "1"
    And I click on "Save" "button"
    And I am on site homepage
    Then "//img[contains(@class, 'headingLogo') and contains(@src, 'pluginfile.php') and contains(@src, 'test')]" "xpath_element" should exist
    And I click on "Collapse navigation" "button"
    Then "//img[contains(@class, 'headingLogo') and contains(@src, 'pluginfile.php') and contains(@src, 'leaves-green')]" "xpath_element" should exist

  Scenario: Show and hide navigation icons
    Given I set the field "Enabled" to "0"
    And I click on "Save" "button"
    When I follow "Theme settings"
    Then "//div[contains(@class, 'tui-theme_inspire-navItem__icon')]" "xpath_element" should not exist

    Given I follow "Inspire"
    Given I set the field "Enabled" to "1"
    And I click on "Save" "button"
    When I follow "Theme settings"
    Then "//div[contains(@class, 'tui-theme_inspire-navItem__icon')]" "xpath_element" should exist

  Scenario: Default desktop experience is expanded, with navigation icons enabled
    Given I set the field "Enabled" to "1"
    And I click on the "Expanded" tui radio
    And I click on "Save" "button"
    When I follow "Theme settings"
    Then "//span[contains(@class, 'tui-theme_inspire-navItem__headingText')]" "xpath_element" should exist
    And "//div[contains(@class, 'tui-theme_inspire-navItem__icon')]" "xpath_element" should exist

  Scenario: Default desktop experience is expanded, with navigation icons disabled
    Given I set the field "Enabled" to "0"
    And I click on the "Expanded" tui radio
    And I click on "Save" "button"
    When I follow "Theme settings"
    Then "//span[contains(@class, 'tui-theme_inspire-navItem__headingText')]" "xpath_element" should exist
    And "//div[contains(@class, 'tui-theme_inspire-navItem__icon')]" "xpath_element" should not exist

  Scenario: Default desktop experience is collapsed with navigation icons enabled
    Given I set the field "Enabled" to "1"
    And I click on the "Collapsed/hidden" tui radio
    And I click on "Save" "button"
    When I follow "Theme settings"
    Then "//span[contains(@class, 'tui-theme_inspire-navItem__headingText')]" "xpath_element" should not exist
    And "//div[contains(@class, 'tui-theme_inspire-navItem__icon')]" "xpath_element" should exist

  Scenario: Default desktop experience is collapsed and nav icons are disabled
    Given I click on the "Collapsed/hidden" tui radio
    And I set the field "Enabled" to "0"
    And I click on "Save" "button"
    When I follow "Theme settings"
    Then "//span[contains(@class, 'tui-theme_inspire-navItem__headingText')]" "xpath_element" should not exist
    And "//div[contains(@class, 'tui-theme_inspire-navItem__icon')]" "xpath_element" should not exist

  Scenario: Logomark shows when desktop experience is collapsed
    Given I click on the "Collapsed/hidden" tui radio
    And I click on "Save" "button"
    When I follow "Theme settings"
    Then "img[src*=sitelogomark]" "css_element" should exist

  Scenario: Background colour changes in nav menu
    Given I set the field "Background colour" to "#fb5e39"
    And I click on "Save" "button"
    When I follow "Theme settings"
    Then element ":root" should have a css property "--nav-bg-color" with a value of "#fb5e39"

  Scenario: Text colour changes in nav menu
    Given I set the field "Text colour" to "#171025"
    And I click on "Save" "button"
    When I follow "Theme settings"
    Then element ":root" should have a css property "--nav-text-color" with a value of "#171025"

  Scenario: Select highlight colour changes in nav menu
    Given I set the field "Selected colour" to "#7c3c40"
    And I click on "Save" "button"
    When I follow "Home"
    Then element ":root" should have a css property "--nav-selected-color" with a value of "#7c3c40"
