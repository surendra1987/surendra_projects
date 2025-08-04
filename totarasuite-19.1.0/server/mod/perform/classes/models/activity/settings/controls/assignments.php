<?php
/**
 * This file is part of Totara Perform
 *
 * Copyright (C) 2024 onwards Totara Learning Solutions LTD
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
 * @author Oleg Demeshev <oleg.demeshev@totara.com>
 * @package mod_perform
 */

namespace mod_perform\models\activity\settings\controls;

use core\format;
use core\orm\collection;
use mod_perform\user_groups\grouping;
use core\webapi\formatter\field\string_field_formatter;
use mod_perform\user_groups\formatter\grouping as grouping_formatter;

/**
 * Class assignments control
 *
 * @package mod_perform\models\activity\settings\control
 */
class assignments extends control {

    /**
     * @inheritDoc
     */
    public function get(): array {
        $default_track = $this->activity->get_default_track();

        return [
            'activity_context_id' => $this->activity->get_context()->id,
            'draft' => $this->activity->is_draft(),
            'can_assign_positions' => $default_track->can_assign_positions,
            'can_assign_organisations' => $default_track->can_assign_organisations,
            'types' => static::get_types(),
            'assignees' => $this->get_assignees($default_track->assignments)
        ];
    }

    /**
     * @param collection $track_assignments
     * @return array
     */
    private function get_assignees(collection $track_assignments): array {
        $assignees = [];
        $formatter = new string_field_formatter(format::FORMAT_PLAIN, $this->activity->get_context());

        foreach ($track_assignments as $track_assignment) {
            /** @var grouping $grouping */
            $grouping = $track_assignment->group;
            $grouping_formatter = new grouping_formatter(
                $grouping,
                $this->activity->get_context()
            );
            $assignees[] = [
                'type' => $track_assignment->type,
                'group' => [
                    // Converting 'id' to string requires for TUI adder modal for cohort, organisation, position, etc
                    'id' => $formatter->format($grouping->get_id()),
                    // Converting 'type' to int requires to compare with get_types()::grouping::COHORT/ORG/POS/USER,
                    // see static::get_types() below
                    'type' => (int)$grouping->get_type(),
                    'type_label' => $grouping_formatter->format('type_label', format::FORMAT_PLAIN),
                    'name' => $grouping_formatter->format('name', format::FORMAT_PLAIN),
                    'size' => $grouping_formatter->format('size', format::FORMAT_PLAIN),
                    'extra' => $grouping_formatter->format('extra', format::FORMAT_PLAIN),
                ]
            ];
        }
        return $assignees;
    }

    /**
     * @return array
     */
    private static function get_types(): array {
        return [
            'aud' => grouping::COHORT,
            'org' => grouping::ORG,
            'pos' => grouping::POS,
            'ind' => grouping::USER
        ];
    }
}
