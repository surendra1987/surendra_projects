@totara @totara_webhook @javascript
Feature: Ensure that when rotating webhook secrets, the secret is updated

  Background:
    Given the following "webhook" exist in "totara_webhook" plugin:
      | name              | endpoint            | auth_class                               |
      | behat_webhook     | example.com/webhook | totara_webapi\fixtures\mock_webhook_auth |
    And I force webhook "behat_webhook" auth_config to "original_auth_config"

  Scenario: Rotate client signing secret
    Given I am on a totara site
    And I log in as "admin"
    And I navigate to "Webhooks" node in "Site administration > Development > API"
    And I should see "behat_webhook"
    When I click on "behat_webhook" "link"
    Then I should see "signing secret"
    When  I click on "Show" "button_exact" in the ".tui-form" "css_element"
    Then "input[type=text]" "css_element" should exist in the "//div[@class='tui-inputGroup']" "xpath_element"
    And "input[type=password]" "css_element" should not exist in the "//div[@class='tui-inputGroup']" "xpath_element"
    And "Show" "button_exact" should not exist
    And "Hide" "button_exact" should exist
    When I click on "Rotate signing secret" "button"
    Then I should see "Are you sure you want ot rotate this signing secret"
    When I confirm the tui confirmation modal
    Then I should see "Signing secret updated" in the tui success notification toast and close it
    And the field "Signing secret" does not match value "original_auth_config"

  Scenario: Webhooks secrets are visible but cannot be rotated without correct permissions.
    Given the following "users" exist:
      | username  | firstname | lastname |
      | user      | Staff     | User     |
    And the following "system role assigns" exist:
      | user  | role    |
      | user  | manager |
    And the following "permission overrides" exist:
      |           capability                 | permission |   role    | contextlevel |reference|
      | totara/webhook:managetotara_webhooks |  Prohibit  |  manager  |    System    |         |
      | totara/webhook:viewtotara_webhooks   |  Allow     |  manager  |    System    |         |
    And I log in as "user"
    And I navigate to "Webhooks" node in "Site administration > Development > API"
    And I should see "behat_webhook"
    And I click on "behat_webhook" "link"
    Then  I click on "Show" "button_exact" in the ".tui-form" "css_element"
    Then "input[type=text]" "css_element" should exist in the "//div[@class='tui-inputGroup']" "xpath_element"
    And "Rotate signing secret" "button" should not exist
