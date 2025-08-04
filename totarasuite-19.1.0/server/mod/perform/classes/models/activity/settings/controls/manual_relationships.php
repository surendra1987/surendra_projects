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
use totara_core\entity\relationship;
use totara_core\relationship\relationship_provider;
use core\webapi\formatter\field\string_field_formatter;
use mod_perform\models\activity\activity_manual_relationship_selection;

/**
 * Class manual_relationships control
 *
 * @package mod_perform\models\activity\settings\control
 */
class manual_relationships extends control {

    /**
     * @inheritDoc
     */
    public function get(): array {
        $formatter = new string_field_formatter(format::FORMAT_PLAIN, $this->activity->get_context());
        return [
            'options' => static::get_selector_options($formatter),
            'relationships' => $this->get_relationships($formatter)
        ];
    }

    /**
     * @param string_field_formatter $formatter
     * @return array
     * @throws \coding_exception
     */
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

    /**
     * @param string_field_formatter $formatter
     * @return array
     * @throws \coding_exception
     */
    private function get_relationships(string_field_formatter $formatter): array {
        $manual_relationships = $this->activity->get_manual_relationships();
        $relationships = [];
        foreach ($manual_relationships as $activity_manual_relationship_selection) {
            /** @var activity_manual_relationship_selection $activity_manual_relationship_selection */
            $manual_relationship = $activity_manual_relationship_selection->manual_relationship;
            $selector_relationship = $activity_manual_relationship_selection->selector_relationship;
            $relationships[] = [
                'id' => $manual_relationship->get_id(),
                'name' => $formatter->format(
                    $manual_relationship->get_name_plural()
                ),
                'mutable' => $this->activity->is_draft(),
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
