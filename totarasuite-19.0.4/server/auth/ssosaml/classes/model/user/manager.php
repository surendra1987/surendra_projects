<?php
/**
 * This file is part of Totara Talent Experience Platform
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
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 *
 * @author Kunle Odusan <kunle.odusan@totara.com>
 * @package auth_ssosaml
 */

namespace auth_ssosaml\model\user;

use auth_ssosaml\data_provider\idp_user_map as idp_user_map_data_provider;
use auth_ssosaml\exception\create_user as create_user_exception;
use auth_ssosaml\exception\user_validation;
use auth_ssosaml\model\idp;
use auth_ssosaml\model\idp_user_map;
use auth_ssosaml\model\user\field\factory;
use auth_ssosaml\provider\data\authn_response;
use auth_ssosaml\provider\logging\contract as logger_contract;
use auth_ssosaml\provider\logging\factory as logger_factory;
use core\entity\tenant;
use core\entity\user;
use core\event\user_login_failed;
use core\login\complete_login_callback;
use core_text;
use core_user;
use stdClass;
use function user_create_user;

/**
 * Manages the updating of user profile fields and login of valid users
 */
class manager {

    /**
     * @var idp
     */
    private idp $idp;

    /**
     * @var logger_contract
     */
    private logger_contract $logger;

    /**
     * @param idp $idp
     * @codeCoverageIgnore
     */
    public function __construct(idp $idp) {
        global $CFG;
        require_once $CFG->libdir . '/authlib.php';
        require_once $CFG->libdir . '/moodlelib.php';
        require_once $CFG->dirroot . '/user/lib.php';
        require_once $CFG->dirroot . '/user/profile/lib.php';
        require_once $CFG->dirroot . '/login/lib.php';

        $this->idp = $idp;
        $this->logger = logger_factory::get_logger($this->idp);
    }

    /**
     * @param authn_response $response
     * @return array
     */
    public function process_login(authn_response $response): array {
        global $CFG;

        $idp_field = $this->idp->idp_user_id_field;
        $totara_field = $this->idp->totara_user_id_field;
        $user_identifier = $response->get_attribute($idp_field);

        if (empty($user_identifier)) {
            $event = user_login_failed::create(
                [
                    'other' => ['username' => 'unknown', 'reason' => AUTH_LOGIN_NOUSER]
                ]
            );
            $event->trigger();
            return $this->login_failure(
                $response,
                null,
                'authentication_failure',
                get_string('auth_failure:user_identifier_not_found', 'auth_ssosaml', $idp_field)
            );
        }
        $delimiter = $this->idp->field_mapping_config['delimiter'];
        $plugin = get_auth_plugin('ssosaml');

        if (is_array($user_identifier)) {
            $user_identifier = implode($delimiter, $response->get_attribute($idp_field));
        }

        $user_identifier = core_text::strtolower($user_identifier);

        $query = user::repository();

        if ($totara_field === 'email') {
            $query->where_raw('lower(email) = :email', ['email' => $user_identifier]);  
        } else if ($totara_field === 'idnumber') {
            $query->where_raw('lower(idnumber) = :idnumber', ['idnumber' => $user_identifier]);
        } else {
            $query->where($totara_field, $user_identifier);
        }

        $matching_users = $query->filter_by_not_deleted()
            ->filter_by_not_guest()
            ->order_by($this->idp->totara_user_id_field)
            ->get();

        if ($matching_users->count() === 0) {
            if (!$this->idp->create_users) {
                $event = user_login_failed::create(
                    [
                        'other' => ['username' => $user_identifier ?? 'unknown', 'reason' => AUTH_LOGIN_NOUSER]
                    ]
                );
                $event->trigger();

                return $this->login_failure(
                    $response,
                    $user_identifier,
                    'authentication_failure',
                    get_string('auth_failure:no_user_no_create', 'auth_ssosaml')
                );
            }

            // No matching users, try to create them
            try {
                $user = $this->create_user($response);
                return $this->log_user_in($response, $user, true);
            } catch (user_validation $exception) {
                $event = user_login_failed::create(
                    [
                        'other' => ['username' => $user_identifier ?? 'unknown', 'reason' => AUTH_LOGIN_NOUSER]
                    ]
                );
                $event->trigger();

                return $this->login_failure($response, $user_identifier, 'authentication_failure', $exception->getMessage());
            }
        }

        if ($matching_users->count() > 1) {
            // If multiple users exist, try to filter to one matching the plugin auth type
            $matching_users = $matching_users->filter('auth', $plugin->authtype);
            if ($matching_users->count() !== 1) {
                $event = user_login_failed::create(
                    [
                        'other' => ['username' => $user_identifier ?? 'unknown', 'reason' => AUTH_LOGIN_UNAUTHORISED]
                    ]
                );
                $event->trigger();

                // We couldn't find a single user of the auth type, fail back to the error message page.
                return $this->login_failure(
                    $response,
                    $user_identifier,
                    'authentication_failure',
                    get_string('auth_failure:multiple_matches', 'auth_ssosaml')
                );
            }
        }

        /** @var user $user */
        $user = $matching_users->first();

        // We have a single user, validate that it's still good to login (not suspended)
        if ($user->suspended || !$user->confirmed || $user->auth === 'nologin') {
            $event = user_login_failed::create(
                [
                    'userid' => $user->id,
                    'other' => [
                        'username' => $user->username,
                        'reason' => ($user->suspended ? AUTH_LOGIN_SUSPENDED : AUTH_LOGIN_UNAUTHORISED),
                    ]
                ]
            );
            $event->trigger();

            return $this->login_failure(
                $response,
                $user_identifier,
                'authentication_failure',
                get_string('auth_failure:login_disabled', 'auth_ssosaml')
            );
        }

        // If they're a tenant member, check their tenant isn't suspended
        if (!empty($CFG->tenantsenabled) && !empty($user->tenantid)) {
            $tenant_is_valid = tenant::repository()
                ->where('id', $user->tenantid)
                ->where('suspended', 0)
                ->exists();
            if (!$tenant_is_valid) {
                $event = user_login_failed::create(
                    [
                        'userid' => $user->id,
                        'other' => [
                            'username' => $user->username,
                            'reason' => AUTH_LOGIN_SUSPENDED,
                        ]
                    ]
                );
                $event->trigger();

                return $this->login_failure(
                    $response,
                    $user_identifier,
                    'authentication_failure',
                    get_string('auth_failure:tenant_suspended', 'auth_ssosaml')
                );
            }
        }

        // We have a single user, decide how to act based on the autolink option of this IdP.
        $auto_link = $this->idp->autolink_users;

        // Autolinking with no confirmation, just log the user in
        if ($auto_link === idp::AUTOLINK_NO_CONFIRMATION) {
            return $this->log_user_in($response, $user->to_record(), false);
        }

        // No autolinking, user must have authtype of SSOSAML
        if ($auto_link === idp::AUTOLINK_NONE) {
            if ($user->auth !== $plugin->authtype) {
                $event = user_login_failed::create(
                    [
                        'userid' => $user->id,
                        'other' => [
                            'username' => $user->username,
                            'reason' => AUTH_LOGIN_UNAUTHORISED,
                        ]
                    ]
                );
                $event->trigger();

                // User does not have the correct auth type, cannot link
                return $this->login_failure(
                    $response,
                    $user_identifier,
                    'authentication_failure',
                    get_string('auth_failure:auth_type', 'auth_ssosaml', $plugin->authtype)
                );
            }

            return $this->log_user_in($response, $user->to_record(), false);
        }

        // End result must be we need email confirmation
        // Lookup their idp_user mapping record
        $idp_map = idp_user_map_data_provider::get_latest_for_user($this->idp->id, $user->id);

        if ($idp_map && $idp_map->is_confirmed()) {
            // User has been confirmed
            return $this->log_user_in($response, $user->to_record(), false);
        }

        // Recreate the verification code & send the email
        $idp_map = idp_user_map::create_and_invalidate($this->idp->id, $user->id);
        $idp_label = $this->idp->label ?? get_string('default_label', 'auth_ssosaml');
        if (!$idp_map->send_confirmation_email($user_identifier, $user->to_record(), $idp_label)) {
            $event = user_login_failed::create(
                [
                    'userid' => $user->id,
                    'other' => [
                        'username' => $user->username,
                        'reason' => AUTH_LOGIN_UNAUTHORISED,
                    ]
                ]
            );
            $event->trigger();

            return $this->login_failure(
                $response,
                $user_identifier,
                'verification_failure',
                get_string('auth_failure:validation_email_send', 'auth_ssosaml')
            );
        }

        // Bounce them to the confirmation required screen
        return [
            'action' => 'confirm',
        ];
    }

    /**
     * @param authn_response $response
     * @param string|null $user_identifier - The user identifier from the IDP
     * @param string $error - The error type
     * @param string|null $validation_message
     * @return array
     *
     * @note Param $error requires three language strings to accompany any type added:
     *   - `<error>_title`, The title to display
     *   - `<error>_description_with_identifier`, Which takes a parameter to display the $user_identifier
     *   - `<error>_unknown_identifier`, Which is used when no $user_identifier is given
     * See `authentication_failure_` in `server/auth/ssosaml/lang/en/auth_ssosaml.php` for an example
     */
    protected function login_failure(authn_response $response, ?string $user_identifier, string $error, ?string $validation_message = null): array {
        if ($response->log_id) {
            $error_message = $validation_message;
            if (!empty($user_identifier)) {
                $error_message = get_string('error:auth_failure_for', 'auth_ssosaml', [
                    'error' => $error,
                    'username' => $user_identifier,
                    'validation_message' => $validation_message,
                ]);
            }
            $this->logger->log_error($response->log_id, $error_message);
        }
        return [
            'action' => 'error',
            'error' => $error,
            'user_identifier' => $user_identifier,
            'reason' => $validation_message,
        ];
    }

    /**
     * Complete the user login process.
     *
     * @param authn_response $response
     * @param stdClass $user
     * @param bool $new_user
     * @return void
     */
    protected function log_user_in(authn_response $response, stdClass $user, bool $new_user = false): void {
        if ($response->log_id) {
            $this->logger->update_log_entry_user_id($response->log_id, $user->id);
        }

        complete_user_login(
            $user,
            complete_login_callback::create(
                [manager::class, 'complete_login_callback'],
                [$new_user, $response, $this->idp->id]
            )
        );
    }

    /**
     * Create a Totara user based on the IdP login response.
     *
     * @param authn_response $response
     * @return stdClass
     */
    private function create_user(authn_response $response): stdClass {
        $user = new stdClass();
        self::map_attributes_to_user_fields($this->idp, $response, $user);

        // set default username if it was not configured
        if (empty($user->username)) {
            $user->username = factory::get_instance(
                'username',
                $this->idp->field_mapping_config,
                [
                    'logger' => $this->logger,
                    'log_id' => $response->log_id,
                ]
            )
                ->parse_attribute(
                    $response->get_attribute($this->idp->idp_user_id_field)
                );
            if (empty($user->username)) {
                throw new create_user_exception('No username was provided or could be calculated');
            }
            $user->username = core_text::strtolower($user->username);
        }

        // Various values that user_create_user doesn't validate or set.
        $user->confirmed = 1;
        $user->auth = get_auth_plugin('ssosaml')->authtype;
        $user->lastip = getremoteaddr();
        $user->timecreated = time();
        $user->timemodified = $user->timecreated;
        $user->password = AUTH_PASSWORD_NOT_CACHED;

        $user->id = user_create_user($user, false);
        // Store any custom profile fields.
        profile_save_data($user);

        // We created this user, so this user is valid for this map
        idp_user_map::create_and_invalidate($this->idp->id, $user->id, idp_user_map::STATUS_CONFIRMED);

        // Make sure all user data is fetched.
        return core_user::get_user($user->id);
    }

    /**
     * Callback executed after complete_user_login is called.
     * In here, we update the user's profile, the saml session record and redirect the user
     * @param bool $new_user
     * @param authn_response $response
     * @param int $idp_id
     * @return void
     */
    public static function complete_login_callback(bool $new_user, authn_response $response, int $idp_id) {
        global $USER;
        $user_record = (new user($USER->id))->to_record();
        $redirect_url = core_login_get_return_url();
        $idp = idp::load_by_id($idp_id);

        // update user profile if an existing user was found.
        if (!$new_user && $user_record->auth === get_auth_plugin('ssosaml')->authtype) {
            $count = self::map_attributes_to_user_fields($idp, $response, $user_record, true);
            // Only save the user if we actually changed fields (avoid pointless writes).
            if ($count > 0) {
                profile_save_data($user_record);
                user_update_user($user_record, false);

                // We have changed $user, we need to update $USER in session
                $user = get_complete_user_data('id', $user_record->id);
                \core\session\manager::set_user($user);
            }
        }

        // create if IdP initiated. Complete if SP initiated.
        $session_manager = $idp->get_session_manager();
        $request_id = $response->in_response_to;
        if (empty($request_id)) {
            $session_manager->create_idp_initiated_session($user_record->id, $response);
        } else {
            $session_manager->complete_sp_initiated_session($user_record->id, $response);
        }

        // Successfully created or logged existing user in
        // Redirect 'em on
        $relay_state = clean_param($response->relay_state, PARAM_LOCALURL);
        if (!empty($relay_state)) {
            $redirect_url = $relay_state;
        }

        redirect(new \moodle_url($redirect_url));
    }

    /**
     * Map attributes from the IdP unto the user record
     *
     * @param authn_response $response
     * @param stdClass $user_record
     * @param bool $is_login
     * @return int The number of fields that were set.
     */
    private static function map_attributes_to_user_fields(idp $idp, authn_response $response, stdClass $user_record, bool $is_login = false): int {
        $mapped_fields = $idp->field_mapping_config['field_maps'];
        $field_mapping_config = $idp->field_mapping_config;
        $logger = logger_factory::get_logger($idp);

        $changed_count = 0;

        foreach ($mapped_fields as $mapped_field) {
            // If this is a login, only update this field if it needs to be.
            if ($is_login && $mapped_field['update'] !== 'LOGIN') {
                continue;
            }

            $user_field = $mapped_field['internal'];
            $response_attribute_value = $response->get_attribute($mapped_field['external']);
            // Check if attribute has a value
            if (!is_null($response_attribute_value)) {
                $new_value = factory::get_instance(
                    $user_field,
                    $field_mapping_config,
                    [
                        'logger' => $logger,
                        'log_id' => $response->log_id,
                    ]
                )
                    ->parse_attribute(
                        $response_attribute_value,
                        $user_record->id ?? null
                    );

                // Only update if the value is different, to save pointless writes.
                if (!isset($user_record->$user_field) || $user_record->$user_field !== $new_value) {
                    $user_record->$user_field = $new_value;
                    $changed_count++;
                }
            }
        }

        return $changed_count;
    }
}
