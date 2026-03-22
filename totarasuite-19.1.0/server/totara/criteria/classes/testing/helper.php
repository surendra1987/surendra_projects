<?php
/*
 * This file is part of Totara Learn
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
 * @author Ning Zhou <ning.zhou@totara.com>
 * @package totara_criteria
 */

namespace totara_criteria\testing;

use core\hook\modify_event_data;
use core_phpunit\hook_sink;
use totara_core\hook\base;

trait helper {

    /**
     * Finish hook redirection and return hooks received.
     *
     * @param hook_sink $sink
     * @return base[]
     */
    private function get_hooks(hook_sink $sink): array {
        return $sink->get_hooks_by_classname(modify_event_data::class, false);
    }

    /**
     * Count the hooks
     *
     * @param hook_sink $sink
     * @return int
     */
    private function get_hooks_count(hook_sink $sink): int {
        return count($this->get_hooks($sink));
    }
}