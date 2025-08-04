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
 * @author Matthias Bonk <matthias.bonk@totara.com>
 * @package mod_perform
 */

use core\orm\query\builder;
use core_phpunit\testcase;
use mod_perform\rb\util;
use mod_perform\testing\generator as perform_generator;
use totara_job\job_assignment;

/**
 * @group perform
 */
class mod_perform_rb_util_test extends testcase {

    use totara_reportbuilder\phpunit\report_testing;

    public function test_get_report_on_subjects_activities_sql_for_report_on_staff_responses(): void {
        self::setAdminUser();
        $manager = self::getDataGenerator()->create_user();
        $subject_user1 = self::getDataGenerator()->create_user();
        $subject_user2 = self::getDataGenerator()->create_user();

        // Only assign subject user 1 to the manager.
        /** @var job_assignment $manager_ja */
        $manager_ja = job_assignment::create_default($manager->id);
        job_assignment::create_default($subject_user1->id, ['managerjaid' => $manager_ja->id]);

        $perform_generator = perform_generator::instance();

        $activity1 = $perform_generator->create_activity_in_container();
        $activity2 = $perform_generator->create_activity_in_container();
        $perform_generator->create_subject_instance([
            'activity_id' => $activity1->id,
            'subject_user_id' => $subject_user1->id,
        ]);
        $perform_generator->create_subject_instance([
            'activity_id' => $activity2->id,
            'subject_user_id' => $subject_user2->id,
        ]);

        [$sql_where, $params] = util::get_report_on_subjects_activities_sql($manager->id, 'act.id');

        $activities = builder::table('perform')
            ->as('act')
            ->where_raw($sql_where, $params)
            ->get();

        // By default, the manager has no capability to report on any subjects.
        self::assertEquals(0, $activities->count());

        // Assign 'report_on_staff_responses' to the user role.
        $user_role = builder::table('role')
            ->where('shortname','user')
            ->one(true);
        assign_capability('mod/perform:report_on_staff_responses', CAP_ALLOW, $user_role->id, context_system::instance()->id);

        [$sql_where, $params] = util::get_report_on_subjects_activities_sql($manager->id, 'act.id');

        $activities = builder::table('perform')
            ->as('act')
            ->where_raw($sql_where, $params)
            ->get();
        self::assertEquals(1, $activities->count());
        self::assertEquals($activity1->id, $activities->first()->id);

        // Add the other subject user as a direct report as well.
        job_assignment::create_default($subject_user2->id, ['managerjaid' => $manager_ja->id]);

        [$sql_where, $params] = util::get_report_on_subjects_activities_sql($manager->id, 'act.id');

        $activities = builder::table('perform')
            ->as('act')
            ->where_raw($sql_where, $params)
            ->get();
        self::assertEquals(2, $activities->count());
        self::assertEqualsCanonicalizing([$activity1->id, $activity2->id], $activities->pluck('id'));
    }

    public function test_get_manage_participation_sql_for_manage_staff_participation(): void {
        self::setAdminUser();
        $manager = self::getDataGenerator()->create_user();
        $subject_user1 = self::getDataGenerator()->create_user();
        $subject_user2 = self::getDataGenerator()->create_user();

        // Only assign subject user 1 to the manager.
        /** @var job_assignment $manager_ja */
        $manager_ja = job_assignment::create_default($manager->id);
        job_assignment::create_default($subject_user1->id, ['managerjaid' => $manager_ja->id]);

        [$sql_where, $params] = util::get_manage_participation_sql($manager->id, 'usr.id');

        $users = builder::table('user')
            ->as('usr')
            ->where_raw($sql_where, $params)
            ->get();

        // By default, the manager has the capability to manage direct staff.
        self::assertEquals(1, $users->count());
        self::assertEquals($subject_user1->id, $users->first()->id);

        // Add the other subject user as a direct report as well.
        job_assignment::create_default($subject_user2->id, ['managerjaid' => $manager_ja->id]);

        [$sql_where, $params] = util::get_manage_participation_sql($manager->id, 'usr.id');

        $users = builder::table('user')
          ->as('usr')
          ->where_raw($sql_where, $params)
          ->get();
        self::assertEquals(2, $users->count());
        self::assertEqualsCanonicalizing([$subject_user1->id, $subject_user2->id], $users->pluck('id'));
    }

    public function test_get_report_on_subjects_sql_for_report_on_staff_responses(): void {
        self::setAdminUser();
        $manager = self::getDataGenerator()->create_user();
        $subject_user1 = self::getDataGenerator()->create_user();
        $subject_user2 = self::getDataGenerator()->create_user();

        // Only assign subject user 1 to the manager.
        /** @var job_assignment $manager_ja */
        $manager_ja = job_assignment::create_default($manager->id);
        job_assignment::create_default($subject_user1->id, ['managerjaid' => $manager_ja->id]);

        [$sql_where, $params] = util::get_report_on_subjects_sql($manager->id, 'usr.id');

        $users = builder::table('user')
            ->as('usr')
            ->where_raw($sql_where, $params)
            ->get();

        // By default, the manager has no capability to report on any subjects.
        self::assertEquals(0, $users->count());

        // Assign 'report_on_staff_responses' to the user role.
        $user_role = builder::table('role')
            ->where('shortname','user')
            ->one(true);
        assign_capability('mod/perform:report_on_staff_responses', CAP_ALLOW, $user_role->id, context_system::instance()->id);

        [$sql_where, $params] = util::get_report_on_subjects_sql($manager->id, 'usr.id');

        $users = builder::table('user')
            ->as('usr')
            ->where_raw($sql_where, $params)
            ->get();
        self::assertEquals(1, $users->count());
        self::assertEquals($subject_user1->id, $users->first()->id);

        // Add the other subject user as a direct report as well.
        job_assignment::create_default($subject_user2->id, ['managerjaid' => $manager_ja->id]);

        [$sql_where, $params] = util::get_report_on_subjects_sql($manager->id, 'usr.id');

        $users = builder::table('user')
            ->as('usr')
            ->where_raw($sql_where, $params)
            ->get();
        self::assertEquals(2, $users->count());
        self::assertEqualsCanonicalizing([$subject_user1->id, $subject_user2->id], $users->pluck('id'));
    }

    /**
     * Regression test for a particular bug that was caused by out-of-order array keys.
     *
     * @return void
     */
    public function test_export_for_props_has_consecutive_array_keys(): void {
        self::setAdminUser();
        $rid = $this->create_report('perform_response_base', 'Test response report');
        $config = (new rb_config());
        $report = reportbuilder::create($rid, $config);

        $export_for_props = util::export_for_props($report);
        self::assertSame([0, 1], array_keys($export_for_props));

        // Add CSV for Excel to the configured options - this resulted in a bug.
        set_config('exportoptions', 'csv,csv_excel,excel', 'reportbuilder');
        $export_for_props = util::export_for_props($report);
        self::assertSame([0, 1], array_keys($export_for_props));
    }
}
