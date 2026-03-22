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
use core\entity\user;

/**
 * Email field processor
 */
class email implements processor {

    /**
     * Field mapping configuration used.
     * Specifies the Delimiter.
     *
     * @var array
     */
    protected array $field_mapping_config;

    /**
     * @param array $field_mapping_config
     */
    public function __construct(array $field_mapping_config) {
        $this->field_mapping_config = $field_mapping_config;
    }

    /**
     * @inheritDoc
     */
    public static function instance(array $field_info, array $field_mapping_config, array $log_info): processor {
        return new self($field_mapping_config);
    }

    /**
     * @inheritDoc
     */
    public function parse_attribute($attribute_value, ?int $user_id = null) {
        if (is_array($attribute_value)) {
            $attribute_value = $attribute_value[0];
        }
        if (!validate_email($attribute_value)
            || email_is_not_allowed($attribute_value)
            || $this->email_is_taken($attribute_value, $user_id)
        ) {
            throw new field_mapping_exception("Invalid email provided");
        }

        return $attribute_value;
    }

    /**
     * Checks if the email address is taken.
     *
     * @param string $email
     * @param int|null $user_id
     * @return bool
     */
    private function email_is_taken(string $email, int $user_id = null): bool {
        $email_is_taken = user::repository()
            ->filter_by_email($email)
            ->where('id', '!=', $user_id)
            ->filter_by_not_deleted()
            ->exists();

        if ($email_is_taken && empty(get_config('core', 'allowaccountssameemail'))) {
            return true;
        }

        return false;
    }
}