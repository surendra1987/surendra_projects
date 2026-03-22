@core @totara @auth @ssosaml @auth_ssosaml @javascript
Feature: Setup the ssosaml plugin and issuers so that a user can login using a Idp service.

  Background:
    When I log in as "admin"
    And I navigate to "Plugins > Authentication > Manage authentication" in site administration
    And I click on "Enable" "link" in the "SAML 2.0 (SSO)" "table_row"
    And I navigate to "Plugins > Authentication > SAML 2.0 (SSO)" in site administration
    Then I should see "SAML 2.0 (SSO)"

  Scenario: Idp list screen
    Then I should see "There are no IdPs currently configured."
    And I click on "Global settings" "link"
    And I should see "User attribute locking"
    And I should see "First name"
    And I should see "Surname"
    And I should see "Email address"
    And I click on "//button[@aria-label='Add fields']" "xpath_element"
    Then I should see "Add fields" in the tui modal
    And I click on the "selectAll" tui checkbox
    And I click on "Add" "button_exact"
    And I click on "Save" "button"
    Then I should see "Changes saved"
    And the field "fields[0][locked]" matches value "UNLOCKED"

  Scenario: Create and edit IdP
    And I click on "Identity providers" "link"
    When I click on "Add new provider" "button"
    And I should see "Add IdP configuration"
    And I set the field "label" to "Example IdP"
    And I click on "//button[@aria-label='XML']" "xpath_element"
    And I set the field "metadataBlob" to "<EntityDescriptor ID=\"idp_2_desc\" xmlns=\"urn:oasis:names:tc:SAML:2.0:metadata\" entityID=\"some_entity\"><IDPSSODescriptor protocolSupportEnumeration=\"urn:oasis:names:tc:SAML:2.0:protocol\"><KeyDescriptor><KeyInfo xmlns=\"http://www.w3.org/2000/09/xmldsig#\"><X509Data><X509Certificate>cert</X509Certificate></X509Data></KeyInfo></KeyDescriptor><SingleSignOnService Binding=\"urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Redirect\" Location=\"https://foo/\" /></IDPSSODescriptor></EntityDescriptor>"
    And I set the field "userIdField[external]" to "foobar"
    And I click on "Show advanced settings" "button"
    And I click on the "advancedSettings[createUsers]" tui checkbox
    And I click on "Field mappings" "link"
    And I should see "Username" in the ".tui-auth_ssosaml-simpleTable" "css_element"

    And I click on "//button[@aria-label='Add fields']" "xpath_element"
    Then I should see "Add fields" in the tui modal
    And I click on the "selectAll" tui checkbox
    And I click on "Add" "button_exact"
    When I set the following fields to these values:
      | advancedSettings[fieldMaps][0][external]           | first_name |
      | advancedSettings[fieldMaps][1][external]           | last_name |
      | advancedSettings[fieldMaps][2][external]           | email |
      | advancedSettings[fieldMaps][3][external]           | city |
      | advancedSettings[fieldMaps][4][external]           | country |
      | advancedSettings[fieldMaps][5][external]           | language |
      | advancedSettings[fieldMaps][6][external]           | description |
      | advancedSettings[fieldMaps][7][external]           | webpage |
      | advancedSettings[fieldMaps][8][external]           | id_number |
      | advancedSettings[fieldMaps][9][external]           | institution |
      | advancedSettings[fieldMaps][10][external]          | department |
      | advancedSettings[fieldMaps][11][external]          | phone |
      | advancedSettings[fieldMaps][12][external]          | mobile_phone |
      | advancedSettings[fieldMaps][13][external]          | address |
      | advancedSettings[fieldMaps][14][external]          | firstname_phonetic |
      | advancedSettings[fieldMaps][15][external]          | surname_phonetic |
      | advancedSettings[fieldMaps][16][external]          | middle_name |
      | advancedSettings[fieldMaps][17][external]          | alternate_name |

    And I click on "Save" "button"
    Then I should see "IdP saved"
    And I should see "Example IdP" in the ".tui-auth_ssosaml-manageIdps__cards" "css_element"
    And I should not see "Debug mode on" in the ".tui-auth_ssosaml-manageIdps__cards" "css_element"
    And the "Enable" tui toggle switch should be "off"

    # Edit IdP
    And I click on "//button[@aria-label='Actions for IdP']" "xpath_element" in the ".tui-auth_ssosaml-manageIdps__cards" "css_element"
    And I click on "Edit" "link" in the ".tui-auth_ssosaml-manageIdps__cards" "css_element"
    And I set the field "userIdField[external]" to "username"
    Then I click on "Show advanced settings" "button"
    And I click on the "advancedSettings[debug]" tui checkbox
    And I click on "Save" "button"

    # Toggle enabled
    And the "Enable" tui toggle switch should be "off"
    And I click on the "Enable" tui toggle button
    And I click on "Enable now" "button"
    And the "Enable" tui toggle switch should be "on"

    # View IdP
    And I click on "//button[@aria-label='Actions for IdP']" "xpath_element" in the ".tui-auth_ssosaml-manageIdps__cards" "css_element"
    And I click on "View" "link" in the ".tui-auth_ssosaml-manageIdps__cards" "css_element"
    And I should see "Service provider metadata configuration"

    And I click on "Service provider metadata configuration" "button"
    And I should see "Use these details to establish a new SSO connection with your identity provider."

    And I should see "Provider general information"
    And I should see "Example IdP"
    And I should see "Enabled"

    And I should see "Advanced settings"
    And I should see "SAML 2.0 (SSO)"
    And I should see "Logout at IdP"

    And I should see "Field mappings"
    And I should see the tui datatable contains:
      | Totara field          | IdP field          | Update Totara field |
      | Username              | username           | On create           |
      | First name            | first_name         | On create           |
      | Surname               | last_name          | On create           |
      | Email address         | email              | On create           |
      | City/town             | city               | On create           |
      | Country               | country            | On create           |
      | Language              | language           | On create           |
      | Description           | description        | On create           |
      | Web page              | webpage            | On create           |
      | ID number             | id_number          | On create           |
      | Institution           | institution        | On create           |
      | Department            | department         | On create           |
      | Phone                 | phone              | On create           |
      | Mobile phone          | mobile_phone       | On create           |
      | Address               | address            | On create           |
      | First name - phonetic | firstname_phonetic | On create           |
      | Surname - phonetic    | surname_phonetic   | On create           |
      | Middle name           | middle_name        | On create           |
      | Alternate name        | alternate_name     | On create           |
    And I click on "Back to Manage IdPs" "link"

    #Click on Test IdP
    And I click on "//button[@aria-label='Actions for IdP']" "xpath_element" in the ".tui-auth_ssosaml-manageIdps__cards" "css_element"
    And I click on "Test" "link" in the ".tui-auth_ssosaml-manageIdps__cards" "css_element"
    And I should see "What would you like to test?"
    And I should see "Test IdP (Example IdP)"
    And I click on the "Logout (LogoutRequest)" tui radio
    And I click on the "Login (AuthnRequest)" tui radio
    And I click on "Cancel" "link"

    #IdP logs
    And the following "saml_log_request" exist in "auth_ssosaml" plugin:
      | idp_id | request_id | session_id | type  | content_request | content_response | test| status |
      | 1      | r_1234     | s_1234     | Login | test            | test             | 0   | 1      |

    And I click on "//button[@aria-label='Actions for IdP']" "xpath_element" in the ".tui-auth_ssosaml-manageIdps__cards" "css_element"
    And I click on "Logs" "link" in the ".tui-auth_ssosaml-manageIdps__cards" "css_element"
    And I should see "Example IdP logs"
    And I should see the tui datatable contains:
      | Type  | Status |
      | Login | Failed |
    Then I click on "Back to Manage IdPs" "link"

    And I click on "//button[@aria-label='Actions for IdP']" "xpath_element" in the ".tui-auth_ssosaml-manageIdps__cards" "css_element"
    And I click on "Edit" "link" in the ".tui-auth_ssosaml-manageIdps__cards" "css_element"
    Then I click on "Show advanced settings" "button"
    And I click on the "advancedSettings[debug]" tui checkbox
    And I click on "Save" "button"

    And I click on "//button[@aria-label='Actions for IdP']" "xpath_element" in the ".tui-auth_ssosaml-manageIdps__cards" "css_element"
    And I click on "Logs" "link" in the ".tui-auth_ssosaml-manageIdps__cards" "css_element"
    And I should see "Enable debug mode in IdP settings to view logs. Logs will be deleted when debug mode is switched off."
    Then I click on "Back to Manage IdPs" "link"

    # Delete IdP
    And I click on "//button[@aria-label='Actions for IdP']" "xpath_element" in the ".tui-auth_ssosaml-manageIdps__cards" "css_element"
    And I click on "Delete" "button" in the ".tui-auth_ssosaml-manageIdps__cards" "css_element"
    And I should see "Delete IdP"
    And I should see "Are you sure you want to delete this IdP?"
    And I click on "Delete" "button" in the ".tui-modal-wrap" "css_element"
    And I should see "There are no IdPs currently configured."

    And I log out
