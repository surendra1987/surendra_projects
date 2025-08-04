<?php
/**
 * This file is part of Totara Core
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
 * @author Qingyang Liu <qingyang.liu@totara.com>
 * @package
 */

namespace tool_diagnostic\provider;

use cache_administration_helper;
use html_table;
use html_table_cell;
use html_table_row;
use html_writer;
use tool_diagnostic\content\content;

class cache_information extends base {

    /** @var int */
    protected static $order = 90;

    public static function get_id(): string {
        return 'cache_information';
    }

    public function get_content(): content {
        $content = new content();
        $content->set_id(static::get_id());
        $content->set_data($this->get_cache_information());
        $content->set_data_type(content::TYPE_HTML);

        return $content;
    }

    private function get_cache_information(): string {
        global $CFG;
        require_once $CFG->dirroot.'/cache/locallib.php';

        $stores = cache_administration_helper::get_store_instance_summaries();
        $definitions = cache_administration_helper::get_definition_summaries();

        $mappings = [];
        foreach ($definitions as $definition) {
            if (!empty($definition['mappings'])) {
                $mappings[] =  implode(', ', $definition['mappings']);
            }
        }
        $mappings = array_count_values($mappings);

        $table = new html_table();
        $table->head = array(
            get_string('storename', 'cache'),
            get_string('plugin', 'cache'),
            get_string('storeready', 'cache'),
            get_string('mappings', 'cache'),
            get_string('modes', 'cache'),
            get_string('supports', 'cache'),
            get_string('locking', 'cache'),
            get_string('countusage', 'tool_diagnostic')
        );

        $table->data = [];
        foreach ($stores as $store) {
            $modes = [];
            foreach ($store['modes'] as $mode => $enabled) {
                if ($enabled) {
                    $modes[] = get_string('mode_'.$mode, 'cache');
                }
            }

            $supports = [];
            foreach ($store['supports'] as $support => $enabled) {
                if ($enabled) {
                    $supports[] = get_string('supports_'.$support, 'cache');
                }
            }

            $isready = $store['isready'] && $store['requirementsmet'];
            $readycell = new html_table_cell;
            if ($isready) {
                $readycell->text = 1;
            }

            if (!$isready && (int)$store['mappings'] > 0) {
                $readycell->text = 0;
            }

            $store_name = $store['name'];
            if (!empty($store['default'])) {
                $store_name = get_string('store_'.$store['name'], 'cache');
            }

            $lock = $store['lock']['name'];
            if (!empty($store['lock']['default'])) {
                $lock = get_string($store['lock']['name'], 'cache');
            }

            $row = new html_table_row(array(
                $store_name,
                get_string('pluginname', 'cachestore_'.$store['plugin']),
                $readycell,
                $store['mappings'],
                implode(', ', $modes),
                implode(', ', $supports),
                $lock,
                $mappings[$store_name] ?? 0
            ));
            $table->data[] = $row;
        }

        return html_writer::table($table);
    }
}
