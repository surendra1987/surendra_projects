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
 * @author Navjeet Singh <navjeet.singh@totara.com>
 * @package core_mfa
 */

$string['add_factor'] = 'Add factor';
$string['added_date'] = 'Added date';
$string['admin_mfa_reset_done'] = 'MFA instances for the admin has been revoked';
$string['admin_username_prompt'] = 'Enter the admin username';
$string['auth_plugins'] = 'Authentication plugins';
$string['check_auth_plugin_compatibility'] = 'Authentication plugins are compatible with multi-factor authentication plugins.';
$string['check_auth_plugin_compatibility_detail'] = 'All authentication plugins should be compatible with multi-factor authentication plugins.';
$string['check_auth_plugin_compatibility_name'] = 'Multi-factor authentication compatibility';
$string['choose_a_factor'] = 'Choose a factor';
$string['choose_desc'] = 'Choose a factor to verify your identity.';
$string['configured_factors_empty_state'] = 'You haven\'t added any factors yet.';
$string['event_factor_enabled_changed'] = 'MFA factor enabled/disabled';
$string['event_instance_created'] = 'MFA factor registered';
$string['event_instance_deleted'] = 'MFA factor unregistered';
$string['event_instances_revoked'] = 'MFA factors revoked';
$string['factor'] = 'Factor';
$string['factors'] = 'Factors';
$string['manage_mfa_subtitle'] = 'Add an additional layer of security to your account during login';
$string['manage_multi_factor_authentication'] = 'Manage multi-factor authentication';
$string['name'] = 'Multi-factor authentication';
$string['not_site_admin'] = 'Not a site admin';
$string['register_a_new_factor'] = 'Register a new factor';
$string['remove_factor'] = 'Remove factor';
$string['remove_factor_confirm_message'] = 'Are you sure you want to remove this factor?';
$string['remove_x'] = 'Remove {$a}';
$string['reset_mfa'] = 'Reset MFA';
$string['reset_mfa_cli'] = 'Reset admin user MFA by username';
$string['step_1'] = 'Step 1';
$string['step_2'] = 'Step 2';
$string['two_factor_verification'] = 'Two-factor verification';
$string['use_a_different_factor'] = 'Use a different factor';
$string['verify'] = 'Verify';

/**
 * Errors
 */
$string['error:auth_plugin_compatibility'] = 'This plugins is not compatible with multi-factor authentication plugins.';
$string['error:auth_plugins_compatibility'] = 'Some authentication plugins are not compatible with multi-factor authentication plugins.';
$string['error:factor_verification'] = 'This factor could not be verified';

/**
 * Notifications
 */
$string['builtin_mfa_instance_created_body'] = "Hi [recipient:full_name],\n\n[factor:name] is now enabled for your account. When logging in next time you will need to enter your password and a security code.\n\nIf you have any questions regarding this email, contact your administrator.";
$string['builtin_mfa_instance_created_subject'] = 'Multi-factor authentication added to your account';
$string['builtin_mfa_instance_created_title'] = 'Multi-factor authentication added to user';
$string['builtin_mfa_instance_deleted_body'] = "Hi [recipient:full_name],\n\n[factor:name] is now removed from your account.\n\nIf you have any questions regarding this email, contact your administrator.";
$string['builtin_mfa_instance_deleted_subject'] = 'Multi-factor authentication removed from your account';
$string['builtin_mfa_instance_deleted_title'] = 'Multi-factor authentication removed from your account';
$string['placeholder_group_instance'] = 'Factor {$a}';
$string['placeholder_instance_added'] = 'Date Added';
$string['placeholder_instance_name'] = 'Name';
$string['resolver_mfa_instance_created_title'] = 'User added multi-factor authentication';
$string['resolver_mfa_instance_deleted_title'] = 'User deleted multi-factor authentication';
