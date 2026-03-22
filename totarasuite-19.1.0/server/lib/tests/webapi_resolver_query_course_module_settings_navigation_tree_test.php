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
 * @package core
 */

use totara_webapi\phpunit\webapi_phpunit_helper;

global $CFG;

require_once($CFG->dirroot . '/lib/tests/webapi_resolver_query_course_settings_navigation_tree_test.php');

class core_webapi_resolver_query_course_module_settings_navigation_tree_test extends core_webapi_resolver_query_course_settings_navigation_tree_test {

    private const QUERY = 'core_course_module_settings_navigation_tree';

    public function test_resolve_tree_for_module_context(): void {
        self::setAdminUser();

        $generator = self::getDataGenerator();

        $course = $generator->create_course();
        $module = $generator->create_module('assign', ['course' => $course]);
        $module_context = context_module::instance($module->cmid);

        $expected_ids = [
            'modulesettings_modedit' => [],
            'modulesettings_roleassign' => [],
            'modulesettings_roleoverride' => [],
            'modulesettings_rolecheck' => [],
            'modulesettings_filtermanage' => [],
            'modulesettings_logreport' => [],
            'modulesettings_backup' => [],
            'modulesettings_restore' => [],
        ];

        $result = $this->resolve_graphql_query(
            self::QUERY,
            [
                'context_id' => $module_context->id,
                'page_url' => static::PAGE_URL,
            ]
        );
        $this->assert_tree_structure_same($expected_ids, $this->simplify_tree($result['trees']));
    }
}