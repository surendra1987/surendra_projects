<?php
/**
 * This file is part of Totara Core
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
 * @author  Kian Nguyen <kian.nguyen@totaralearning.com>
 * @package container_course
 */
namespace container_course\interactor;

use container_course\course;
use container_course\non_interactive_enrolment;
use context;
use core\entity\user;
use core_container\factory;
use core_enrol\enrolment_approval_helper;
use moodle_exception;
use totara_core\advanced_feature;

class course_interactor {
    /**
     * @var int
     */
    private $actor_id;

    /**
     * @var course
     */
    private $course;

    /**
     * course_interactor constructor.
     * @param course   $course
     * @param int|null $user_id
     */
    public function __construct(course $course, ?int $user_id = null) {
        global $USER;

        $this->course = $course;
        $this->actor_id = $user_id ?? $USER->id;
    }

    /**
     * @param int      $course_id
     * @param int|null $user_id
     * @return course_interactor
     */
    public static function from_course_id(int $course_id, ?int $user_id = null): course_interactor {
        /** @var course $course */
        $course = factory::from_id($course_id);
        return new static($course, $user_id);
    }

    /**
     * @return int
     */
    public function get_actor_id(): int {
        return $this->actor_id;
    }

    /**
     * External view.
     * Viewing the course as non participant of the course. The actor does not
     * have to enrolled into the course to view it.
     *
     * @return bool
     */
    public function can_view(): bool {
        global $CFG;
        if (!function_exists('totara_course_is_viewable')) {
            require_once("{$CFG->dirroot}/totara/core/totara.php");
        }

        return totara_course_is_viewable($this->course->id, $this->actor_id);
    }

    /**
     * @return void
     */
    public function require_view(): void {
        if (!$this->can_view()) {
            throw new moodle_exception('error:course_hidden', 'container_course');
        }
    }

    /**
     * @return void
     */
    public function require_access(): void {
        if (!$this->can_access()) {
            throw new moodle_exception('error:course_access', 'container_course');
        }
    }

    /**
     * Checks if the user is able to access to course.
     * This is different from view. View allow you to view the course item
     * in find learning, or view the course enrol page.
     *
     * Access means that user is able to access the course after all the enrolments
     * are resolved.
     *
     * @return bool
     */
    public function can_access(): bool {
        $course_record = $this->course->to_record();
        return can_access_course($course_record, $this->actor_id);
    }

    /**
     * Checks if the current user actor is enrolled into the course or not.
     *
     * @return bool
     */
    public function is_enrolled(): bool {
        global $CFG;
        require_once("{$CFG->dirroot}/lib/enrollib.php");

        $context = $this->course->get_context();

        return is_enrolled($context, $this->actor_id);
    }

    /**
     * Checks if the current user actor is able to enrol into the course or not.
     *
     * @return bool
     */
    public function can_enrol(): bool {
        if (!$this->can_access()) {
            // User is not able to see the course, hence cannot enrol.
            return false;
        }

        return !$this->is_enrolled() || !$this->is_enrolled_not_pending_approval();
    }

    /**
     * Checks if the current user actor is a guess of this very course.
     *
     * @return bool
     */
    public function is_guest(): bool {
        $context = $this->course->get_context();
        return is_guest($context, $this->actor_id);
    }

    /**
     * @return bool
     */
    public function is_site_guest(): bool {
        return isguestuser($this->actor_id);
    }

    /**
     * @return bool
     */
    public function is_siteadmin(): bool {
        return is_siteadmin($this->actor_id);
    }

    /**
     * Checks if the current user actor has the capability moodle/course:view
     * within the course's context.
     *
     * @return bool
     */
    public function has_view_capability(): bool {
        $context = $this->course->get_context();
        return has_capability(
            "moodle/course:view",
            $context,
            $this->actor_id
        );
    }

    /**
     * @return bool
     */
    public function non_interactive_enrol_instance_enabled(): bool {
        $results = non_interactive_enrolment::get_non_interactive_enrols($this->get_course_id(), $this->actor_id);
        return count($results) > 0;
    }

    /**
     *
     * @return bool
     */
    public function supports_non_interactive_enrol(): bool {
        $results = non_interactive_enrolment::get_non_interactive_enrols($this->get_course_id(), $this->actor_id);
        if (count($results) == 1) {
            $result = reset($results);
            return $result;
        }

        return false;
    }

    /**
     * @return int
     */
    public function get_course_id(): int {
        return $this->course->id;
    }

    /**
     * @return bool
     */
    public function can_mark_self_complete(): bool {
        if (!$this->is_enrolled()) {
            return false;
        }

        $completion = new \completion_info($this->course->to_record());
        if (!$completion->is_enabled()) {
            return false;
        }

        if (!$completion->is_tracked_user($this->actor_id)) {
            return false;
        }

        // Get course completion.
        $completion = $completion->get_completion($this->actor_id, COMPLETION_CRITERIA_TYPE_SELF);
        if (!$completion) {
            return false;
        }

        // Check if the user has already marked themselves as complete.
        if ($completion->is_complete()) {
            return false;
        }

        return true;
    }

    /**
     * @return bool
     */
    public function guest_enrol_enabled(): bool {
        return course_has_guest_enrolment_plugin_enabled($this->get_course_id());
    }

    /**
     * @return bool
     */
    public function can_view_hidden_activities(): bool {
        return has_capability('moodle/course:viewhiddenactivities', $this->course->get_context(), $this->actor_id);
    }

    /**
     * @return bool
     */
    public function can_update_course(): bool {
        return has_capability('moodle/course:update', $this->course->get_context(), $this->actor_id);
    }

    /**
     *
     * @return bool
     */
    public function can_view_hidden_course(): bool {
        $context = $this->course->get_context();
        return has_capability(
            "moodle/course:viewhiddencourses",
            $context,
            $this->actor_id
        );
    }

    /**
     *
     * @return bool
     */
    public function can_view_enrol(): bool {
        $context = $this->course->get_context();
        return has_capability(
            "moodle/course:enrolreview",
            $context,
            $this->actor_id
        );
    }

    /**
     * @return context
     */
    public function get_course_context(): context {
        return $this->course->get_context();
    }

    /**
     * Check tenant user can be enrolled into tenant course or not.
     *
     * @return bool
     */
    public function can_enrol_tenant_user_into_course(): bool {
        $user = new user($this->actor_id);
        if (!empty($user->tenantid)) {
            if (!is_null($this->get_course_context()->tenantid) && $this->get_course_context()->tenantid !== $user->tenantid) {
                return false;
            }
        }

        return true;
    }


    /**
     * Check non_interactive_enrol requires approval or not. non_interactive_enrol is enabled, if only one enrol is enabled and
     * guest enrol enabled.
     *
     * @return bool
     */
    public function non_interactive_enrol_requires_approval(): bool {
        if ($this->supports_non_interactive_enrol() && advanced_feature::is_enabled('approval_workflows')) {
            $instances = non_interactive_enrolment::get_non_interactive_enrolment_instances($this->get_course_id(), $this->actor_id);
            $instance = reset($instances);
            if ($instance->workflow_id) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check user enrolment is pending for approval or not.
     *
     * @return bool
     */
    public function is_enrolled_not_pending_approval(): bool {
        return !enrolment_approval_helper::user_enrolment_pending($this->get_course_id(), $this->actor_id);
    }
}