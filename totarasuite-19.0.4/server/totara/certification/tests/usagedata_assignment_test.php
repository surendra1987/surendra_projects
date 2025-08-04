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
 * @package totara_certification
 */

use core\orm\query\builder;
use core_phpunit\testcase;
use totara_program\assignments\assignments;
use totara_program\program;

class totara_certification_usagedata_assignment_test extends testcase {

    /**
     * @return void
     */
    public function test_export(): void {
        $generator = $this->getDataGenerator();
        $generator_program = $generator->get_plugin_generator('totara_program');
        $cert1 = $generator_program->create_certification();
        $cert2 = $generator_program->create_certification();
        $cert3 = $generator_program->create_certification();

        $p1 = $generator_program->create_program();
        $p2 = $generator_program->create_program();

        $user = $generator->create_user();
        $user1 = $generator->create_user();
        $user2 = $generator->create_user();
        $user3 = $generator->create_user();
        $user4 = $generator->create_user();
        $user5 = $generator->create_user();
        $user6 = $generator->create_user();

        $cohort = $generator->create_cohort();
        cohort_add_member($cohort->id, $user->id);
        cohort_add_member($cohort->id, $user1->id);
        cohort_add_member($cohort->id, $user2->id);
        cohort_add_member($cohort->id, $user3->id);

        $generator_program->assign_to_program($cert1->id, assignments::ASSIGNTYPE_INDIVIDUAL, $user4->id);
        $generator_program->assign_to_program($cert2->id, assignments::ASSIGNTYPE_COHORT, $cohort->id);
        $generator_program->assign_to_program($cert3->id, assignments::ASSIGNTYPE_INDIVIDUAL, $user5->id);
        $generator_program->assign_to_program($cert3->id, assignments::ASSIGNTYPE_INDIVIDUAL, $user6->id);

        $generator_program->assign_to_program($p1->id, assignments::ASSIGNTYPE_INDIVIDUAL, $user4->id);
        $generator_program->assign_to_program($p2->id, assignments::ASSIGNTYPE_INDIVIDUAL, $user4->id);

        $results = (new \totara_certification\usagedata\assignment())->export();
        $this->assertEquals(0, $results['total_learners']);
        $this->assertEquals(0, $results['count_learners_with_duedate_assignment']);
        $this->assertEquals(0, $results['count_fixed_due_date']);
        $this->assertEquals(0, $results['count_relative_due_date']);

        builder::table('prog_assignment')->where('programid', $cert1->id)
            ->update(['completionevent' => 6, 'completionoffsetamount' => 1, 'completionoffsetunit' => 1]);
        builder::table('prog_assignment')->where('programid', $cert2->id)
            ->update(['completiontime' => strtotime("+1 week")]);

        $program = new program($cert1->id);
        $program->update_learner_assignments(true);
        $program = new program($cert2->id);
        $program->update_learner_assignments(true);
        $program = new program($cert3->id);
        $program->update_learner_assignments(true);

        $results = (new \totara_certification\usagedata\assignment())->export();
        $this->assertEquals(7, $results['total_learners']);
        $this->assertEquals(5, $results['count_learners_with_duedate_assignment']);
        $this->assertEquals(1, $results['count_fixed_due_date']);
        $this->assertEquals(1, $results['count_relative_due_date']);
    }
}