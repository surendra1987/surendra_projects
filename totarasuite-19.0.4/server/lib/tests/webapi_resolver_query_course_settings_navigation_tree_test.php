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

use core_phpunit\testcase;
use totara_core\tui\tree\tree_node;
use totara_webapi\phpunit\webapi_phpunit_helper;

class core_webapi_resolver_query_course_settings_navigation_tree_test extends testcase {

    use webapi_phpunit_helper;

    private const QUERY = 'core_course_settings_navigation_tree';

    protected const PAGE_URL = '/index.php?id=4';
    /**
     * @return void
     */
    public function test_resolve_tree_for_course_context(): void {
        $generator = self::getDataGenerator();
        $course = $generator->create_course();
        $course_context = context_course::instance($course->id);

        self::setAdminUser();

        $expected_ids = [
            'courseadmin_editsettings' => [],
            'courseadmin_turneditingonoff' => [],
            'courseadmin_users' => [
                'courseadmin_review' => [],
                'courseadmin_manageinstances' => null,
                'courseadmin_groups' => [],
                'courseadmin_override' => [
                    'courseadmin_permissions' => [],
                ],
                'courseadmin_otherusers' => [],
            ],
            'courseadmin_coursereports' => null,
            'courseadmin_grades' => [],
            'courseadmin_gradebooksetup' => [],
            'courseadmin_outcomes' => [],
            'courseadmin_coursebadges' => [
                'courseadmin_coursebadges' => [],
                'courseadmin_newbadge' => [],
            ],
            'courseadmin_backup' => [],
            'courseadmin_restore' => [],
            'courseadmin_import' => [],
            'courseadmin_reset' => [],
            'courseadmin_questionbank' => [
                'courseadmin_questions' => [],
                'courseadmin_categories' => [],
                'courseadmin_import' => [],
                'courseadmin_export' => [],
            ],
            'courseadmin_switchroleto' => null,
        ];

        $result = $this->resolve_graphql_query(
            self::QUERY,
            [
                'context_id' => $course_context->id,
                'page_url' => self::PAGE_URL,
            ]
        );

        $this->assert_tree_structure_same($expected_ids, $this->simplify_tree($result['trees']));
    }

    /**
     * Make sure that the resolved settings tree has the options that we would expect.
     *
     * @param array $expected
     * @param array $actual
     */
    protected function assert_tree_structure_same(array $expected, array $actual): void {
        foreach ($expected as $expected_id => $expected_children) {
            $this->assertArrayHasKey($expected_id, $actual, "Couldn't find a settings node with ID '$expected_id' in the tree.");
            if ($expected_children === null) {
                continue;
            }
            if (empty($expected_children)) {
                $this->assertEmpty($actual[$expected_id], "Expected settings node with ID '$expected_id' to not have any children");
            }

            $this->assert_tree_structure_same($expected_children, $actual[$expected_id]);
        }
    }

    /**
     * Convert tree nodes into a readable recursive structure of [ ID => child IDs]
     * This allows us to easily check that the correct settings nodes are present.
     *
     * @param tree_node[] $nodes
     * @param tree_node|null $parent
     * @return array
     */
    protected function simplify_tree(array $nodes, tree_node $parent = null): array {
        $ids = [];
        foreach ($nodes as $node) {
            $node_id = $node->get_id();
            if ($parent) {
                $node_id = str_replace($parent->get_id() . '/', '', $node_id);
            }

            $ids[$node_id] = $this->simplify_tree($node->get_children(), $node);
        }
        return $ids;
    }
}