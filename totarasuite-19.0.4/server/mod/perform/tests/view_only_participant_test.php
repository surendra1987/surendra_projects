<?php
/**
 * This file is part of Totara Learn
 *
 * Copyright (C) 2022 onwards Totara Learning Solutions LTD
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
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 *
 * @author Murali Nair <murali.nair@totara.com>
 * @package mod_perform
 */

use mod_perform\constants;
use mod_perform\entity\activity\participant_instance;
use mod_perform\entity\activity\subject_instance;
use mod_perform\state\participant_instance\availability_not_applicable;
use mod_perform\task\sync_participant_instances_task;
use totara_core\entity\relationship as relationship_entity;
use totara_job\job_assignment;
use totara_job\entity\job_assignment as job_assignment_entity;

require_once(__DIR__ . '/participant_instance_sync_testcase.php');

/**
 * @group perform
 */
class mod_perform_view_only_participant_test extends mod_perform_participant_instance_sync_testcase {
    // Cache of relationship idnumbers to record ids.
    private static $relationships = [];

    /**
     * Data provider for test_view_only_user_change
     */
    public static function td_view_only_user_change(): array {
        $initial = [
            constants::RELATIONSHIP_MANAGER,
            constants::RELATIONSHIP_MANAGERS_MANAGER,
            constants::RELATIONSHIP_APPRAISER,
            constants::RELATIONSHIP_DIRECT_REPORT
        ];

        return [
            'change manager, manager manager' => [$initial, 'new_manager_and_managers_manager'],
            'change manager manager' => [$initial, 'new_managers_manager'],
            'change manager, old manager manager' => [$initial, 'new_manager_keep_old_managers_manager'],
            'change appraiser' => [$initial, 'new_appraiser'],
            'change direct report' => [$initial, 'new_direct_report'],
            'change manager, manager manager, appraiser' => [$initial, 'new_manager_managers_manager_and_appraiser']
        ];
    }

    /**
     * @return int
     */
    protected static function create_new_user_id(): int {
        return static::getDataGenerator()->create_user()->id;
    }

    /**
     * @param int $user_id
     * @return job_assignment
     * @throws coding_exception
     */
    protected static function get_manager_job_assignment(int $user_id): job_assignment {
        $mgr_jaid = job_assignment_entity::repository()
            ->where('userid', $user_id)
            ->one(true)
            ->managerjaid;

        return job_assignment::get_with_id($mgr_jaid);
    }

    /**
     * @param int $subject
     * @return int[]
     */
    protected static function new_manager_and_managers_manager(int $subject): array {
        $mgr = static::create_new_user_id();
        static::replace_manager($subject, $mgr);

        $mgr2 = static::create_new_user_id();
        static::replace_manager($mgr, $mgr2);

        return [
            constants::RELATIONSHIP_MANAGER => $mgr,
            constants::RELATIONSHIP_MANAGERS_MANAGER => $mgr2
        ];
    }

    /**
     * @param int $subject
     * @return int[]
     * @throws coding_exception
     */
    protected static function new_managers_manager(int $subject): array {
        $mgr2 = static::create_new_user_id();
        $mgr2_ja = job_assignment::create_default($mgr2);

        static::get_manager_job_assignment($subject)->update(['managerjaid' => $mgr2_ja->id]);

        return [constants::RELATIONSHIP_MANAGERS_MANAGER => $mgr2];
    }

    /**
     * @param int $subject
     * @return int[]
     * @throws coding_exception
     */
    protected static function new_manager_keep_old_managers_manager(int $subject): array {
        $mgr2_jaid = static::get_manager_job_assignment($subject)->managerjaid;

        $mgr = static::create_new_user_id();
        static::replace_manager($subject, $mgr);

        static::get_manager_job_assignment($subject)->update(['managerjaid' => $mgr2_jaid]);

        return [constants::RELATIONSHIP_MANAGER => $mgr];
    }

    /**
     * @param int $subject
     * @return int[]
     */
    protected static function new_appraiser(int $subject): array {
        $user = static::create_new_user_id();
        static::replace_appraiser($subject, $user);
        return [constants::RELATIONSHIP_APPRAISER => $user];
    }

    /**
     * @param int $subject
     * @return array
     * @throws coding_exception
     */
    protected static function new_direct_report(int $subject): array {
        foreach (job_assignment::get_staff($subject) as $ja) {
            $ja->update(['managerjaid' => null]);
        }

        [$direct_report, ] = static::add_direct_report_for_user($subject);
        return [constants::RELATIONSHIP_DIRECT_REPORT => $direct_report->id];
    }

    /**
     * @param int $subject
     * @return array
     */
    protected static function new_manager_managers_manager_and_appraiser(int $subject): array {
        return array_merge(
            static::new_manager_and_managers_manager($subject),
            static::new_appraiser($subject)
        );
    }

    /**
     * Test changing of users in a view only role in an activity.
     *
     * @param string[] $view_only initial set of view only relationships to be
     *        generated in an activity.
     * @param callable $assign_users int->array<string,int> callable that takes
     *        a subject user id, assigns new users to one or more of view only
     *        relationships and returns a mapping of new user ids to those view
     *        only relationships.
     *
     * @dataProvider td_view_only_user_change
     */
    public function test_view_only_user_change(
        array $initial_view_only,
        string $assign_method
    ): void {
        $original = $this->create_test_data($initial_view_only);
        $subject = $original->subject;

        $new_view_only = static::$assign_method($subject);

        $removed = array_intersect_key($original->view_only, $new_view_only);
        $final_view_only = array_merge($original->view_only, $new_view_only);

        $this->sync_and_verify_participants(
            $subject, $original->responding, $final_view_only, $removed
        );
    }

    /**
     * Generates test data.
     *
     * @param string[] $view_only list of constants::RELATIONSHIP_XYZ strings
     *        indicating which view only relationships to create.
     *
     * @return stdClass data structure with these fields:
     *         - int subject: subject user id in generated Perform activity.
     *         - array<string,int> responding: mapping of responding roles/user
     *           ids (including the subject).
     *         - array<string,int> view_only: mapping of view only roles/user
     *           ids.
     */
    private function create_test_data(
        array $view_only
    ): stdClass {
        set_config('perform_sync_participant_instance_creation', 1);
        set_config('perform_sync_participant_instance_closure', 1);

        if (empty(self::$relationships)) {
            self::$relationships = array_reduce(
                relationship_entity::repository()->get()->all(),
                function (array $mapping, relationship_entity $entity): array {
                    $mapping[$entity->idnumber] = $entity->id;
                    return $mapping;
                },
                []
            );
        }

        $subject = null;
        $responding_participants = [];
        $view_only_participants = [];

        $raw = $this->create_activity_with_relationships(null, $view_only);
        foreach ($raw['participants'] as $uid => $relationship) {
            if ($relationship === constants::RELATIONSHIP_SUBJECT) {
                $subject = $uid;
            }

            if (in_array($relationship, $view_only)) {
                $view_only_participants[$relationship] = $uid;
            } else {
                $responding_participants[$relationship] = $uid;
            }
        }

        $this->sync_and_verify_participants(
            $subject, $responding_participants, $view_only_participants
        );

        return (object) [
            'subject' => $subject,
            'responding' => $responding_participants,
            'view_only' => $view_only_participants
        ];
    }

    /**
     * Runs the sync_participant_instances_task and verifies participants are
     * assigned/removed correctly.
     *
     * @param int $subject user id of the target of the perform activity.
     * @param array<string,int> $responding mapping of responding roles/user ids.
     * @param array<string,int> $view_only mapping of view only roles/user ids.
     * @param array<string,int> $removed mapping of replaced view only roles/user
     *        ids.
     */
    private function sync_and_verify_participants(
        int $subject,
        array $responding,
        array $view_only,
        array $removed=[]
    ): void {
        subject_instance::repository()->update(['needs_sync' => 1]);
        (new sync_participant_instances_task())->execute();
        $this->assertFalse(
            subject_instance::repository()->where('needs_sync', 1)->exists()
        );

        $this->assert_open_participant_instances(
            $subject, array_flip($responding)
        );

        $this->assert_view_only_participant_instances(
            $subject, array_flip($view_only)
        );

        $this->assert_participant_instances_count(
            $subject, count($responding) + count($view_only)
        );

        if ($removed) {
            $this->assert_absent_view_only_participant_instances(
                $subject, $removed
            );
        }
    }

    /**
     * Check that given view only participant instances DO NOT exist.
     *
     * @param int $subject activity subject user id.
     * @param array<string,int> $relationships mapping of relationship idnumbers
     *        to user ids.
     */
    private function assert_absent_view_only_participant_instances(
        int $subject,
        array $relationships
    ): void {
        $availability = availability_not_applicable::get_code();

        foreach ($relationships as $relationship => $uid) {
            $relationship_id = self::$relationships[$relationship];

            $pi_exists = participant_instance::repository()
                ->join([subject_instance::TABLE, 'si'], 'subject_instance_id', 'si.id')
                ->where('si.subject_user_id', $subject)
                ->where('participant_id', $uid)
                ->where('core_relationship_id', $relationship_id)
                ->where('availability', $availability)
                ->exists();

            $this->assertFalse(
                $pi_exists,
                "view only $relationship exist: $uid"
            );
        }
    }
}
