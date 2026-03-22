<?php
/*
 * This file is part of Totara Learn
 *
 * Copyright (C) 2019 onwards Totara Learning Solutions LTD
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
 * @author Alastair Munro <alastair.munro@totaralearning.com>
 * @package totara_program
 */

use totara_program\assignment\base as assignment_base;
use totara_program\assignment\cohort as cohort_assignment;
use totara_program\assignments\assignments;
use totara_program\content\program_content;
use totara_program\task\user_assignments_task;
use totara_program\entity\program_assignment;

class totara_program_program_assignment_cohorts_test extends \core_phpunit\testcase {

    public function test_show_in_ui() {
        $assignment = "\\totara_program\assignment\cohort";
        self::assertTrue($assignment::show_in_ui());
    }

    public function test_can_be_updated() {
        $assignment = "\\totara_program\assignment\cohort";
        $generator = $this->getDataGenerator();
        $programgenerator = $generator->get_plugin_generator('totara_program');
        $program1 = $programgenerator->create_program();
        self::setAdminUser();
        self::assertTrue($assignment::can_be_updated($program1->id));

        self::setUser($generator->create_user());
        self::assertFalse($assignment::can_be_updated($program1->id));
    }

    public function test_get_user_count() {
        global $DB;

        $generator = $this->getDataGenerator();
        $programgenerator = $generator->get_plugin_generator('totara_program');

        $user1 = $generator->create_user();
        $user2 = $generator->create_user();
        $user3 = $generator->create_user();
        $user4 = $generator->create_user();

        $cohortgenerator = $this->getDataGenerator()->get_plugin_generator('totara_cohort');
        $audience1 = $this->getDataGenerator()->create_cohort(['name' => 'Audience 1']);
        $cohortgenerator->cohort_assign_users($audience1->id, [$user1->id, $user2->id, $user3->id]);

        $audience2 = $this->getDataGenerator()->create_cohort(['name' => 'Audience 2']);
        $cohortgenerator->cohort_assign_users($audience2->id, [$user1->id, $user4->id]);

        $program1 = $programgenerator->create_program();

        $programgenerator->assign_to_program($program1->id, assignments::ASSIGNTYPE_COHORT, $audience1->id);
        $programgenerator->assign_to_program($program1->id, assignments::ASSIGNTYPE_COHORT, $audience2->id);

        $assign1id = $DB->get_field('prog_assignment', 'id', ['programid' => $program1->id, 'assignmenttype' => 3, 'assignmenttypeid' => $audience1->id]);
        $assignment1 = cohort_assignment::create_from_id($assign1id);
        $assign2id = $DB->get_field('prog_assignment', 'id', ['programid' => $program1->id, 'assignmenttype' => 3, 'assignmenttypeid' => $audience2->id]);
        $assignment2 = cohort_assignment::create_from_id($assign2id);

        $this->assertEquals(3, $assignment1->get_user_count());
        $this->assertEquals(2, $assignment2->get_user_count());
    }

    public function test_get_name() {
        global $DB;

        $generator = $this->getDataGenerator();
        $programgenerator = $generator->get_plugin_generator('totara_program');

        $user1 = $generator->create_user();
        $user2 = $generator->create_user();
        $user3 = $generator->create_user();
        $user4 = $generator->create_user();

        $cohortgenerator = $this->getDataGenerator()->get_plugin_generator('totara_cohort');
        $audience1 = $this->getDataGenerator()->create_cohort(['name' => 'Audience 1']);
        $cohortgenerator->cohort_assign_users($audience1->id, [$user1->id, $user2->id, $user3->id]);

        $audience2 = $this->getDataGenerator()->create_cohort(['name' => 'Audience 2']);
        $cohortgenerator->cohort_assign_users($audience2->id, [$user1->id, $user4->id]);

        $program1 = $programgenerator->create_program();

        $programgenerator->assign_to_program($program1->id, assignments::ASSIGNTYPE_COHORT, $audience1->id);

        $assign1id = $DB->get_field('prog_assignment', 'id', ['programid' => $program1->id, 'assignmenttype' => 3, 'assignmenttypeid' => $audience1->id]);
        $assignment1 = cohort_assignment::create_from_id($assign1id);

        // Does the name match?
        $this->assertEquals($audience1->name, $assignment1->get_name());
    }

    /**
     * Test to see if user user_assignment records are created
     * correctly for new assignments
     */
    public function test_create_from_instance_id() {
        global $DB, $CFG;

        $this->setAdminUser();

        $generator = $this->getDataGenerator();
        $programgenerator = $generator->get_plugin_generator('totara_program');

        $user1 = $generator->create_user();
        $user2 = $generator->create_user();
        $user3 = $generator->create_user();
        $user4 = $generator->create_user();

        $cohortgenerator = $this->getDataGenerator()->get_plugin_generator('totara_cohort');
        $audience1 = $this->getDataGenerator()->create_cohort(['name' => 'Audience 1']);
        $cohortgenerator->cohort_assign_users($audience1->id, [$user1->id, $user2->id, $user3->id]);

        $program1 = $programgenerator->create_program();

        $cohorttypeid = 3;
        $assignment = assignment_base::create_from_instance_id($program1->id, $cohorttypeid, $audience1->id);
        $assignment->save();

        $this->assertInstanceOf('\totara_program\assignment\cohort', $assignment);

        $reflection = new ReflectionClass('\totara_program\assignment\cohort');
        $property = $reflection->getProperty('typeid');
        $property->setAccessible(true);
        $this->assertEquals(3, $property->getValue($assignment));

        $property = $reflection->getProperty('instanceid');
        $property->setAccessible(true);
        $this->assertEquals($audience1->id, $property->getValue($assignment));

        // Check all the correct records were created.
        $this->assertEquals(1, $DB->count_records('prog_assignment', ['programid' => $program1->id]));
        //$this->assertEquals(3, $DB->count_records('prog_user_assignment', ['programid' => $program1->id]));
        //$this->assertEquals(3, $DB->count_records('prog_completion', ['programid' => $program1->id]));
    }

    public function test_get_type() {
        global $DB;

        $generator = $this->getDataGenerator();
        $programgenerator = $generator->get_plugin_generator('totara_program');

        $user1 = $generator->create_user();
        $user2 = $generator->create_user();
        $user3 = $generator->create_user();

        $cohortgenerator = $this->getDataGenerator()->get_plugin_generator('totara_cohort');
        $audience1 = $this->getDataGenerator()->create_cohort(['name' => 'Audience 1']);
        $cohortgenerator->cohort_assign_users($audience1->id, [$user1->id, $user2->id, $user3->id]);

        $program1 = $programgenerator->create_program();

        $programgenerator->assign_to_program($program1->id, assignments::ASSIGNTYPE_COHORT, $audience1->id);

        $assignments = $DB->get_records('prog_assignment', ['programid' => $program1->id]);
        $record = reset($assignments);
        $assignment = cohort_assignment::create_from_id($record->id);
        $this->assertEquals(3, $assignment->get_type());
    }

    public function test_cohort_assignment_delete() {
        global $DB;

        $this->setAdminUser();

        $generator = $this->getDataGenerator();
        $programgenerator = $generator->get_plugin_generator('totara_program');

        $user1 = $generator->create_user();
        $user2 = $generator->create_user();
        $user3 = $generator->create_user();

        $cohortgenerator = $this->getDataGenerator()->get_plugin_generator('totara_cohort');
        $audience1 = $this->getDataGenerator()->create_cohort(['name' => 'Audience 1']);
        $cohortgenerator->cohort_assign_users($audience1->id, [$user1->id, $user2->id]);

        $program1 = $programgenerator->create_program();

        $programgenerator->assign_to_program($program1->id, assignments::ASSIGNTYPE_COHORT, $audience1->id);
        $programgenerator->assign_to_program($program1->id, assignments::ASSIGNTYPE_INDIVIDUAL, $user3->id);

        // Run cron
        $task = new user_assignments_task();
        $task->execute();

        $assignments = $DB->get_records('prog_assignment', ['programid' => $program1->id]);
        $this->assertCount(2, $assignments);
        $user_assignments = $DB->get_records('prog_user_assignment');
        $this->assertCount(3, $user_assignments);

        // Delete record manually to circumvent any clean up
        $DB->delete_records('cohort', ['id' => $audience1->id]);

        $audience_assignment = $DB->get_record('prog_assignment', ['assignmenttype' => assignments::ASSIGNTYPE_COHORT, 'assignmenttypeid' => $audience1->id]);
        $assignment = cohort_assignment::create_from_id($audience_assignment->id);
        $assignment->remove();

        $assignments = $DB->get_records('prog_assignment', ['programid' => $program1->id]);
        $this->assertCount(1, $assignments);
        $assignment = reset($assignments);
        $this->assertEquals(assignments::ASSIGNTYPE_INDIVIDUAL, $assignment->assignmenttype);
        $this->assertEquals($user3->id, $assignment->assignmenttypeid);

        $user_assignments = $DB->get_records('prog_user_assignment');
        $this->assertCount(1, $user_assignments);
        $user_assignment = reset($user_assignments);
        $this->assertEquals($user3->id, $user_assignment->userid);
        $this->assertEquals($assignment->id, $user_assignment->assignmentid);
    }

    public function test_program_assignment_cohort_deleted() {
        global $DB;

        $this->setAdminUser();

        // Generate program and cohort assignments
        $generator = $this->getDataGenerator();
        $programgenerator = $generator->get_plugin_generator('totara_program');
        $cohortgenerator = $this->getDataGenerator()->get_plugin_generator('totara_cohort');

        $user1 = $generator->create_user();
        $user2 = $generator->create_user();
        $user3 = $generator->create_user();
        $user4 = $generator->create_user();
        $user5 = $generator->create_user();

        $audience1 = $this->getDataGenerator()->create_cohort(['name' => 'Audience 1']);
        $audience2 = $this->getDataGenerator()->create_cohort(['name' => 'Audience 2']);

        $cohortgenerator->cohort_assign_users($audience1->id, [$user1->id, $user2->id]);
        $cohortgenerator->cohort_assign_users($audience2->id, [$user3->id, $user4->id]);

        $program1 = $programgenerator->create_program();

        $programgenerator->assign_to_program($program1->id, assignments::ASSIGNTYPE_COHORT, $audience1->id);
        $programgenerator->assign_to_program($program1->id, assignments::ASSIGNTYPE_COHORT, $audience2->id);
        $programgenerator->assign_to_program($program1->id, assignments::ASSIGNTYPE_INDIVIDUAL, $user5->id);

        // Run cron
        $task = new user_assignments_task();
        $task->execute();

        $assignments1 = $DB->get_records_menu('prog_assignment', ['programid' => $program1->id], '', 'assignmenttypeid, id');
        $user_assignments = $DB->get_records('prog_user_assignment', ['programid' => $program1->id]);
        $this->assertCount(3, $assignments1);
        $this->assertCount(5, $user_assignments);

        // Delete cohort using the cohort UI
        cohort_delete_cohort($audience1);

        // Check the correct records remain
        $assignments2 = $DB->get_records('prog_assignment', ['programid' => $program1->id]);
        $user_assignments = $DB->get_records('prog_user_assignment', ['programid' => $program1->id]);
        $this->assertCount(2, $assignments2);
        $this->assertCount(3, $user_assignments);

        $this->assertNotEmpty($assignments2[$assignments1[$audience2->id]]);
        $this->assertNotEmpty($assignments2[$assignments1[$user5->id]]);

        $audience_user_assignments = $DB->get_records('prog_user_assignment', ['assignmentid' => $assignments1[$audience1->id]]);
        $this->assertCount(0, $audience_user_assignments);
    }

    /**
     *
     */
    public function test_certification_duedate_fixed_date() {
        $this->setAdminUser();

        // Generate program and cohort assignments
        $generator = $this->getDataGenerator();
        $programgenerator = $generator->get_plugin_generator('totara_program');
        $cohortgenerator = $this->getDataGenerator()->get_plugin_generator('totara_cohort');

        $user1 = $generator->create_user();

        $audience1 = $this->getDataGenerator()->create_cohort(['name' => 'Audience 1']);
        $cohortgenerator->cohort_assign_users($audience1->id, [$user1->id]);

        $program = $programgenerator->create_certification(
            ['recertifydatetype' => 3, 'minimumactiveperiod' => '50 day', 'activeperiod' => '1 year' ]
        );
        $programgenerator->assign_to_program($program->id, assignments::ASSIGNTYPE_COHORT, $audience1->id);

        $program_assignment = program_assignment::repository()->where('programid', $program->id)->one();
        // Mock the past completiontime
        program_assignment::repository()
            ->where('programid', $program->id)
            ->update_record(['completiontime' => 1668742224, 'id' => $program_assignment->id]);
        certif_create_completion($program->id, $user1->id);
        $this->assertEquals('11/18/2022', date('m/d/Y', 1668742224));

        // Timedue is -1;
        $record = \totara_program\entity\program_completion::repository()
            ->where('programid', $program->id)
            ->one();
        $this->assertEquals(-1, $record->timedue);

        $program = new \totara_program\program($program->id);
        $program->update_learner_assignments(true);
        $record = \totara_program\entity\program_completion::repository()
            ->where('programid', $program->id)
            ->one();

        // If 18-November is past this year, then it's next year
        $expecteddue = strtotime(date('Y-m-d', strtotime(date('Y') . '-11-18')));
        if ($expecteddue < strtotime(date('Y-m-d'))) {
            $expecteddue = strtotime(date('Y-m-d', strtotime((date('Y') + 1) . '-11-18')));
        }
        $this->assertEquals(date('m/d/Y', $expecteddue), date('m/d/Y', $record->timedue));

        // Expected due date calculated here to ensure a slow test execution wouldn't skew the data.
        $expectedduedatecert = strtotime("+1 year 7 day");
        $expectedduedatecourse = strtotime("+30 day");
        $program1 = $programgenerator->create_certification(
            ['recertifydatetype' => 3, 'minimumactiveperiod' => '50 day', 'activeperiod' => '1 year' ]
        );
        $programgenerator->assign_to_program($program1->id, assignments::ASSIGNTYPE_COHORT, $audience1->id);

        $course = $generator->create_course();
        $coursesetdata = [
            [
                'type' => program_content::CONTENTTYPE_MULTICOURSE,
                'certifpath' => CERTIFPATH_CERT,
                'timeallowed' => 2592000, // 30 days
                'courses' => [$course]
            ],
        ];
        $programgenerator->legacy_add_coursesets_to_program($program1, $coursesetdata);
        $program_assignment = program_assignment::repository()->where('programid', $program1->id)->one();

        $time = strtotime("+7 day");
        program_assignment::repository()
            ->where('programid', $program1->id)
            ->update_record(['completiontime' => $time, 'id' => $program_assignment->id]);
        certif_create_completion($program1->id, $user1->id);

        $program = new \totara_program\program($program1->id);
        $program->update_learner_assignments(true);
        $record = \totara_program\entity\program_completion::repository()
            ->where('programid', $program->id)
            ->where('coursesetid', 0)
            ->one();

        // The completion due date for the certification must now be +1 year (certificate active period) +7 days.
        self::assertTrue($record->timedue >= $expectedduedatecert);

        $record = \totara_program\entity\program_completion::repository()
            ->where('programid', $program->id)
            ->where('coursesetid', '!=', 0)
            ->one();
        // The completion due date for the course set is +30 day
        self::assertTrue($record->timedue >= $expectedduedatecourse);
        self::assertEquals(0, \core\orm\query\builder::get_db()->count_records('prog_exception'));
    }
}
