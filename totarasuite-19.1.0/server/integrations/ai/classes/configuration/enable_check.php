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
 * @author Kunle Odusan <kunle.odusan@totara.com>
 * @package core_ai
 */

namespace core_ai\configuration;

/**
 * An enable_check is a structured object that allows something to declare whether
 * it can be enabled or not, and why.
 */
class enable_check {

    private bool $can_be_enabled;

    private array $reasons;

    public function __construct(bool $can_be_enabled = true, array $reasons = []) {
        $this->can_be_enabled = $can_be_enabled;
        $this->reasons = $reasons;
    }

    /**
     * Can the plugin be enabled?
     *
     * @return bool
     */
    public function can_be_enabled(): bool {
        return $this->can_be_enabled;
    }

    /**
     * Get the reasons why the enable_check result is what it is.
     *
     * @return array of strings
     */
    public function get_reasons(): array {
        return $this->reasons;
    }
}
