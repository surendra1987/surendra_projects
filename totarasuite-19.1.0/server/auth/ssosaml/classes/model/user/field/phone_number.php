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
 * @author Navjeet Singh <navjeet.singh@totara.com>
 * @package auth_ssosaml
 */

namespace auth_ssosaml\model\user\field;

use auth_ssosaml\provider\logging\contract;
use core_text;

/**
 * Phone1 or Phone2 field processor.
 */
class phone_number implements processor {

    /**
     * Field mapping configuration used.
     * Specifies the Delimiter.
     *
     * @var array
     */
    protected array $field_mapping_config;

    protected array $field_info;

    protected contract $logger;

    protected int $log_id;

    /**
     * @param array $field_mapping_config
     */
    public function __construct(array $field_info, array $field_mapping_config, array $log_info) {
        $this->field_info = $field_info;
        $this->field_mapping_config = $field_mapping_config;
        $this->logger = $log_info['logger'];
        $this->log_id = $log_info['log_id'];
    }

    public static function instance(array $field_info, array $field_mapping_config, array $log_info): processor {
        return new self($field_info, $field_mapping_config, $log_info);
    }

    /**
     * Convert phone1 or phone2 field value
     *
     * @param string|array $attribute_value
     * @param int|null $user_id
     * @return string
     */
    public function parse_attribute($attribute_value, ?int $user_id = null): string {
        if (is_array($attribute_value)) {
            $attribute_value = implode($this->field_mapping_config['delimiter'], $attribute_value);
        }

        // Check the length of phone field
        if (!is_null($attribute_value) && core_text::strlen($attribute_value) > 20) {
            $attribute_value = '';
            $this->logger->log_notice(
                $this->log_id,
                get_string(
                    'error:invalid_phone_field_value',
                    'auth_ssosaml',
                    get_string($this->field_info['name'])
                )
            );
        }

        return clean_param($attribute_value, $this->field_info['type']);
    }
}