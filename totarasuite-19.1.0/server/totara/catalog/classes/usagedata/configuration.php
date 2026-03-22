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
 * @author Nathan Lewis <nathan.lewis@totara.com>
 * @package totara_catalog
 */

namespace totara_catalog\usagedata;

use tool_usagedata\export;
use totara_catalog\local\config;

class configuration implements export {

    /**
     * @inheritdoc
     */
    public function get_summary(): string {
        return get_string('usagedata_configuration', 'totara_catalog');
    }

    /**
     * @inheritDoc
     */
    public function get_type(): int {
        return export::TYPE_OBJECT;
    }

    /**
     * @inheritdoc
     */
    public function export(): array {
        $config = config::instance();

        $included_items = implode(',', $config->get_learning_types_in_catalog());

        $browse_menu = $config->get_value('browse_by');
        if ($browse_menu == 'custom') {
            $browse_menu .= ':' . $config->get_value('browse_by_custom');
        }

        $featured_enabled = $config->get_value('featured_learning_enabled');
        if ($featured_enabled) {
            $featured_source = $config->get_value('featured_learning_source') . ':' .
                $config->get_value('featured_learning_value');
        } else {
            $featured_source = 'disabled';
        }

        return [
            'included_items' => $included_items,
            'page_size' => (int)$config->get_value('items_per_load'),
            'browse_menu' => $browse_menu,
            'featured_source' => $featured_source,
            'details_content' => (bool)$config->get_value('details_content_enabled'),
            'image_displayed' => (bool)$config->get_value('image_enabled'),
            'filters' => implode(',', array_keys($config->get_value('filters'))),
            'search_fallback' => (int)$config->get_value('search_fallback'),
            'suggest_plugin' => $config->get_value('suggest_plugin'),
        ];
    }
}
