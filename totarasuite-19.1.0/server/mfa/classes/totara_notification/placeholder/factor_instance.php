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
 * @package core_mfa
 */

namespace core_mfa\totara_notification\placeholder;

use core_mfa\model\instance;
use totara_notification\placeholder\abstraction\placeholder_instance_cache;
use totara_notification\placeholder\abstraction\single_emptiable_placeholder;
use totara_notification\placeholder\option;

/**
 * Placeholders for a single MFA instance
 */
class factor_instance extends single_emptiable_placeholder {
    /**
     * @var string|null
     */
    protected ?string $factor_name;

    /**
     * @var int|null
     */
    protected ?int $date_created;

    /**
     * @param string|null $factor_name
     * @param int|null $date_created
     */
    public function __construct(?string $factor_name, ?int $date_created) {
        $this->factor_name = $factor_name;
        $this->date_created = $date_created;
    }

    /**
     * @param array $event_data
     * @return self
     */
    public static function from_event_data(array $event_data): self {
        return new self($event_data['factor_name'], $event_data['date_created']);
    }

    /**
     * @return array|option[]
     */
    public static function get_options(): array {
        return [
            option::create('name', get_string('placeholder_instance_name', 'core_mfa')),
            option::create('date_added', get_string('placeholder_instance_added', 'core_mfa')),
        ];
    }

    /**
     * @param string $key
     * @return bool
     */
    protected function is_available(string $key): bool {
        return !empty($this->factor_name);
    }

    /**
     * @param string $key
     * @return string
     */
    protected function do_get(string $key): ?string {
        if (empty($this->factor_name)) {
            throw new \coding_exception('No factor was provided or known');
        }

        switch ($key) {
            case 'name':
                return $this->factor_name;

            case 'date_added':
                $strftimedatetime = get_string('strftimedatetime');
                return userdate($this->date_created, $strftimedatetime);
        }

        throw new \coding_exception("Invalid key '{$key}'");
    }
}