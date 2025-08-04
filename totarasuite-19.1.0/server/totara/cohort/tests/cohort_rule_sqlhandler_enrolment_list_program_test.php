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
 * @author Qingyang Liu <nathan.lewis@totara.com>
 * @package totara_cohort
 */

use core_phpunit\testcase;
use totara_cohort\testing\generator as audience_generator;
use totara_program\assignments\assignments;
use totara_program\testing\generator as program_generator;

class totara_cohort_cohort_rule_sqlhandler_enrolment_list_program_test extends testcase {

    public function test_construct_sql_snippet_with_multiple_assignments(): void {
        global $DB;

        $generator = self::getDataGenerator();
        $user = $generator->create_user();

        $audience_generator = audience_generator::instance();
        $audience = $audience_generator->create_cohort(['name' => 'Audience 1']);
        $audience_generator->cohort_assign_users($audience->id, [$user->id]);

        $program_generator = program_generator::instance();
        $program = $program_generator->create_program();
        // The user is assigned in two ways: audience and individual.
        $program_generator->assign_to_program($program->id, assignments::ASSIGNTYPE_COHORT, $audience->id, null, true);
        $program_generator->assign_to_program($program->id, assignments::ASSIGNTYPE_INDIVIDUAL, $user->id, null, true);

        $enrolment_list_program_handler = new cohort_rule_sqlhandler_enrolment_list_program();
        $enrolment_list_program_handler->listofids = [$program->id];
        $enrolment_list_program_handler->operator = COHORT_RULE_ENROLMENT_OP_ALL;
        $sql_snippet = $enrolment_list_program_handler->get_sql_snippet();

        // Check that the user is included in the results.
        $sql = "SELECT u.id FROM {user} u WHERE " . $sql_snippet->sql;
        $results = $DB->get_records_sql($sql, $sql_snippet->params);
        self::assertCount(1, $results);
        $result = reset($results);
        self::assertEquals($user->id, $result->id);
    }
}