@totara @totara_webhook @javascript
Feature: Ensuring a webhook can be enabled or disabled, and that is consistantly presented throughout the UI.

  Background:
    Given I am on a totara site
    And I log in as "admin"
    When I navigate to "Webhooks" node in "Site administration > Development > API"
    Then I follow "Create"

  Scenario: When I create and edit a webhook I should see its status displayed consistently on all relevant UI.
    Given I set the following fields to these values:
      | Name     |     test       |
      | Endpoint | http://abc.com |
    When I set the field "Enabled" to "1"
    When I click on "Submit" "button"
    Then I should see "Enabled"
    And  I wait until the page is ready
    When I click on "test" "link" in the ".tui-dataTableCell" "css_element"
    Then I should see "Enabled"
    And I click on "Edit" "link"
    And  I wait until the page is ready
    Then I should see "Enabled"
    When I set the field "Enabled" to "0"
    And I click on "Submit" "button"
    Then I should see "Disabled"
    When I click on "test" "link" in the ".tui-dataTableCell" "css_element"
    Then I should see "Disabled"
    And I click on "Edit" "link"
    Then I should see "Disabled"
