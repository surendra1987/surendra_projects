<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Class for loading/storing issuers from the DB.
 *
 * @package    auth_oauth2
 * @copyright  2017 Damyon Wiese
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace auth_oauth2;

defined('MOODLE_INTERNAL') || die();

/**
 * Class for loading/storing issuer from the DB
 *
 * @copyright  2017 Damyon Wiese
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class linked_login extends \core\persistent {

    const TABLE = 'auth_oauth2_linked_login';

    const USERNAME_TYPE_USERNAME = 0;
    const USERNAME_TYPE_EXTERNAL_ID = 1;

    /**
     * Return the definition of the properties of this model.
     *
     * @return array
     */
    protected static function define_properties() {
        return array(
            'issuerid' => array(
                'type' => PARAM_INT
            ),
            'userid' => array(
                'type' => PARAM_INT,
                'null' => NULL_ALLOWED,
                'optional' => true,
                'default' => null,
            ),
            'username' => array(
                'type' => PARAM_RAW
            ),
            'username_type' => array(
                'type' => PARAM_INT,
            ),
            'email' => array(
                'type' => PARAM_RAW
            ),
            'confirmed' => array(
                'type' => PARAM_BOOL
            ),
            'confirmtoken' => array(
                'type' => PARAM_RAW,
                'optional' => true,
                'default' => '',
            ),
            'confirmtokenexpires' => array(
                'type' => PARAM_INT,
                'null' => NULL_ALLOWED,
                'optional' => true,
                'default' => null,
            )
        );
    }

    /**
     * Find a record by either external_id (preferred) or username
     *
     * @param int $issuerid
     * @param array $userinfo
     * @return static|null
     * @throws \moodle_exception if neither external_id or username are provided
     */
    public static function get_record_by_external_id_or_username(int $issuerid, array $userinfo): ?linked_login {
        if (empty($userinfo['external_id']) && empty($userinfo['username'])) {
            throw new \moodle_exception('loginerror_userincomplete', 'auth_oauth2');
        }

        $linkedlogin = null;

        if (isset($userinfo['external_id'])) {
            $linkedlogin = static::get_record([
                'issuerid' => $issuerid,
                'username' => $userinfo['external_id'],
                'username_type' => self::USERNAME_TYPE_EXTERNAL_ID
            ]);
        }

        if (!$linkedlogin && isset($userinfo['username'])) {
            $linkedlogin = static::get_record([
                'issuerid' => $issuerid,
                'username' => $userinfo['username'],
                'username_type' => self::USERNAME_TYPE_USERNAME
            ]);
        }

        return $linkedlogin ? $linkedlogin : null;
    }

    /**
     * Set the external_id, or username if external_id is not provided
     *
     * @param array $userinfo
     * @return void
     */
    public function set_external_id_or_username(array $userinfo) {
        if (isset($userinfo['external_id'])) {
            $this->set('username', $userinfo['external_id']);
            $this->set('username_type', self::USERNAME_TYPE_EXTERNAL_ID);
        } else if (isset($userinfo['username'])) {
            $this->set('username', $userinfo['username']);
            $this->set('username_type', self::USERNAME_TYPE_USERNAME);
        } else {
            throw new \moodle_exception('loginerror_userincomplete', 'auth_oauth2');
        }
    }

    /**
     * Get userinfo -- email, external_id, username
     *
     * @return array
     */
    public function get_userinfo(): array {
        $userinfo = [
            'email' => $this->get('email')
        ];
        $username = $this->get('username');
        $type = $this->get('username_type');
        switch ($type) {
            case self::USERNAME_TYPE_EXTERNAL_ID:
                $userinfo['external_id'] = $username;
                break;
            case self::USERNAME_TYPE_USERNAME:
                $userinfo['username'] = $username;
                break;
            default:
                throw new \coding_exception('Unknown username_type value: ' . $type);
        }
        return $userinfo;
    }
}
