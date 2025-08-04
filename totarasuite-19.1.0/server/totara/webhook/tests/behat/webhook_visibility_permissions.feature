@totara @totara_webhook @javascript
Feature: Webhook menu link visibility is consistent with user permissions.

  Background:
    Given I am on a totara site

  Scenario: Webhook link is visible for an admin with webhooks enabled.
    Given I log in as "admin"
    And I navigate to "System information > Configure features > Shared services settings" in site administration
    And I set the following fields to these values:
      | Enable Webhooks | 1 |
    And I click on "Save changes" "button"
    Then I navigate to "Webhooks" node in "Site administration > Development > API"
    Then I should see "Manage Webhooks"
    And I should see "Create"

  Scenario: Webhook link is not visible for an admin when webhooks are disabled.
    Given I log in as "admin"
    And I navigate to "System information > Configure features > Shared services settings" in site administration
    And I set the following fields to these values:
    | Enable Webhooks | 0 |
    And I click on "Save changes" "button"
    When I navigate to "Debugging" node in "Site administration > Development"
    Then I should not see "Webhooks"

  Scenario: Webhook link is not visible when webhooks is enabled but the user does not have the correct permissions.
    Given the following "users" exist:
      | username  | firstname | lastname |
      | user      | Staff     | User     |
    And the following "system role assigns" exist:
      | user  | role    |
      | user  | manager |
    And the following "permission overrides" exist:
      |           capability               | permission |   role    | contextlevel |reference|
      |totara/webhook:managetotara_webhooks|  Prohibit  |  manager  |    System    |         |
      |totara/webhook:viewtotara_webhooks  |  Prohibit  |  manager  |    System    |         |
    And I log in as "user"
    And I toggle open the admin quick access menu
#  "There is no way for a non-admin user to access the API Development menu without adding it to their menu, so we will use that as our metric"
    And I follow "Menu settings"
    And I click on "Add menu item..." "link" in the "//div[h3[contains(., 'Core platform')]]/.." "xpath_element"
    And I click on "Development" "link" in the "//div[h3[contains(., 'Core platform')]]/.." "xpath_element"
    When I click on "API" "link" in the "//div[h3[contains(., 'Core platform')]]/.." "xpath_element"
    Then I should not see "Webhooks"

  Scenario: Webhook link is visible when webhooks are enabled and the user has the correct permissions.
    Given the following "users" exist:
      | username  | firstname | lastname |
      | user      | Staff     | User     |
    And the following "system role assigns" exist:
      | user  | role    |
      | user  | manager |
    And the following "permission overrides" exist:
      |           capability               | permission |   role    | contextlevel |reference|
      |totara/webhook:managetotara_webhooks|  Prohibit  |  manager  |    System    |         |
    And I log in as "user"
    And I toggle open the admin quick access menu
    And I follow "Menu settings"
    And I click on "Add menu item..." "link" in the "//div[h3[contains(., 'Core platform')]]/.." "xpath_element"
    And I click on "Development" "link" in the "//div[h3[contains(., 'Core platform')]]/.." "xpath_element"
    When I click on "API" "link" in the "//div[h3[contains(., 'Core platform')]]/.." "xpath_element"
    Then I should see "Webhooks"

  Scenario: Webhook link is not visible when webhooks is enabled but the user does not have the correct permissions.
    Given the following "users" exist:
      | username  | firstname | lastname |
      | user      | Staff     | User     |
    And the following "system role assigns" exist:
      | user  | role    |
      | user  | manager |
    And the following "permission overrides" exist:
      |           capability               | permission |   role    | contextlevel |reference|
      |totara/webhook:managetotara_webhooks|  Prohibit  |  manager  |    System    |         |
      |totara/webhook:viewtotara_webhooks  |  Allow     |  manager  |    System    |         |
    And I log in as "user"
    And I toggle open the admin quick access menu
    And I follow "Menu settings"
    And I click on "Add menu item..." "link" in the "//div[h3[contains(., 'Core platform')]]/.." "xpath_element"
    And I click on "Development" "link" in the "//div[h3[contains(., 'Core platform')]]/.." "xpath_element"
    When I click on "API" "link" in the "//div[h3[contains(., 'Core platform')]]/.." "xpath_element"
    Then I should see "Webhooks"
    When I navigate to "Webhooks" node in "Site administration > Development > API"
    And I should not see "Create" in the ".tui-pageHeading" "css_element"