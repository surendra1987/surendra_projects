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
 * @author Matthias Bonk <matthias.bonk@totara.com>
 * @package mod_perform
 */

namespace mod_perform\usagedata;

use mod_perform\entity\activity\participant_instance;
use tool_usagedata\export;

/**
 * Usage data export class for metrics related to participant instances.
 */
class participant_instances implements export {

    /**
     * @inheritDoc
     */
    public function get_summary(): string {
        return get_string('count_of_participant_instances_with_access_removed', 'mod_perform');
    }

    /**
     * @inheritDoc
     */
    public function get_type(): int {
        return export::TYPE_OBJECT;
    }

    /**
     * @inheritDoc
     */
    public function export(): array {

        $count = participant_instance::repository()
            ->where('access_removed', 1)
            ->count();

        return [
            'count_of_participant_instances_with_access_removed' => $count,
        ];
    }
}
