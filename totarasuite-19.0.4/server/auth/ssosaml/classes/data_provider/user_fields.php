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
 * @author Simon Chester <simon.chester@totara.com>
 * @package auth_ssosaml
 */

namespace auth_ssosaml\data_provider;

/**
 * Data provider for user fields
 */
class user_fields {
    /**
     * Get info about user profile fields, including custom fields
     *
     * @return array
     */
    public static function get_info(): array {
        $authplugin = get_auth_plugin('ssosaml');
        $userfields = $authplugin->userfields;
        $custom_field_info = profile_get_custom_fields();

        $result = [];

        foreach ($userfields as $field) {
            // special case lang/url labels -- same as display_auth_lock_options()
            if ($field === 'lang') {
                $result[$field] = [
                    'id' => $field,
                    'label' => get_string('language'),
                    'lockable' => false,
                    'custom' => false,
                ];
            } else if ($field === 'url') {
                $result[$field] = [
                    'id' => $field,
                    'label' => get_string('webpage'),
                    'lockable' => true,
                    'custom' => false,
                ];
            } else {
                $result[$field] = [
                    'id' => $field,
                    'label' => get_string($field),
                    'lockable' => $field !== 'username',
                    'custom' => false,
                ];
            }
        }

        foreach ($custom_field_info as $custom_field) {
            $result['profile_field_' . $custom_field->shortname] = [
                'id' => 'profile_field_' . $custom_field->shortname,
                'label' => $custom_field->name,
                'lockable' => true,
                'custom' => true,
            ];
        }

        return $result;
    }

    /**
     * Returns the list of user id fields that can be used as an identifier.
     *
     * @return array
     */
    public static function get_user_id_fields(): array {
        global $CFG;

        $fields = [
            'username' => get_string('username', 'core'),
            'email' => get_string('email', 'core'),
            'idnumber' => get_string('idnumber', 'core'),
        ];

        // If duplicate emails are allowed, we cannot use email as an identifier field.
        if (!empty($CFG->allowaccountssameemail)) {
            unset($fields['email']);
        }

        return $fields;
    }
}
