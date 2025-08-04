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
use core_phpunit\testcase;
use core\webapi\formatter\field\string_field_formatter;
use totara_core\entity\relationship;
use totara_core\relationship\relationship_provider;
use mod_perform\models\activity\activity;
use mod_perform\testing\activity_controls_trait;
use mod_perform\models\activity\settings\controls\control_manager;
use mod_perform\models\activity\activity_manual_relationship_selection;

class mod_perform_activity_controls_manual_relationships_test extends testcase {
    use activity_controls_trait;

    public function test_manual_relationships_control_structure(): void {
        $activity = $this->create_activity();
        $formatter = new string_field_formatter(format::FORMAT_PLAIN, $activity->get_context());
        $controls = (new control_manager($activity->id))->get_controls(['manual_relationships']);
        $data = $controls['manual_relationships'];

        static::assertEquals(
            [
                'options' => static::get_selector_options($formatter),
                'relationships' => $this->get_relationships($activity, $formatter)
            ],
            $data
        );
    }

    public function test_manual_relationships_control_options(): void {
        $activity = $this->create_activity();
        $formatter = new string_field_formatter(format::FORMAT_PLAIN, $activity->get_context());
        $controls = (new control_manager($activity->id))->get_controls(['manual_relationships']);
        $data = $controls['manual_relationships'];

        static::assertEquals(
            static::get_selector_options($formatter),
            $data['options']
        );
        static::assertEqualsCanonicalizing(
            ['Subjects', 'Managers', 'Manager\'s managers', 'Appraisers', 'Direct reports'],
            array_map(static fn ($option) => $option['label'], $data['options'])
        );
    }

    public function test_manual_relationships_control_relationships(): void {
        $activity = $this->create_activity();
        $formatter = new string_field_formatter(format::FORMAT_PLAIN, $activity->get_context());
        $controls = (new control_manager($activity->id))->get_controls(['manual_relationships']);
        $data = $controls['manual_relationships'];

        static::assertEquals(
            $this->get_relationships($activity, $formatter),
            $data['relationships']
        );
        static::assertEqualsCanonicalizing(
            ['Reviewers', 'Peers', 'Mentors', 'External respondents'],
            array_map(static fn ($relationship) => $relationship['name'], $data['relationships'])
        );
        static::assertEqualsCanonicalizing(
            array_fill(0, 4, 'Subjects'),
            array_map(static fn ($selector) => $selector['selector']['name'], $data['relationships'])
        );
    }

    private static function get_selector_options(string_field_formatter $formatter): array {
        $selector_options = (new relationship_provider())
            ->filter_by_type(relationship::TYPE_STANDARD)
            ->get();
        $options = [];
        foreach ($selector_options as $selector_option) {
            $options[] = [
                'id' => $selector_option->get_id(),
                'label' => $formatter->format(
                    $selector_option->get_name_plural()
                )
            ];
        }
        return $options;
    }

    private function get_relationships(activity $activity, string_field_formatter $formatter): array {
        $manual_relationships = $activity->get_manual_relationships();
        $relationships = [];
        foreach ($manual_relationships as $activity_manual_relationship_selection) {
            /** @var activity_manual_relationship_selection $manual_relation_selection */
            $manual_relationship = $activity_manual_relationship_selection->manual_relationship;
            $selector_relationship = $activity_manual_relationship_selection->selector_relationship;
            $relationships[] = [
                'id' => $manual_relationship->get_id(),
                'name' => $formatter->format(
                    $manual_relationship->get_name_plural()
                ),
                'mutable' => $activity->is_draft(),
                'selector' => [
                    'id' => $selector_relationship->get_id(),
                    'name' => $formatter->format(
                        $selector_relationship->get_name_plural()
                    )
                ]
            ];
        }
        return $relationships;
    }
}
