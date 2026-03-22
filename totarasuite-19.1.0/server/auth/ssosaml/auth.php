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
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @author Cody Finegan <cody.finegan@totara.com>
 * @package auth_ssosaml
 */

use auth_ssosaml\data_provider\idp as idp_provider;
use auth_ssosaml\entity\session;
use auth_ssosaml\local\util;
use auth_ssosaml\model\idp;
use auth_ssosaml\provider\process\logout\make_request;
use core\orm\query\exceptions\record_not_found_exception;

defined('MOODLE_INTERNAL') || die();
global $CFG;
require_once($CFG->libdir . '/authlib.php');

/**
 * Auth plugin for SAML connections.
 */
final class auth_plugin_ssosaml extends auth_plugin_base {

    /**
     * Setup the plugin in the constructor.
     */
    public function __construct() {
        $this->authtype = 'ssosaml';
        $this->config = get_config('auth_ssosaml');

        // Username is a field we should be able to map during creation
        if (!in_array('username', $this->userfields)) {
            array_unshift($this->userfields, "username");
        }
    }

    /**
     * This auth type can be manually set.
     *
     * @return bool
     */
    public function can_be_manually_set(): bool {
        return true;
    }

    /**
     * There is no password to change.
     *
     * @return false
     */
    public function can_change_password(): bool {
        return false;
    }

    /**
     * No passwords to work with.
     *
     * @return false
     */
    public function can_reset_password(): bool {
        return false;
    }

    /**
     * @inheritDoc
     */
    public function ignore_timeout_hook($user, $sid, $timecreated, $timemodified) {
        if ($user->auth !== $this->authtype) {
            return false;
        }

        // delete associated IdP session record.
        session::repository()
            ->where('session_id', $sid)
            ->where('user_id', $user->id)
            ->delete();

        return false;
    }

    /**
     * This plugin does not use the internal password hashing.
     *
     * @return false
     */
    public function is_internal(): bool {
        return false;
    }

    /**
     * Totara should automatically update internal user records
     * from the external data sources.
     *
     * @return true
     */
    public function is_synchronised_with_external(): bool {
        return true;
    }

    /**
     * List of identity providers that are shown on the login page.
     *
     * @param $wantsurl
     * @return array
     */
    public function loginpage_idp_list($wantsurl): array {
        if (!util::is_openssl_loaded()) {
            return [];
        }

        $enabled_idps = idp_provider::get_visible();
        $idps = [];

        foreach ($enabled_idps as $enabled_idp) {
            $idps[] = [
                'url' => (new moodle_url("/auth/ssosaml/sp/sso.php", [
                    'id' => $enabled_idp->id,
                    'wantsurl' => $wantsurl,
                ]))->out(false),
                'name' => format_string($enabled_idp->label ? $enabled_idp->label : get_string('default_label', 'auth_ssosaml')),
                'icon' => new pix_icon('i/user', 'Login'),
                'iconurl' => null,
            ];
        }

        return $idps;
    }

    /**
     * Check if we need to trigger a auth redirect on the login page.
     *
     * @return void
     */
    public function loginpage_hook(): void {
        // We might need to redirect if we're told to.
        $this->handle_redirects();
    }

    /**
     * SAML does not work with passwords on this side of things.
     *
     * @return true
     */
    public function prevent_local_passwords(): bool {
        return true;
    }

    /**
     * We don't work with usernames/passwords in this plugin.
     *
     * @param string $username
     * @param string $password
     * @return false
     */
    public function user_login($username, $password): bool {
        return false;
    }

    /**
     * Handle the user logout behaviour.
     *
     * @return void
     */
    public function logoutpage_hook(): void {
        (new make_request())->execute();
    }

    /**
     * Figures out if we should automatically redirect the visitor to SAML.
     *
     * @return void
     */
    protected function handle_redirects(): void {
        // No redirects on a POST method
        if (!empty($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'POST') {
            return;
        }

        // This isn't used by us, but can be used by other auth plugins
        $no_redirect = optional_param('noredirect', 0, PARAM_BOOL);
        $idp_id = optional_param('ssosaml_idp', null, PARAM_INT);
        if (!empty($no_redirect) || empty($idp_id)) {
            return;
        }

        try {
            $idp = idp::load_by_id($idp_id);
        } catch (record_not_found_exception $ex) {
            // If the IdP wasn't found, just continue on. Don't expose the existence of an IdP via the login page at all.
            return;
        }

        // Got to be enabled
        if (!$idp->status) {
            return;
        }

        $url = new moodle_url('/auth/ssosaml/sp/sso.php', ['id' => $idp->id]);
        redirect($url);
    }

    /**
     * @inheritDoc
     */
    public static function supports_mfa(): bool {
        return true;
    }
}
