<?php
/**
 * This file is part of Totara Core
 *
 * Copyright (C) 2025 onwards Totara Learning Solutions LTD
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
 * @package goaltype_basic
 */

namespace goaltype_basic\hook;

use goaltype_basic\model\creation_option;

/**
 * Hook for plugins to alter creation options
 */
class creation_options extends \totara_core\hook\base {
    /** @var creation_option[] */
    private array $options;

    /**
     * @param creation_option[] $options
     */
    public function __construct(array $options) {
        $this->options = $options;
    }

    /**
     * @return creation_option[]
     */
    public function get_options(): array {
        return $this->options;
    }

    /**
     * @param creation_option $option
     * @return void
     */
    public function add_option(creation_option $option): void {
        $this->options[] = $option;
    }
}
