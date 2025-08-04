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
 * @author Oleg Demeshev <oleg.demeshev@totara.com>
 * @package mod_perform
 */

use core\format;
use core_phpunit\testcase;
use totara_webapi\phpunit\webapi_phpunit_helper;
use mod_perform\testing\generator as perform_generator;
use mod_perform\dates\resolvers\dynamic\user_custom_field;

/**
 * @coversDefaultClass \mod_perform\webapi\resolver\type\dynamic_date_source
 *
 * @group perform
 */
class mod_perform_webapi_resolver_type_dynamic_date_source_test extends testcase {

    use webapi_phpunit_helper;
    private const QUERY_TYPE = 'mod_perform_dynamic_date_source';

    public function test_name_format_successful(): void {
        [ , $context] = $this->generate_test_data();
        $custom_field_date_resolver = new user_custom_field();
        $result = $custom_field_date_resolver->get_options();

        $this->assertEquals(
            'Date last activity',
            $this->resolve_graphql_type(
                self::QUERY_TYPE,
                'display_name',
                $result->first(),
                ['format' => format::FORMAT_PLAIN],
                $context
            )
        );
    }

    private function generate_test_data(): array {
        global $DB;
        static::setAdminUser();
        $DB->delete_records('user_info_field');
        // Enable the multilang filter and set it to apply to headings and content.
        filter_set_global_state('multilang', TEXTFILTER_ON);
        filter_set_applies_to_strings('multilang', true);
        filter_manager::reset_caches();

        $data_generator = static::getDataGenerator();
        $data = [];
        $data['user'] = $data_generator->create_user();
        $data['datetime']  = $DB->insert_record(
            'user_info_field',
            (object)[
                'shortname' => 'datetime',
                'name' => '<span lang="en" class="multilang">Date last activity</span><span lang="es" class="multilang">Fecha de última actividad</span>',
                'categoryid' => 1,
                'datatype' => 'datetime'
            ]
        );
        $DB->insert_record(
            'user_info_data',
            (object)[
                'userid' => $data['user']->id,
                'fieldid' => $data['datetime'],
                'data' => time()
            ]
        );

        $generator = perform_generator::instance();
        $activity = $generator->create_activity_in_container();
        $context = $activity->get_context();

        return [$data, $context];
    }
}
