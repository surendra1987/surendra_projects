@core @totara @core_mfa @javascript @vuejs
Feature: Login with MFA
  Background:
    Given I am on a totara site
    And the following "users" exist:
      | username | firstname | lastname | email                    |
      | alice    | Alice     | Smith    | alice.smith@example.com  |
      | bill     | Bill      | Orange   | bill.orange@example.com  |
    And I log in as "admin"

  Scenario: Login flow with MFA
    And I navigate to "Plugins > Multi-factor authentication > Manage multi-factor authentication" in site administration
    Then the "generaltable" table should contain the following:
      | Name                | Enable |
      | Button              | Enable |
      | Authenticator app   | Enable |

    # Enable Factors
    And I click on "Enable" "link" in the "Button" "table_row"
    And I click on "Enable" "link" in the "Authenticator app" "table_row"
    Then the "generaltable" table should contain the following:
      | Name                | Enable  |
      | Button              | Disable |
      | Authenticator app   | Disable |

    # MFA available in preferences
    And I follow "Preferences" in the user menu
    And I follow "Manage multi-factor authentication"
    And I should see "Add factor"
    And I should see "You haven't added any factors yet."

    # Configure Button factors for the user
    When the following "mfa instances" exist in "core_mfa" plugin:
      | user  | type   | label           | config              |
      | admin | button | instance active | {"secret": "efghi"} |
      | admin | button | instance delete | {"secret": "jklmn"} |
    And I reload the page
    Then I should see the tui datatable contains:
      | Factor | Name            | Added date       |
      | Button | instance active | ##today##j F Y## |
      | Button | instance delete | ##today##j F Y## |

    # Delete a factor
    When I click on "Remove instance delete" "button" in the tui datatable row with "instance delete" "Name"
    Then I should see "Are you sure you want to remove this factor?" in the tui modal
    And I confirm the tui confirmation modal
    And I wait for pending js

    # New Login flow
    When I log out
    And I log in as "admin"
    Then I should see "Press the button"
    When I click on "Verify" "button"
    Then I should see "Home"
