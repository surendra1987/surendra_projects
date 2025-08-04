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
 * @author Nathan Lewis <nathan.lewis@totara.com>
 * @author Chris Snyder <chris.snyder@totara.com>
 * @package core_enrol
 */

namespace core_enrol\model;

use coding_exception;
use core\collection;
use core\entity\course;
use core\entity\user;
use core\entity\user_enrolment;
use core\model\enrol;
use core\model\user_enrolment as user_enrolment_model;
use core\orm\entity\model;
use core\orm\query\builder;
use core_enrol\enrolment_approval_helper;
use core_enrol\entity\user_enrolment_application as entity;
use core_enrol\event\user_enrolment_application_created;
use mod_approval\model\application\application as approval_application_model;
use totara_job\entity\job_assignment;

/**
 * @property int $id
 * @property int $user_enrolments_id
 * @property int $approval_application_id
 * @property int $timecreated
 * @property int $timemodified
 * @property-read user_enrolment_model $user_enrolment
 * @property-read approval_application_model $approval_application
 * @property-read course $course
 * @property-read user $user
 */
class user_enrolment_application extends model {

    /**
     * @var string[]
     */
    protected $entity_attribute_whitelist = [
        'id',
        'user_enrolments_id',
        'approval_application_id',
        'approval_application',
        'timecreated',
        'timemodified',
    ];

    /**
     * @var string[]
     */
    protected $model_accessor_whitelist = [
        'enrolment_instance',
        'user_enrolment',
        'approval_application',
        'course',
        'user',
    ];

    /**
     * @return string
     */
    protected static function get_entity_class(): string {
        return entity::class;
    }

    /**
     * @return enrol
     */
    public function get_enrolment_instance(): enrol {
        return $this->get_user_enrolment()->get_enrolment();
    }

    /**
     * @return user_enrolment_model
     */
    public function get_user_enrolment(): user_enrolment_model {
        return user_enrolment_model::load_by_entity($this->entity->user_enrolment);
    }

    /**
     * @return approval_application_model
     */
    public function get_approval_application(): approval_application_model {
        return approval_application_model::load_by_entity($this->entity->approval_application);
    }

    /**
     * Gets the course entity associated with this user_enrolment_application record.
     *
     * @return course
     */
    public function get_course(): course {
        return $this->get_user_enrolment()->get_enrolment()->course;
    }

    /**
     * Get the user associated with this user_enrolment_application record.
     *
     * @return user
     */
    public function get_user(): user {
        return new user($this->get_user_enrolment()->userid);
    }

    /**
     * Create a user enrolment application record only, and do not trigger any events.
     *
     * This method is probably only useful for unit test setup.
     *
     * @param int $user_enrolments_id
     * @param int $approval_application_id
     * @return static
     */
    public static function create(int $user_enrolments_id, int $approval_application_id): static {
        $now = time();

        $entity = new entity();
        $entity->user_enrolments_id = $user_enrolments_id;
        $entity->approval_application_id = $approval_application_id;
        $entity->timecreated = $now;
        $entity->timemodified = $now;
        $entity->save();

        return static::load_by_entity($entity);
    }

    /**
     * Given a user_enrolment and maybe a job assignment, create a new workflow application and associated user_enrolment_application record.
     *
     * @param int $user_enrolment_id
     * @param int|null $job_assignment_id
     * @return static
     */
    public static function create_from_user_enrolment(int $user_enrolment_id, int $job_assignment_id = null): self {
        $user_enrolment = \core\model\user_enrolment::load_by_id($user_enrolment_id);

        $job_assignment = null;
        if ($job_assignment_id) {
            $job_assignment = new job_assignment($job_assignment_id);
            if ($job_assignment->userid != $user_enrolment->userid) {
                throw new coding_exception('Job assignment user id does not match user enrolment user id');
            }
        }

        // Return from method.
        /** @var self $user_enrolment_application */
        $user_enrolment_application = builder::get_db()->transaction(function () use ($user_enrolment, $job_assignment) {
            $instance = $user_enrolment->get_enrolment();
            $application = enrolment_approval_helper::create_application(
                $instance,
                new user($user_enrolment->userid),
                $job_assignment
            );

            $now = time();

            $entity = new entity();
            $entity->user_enrolments_id = $user_enrolment->id;
            $entity->approval_application_id = $application->id;
            $entity->timecreated = $now;
            $entity->timemodified = $now;
            $entity->save();

            // Return from transaction closure.
            return static::load_by_entity($entity);
        });

        // This happens outside the transaction on purpose, in case an event observer fails.
        $user_enrolment_application->trigger_created_event();

        return $user_enrolment_application;
    }

    /**
     * Trigger the user_enrolment_application_created event, so that approvalform_enrol can observe it.
     *
     * @return void
     * @throws coding_exception
     */
    public function trigger_created_event() {
        // Create and trigger event.
        user_enrolment_application_created::create([
            'objectid' => $this->id,
            'other' => [
                'user_enrolments_id' => $this->user_enrolments_id,
                'approval_application_id' => $this->approval_application_id,
            ],
            'context' => \context_course::instance($this->course->id),
        ])->trigger();
    }

    /**
     * Replaces the application linked via this user_enrolment_application record with a new application.
     *
     * @return approval_application_model
     */
    public function replace_application_with_new(): approval_application_model {
        if (!enrolment_approval_helper::needs_create_new_application($this->approval_application)) {
            throw new coding_exception('Existing application does not need to be replaced.');
        }

        if (!enrolment_approval_helper::approval_available_for($this->user_enrolment->enrolid, $this->user->id)) {
            throw new coding_exception('User cannot create application, approval is not available.');
        }

        $job_assignment = null;
        if ($this->approval_application) {
            $job_assignment = $this->approval_application->job_assignment;
        }

        $new_application = builder::get_db()->transaction(function () use ($job_assignment) {
            $application = enrolment_approval_helper::create_application($this->user_enrolment->get_enrolment(), $this->user, $job_assignment);
            $this->set_approval_application_id($application->id);
            return $application;
        });

        // Re-trigger the creation event for this user_enrolment_application record.
        $this->trigger_created_event();

        return $new_application;
    }

    /**
     * Find a user enrolment application record by user enrolment
     *
     * Each user enrolment can only have one user_enrolments_application record, so we can look up using just this ID.
     * Returns null if no record is found.
     *
     * @param int $user_enrolment_id
     * @return static|null
     */
    public static function find_with_user_enrolment_id(
        int $user_enrolment_id
    ): ?self {
        return entity::repository()
            ->where('user_enrolments_id', '=', $user_enrolment_id)
            ->get()
            ->map_to(self::class)
            ->first();
    }

    /**
     * Find multiple user enrolment application records when given a list of user enrolments
     *
     * The result contains only those user enrolment application records that can be found.
     *
     * @param int[] $user_enrolment_ids
     * @return collection|self[]
     */
    public static function find_with_user_enrolment_ids(
        array $user_enrolment_ids
    ): collection {
        return entity::repository()
            ->where_in('user_enrolments_id', $user_enrolment_ids)
            ->get()
            ->map_to(self::class);
    }

    /**
     * Find a user enrolment application record by application
     *
     * Each application can only have one user_enrolments_application record, so we can look up using just this ID.
     * Returns null if no record is found.
     *
     * @param int $application_id
     * @return static|null
     */
    public static function find_with_application_id(
        int $application_id
    ): ?self {
        return entity::repository()
            ->where('approval_application_id', '=', $application_id)
            ->get()
            ->map_to(self::class)
            ->first();
    }

    /**
     * Attempts to find a user_enrolment_application by instance and user.
     *
     * @param int $enrol_id
     * @param int $user_id
     * @return static|null
     */
    public static function find_with_instance_and_user_id(int $enrol_id, int $user_id): ?self {
        // First find the user enrolment.
        $user_enrolment = user_enrolment::repository()
            ->where('enrolid', '=', $enrol_id)
            ->where('userid', '=', $user_id)
            ->order_by('id')
            ->first();
        if ($user_enrolment) {
            return static::find_with_user_enrolment_id($user_enrolment->id);
        }
        return null;
    }

    /**
     * @param int $application_id
     * @return void
     */
    public function set_approval_application_id(int $application_id): void {
        $this->entity->approval_application_id = $application_id;
        $this->entity->save();
        $this->refresh();
    }

    /**
     * @return $this
     *
     */
    public function refresh(): self {
        $this->entity->refresh();

        return $this;
    }

    /**
     * @return $this
     *
     */
    public function delete(): void {
        $this->entity->delete();
    }
}