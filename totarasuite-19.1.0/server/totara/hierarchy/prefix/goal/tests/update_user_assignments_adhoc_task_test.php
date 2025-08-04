<?php
/**
 * This file is part of Totara Perform
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
 * @author Scott Davies <scott.davies@totara.com>
 * @package hierarchy_goal
 */

use core\entity\user;
use core_phpunit\testcase;
use hierarchy_goal\task\update_user_assignments_adhoc_task;
use hierarchy_goal\entity\company_goal_assignment as goal_record_entity;
use totara_job\job_assignment;

class hierarchy_goal_update_user_assignments_adhoc_task_test extends testcase {

    public function test_execute_for_assigning_goal_to_audience(): void {
        self::setAdminUser();
        [$user1, $company_goal] = $this->helper_create_test_user_goal();
        // Create a test audience.
        $record = ['contextid' => \context_system::instance()->id,
            'name' => 'audience_' . uniqid(),
            'idnumber' => uniqid(),
            'description' => "Test cohort " . uniqid(),
            'descriptionformat' => FORMAT_MOODLE,
            'visible' => 1,
            'component' => ''
        ];
        $audience_id = cohort_add_cohort((object)$record);
        // Assign member(s) to the audience.
        cohort_add_member($audience_id, $user1->id);

        $assigntype = 2; // audience
        $assignto = $audience_id;
        $type = goal::goal_assignment_type_info($assigntype);
        $relationship = $this->helper_get_relationship($assignto, $company_goal->id, $assigntype);

        self::assertFalse($this->helper_is_task_queued());

        // Operate #1.
        $task = update_user_assignments_adhoc_task::enqueue($company_goal, $company_goal->id, $assigntype, $relationship, $type);
        // Assert #1.
        self::assertTrue($this->helper_is_task_queued());
        self::assertEquals(0, goal_record_entity::repository()->count());

        $event_sink = $this->redirectEvents();
        // Operate #2.
        $task->execute();
        // Assert #2.
        self::assertEquals(1, goal_record_entity::repository()->count());
        $events = $event_sink->get_events();
        self::assertCount(1, $events);
        $event = reset($events);
        self::assertInstanceOf(\hierarchy_goal\event\assignment_cohort_created::class, $event);
        $event_sink->clear();
    }

    public function test_execute_for_assigning_goal_to_position(): void {
        self::setAdminUser();
        [$user1, $company_goal] = $this->helper_create_test_user_goal();
        // Create a test position.
        $hierarchy_generator = $this->getDataGenerator()->get_plugin_generator('totara_hierarchy');
        $pos_fwk = $hierarchy_generator->create_framework('position');
        $position1 = $hierarchy_generator->create_hierarchy($pos_fwk->id, 'position');
        // Assign member(s) to the position.
        job_assignment::create([
            'userid' => $user1->id,
            'idnumber' => 'jaSalesTrainee',
            'fullname' => 'jaSalesTrainee',
            'positionid' => $position1->id
        ]);

        $assigntype = 3; // position
        $assignto = $position1->id;
        $type = goal::goal_assignment_type_info($assigntype);
        $relationship = $this->helper_get_relationship($assignto, $company_goal->id, $assigntype);

        $task = update_user_assignments_adhoc_task::enqueue($company_goal, $company_goal->id, $assigntype, $relationship, $type);
        self::assertEquals(0, goal_record_entity::repository()->count());

        $event_sink = $this->redirectEvents();
        // Operate.
        $task->execute();

        // Assert.
        self::assertEquals(1, goal_record_entity::repository()->count());
        $events = $event_sink->get_events();
        self::assertCount(1, $events);
        $event = reset($events);
        self::assertInstanceOf(\hierarchy_goal\event\assignment_position_created::class, $event);
        $event_sink->clear();
    }

    public function test_execute_for_assigning_goal_to_organisation(): void {
        self::setAdminUser();
        [$user1, $company_goal] = $this->helper_create_test_user_goal();
        // Create a test organisation.
        $hierarchy_generator = $this->getDataGenerator()->get_plugin_generator('totara_hierarchy');
        $org_fw = $hierarchy_generator->create_framework('organisation', ['idnumber' => 'ORGF1']);
        $org1 = $hierarchy_generator->create_hierarchy($org_fw->id, 'organisation', [
            'idnumber' => 'org1' . uniqid(),
            'fullname' => 'orgname1'
        ]);
        // Assign member(s) to the organisation.
        job_assignment::create([
            'userid' => $user1->id,
            'idnumber' => 'salesorg1',
            'fullname' => 'sales org 1',
            'organisationid' => $org1->id
        ]);

        $assigntype = 4; // organisation
        $assignto = $org1->id;
        $type = goal::goal_assignment_type_info($assigntype);
        $relationship = $this->helper_get_relationship($assignto, $company_goal->id, $assigntype);

        $task = update_user_assignments_adhoc_task::enqueue($company_goal, $company_goal->id, $assigntype, $relationship, $type);
        self::assertEquals(0, goal_record_entity::repository()->count());

        $event_sink = $this->redirectEvents();
        // Operate.
        $task->execute();

        // Assert.
        self::assertEquals(1, goal_record_entity::repository()->count());
        $events = $event_sink->get_events();
        self::assertCount(1, $events);
        $event = reset($events);
        self::assertInstanceOf(\hierarchy_goal\event\assignment_organisation_created::class, $event);
        $event_sink->clear();
    }

    /**
     * @param $assignto
     * @param $goalid
     * @param $assigntype
     * @return stdClass
     */
    private function helper_get_relationship($assignto, $goalid, $assigntype): stdClass {
        global $DB;
        $type = goal::goal_assignment_type_info($assigntype);
        $field = $type->field;

        // Add relationship.
        $relationship = new stdClass();
        $relationship->$field = $assignto;
        $relationship->assigntype = $assigntype;
        $relationship->goalid = $goalid;
        $relationship->timemodified = time();
        $relationship->usermodified = user::logged_in()->id;
        $relationship->includechildren = 0;
        $relationship->id = $DB->insert_record($type->table, $relationship);
        return $relationship;
    }

    /**
     * @return array
     */
    private function helper_create_test_user_goal(): array {
        $generator = self::getDataGenerator();
        $user1 = $generator->create_user();
        /** @var $hierarchy_generator */
        $hierarchy_generator = $generator->get_plugin_generator('totara_hierarchy');
        $framework = $hierarchy_generator->create_goal_frame(['name' => 'frame1' . uniqid()]);
        $company_goal = $hierarchy_generator->create_goal(
            ['fullname' => 'goal1' . uniqid() , 'frameworkid' => $framework->id, 'targetdate' => time()]
        );
        return [$user1, $company_goal];
    }

    /**
     * @return bool
     */
    public function helper_is_task_queued(): bool {
        global $DB;

        $class_filter = $DB->sql_like('classname', ':task_class');
        $sql = "
            SELECT 1
            FROM {task_adhoc}
            WHERE component = :component
            AND $class_filter
        ";
        $filters = [
            'component' => 'totara_hierarchy',
            'task_class' => '%update_user_assignments_adhoc_task'
        ];

        return $DB->record_exists_sql($sql, $filters);
    }
}
