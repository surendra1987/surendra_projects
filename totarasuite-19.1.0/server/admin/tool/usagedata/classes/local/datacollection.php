<?php
/**
 * This file is part of Totara Talent Experience Platform
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
 * @author Aaron Machin <aaron.machin@totara.com>
 * @package tool_usagedata
 */

namespace tool_usagedata\local;

use stdClass;

final class datacollection implements \IteratorAggregate {

    /** @var data[] */
    private array $data = [];

    public function __construct(array $data) {
        $this->data = $data;
    }

    public function getIterator(): \ArrayIterator {
        return new \ArrayIterator($this->data);
    }

    public function to_hierarchical_array(): array {
        $return = [];
        foreach ($this->data as $data) {
            $component_type = $data->component_type;
            $component_name = $data->component_name;
            if (!isset($return[$component_type])) {
                $return[$component_type] = [
                    $component_name => []
                ];
            }
            if (!isset($return[$component_type][$component_name])) {
                $return[$component_type][$component_name] = [];
            }

            $return[$component_type][$component_name][$data->name] = $data->export();
            $error = $data->get_error();
            if (!empty($error)) {
                if (!isset($return['errors'])) {
                    $return['errors'] = [];
                }

                $return['errors'][] = $error;
            }
        }
        return $return;
    }

    public function to_informative_list_items(): array {
        $result = [];
        foreach ($this->data as $data) {
            $is_core = $data->component === 'core';
            $component_type = $data->component_type;
            $component_name = $data->component_name;
            $result = $this->setup_parent_entry($result, $component_type, $is_core, $data);
            $this->add_entry($is_core, $data, $result, $component_type, $component_name);
        }

        $return = [];
        foreach ($result as $component) {
            $component->children = array_values($component->children);
            $component->has_children = !empty($component->children);
            $return[] = $component;
        }

        return $return;
    }

    /**
     * @param array $result
     * @param mixed $component_type
     * @param bool $is_core
     * @param mixed $data
     * @return array
     * @throws \coding_exception
     */
    public function setup_parent_entry(array $result, mixed $component_type, bool $is_core, mixed $data): array {
        if (!isset($result[$component_type])) {
            if ($is_core) {
                $result[$data->component] = (object) [
                    'title' => 'Core',
                    'items' => []
                ];
            } else {
                $result[$component_type] = (object) [
                    'title' => get_string('type_' . $component_type . '_plural', 'plugin'),
                    'children' => []
                ];
            }
        }
        return $result;
    }

    /**
     * @param bool $is_core
     * @param mixed $data
     * @param array $result
     * @param mixed $component_type
     * @param mixed $component_name
     * @return void
     * @throws \coding_exception
     */
    public function add_entry(bool $is_core, mixed $data, array $result, mixed $component_type, mixed $component_name): void {
        if ($is_core) {
            $result['core']->items[] = $data->purpose();
        } else {
            if (!isset($result[$component_type]->children[$component_name])) {
                if ($component_type === 'core') {
                    if (get_string_manager()->string_exists('type_' . $component_name . '_plural', 'plugin')) {
                        $title = get_string('type_' . $component_name . '_plural', 'plugin');
                    } else {
                        $title = get_string($component_name, 'core');
                    }
                } else {
                    $title = get_string('pluginname', $data->component);
                }

                $obj = new stdClass;
                $obj->title = $title;
                $obj->items = [];
                $result[$component_type]->children[$component_name] = $obj;
            }
            $result[$component_type]->children[$component_name]->items[] = $data->purpose();
        }
    }

}