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

use core\format;
use core\orm\collection;
use core\webapi\formatter\field\string_field_formatter;
use core_phpunit\testcase;
use mod_perform\models\activity\settings\controls\control_manager;
use mod_perform\models\activity\activity as activity_model;
use mod_perform\testing\activity_controls_trait;
use mod_perform\testing\activity_generator_configuration;
use mod_perform\user_groups\formatter\grouping as grouping_formatter;
use mod_perform\user_groups\grouping;

/**
 * @group perform
 */
class mod_perform_activity_controls_assignments_test extends testcase {
    use activity_controls_trait;

    public function test_assignments_structure_controls(): void {
        $activity = $this->create_activity();

        $controls = (new control_manager($activity->id))->get_controls(['assignments']);
        $assignments_control = $controls['assignments'];

        static::assertEquals(
            $this->get_assignments($activity),
            $assignments_control
        );
        static::assertCount(1, $assignments_control['assignees']);
    }

    public function test_assignments_with_track(): void {
        $configuration = activity_generator_configuration::new()
            ->set_cohort_assignments_per_activity(4);
        $activity = $this->create_activity($configuration);

        $controls = (new control_manager($activity->id))->get_controls(['assignments']);
        $assignments_control = $controls['assignments'];

        static::assertEquals(
            $this->get_assignments($activity),
            $assignments_control
        );
        static::assertCount(4, $assignments_control['assignees']);
        foreach ($assignments_control['assignees'] as $assignment) {
            static::assertEquals(grouping::COHORT, $assignment['type']);
        }

        $assignees = $assignments_control['assignees'];
        $track_assignments = ($activity->get_default_track())->assignments;
        $i = 0;
        foreach ($track_assignments as $track_assignment) {
            /** @var grouping $grouping */
            $grouping = $track_assignment->group;
            $grouping_formatter = new grouping_formatter(
                $grouping,
                $activity->get_context()
            );
            $size = $grouping_formatter->format('size', format::FORMAT_PLAIN);
            $extra = $grouping_formatter->format('extra', format::FORMAT_PLAIN);
            static::assertEquals($size, $assignees[$i]['group']['size']);
            static::assertEquals($extra, $assignees[$i]['group']['extra']);
            $i++;
        }
    }

    private function get_assignments(activity_model $activity): array {
        $default_track = $activity->get_default_track();
        return             [
            'activity_context_id' => $activity->get_context()->id,
            'draft' => $activity->is_draft(),
            'can_assign_positions' => $default_track->can_assign_positions,
            'can_assign_organisations' => $default_track->can_assign_organisations,
            'types' => static::get_types(),
            'assignees' => $this->get_assignees($activity, $default_track->assignments)
        ];
    }

    private function get_assignees(
        activity_model $activity,
        collection $track_assignments
    ): array {
        $assignees = [];
        $formatter = new string_field_formatter(format::FORMAT_PLAIN, $activity->get_context());
        foreach ($track_assignments as $track_assignment) {
            /** @var grouping $grouping */
            $grouping = $track_assignment->group;
            $grouping_formatter = new grouping_formatter(
                $grouping,
                $activity->get_context()
            );
            $assignees[] = [
                'type' => $track_assignment->type,
                'group' => [
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

    private static function get_types(): array {
        return [
            'aud' => grouping::COHORT,
            'org' => grouping::ORG,
            'pos' => grouping::POS,
            'ind' => grouping::USER
        ];
    }
}
