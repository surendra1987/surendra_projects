<?php
/*
 * This file is part of Totara Learn
 *
 * Copyright (C) 2018 onwards Totara Learning Solutions LTD
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
 * @author Matthias Bonk <matthias.bonk@totaralearning.com>
 * @package totara_catalog
 */

function xmldb_totara_catalog_install() {
    global $CFG, $DB;

    // Set the new config variable controlling which catalog to display.
    if (empty($CFG->catalogtype)) {
        $catalogtype = 'explore';
        if (isset($CFG->enhancedcatalog)) {
            $previous_setting = (string)$CFG->enhancedcatalog;
            if ($previous_setting === '1') {
                $catalogtype = 'enhanced';
            } else if ($previous_setting === '0') {
                $catalogtype = 'moodle';
            }
        }

        set_config('catalogtype', $catalogtype);
    }

    // Fire an adhoc task to populate the catalog - will happen first time cron runs.
    $adhoctask = new \totara_catalog\task\refresh_catalog_adhoc();
    $adhoctask->set_component('totara_catalog');
    core\task\manager::queue_adhoc_task($adhoctask);

    // Add catalogue block to explore catalog page
    if (
        core_plugin_manager::instance()->get_plugin_info('block_totara_catalog') &&
        !$DB->record_exists('block_instances', ['blockname' => 'totara_catalog', 'pagetypepattern' => 'totara-catalog-explore', 'defaultregion' => 'bottom'])
    ) {
        $page = new moodle_page();
        $page->set_context(context_system::instance());

        $block_config = new stdClass();
        $block_config->catalogurl = '/totara/catalog/explore.php?orderbykey=time';
        $block_config->collectiontitle = get_string('sort_time', 'totara_catalog');
        $block_config->numberofitems = 10;

        $block_manager = new block_manager($page);
        $block_manager->add_region('bottom');
        $block_manager->add_block('totara_catalog', 'bottom', 0, true, 'totara-catalog-explore', null, $block_config);
    }
}
