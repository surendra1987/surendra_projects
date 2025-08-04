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
 * @author Murali Nair <murali.nair@totara.com>
 * @package perform_goal
 */

namespace perform_goal\model\goal_task_type;

use context_system;
use completion_completion;
use core\entity\course;
use core\format;
use core\webapi\formatter\field\string_field_formatter;
use perform_goal\model\goal_task_resource;

global $CFG;
require_once($CFG->dirroot . '/course/lib.php');

/**
 * Goal task course resource type.
 */
final class course_type extends type {

    /** @var ?course $entity */
    protected ?course $entity = null;

    /** @var int TYPECODE */
    public const TYPECODE = 1;

    /**
     * @inheritDoc
     */
    public function authorized(): bool {
        $course_id = $this->resource_id();

        return is_null($course_id)
            ? false
            : totara_course_is_viewable($course_id);
    }

    /**
     * Returns an instance of this class.
     *
     * @param ?int $course_id associated course record id if any.
     *
     * @return self the instance.
     */
    public static function create(?int $course_id = null): self {
        return new self($course_id);
    }

    /**
     * {@inheritDoc}
     */
    public function code(): int {
        return self::TYPECODE;
    }

    /**
     * {@inheritDoc}
     */
    public function custom_data(goal_task_resource $resource): array {
        $course = $this->resource();
        if (!$course) {
            return [];
        }

        // We need to calculate a subject user's course progress the same way the 'Current Learning' block does.
        // 1. If completion/progress tracking is disabled for a course, subject progress should be returned as null.
        // 2. If a user is enrolled in a course, for the subject_progress value provide 0 by default. Provide null if not.
        $progress = null;

        $subject_id = $resource->task->goal->user_id;
        if ($subject_id && $course->enablecompletion) {
            $course_context = \context_course::instance($course->id);
            if (is_enrolled($course_context, $subject_id)) {
                $completion = new completion_completion(['userid' => $subject_id, 'course' => $course->id]);
                $progressinfo = $completion->get_progressinfo();
                $progress = $progressinfo->get_percentagecomplete();
                // 'false' here usually means the course has no completion criteria set up, so subject user's progress should really be null.
                if ($progress === false) {
                    $progress = null;
                }
            }
        }

        $formatter = new string_field_formatter(format::FORMAT_PLAIN, context_system::instance());

        return [
            'name' => $formatter->format($course->fullname),
            'url' => course_get_url($course->id)->out(false), // this is the course view URL for a learner.
            'image_data' => course_get_image($course->id)->out(false), // this is the course Appearance >Image file.
            'subject_progress' => $progress
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function resource() {
        $course_id = $this->resource_id();
        if (is_null($course_id) || !$this->authorized()) {
            return null;
        }

        if (!($this->entity instanceof course)) {
            try {
                $this->entity = course::repository()
                    ->where('id', $course_id)
                    ->where('containertype', 'container_course')
                    ->one(true);
            } catch (\Throwable $exception) {
                return null;
            }
        }
        return $this->entity;
    }

    /**
     * {@inheritDoc}
     */
    public function resource_exists(): bool {
        return (bool)$this->resource();
    }
}