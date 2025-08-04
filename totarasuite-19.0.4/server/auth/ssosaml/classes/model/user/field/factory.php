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

namespace auth_ssosaml\model\user\field;

use core_user;

/**
 * Attribute processor to process attributes used in the user profile.
 */
abstract class factory {

    /**
     * A list of custom fields and their associated type
    */
    private static array $custom_fields;

    /**
     * Get instance of user field processor.
     *
     * @param string $field
     * @param array $field_mapping_config
     * @param array $log_info
     * @return processor
     */
    public static function get_instance(string $field, array $field_mapping_config, array $log_info): processor {
        $field_info = self::get_field_info($field);

        switch ($field) {
            case 'email':
                return email::instance($field_info, $field_mapping_config, $log_info);
            case 'username':
                return username::instance($field_info, $field_mapping_config, $log_info);
            case 'phone1':
            case 'phone2':
                return phone_number::instance($field_info, $field_mapping_config, $log_info);
            default:
                return default_processor::instance($field_info, $field_mapping_config, $log_info);
        }
    }

    /**
     * Get the field info
     *
     * @param string $field
     * @return array
    */
    private static function get_field_info(string $field): array {
        if(!isset(self::$custom_fields)) {
            $custom_fields = profile_get_custom_fields();
            $mapped_fields = [];

            foreach ($custom_fields as $custom_field) {
                $mapped_fields["profile_field_" . $custom_field->shortname] = $custom_field->datatype;
            }
            self::$custom_fields = $mapped_fields;
        }

        return [
            'name' => $field,
            'type' => isset(self::$custom_fields[$field])
                ? PARAM_NOTAGS // Default to no tags till we fix in TL-37286
                : core_user::get_property_definition($field)['type']
        ];
    }
}
