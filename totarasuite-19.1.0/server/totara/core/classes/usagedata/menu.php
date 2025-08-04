<?php
/**
 * This file is part of Totara Core
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
 * @author Qingyang Liu <qingyang.liu@totara.com>
 * @package totara_core
 */

namespace totara_core\usagedata;

use core\orm\query\builder;
use stdClass;
use tool_usagedata\export;
use totara_core\totara\menu\item;

class menu implements export {

    /**
     * @inheritDoc
     */
    public function get_summary(): string {
        return get_string('navigation_menu_summary', 'totara_core');
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
        $table = builder::table('totara_navigation');
        $rows = $table->fetch();

        $visibility_rules_items = 0;
        $max_visibility_rules = 0;
        $max_title_length = 0;
        $custom_icon_items = 0;
        $classnames = [];
        $unused = null;
        $findlearning = null;

        foreach ($rows as $row) {
            if (!empty($row->icon)) {
                $custom_icon_items++;
            }

            if ($row->classname === '\totara_core\totara\menu\unused') {
                $unused = $row;
            } else if ($row->classname === '\totara_coursecatalog\totara\menu\findlearning') {
                $findlearning = $row;
            }
        }

        foreach ($rows as $row) {
            $item = item::create_instance($row);
            if ($item->is_disabled() || !$item->get_visibility()) {
                continue;
            }

            // Skip unused.
            if ($unused && $item->get_parentid() == $unused->id) {
                continue;
            }

            // Skip legacy catalog and not enabled.
            if ($findlearning && $item->get_parentid() == $findlearning->id) {
                $parent_item = item::create_instance($findlearning);
                if ($parent_item->is_disabled()) {
                    continue;
                }
            }

            $classnames[] = $item->get_classname();
            if ($item->get_visibility() == $item::VISIBILITY_CUSTOM) {
                $visibility_rules_items++;
                $rule_length = builder::table('totara_navigation_settings')->where('itemid', $item->get_id())->count();
                if ($max_visibility_rules < $rule_length) {
                    $max_visibility_rules = $rule_length;
                }
            }

            $length = \core_text::strlen($item->get_title());
            // Only calculate active title length.
            if ($max_title_length < $length) {
                $max_title_length = $length;
            }
        }

        return [
            'total_menu_items' => count($rows),
            'visibility_rules_items' => $visibility_rules_items,
            "max_visibility_rules" => $max_visibility_rules,
            'custom_icon_items' => $custom_icon_items,
            'max_title_length' => $max_title_length,
            'active_classnames' => $classnames
        ];
    }
}