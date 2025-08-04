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
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @author  Oleg Demeshev <oleg.demeshev@totara.com>
 * @package perform_goal
 */

namespace perform_goal\testing;

use coding_exception;
use core\json_editor\helper\document_helper;
use core\json_editor\node\paragraph;
use core\json_editor\node\text;

/**
 * This class holds the configuration for creating goals and related objects.
 */
class goal_task_generator_config {

    /** @var int */
    public $goal_id;

    /** @var string|null */
    public $description;

    /** @var string */
    private const task_description = 'Check <a href="https://example.com?id=123">My course</a> for more information';

    /** @var int|null */
    public $resource_id;

    /** @var int|null */
    public $resource_type;

    /** @var int|null */
    public $completed_at;

    /** @var int */
    public $created_at;

    /** @var int */
    public $updated_at;

    /**
     * Use static::new(array $override_defaults) to construct an instance.
     */
    private function __construct() {
        // Placeholder
    }

    /**
     * Create a new configuration instance with a new generated goal.
     *
     * @param array $override_defaults
     * @return static
     */
    public static function new(array $override_defaults = []): self {
        $goal_task_config_data = new self();

        if (empty($override_defaults['goal_id'])) {
            throw new coding_exception('goal id must exist for behat test');
        }

        $goal_task_config_data->description = self::jsondoc_description(self::task_description);

        // Override property values when passed in.
        foreach ($override_defaults as $property => $value) {
            if (!property_exists($goal_task_config_data, $property)) {
                throw new coding_exception('Unknown property: ' . $property);
            }
            $goal_task_config_data->{$property} = (
            $property == 'description'
                ? self::jsondoc_description($value)
                : $value
            );
        }

        return $goal_task_config_data;
    }

    /**
     * Generates a jsondoc goal description.
     *
     * @param string $descr the 'goal' description
     *
     * @return string the jsondoc
     */
    final protected static function jsondoc_description(string $description): string {
        return document_helper::json_encode_document(
            document_helper::create_document_from_content_nodes([
                paragraph::create_json_node_with_content_nodes([
                    text::create_json_node_from_text($description)
                ])
            ])
        );
    }
}