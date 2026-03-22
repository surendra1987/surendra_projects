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
 * @author Scott Davies <scott.davies@totara.com>
 * @package perform_goal
 */

use perform_goal\testing\generator;
use perform_goal\testing\goal_task_generator_config;
use perform_goal\model\goal_task_type\course_type;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/perform/goal/rb_sources/rb_source_perform_goal.php');

/**
 * This is the report source for the Totara Goal Tasks & Courses report.
 */
class rb_source_perform_goal_tasks_and_courses extends rb_source_perform_goal {
    /**
     * @var string
     */
    private string $lang_component_self = 'rb_source_perform_goal_tasks_and_courses';

    public function __construct(rb_global_restriction_set $globalrestrictionset = null) {
        parent::__construct();

        // Override some strings from the parent class.
        $this->sourcetitle = get_string('sourcetitle', $this->lang_component_self);
        $this->sourcesummary = get_string('sourcesummary', $this->lang_component_self);
        $this->sourcelabel = get_string('sourcelabel', $this->lang_component);
    }

    /**
     * @inheritDoc
     */
    protected function define_joinlist() {
        $joinlist = parent::define_joinlist();

        $joinlist[] = new \rb_join(
            'perform_goal_task',
            'INNER',
            '{perform_goal_task}',
            "perform_goal_task.goal_id = base.id",
        );

        $joinlist[] = new \rb_join(
            'perform_goal_task_resource',
            'LEFT',
            '{perform_goal_task_resource}',
            "perform_goal_task_resource.task_id = perform_goal_task.id",
        );

        $joinlist[] = new \rb_join(
            'course',
            'LEFT',
            '{course}',
            "course.id = perform_goal_task_resource.resource_id",
            null,
            ['perform_goal_task', 'perform_goal_task_resource'], // these dependencies are needed for unit test checks.
        );

        return $joinlist;
    }

    /**
     * @inheritDoc
     */
    protected function define_defaultcolumns() {
        $default_columns = [
            [ // goal name
                'type' => 'base',
                'value' => 'name'
            ],
            [ // goal task
                'type' => 'base',
                'value' => 'goal_task_description'
            ],
            [ // course name
                'type' => 'course',
                'value' => 'fullname'
            ],
            [ // goal task status
                'type' => 'base',
                'value' => 'goal_task_status'
            ],
            [ // user full name
                'type' => 'user',
                'value' => 'namelink'
            ],
            [ // goal Progress
                'type' => 'base',
                'value' => 'current_value'
            ],
        ];

        return $default_columns;
    }

    /**
     * @inheritDoc
     */
    protected function define_defaultfilters() {
        $default_filters = parent::define_defaultfilters();

        $new_filters = [
            [
                'type' => 'base',
                'value' => 'goal_task_status'
            ],
            [
                'type' => 'course',
                'value' => 'fullname'
            ]
        ];

        return array_merge($default_filters, $new_filters);
    }

    /**
     * @inheritDoc
     */
    protected function define_columnoptions() {
        $column_options = parent::define_columnoptions();

        // Redefine any columns if needed, e.g. set a different column heading.
        foreach ($column_options as $col) {
            if ($col->name == get_string('current_value', $this->lang_component) && $col->value == 'current_value') {
                $col->name = get_string('current_value', $this->lang_component_self);
            }
        }

        $status_incomplete = get_string('goal_task_status_incomplete', $this->lang_component_self);
        $status_complete = get_string('goal_task_status_complete', $this->lang_component_self);

        $new_columns = [
            new rb_column_option(
                'base',
                'goal_task_description',
                get_string('goal_task_description', $this->lang_component_self),
                'perform_goal_task.description',
                [
                    'joins' => 'perform_goal_task',
                    'displayfunc' => 'format_string',
                    'dbdatatype' => 'char',
                    'outputformat' => 'text',
                ]
            ),
            // Note on how the goal_task status column value is determined:
            // There is a completed_at field in goal_task. If this is null, then the task is incomplete. If the task is complete,
            // the field gets populated with the time of completion.
            new rb_column_option(
                'base',
                'goal_task_status',
                get_string('goal_task_status', $this->lang_component_self),
                "CASE 
                     WHEN perform_goal_task.completed_at IS NULL THEN '{$status_incomplete}'
                     ELSE '{$status_complete}'
                 END",
                [
                    'joins' => 'perform_goal_task',
                    'displayfunc' => 'format_string',
                ]
            ),
            new rb_column_option(
                'course',
                'fullname',
                get_string('course_fullname', $this->lang_component_self),
                'course.fullname',
                [
                    'joins' => ['perform_goal_task',
                        'perform_goal_task_resource',
                        'course',
                    ],
                    'displayfunc' => 'format_string',
                ]
            ),
        ];

        return array_merge($column_options, $new_columns);
    }

    /**
     * @inheritDoc
     */
    protected function define_filteroptions() {
        $filter_options = parent::define_filteroptions();

        $new_filters = [
            new \rb_filter_option('base',
                'goal_task_status',
                get_string('goal_task_status', $this->lang_component_self),
                'select',
                [
                    'selectchoices' => [
                        'incomplete' => get_string('goal_task_status_incomplete', $this->lang_component_self),
                        'complete' => get_string('goal_task_status_complete', $this->lang_component_self),
                    ],
                    'simplemode' => true,
                ]
            ),
            new \rb_filter_option('course',
                'fullname',
                get_string('course_fullname', $this->lang_component_self),
                'text',
            )
        ];

        return array_merge($filter_options, $new_filters);
    }

    public function phpunit_column_test_add_data(totara_reportbuilder_column_test $testcase): void {
        if (!PHPUNIT_TEST) {
            throw new coding_exception('phpunit_column_test_add_data() cannot be used outside of unit tests');
        }

        $user = \core_phpunit\testcase::getDataGenerator()->create_user();
        $goal1 = generator::instance()->create_goal(
            \perform_goal\testing\goal_generator_config::new([
                'user_id' => $user->id
            ])
        );

        $gen = \core\testing\generator::instance();
        $course = $gen->create_course();
        $goal_task_config = goal_task_generator_config::new([
            'goal_id' => $goal1->id,
            'description' => "Task 1 goal 1 with resource",
            'resource_id' => $course->id,
            'resource_type' => course_type::TYPECODE
        ]);
        $goal_task1 = generator::instance()->create_goal_task($goal_task_config);
        $gt_resource = generator::instance()->create_goal_task_resource($goal_task1, $goal_task_config);
    }
}
