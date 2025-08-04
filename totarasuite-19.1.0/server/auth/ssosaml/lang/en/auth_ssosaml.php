<?php
/**
 * This file is part of Totara Core
 *
 * Copyright (C) 2023 onwards Totara Learning Solutions LTD
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @note Automatically cleaned: 2024-09-24
 * @author Cody Finegan <cody.finegan@totara.com>
 * @package auth_ssosaml
 */

$string['actions_for_x'] = 'Actions for {$a}';
$string['add_fields'] = 'Add fields';
$string['add_idp_configuration'] = 'Add IdP configuration';
$string['add_new_provider'] = 'Add new provider';
$string['advanced_settings'] = 'Advanced settings';
$string['app_name'] = 'Totara';
$string['attribute_field_name'] = 'Field names';
$string['attribute_field_values'] = 'Values';
$string['attributes'] = 'Attributes';
$string['authentication_failure_description_unknown_identifier'] = 'You successfully logged in, but we were unable to retrieve your identifier. Please contact the site administrator if you require assistance.';
$string['authentication_failure_description_with_identifier'] = 'You successfully logged in as {$a}, but we could not find an account that matches yours for some reason. Please contact the site administrator if you require assistance.';
$string['authentication_failure_title'] = 'You can\'t get there';
$string['autolink_no_confirmation'] = 'Confirmation not required';
$string['autolink_none'] = 'SAML 2.0 (SSO)';
$string['autolink_with_confirmation'] = 'Confirmation via email';
$string['automatically_create'] = 'Automatically create';
$string['certificate'] = 'Certificate';
$string['certificate_regenerated_event'] = 'Certificate regenerated';
$string['cleanup_task_name'] = 'SSO SAML Session clean up';
$string['confirm_verification_content'] = 'Hi {$a->fullname},

A request has been made to link the {$a->idp_label} login
{$a->user_identifier} to your account at \'{$a->sitename}\'.

To confirm this request and link these logins, please go to this web address:
The link can only be used once and expires in 30 minutes if you don’t use it.

{$a->link}

In most mail programs, this should appear as a blue link
which you can just click on.  If that doesn\'t work,
then copy and paste the address into the address
line at the top of your web browser window.

If you need help, please contact the site administrator,
{$a->admin}';
$string['confirm_verification_subject'] = '{$a}: linked login confirmation';
$string['copied_to_clipboard'] = 'Copied to clipboard';
$string['copy'] = 'Copy';
$string['data_mapping'] = 'Data mapping';
$string['debug_mode_on'] = 'Debug mode on';
$string['default_delimiter'] = ',';
$string['default_label'] = 'Untitled';
$string['delete_idp'] = 'Delete IdP';
$string['delete_idp_confirm_message'] = 'Users who have been created through this identity provider may no longer be able to log in. Are you sure you want to delete this IdP?';
$string['delimiter'] = 'Attribute Delimiter';
$string['delimiter_help'] = 'Attribute Delimiter used to join attributes with multiple values';
$string['disable_idp'] = 'Disable IdP';
$string['disable_idp_confirm_message'] = 'After disabling, users will not be able to sign in via this identity provider. Are you sure you want to disable this IdP?';
$string['disable_now'] = 'Disable now';
$string['disabled'] = 'Disabled';
$string['download_certificate'] = 'Download certificate';
$string['download_service_provider_metadata'] = 'Download service provider metadata';
$string['edit_idp_configuration'] = 'Edit IdP configuration';
$string['enable_debug_to_view_logs'] = 'Enable debug mode in IdP settings to view logs. Logs will be deleted when debug mode is switched off.';
$string['enable_idp'] = 'Enable IdP';
$string['enable_idp_confirm_message'] = 'After enabling, users will be able to log in with this identity provider. Are you sure you want to enable this IdP?';
$string['enable_now'] = 'Enable now';
$string['enabled'] = 'Enabled';
$string['existing_users_automaticallylink'] = 'Automatically link';
$string['existing_users_help'] = 'Existing site users can be automatically linked or linked by email confirmation.';
$string['existing_users_label'] = 'Existing users';
$string['existing_users_require_email_validation'] = 'Require email validation';
$string['expired_assertions_cleanup_task_name'] = 'Cleanup expired authentication assertions';
$string['expired_idp_sessions_cleanup_task_name'] = 'Cleanup expired IdP sessions';
$string['field_mappings'] = 'Field mappings';
$string['global_settings'] = 'Global settings';
$string['hide_advanced_settings'] = 'Hide advanced settings';
$string['identity_providers'] = 'Identity providers';
$string['idp'] = 'IdP';
$string['idp_deleted'] = 'IdP successfully deleted';
$string['idp_field'] = 'IdP field';
$string['idp_metadata'] = 'IdP metadata';
$string['idp_metadata_help'] = 'Enter the metadata URL or the XML code provided by the identity provider.';
$string['idp_metadata_url_field_placeholder'] = 'Paste metadata XML URL';
$string['idp_name_help'] = "Add the identity provider name, for example: Google. This name will appear as the login button label on the site's login page.";
$string['idp_saved'] = 'IdP saved';
$string['listing_no_idps'] = 'There are no IdPs currently configured.';
$string['local_field'] = 'Totara field';
$string['local_field_locking'] = 'Totara field locking';
$string['log_idp_login'] = 'IdP-initiated login';
$string['log_idp_logout'] = 'IdP-initiated logout';
$string['log_login'] = 'Login';
$string['log_logout'] = 'Logout';
$string['log_request'] = 'Request';
$string['log_response'] = 'Response';
$string['log_status_error'] = 'Failed';
$string['log_status_incomplete'] = 'Incomplete';
$string['log_status_success'] = 'Success';
$string['login_page_url'] = 'Log in using this URL:';
$string['logout_at_idp'] = 'Logout at IdP';
$string['logout_behavior'] = 'Logout behavior';
$string['manage_identity_providers'] = 'Manage identity providers';
$string['manage_idps'] = 'Manage IdPs';
$string['message_type'] = 'Type';
$string['metadata'] = 'Metadata';
$string['metadata_panel_info'] = 'Use these details to establish a new SSO connection with your identity provider.';
$string['metadata_panel_title'] = 'Service provider metadata configuration';
$string['metadata_refresh'] = 'Metadata refresh';
$string['metadata_source'] = 'Metadata source';
$string['metadata_url'] = 'Metadata URL';
$string['new_users'] = 'New users';
$string['new_users_help'] = "When \"automatically create\" is enabled, during the first log in a new site user account will be created if an automatic match with an existing site account is not made.";
$string['not_automatically_created'] = 'Not automatically created';
$string['pluginname'] = 'SAML 2.0 (SSO)';
$string['provider_general_information'] = 'Provider general information';
$string['redirect_after_logout'] = 'Redirect after logout';
$string['redirect_after_logout_help'] = 'Redirect users to a URL after they log out.';
$string['regenerate_certificate'] = 'Regenerate certificate';
$string['regenerate_certificate_confirm_message'] = "Are you sure you want to regenerate the certificate?\n\nGenerating a new certificate will break the current connection to the IdP until the new certificate is uploaded there, or it refetches the metadata from Totara (if applicable).";
$string['regenerate_certificate_success_message'] = 'Certificate successfully regenerated';
$string['request_id'] = 'Request ID';
$string['saml_authnrequests_signed'] = 'Sign authentication requests';
$string['saml_entity_id'] = 'Entity ID';
$string['saml_nameid_format'] = 'NameID format';
$string['saml_sign_metadata'] = 'Sign metadata';
$string['saml_wants_assertions_signed'] = 'Require IdP to sign individual assertions';
$string['select_all_fields'] = 'Select all fields';
$string['service_provider_acs_url_long'] = 'Service provider Assertion Consumer Service (ACS) URL';
$string['service_provider_entity_id'] = 'Service provider Entity ID';
$string['service_provider_metadata'] = 'Service provider metadata';
$string['setting_debug'] = 'Debug';
$string['setting_debug_help'] = 'If enabled, capture communications between Totara and IdP for debugging. Captured messages will be deleted when this setting is switched off.';
$string['setting_login_hide'] = 'Hide on login page';
$string['setting_login_hide_help'] = 'If enabled this IdP will be hidden on the login page.';
$string['settings_subtitle'] = 'Configure identity providers for single-sign on';
$string['sha1'] = 'SHA-1';
$string['sha256'] = 'SHA-256';
$string['sha384'] = 'SHA-384';
$string['sha512'] = 'SHA-512';
$string['show_advanced_settings'] = 'Show advanced settings';
$string['signatures'] = 'Signatures';
$string['signatures_help'] = 'Additional signature options. These are not required for secure operation, but may be useful in some cases.';
$string['slo_url'] = 'Single Logout Service URL';
$string['source'] = 'Source';
$string['sp_metadata'] = 'SP metadata';
$string['sso_submit'] = 'Submit';
$string['sso_warning'] = 'Since your browser does not support JavaScript, you must press the button below once to proceed.';
$string['sso_warning_note'] = 'Note:';
$string['ssosaml:manage'] = 'Manage SAML Configuration';
$string['test'] = 'Test';
$string['test_choice'] = 'What would you like to test?';
$string['test_idp'] = 'Test IdP';
$string['test_idp_label'] = 'Test IdP ({$a})';
$string['test_login'] = 'Login (AuthnRequest)';
$string['test_login_success'] = 'Login successful';
$string['test_logout'] = 'Logout (LogoutRequest)';
$string['test_logout_success'] = 'Logout successful';
$string['turn_on'] = 'Turn on';
$string['update_local_field'] = 'Update Totara field';
$string['update_local_field_create'] = 'On create';
$string['update_local_field_login'] = 'On every login';
$string['user_attribute_locking'] = 'User attribute locking';
$string['user_identifier'] = 'User identifier';
$string['user_identifier_help'] = 'Mapping the shared field between the identity provider and the Totara user data field is what keeps the user list in sync.';
$string['verification_complete_description'] = 'Your registration has been confirmed and you can now login to your account.';
$string['verification_complete_title'] = 'Email verification was successful';
$string['verification_failure_description_unknown_identifier'] = 'It seems there was an issue with the verification process and we couldn\'t retrieve your identifier. You may need to try logging in again.';
$string['verification_failure_description_with_identifier'] = 'It seems there was an issue with the verification process when logging in as "{$a}". You may need to try logging in again.';
$string['verification_failure_title'] = 'Something went wrong';
$string['verification_invalid_code_description'] = 'Looks like the verification link has expired or invalid. Please enter your email address and we’ll send another verification link.';
$string['verification_invalid_code_title'] = 'Email verification link expired';
$string['verification_logged_in_description'] = 'You have already logged in. Please log out and verify your account again.';
$string['verification_logged_in_title'] = 'Already logged in';
$string['verification_pending_description'] = 'We have sent an email for verification. Follow the instructions in email for logging into your account. Once it arrives, it will be valid for {$a} minutes.';
$string['verification_pending_title'] = 'Email verification pending';
$string['view_xml'] = 'View XML';
$string['warning_openssl_not_installed'] = 'The "openssl" PHP module is required to configure a SAML IdP. Please install it on your system and try again.';
$string['warning_user_already_logged_in'] = 'You have already logged in';
$string['x_error'] = '{$a} (error)';
$string['x_external_field'] = '{$a} external field';
$string['x_logs'] = '{$a} logs';
$string['x_update_local_field'] = '{$a} update Totara field';
$string['xml'] = 'XML';

/**
 * Errors / Exceptions
 */
$string['auth_failure:auth_type'] = 'User auth type is not set to {$a}';
$string['auth_failure:login_disabled'] = 'User suspended, not confirmed, or set to "nologin" auth type';
$string['auth_failure:multiple_matches'] = 'More than one matching user was found';
$string['auth_failure:no_user_no_create'] = 'User does not exist in Totara, and auto-creation is disabled.';
$string['auth_failure:tenant_suspended'] = 'User is a tenant member of a suspended tenancy.';
$string['auth_failure:user_identifier_not_found'] = 'The user identifier IdP field "{$a}" can not be found in the IdP response.';
$string['auth_failure:validation_email_send'] = 'Failed to send the validation email';
$string['error:auth_failure_for'] = '{$a->error} for user with identifier "{$a->username}": {$a->validation_message}';
$string['error:binding_not_found'] = 'There was no binding available to process this message';
$string['error:duplicate_idp_entity_id'] = 'There is already an IdP associated with this entity ID';
$string['error:entity_id_max'] = 'Entity ID has a maximum length of {$a} characters';
$string['error:invalid_algorithm'] = 'Invalid algorithm provided.';
$string['error:invalid_binding'] = 'Invalid binding was provided.';
$string['error:invalid_field_mapping'] = 'Invalid field mapping.';
$string['error:invalid_message_type'] = 'Invalid type from IdP request or response';
$string['error:invalid_metadata'] = 'The provided metadata is not valid.';
$string['error:invalid_payload'] = 'The payload was missing the attributes: {$a}';
$string['error:invalid_phone_field_value'] = '{$a} field value was too long.';
$string['error:mapping_field_too_long'] = 'The external value for {$a->field} was more than {$a->max} characters.';
$string['error:mapping_internal_invalid'] = 'The field {$a} is not available for mapping, it may have been removed.';
$string['error:metadata_url'] = 'The Metadata URL was not valid.';
$string['error:no_openssl'] = 'The PHP module “openssl” was not found but is mandatory.';
$string['error:not_enabled'] = 'The SAML 2.0 (SSO) authentication plugin is not enabled.';
$string['error:response_assertion_issuer_format_invalid'] = 'The assertion issuer was not valid.';
$string['error:response_assertion_issuer_invalid'] = 'The assertion issuer was not valid.';
$string['error:response_assertion_replayed'] = 'This assertion has already been used.';
$string['error:response_assertion_signature'] = 'There was an error validating the signature of the assertion.';
$string['error:response_assertions_unsigned'] = 'We require assertions to be signed but the IdP assertions were not signed.';
$string['error:response_includes_not_before'] = 'NotBefore attribute was included but should not be.';
$string['error:response_invalid_address'] = 'Request was not addressed to the correct ACS endpoint.';
$string['error:response_invalid_recipient'] = 'The recipient was not expected.';
$string['error:response_invalid_session_request'] = 'Request was not initiated by Service Provider or has already been used.';
$string['error:response_issuer_format_invalid'] = 'Invalid issuer format';
$string['error:response_issuer_invalid'] = 'Issue invalid';
$string['error:response_logout_failed'] = 'Logout was unsuccessful at the IdP';
$string['error:response_missing_field'] = 'Field was not included or was invalid: {$a}';
$string['error:response_no_statement'] = 'The request did not include a statement.';
$string['error:response_response_signature'] = 'There was an error validating the signature of the message.';
$string['error:response_unsigned'] = 'Neither the response nor the assertions were signed.';
$string['error:test_logout_failed'] = 'Logout failed: {$a}';
$string['error:test_logout_session_missing'] = 'There was no matching login session to test the logout against.';
$string['error:test_no_logout'] = 'This IdP does not have the logout protocol enabled.';
$string['error:test_no_matching_logout_key'] = 'The logout test failed, could not match the logout response to what was expected.';
$string['error:test_no_prior_login'] = 'The IdP does not appear to have a login session. Create a login session and then log out.';
$string['error:test_unknown_login'] = 'There was no known login request.';
$string['error:unknown_binding'] = 'The binding requested was not known or supported.';
$string['error:user_identifier_invalid_map'] = 'The User Identifier configuration and field mappings must match.';
$string['error:verification_failed'] = 'There was a problem with the verification process. Please try logging in again.';

/**
 * Deprecated since T19
 */
$string['authentication_failure_description'] = 'You successfully logged in, but we could not find an account that matches yours for some reason. Please contact the site administrator if you require assistance.';
$string['verification_failure_description'] = 'It seems there was an issue with the verification process. You may need to try logging in again.';
