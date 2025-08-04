<?php
/**
 * This file is part of Totara Learn
 *
 * Copyright (C) 2021 onwards Totara Learning Solutions LTD
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
 * @author Qingyang Liu <qingyang.liu@totaralearning.com>
 * @package core
 */

use core\format;
use core_phpunit\testcase;
use totara_webapi\phpunit\webapi_phpunit_helper;

class core_webapi_resolver_type_category_test extends testcase {
    use webapi_phpunit_helper;

    /**
     * @return void
     */
    public function test_category_type_name_with_exception(): void {
        $generator = $this->getDataGenerator();
        $user = $generator->create_user();
        // Login as user.
        $this->setUser($user);
        $cat = $generator->create_category();

        self::expectException(coding_exception::class);
        self::expectExceptionMessage('You are not allowed to view category name');
        $this->resolve_graphql_type(
            'core_category',
            'name',
            $cat,
            ['format' => format::FORMAT_RAW]
        );
    }

    /**
     * @return void
     */
    public function test_category_type_name_with_admin(): void {
        $this->setAdminUser();
        $generator = $this->getDataGenerator();
        $cat = $generator->create_category();
        self::assertEquals(
            $cat->name,
            $this->resolve_graphql_type(
                'core_category',
                'name',
                $cat,
                ['format' => format::FORMAT_RAW]
            )
        );
    }

    /**
     * @return void
     */
    public function test_category_full_path_with_multilang(): void {
        global $SESSION;

        $this->setAdminUser();

        // Turn on the multi-lang filter.
        filter_set_global_state('multilang', TEXTFILTER_ON);
        filter_set_applies_to_strings('multilang', 1);
        $filtermanager = filter_manager::instance();
        $filtermanager->reset_caches();

        // Some custom data set up.
        $gcat_name = '<span lang="en" class="multilang">Grand Category (en)</span>'
                   . '<span lang="fr" class="multilang">Grand Category (fr)</span>'
                   . '<span lang="ja" class="multilang">Grand Category (ja)</span>';
        $gcat = $this->getDataGenerator()->create_category([
            'name' => $gcat_name,
            'parent' => 0
        ]);

        $pcat_name = '<span lang="en" class="multilang">Parent Category (en)</span>'
                   . '<span lang="fr" class="multilang">Parent Category (fr)</span>'
                   . '<span lang="ja" class="multilang">Parent Category (ja)</span>';
        $pcat = $this->getDataGenerator()->create_category([
            'name' => $pcat_name,
            'parent' => $gcat->id
        ]);

        $ccat_name = '<span lang="en" class="multilang">Child Category (en)</span>'
                   . '<span lang="fr" class="multilang">Child Category (fr)</span>'
                   . '<span lang="ja" class="multilang">Child Category (ja)</span>';
        $ccat = $this->getDataGenerator()->create_category([
            'name' => $ccat_name,
            'parent' => $pcat->id
        ]);

        $course = $this->getDataGenerator()->create_course([
            'category' => $ccat->id,
            'shortname' => 'tstcrs',
            'fullname' => 'Test Course'
        ]);

        $SESSION->lang = 'en';
        $expected = "Grand Category (en)";
        self::assertEquals(
            $expected,
            $this->resolve_graphql_type(
                'core_category',
                'full_path',
                $gcat,
                ['format' => format::FORMAT_PLAIN]
            )
        );

        $SESSION->lang = 'ja';
        $expected = "Grand Category (ja) / Parent Category (ja)";
        self::assertEquals(
            $expected,
            $this->resolve_graphql_type(
                'core_category',
                'full_path',
                $pcat,
                ['format' => format::FORMAT_PLAIN]
            )
        );

        $SESSION->lang = 'fr';
        $expected = "Grand Category (fr) / Parent Category (fr) / Child Category (fr)";
        self::assertEquals(
            $expected,
            $this->resolve_graphql_type(
                'core_category',
                'full_path',
                $ccat,
                ['format' => format::FORMAT_PLAIN]
            )
        );
    }
}
