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
 * @author Matthias Bonk <matthias.bonk@totaralearning.com>
 * @package totara_hierarchy
 */

use core\testing\component_generator;
use core_phpunit\testcase;
use hierarchy_goal\entity\company_goal;
use hierarchy_goal\entity\company_goal_assignment_type_extended;
use hierarchy_goal\entity\goal_item_target_date_history;
use hierarchy_goal\entity\personal_goal;
use totara_hierarchy\testing\generator as hierarchy_generator;

global $CFG;
require_once($CFG->dirroot . '/totara/hierarchy/prefix/goal/lib.php');
require_once($CFG->dirroot . '/totara/hierarchy/db/upgradelib.php');

/**
 * @group hierarchy_goal
 */
class totara_hierarchy_upgradelib_test extends testcase {

    public function test_init_goal_target_date_history(): void {
        self::setAdminUser();
        $now = time();

        // Create personal goals without target date history.
        $personal_goals = [];
        $personal_goals[] = $this->create_personal_goal(1, $now);
        $personal_goals[] = $this->create_personal_goal(2, 0);
        $personal_goals[] = $this->create_personal_goal(3, null);

        // Create company goals without target date history.
        $company_goals = [];
        $company_goals[] = $this->create_company_goal(1, $now);
        $company_goals[] = $this->create_company_goal(2, 0);
        $company_goals[] = $this->create_company_goal(3, null);

        self::assertEquals(0, goal_item_target_date_history::repository()->count());

        totara_hierarchy_upgrade_init_goal_target_date_history();

        self::assertEquals(6, goal_item_target_date_history::repository()->count());

        foreach ($personal_goals as $personal_goal) {
            self::assertTrue(
                goal_item_target_date_history::repository()
                    ->where('scope', goal::SCOPE_PERSONAL)
                    ->where('itemid', $personal_goal->id)
                    ->where('targetdate', $personal_goal->targetdate)
                    ->where('timemodified', $personal_goal->timecreated)
                    ->where('usermodified', $personal_goal->usermodified)
                    ->exists()
            );
        }
        foreach ($company_goals as $company_goal) {
            self::assertTrue(
                goal_item_target_date_history::repository()
                    ->where('scope', goal::SCOPE_COMPANY)
                    ->where('itemid', $company_goal->id)
                    ->where('targetdate', $company_goal->targetdate)
                    ->where('timemodified', $company_goal->timecreated)
                    ->where('usermodified', $company_goal->usermodified)
                    ->exists()
            );
        }

        // Repeated calls should not create additional records.
        $max_id = goal_item_target_date_history::repository()->order_by('id', 'DESC')->first()->id;
        self::assertNotEmpty($max_id);
        totara_hierarchy_upgrade_init_goal_target_date_history();
        self::assertEquals(6, goal_item_target_date_history::repository()->count());
        self::assertEquals($max_id, goal_item_target_date_history::repository()->order_by('id', 'DESC')->first()->id);
    }

    /**
     * @return void
     * @throws dml_exception
     */
    public function test_createdtime_migration_function_table():void {
        $now = time();

        $entity1 = new company_goal_assignment_type_extended();
        $entity1->assigntype = 1;
        $entity1->assignmentid = 0;
        $entity1->goalid = 1;
        $entity1->userid = 2;
        $entity1->usermodified = 2;
        $entity1->timecreated = 0;
        $entity1->timemodified = $now - 111;
        $entity1->save();

        $entity2 = new company_goal_assignment_type_extended();
        $entity2->assigntype = 1;
        $entity2->assignmentid = 0;
        $entity2->goalid = 2;
        $entity2->userid = 3;
        $entity2->usermodified = 3;
        $entity2->timecreated = 0;
        $entity2->timemodified = $now - 222;
        $entity2->save();

        totara_hierarchy_upgrade_add_created_time_from_timemodified();

        $entity1->refresh();
        $entity2->refresh();
        $this->assertEquals($now - 111, $entity1->timecreated);
        $this->assertEquals($now - 111, $entity1->timemodified);
        $this->assertEquals($now - 222, $entity2->timecreated);
        $this->assertEquals($now - 222, $entity2->timemodified);

        // Make sure that repeated calls don't update already updated records.
        $entity1->timemodified = $now - 88;
        $entity1->save();

        $entity2->timemodified = $now - 99;
        $entity2->save();

        totara_hierarchy_upgrade_add_created_time_from_timemodified();

        $entity1->refresh();
        $entity2->refresh();
        $this->assertEquals($now - 111, $entity1->timecreated);
        $this->assertEquals($now - 88, $entity1->timemodified);
        $this->assertEquals($now - 222, $entity2->timecreated);
        $this->assertEquals($now - 99, $entity2->timemodified);
    }

    /**
     * @param int $unique_int
     * @param int|null $target_date
     * @return stdClass
     */
    private function create_personal_goal(int $unique_int, ?int $target_date): stdClass {
        $user_id = self::getDataGenerator()->create_user()->id;
        $personal_goal = self::hierarchy_generator()->create_personal_goal(
            $user_id,
            ['name' => 'goal_' . $unique_int, 'targetdate' => $target_date]
        );
        // Update timecreated and usermodified so all records have different values.
        $goal_entity = new personal_goal($personal_goal->id);
        $goal_entity->timecreated = time() - ($unique_int * DAYSECS);
        $goal_entity->usermodified = $user_id;
        $goal_entity->save();

        $personal_goal->timecreated = $goal_entity->timecreated;
        $personal_goal->usermodified = $goal_entity->usermodified;
        return $personal_goal;
    }

    /**
     * @param int $unique_int
     * @param int|null $target_date
     * @return stdClass
     */
    private function create_company_goal(int $unique_int, ?int $target_date): stdClass {
        $user_id = self::getDataGenerator()->create_user()->id;
        $framework = self::hierarchy_generator()->create_goal_frame(['name' => 'framework_' . $unique_int]);
        $company_goal = self::hierarchy_generator()->create_goal(
            ['fullname' => 'goal_' . $unique_int, 'frameworkid' => $framework->id, 'targetdate' => $target_date],
            false
        );

        // Update timecreated and usermodified so all records have different values.
        $goal_entity = new company_goal($company_goal->id);
        $goal_entity->timecreated = time() - ($unique_int * DAYSECS);
        $goal_entity->usermodified = $user_id;
        $goal_entity->save();

        $company_goal->timecreated = $goal_entity->timecreated;
        $company_goal->usermodified = $goal_entity->usermodified;
        return $company_goal;
    }

    /**
     * @return hierarchy_generator|component_generator
     * @throws coding_exception
     */
    private static function hierarchy_generator(): hierarchy_generator {
        return self::getDataGenerator()->get_plugin_generator('totara_hierarchy');
    }
}