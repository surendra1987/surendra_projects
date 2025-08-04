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

use auth_ssosaml\exception\field_mapping_exception;

/**
 * Base contract for the field processor classes.
 */
interface processor {

    /**
     * Get instance of the processor
     *
     * @param array $field_info Containing the field name & type
     * @param array $field_mapping_config
     * @param array $log_info Containing the logger and the log_id
     * @return processor
    */
    public static function instance(array $field_info, array $field_mapping_config, array $log_info): processor;

    /**
     * Parse attribute value to be linked to the user profile
     *
     * @param $attribute_value
     * @param int|null $user_id
     * @throws field_mapping_exception
     * @return mixed
     */
    public function parse_attribute($attribute_value, ?int $user_id = null);
}