@totara @totara_api @javascript
Feature: Enforce IP whitelist on the external API and ensure it returns different responses based on the debug level

  Background:
    Given I am on a totara site
    And the following "client provider" exist in "totara_oauth2" plugin:
      |id | name   | description | client_id   | client_secret   |
      | 1 | Test 1 | Test 1      | client_id_1 | client_secret_1 |
    And the following "users" exist:
      | username | firstname  | lastname  |
      | user1    | user_first | user_last |
    And the following "api clients" exist in "totara_api" plugin:
      | name        | username | client_provider_id |
      | Test client | user1    | 1                  |
    And I log in as "admin"
    And I enable the "api" advanced feature
    And I navigate to "Development > API > API clients" in site administration
    And I click on "Actions for Test client" "button"
    And I follow "Edit client settings"

  Scenario: 404 response is returned by the API when no IPs are allowed and the debug level is None
    When I set the field "Error response" to "None"
    And I set the field "Allowed IP addresses" to "0.0.0.0"
    And I click on "Save" "button"
    And I am using the API endpoint client emulator
    And I set the field "client_id" to "client_id_1"
    And I set the field "client_secret" to "client_secret_1"
    And I set the field "grant_type" to "client_credentials"
    And I click on "Submit Credentials 1" "button"
    And I click on "Submit Request 2" "button"
    Then I should see "Network response was not ok: 404 Not Found"

  Scenario: 403 response is returned by the API when no IPs are allowed and the debug level is Normal
    When I set the field "Error response" to "Normal"
    And I set the field "Allowed IP addresses" to "0.0.0.0"
    And I click on "Save" "button"
    And I am using the API endpoint client emulator
    And I set the field "client_id" to "client_id_1"
    And I set the field "client_secret" to "client_secret_1"
    And I set the field "grant_type" to "client_credentials"
    And I click on "Submit Credentials 1" "button"
    And I click on "Submit Request 2" "button"
    Then I should see "Network response was not ok: 403 Forbidden"
