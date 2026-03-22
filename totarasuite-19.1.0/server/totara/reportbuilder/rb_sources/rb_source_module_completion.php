<?php
/*
 * This file is part of Totara Learn
 *
 * Copyright (C) 2023 onwards Totara Learning Solutions LTD
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
 * @package totara_reportbuilder
 */

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/totara/reportbuilder/rb_sources/rb_source_course_completion.php');

use core\orm\query\builder;
use core\testing\generator as core_generator;

class rb_source_module_completion extends rb_source_course_completion {

    public function __construct($groupid, rb_global_restriction_set $globalrestrictionset = null) {
        // The parent constructor calls the individual define_xxx functions, some of which we have overridden below.
        parent::__construct($groupid, $globalrestrictionset);

        // Override some other properties.
        $this->sourcetitle = get_string('sourcetitle', 'rb_source_module_completion');
        $this->sourcesummary = get_string('sourcesummary', 'rb_source_module_completion');
        $this->sourcelabel = get_string('sourcelabel', 'rb_source_module_completion');
    }

    protected function define_base(): string {
        // Note: Add any additional required fields from course_modules in here.
        return '(SELECT cc.*, cm.id AS cmid, cm.module, cm.instance, cm.completion
                   FROM {course_completions} cc
                   JOIN {course_modules} cm
                     ON cm.course = cc.course)';
    }

    protected function define_joinlist(): array {
        $joins = parent::define_joinlist();

        $joins[] = new rb_join(
            'modules',
            'INNER',
            '{modules}',
            'modules.id = base.module',
            REPORT_BUILDER_RELATION_ONE_TO_ONE
        );

        $joins[] = new rb_join(
            'course_completion_criteria',
            'LEFT',
            '{course_completion_criteria}',
            'course_completion_criteria.criteriatype = ' . COMPLETION_CRITERIA_TYPE_ACTIVITY . '
                AND course_completion_criteria.course = base.course
                AND course_completion_criteria.module = modules.name
                AND course_completion_criteria.moduleinstance = base.cmid',
            REPORT_BUILDER_RELATION_ONE_TO_ONE,
            ['modules']
        );

        $joins[] = new rb_join(
            'course_modules_completion',
            'LEFT',
            '{course_modules_completion}',
            'course_modules_completion.coursemoduleid = base.cmid
                AND course_modules_completion.userid = base.userid',
            REPORT_BUILDER_RELATION_ONE_TO_ONE
        );

        $joins[] = new rb_join(
            'activity_grade_items',
            'LEFT',
            '{grade_items}',
            'activity_grade_items.courseid = base.course
                AND activity_grade_items.itemmodule = modules.name
                AND activity_grade_items.iteminstance = base.instance',
            REPORT_BUILDER_RELATION_ONE_TO_ONE,
            ['modules']
        );

        $joins[] = new rb_join(
            'activity_grade_grades',
            'LEFT',
            '{grade_grades}',
            'activity_grade_grades.itemid = activity_grade_items.id
                AND activity_grade_grades.userid = base.userid',
            REPORT_BUILDER_RELATION_ONE_TO_ONE,
            ['activity_grade_items']
        );

        return $joins;
    }

    protected function define_columnoptions(): array {
        $course_column_options = parent::define_columnoptions();

        $column_options = [
            new rb_column_option(
                'module_completion',
                'time_completed',
                get_string('time_completed', 'rb_source_module_completion'),
                'CASE WHEN course_modules_completion.completionstate = ' . COMPLETION_INCOMPLETE . ' THEN NULL ELSE CASE WHEN course_modules_completion.timecompleted IS NULL THEN course_modules_completion.timemodified ELSE course_modules_completion.timecompleted END END',
                [
                    'joins' => ['course_modules_completion'],
                    'displayfunc' => 'nice_date',
                ]
            ),
            new rb_column_option(
                'module_completion',
                'final_grade',
                get_string('grade', 'rb_source_module_completion'),
                'activity_grade_grades.finalgrade',
                [
                    'joins' => ['activity_grade_grades'],
                    'displayfunc' => 'integer',
                ]
            ),
            new rb_column_option(
                'module_completion',
                'complete',
                get_string('complete', 'rb_source_module_completion'),
                'CASE WHEN course_modules_completion.completionstate = ' . COMPLETION_INCOMPLETE . ' OR course_modules_completion.completionstate IS NULL THEN 0 ELSE 1 END',
                [
                    'joins' => ['course_modules_completion'],
                    'displayfunc' => 'yes_or_no',
                ]
            ),
            new rb_column_option(
                'module',
                'name',
                get_string('module_name', 'rb_source_module_completion'),
                'base.cmid',
                [
                    'nosort' => true,
                    'joins' => ['modules'],
                    'displayfunc' => 'module_name',
                    'extrafields' => ['course_id' => 'base.course'],
                ]
            ),
            new rb_column_option(
                'module',
                'type',
                get_string('module_type', 'rb_source_module_completion'),
                'modules.name',
                [
                    'nosort' => true,
                    'joins' => ['modules'],
                    'displayfunc' => 'module_type',
                ]
            ),
            new rb_column_option(
                'module',
                'completion_enabled',
                get_string('completion_enabled', 'rb_source_module_completion'),
                'CASE WHEN base.completion = ' . COMPLETION_TRACKING_NONE . ' THEN 0 ELSE 1 END',
                [
                    'displayfunc' => 'yes_or_no',
                ]
            ),
            new rb_column_option(
                'module',
                'completion_criteria',
                get_string('completion_criteria', 'rb_source_module_completion'),
                'CASE WHEN course_completion_criteria.id IS NULL THEN 0 ELSE 1 END',
                [
                    'joins' => ['course_completion_criteria'],
                    'displayfunc' => 'yes_or_no',
                ]
            ),
        ];

        return array_merge($column_options, $course_column_options);
    }

    protected function define_defaultcolumns(): array {
        return [
            [
                'type' => 'user',
                'value' => 'namelink',
            ],
            [
                'type' => 'course',
                'value' => 'courselink',
            ],
            [
                'type' => 'module',
                'value' => 'name',
            ],
            [
                'type' => 'module',
                'value' => 'type',
            ],
            [
                'type' => 'module',
                'value' => 'completion_enabled',
            ],
            [
                'type' => 'module',
                'value' => 'completion_criteria',
            ],
            [
                'type' => 'module_completion',
                'value' => 'complete',
            ],
            [
                'type' => 'module_completion',
                'value' => 'time_completed',
            ],
            [
                'type' => 'module_completion',
                'value' => 'final_grade',
            ],
        ];
    }

    protected function define_filteroptions(): array {
        $course_filter_options = parent::define_filteroptions();

        $filter_options = [
            new rb_filter_option(
                'module',
                'type',
                get_string('module_type', 'rb_source_module_completion'),
                'menuofchoices',
                [
                    'selectfunc' => 'module_types',
                ]
            ),
            new rb_filter_option(
                'module',
                'completion_criteria',
                get_string('completion_criteria', 'rb_source_module_completion'),
                'select',
                [
                    'selectfunc' => 'yesno_list',
                ]
            ),
            new rb_filter_option(
                'module',
                'completion_enabled',
                get_string('completion_enabled', 'rb_source_module_completion'),
                'select',
                [
                    'selectfunc' => 'yesno_list',
                ]
            ),
            new rb_filter_option(
                'module_completion',
                'complete',
                get_string('complete', 'rb_source_module_completion'),
                'select',
                [
                    'selectfunc' => 'yesno_list',
                ]
            ),
            new rb_filter_option(
                'module_completion',
                'time_completed',
                get_string('time_completed', 'rb_source_module_completion'),
                'date'
            ),
        ];

        return array_merge($filter_options, $course_filter_options);
    }

    public function rb_filter_module_types(): array {
        // Get the list of modules.
        $modules = builder::table('modules')
            ->select('name')
            ->get()->all();

        // Load the language-specific names.
        $module_objects = array_map(function ($module) {
            return [
                'key' => $module->name,
                'name' => get_string('modulename', 'mod_' . $module->name),
            ];
        }, $modules);

        // Sort them alphabetically in the language of the recipient.
        usort($module_objects, function($a, $b) {
            return strcmp($a['name'], $b['name']);
        });

        // Convert to key => value pairs.
        return array_reduce(
            $module_objects,
            function(array $acc,  array $it): array {
                $acc[$it['key']] = $it['name'];
                return $acc;
            },
            []
        );
    }

    protected function define_defaultfilters(): array {
        return [
            [
                'type' => 'user',
                'value' => 'fullname',
            ],
            [
                'type' => 'module',
                'value' => 'type',
            ],
            [
                'type' => 'module_completion',
                'value' => 'complete',
            ],
            [
                'type' => 'module',
                'value' => 'completion_enabled',
                'advanced' => 1,
            ],
            [
                'type' => 'module',
                'value' => 'completion_criteria',
                'advanced' => 1,
            ],
            [
                'type' => 'module_completion',
                'value' => 'time_completed',
                'advanced' => 1,
            ],
            [
                'type' => 'course',
                'value' => 'fullname',
                'advanced' => 1,
            ],
            [
                'type' => 'course_category',
                'value' => 'path',
                'advanced' => 1,
            ],
            [
                'type' => 'course_completion',
                'value' => 'completeddate',
                'advanced' => 1,
            ],
            [
                'type' => 'course_completion',
                'value' => 'status',
                'advanced' => 1,
            ],
        ];
    }

    protected function define_paramoptions(): array {
        $paramoptions = parent::define_paramoptions();

        $paramoptions[] = new rb_param_option(
            'coursemoduleid',
            'base.cmid'
        );

        return $paramoptions;
    }

    /**
     * Inject column_test data into database.
     * @param totara_reportbuilder_column_test $testcase
     */
    public function phpunit_column_test_add_data(totara_reportbuilder_column_test $testcase) {
        global $DB;

        if (!PHPUNIT_TEST) {
            throw new coding_exception('phpunit_column_test_add_data() cannot be used outside of unit tests');
        }

        // This report has several joins which could result in multiple records if not joined correctly. This test
        // adds a bunch of data to check that duplication does not occur.

        // Add two users.
        // Add one course.
        // Add four course_modules.
        // Result is 8 records in results (two users * four activities).
        $generator = core_generator::instance();

        $user1 = $generator->create_user();
        $user2 = $generator->create_user();

        $course1 = $generator->create_course();

        $module1 = $generator->create_module('choice', ['course' => $course1]);
        $module2 = $generator->create_module('choice', ['course' => $course1]);
        $module3 = $generator->create_module('choice', ['course' => $course1]);
        $module4 = $generator->create_module('choice', ['course' => $course1]);
        $choice_module = $DB->get_record('modules', ['name' => 'choice']);
        $cm1 = $DB->get_record('course_modules', ['module' => $choice_module->id, 'instance' => $module1->id]);
        $cm2 = $DB->get_record('course_modules', ['module' => $choice_module->id, 'instance' => $module2->id]);
        $cm3 = $DB->get_record('course_modules', ['module' => $choice_module->id, 'instance' => $module3->id]);
        $cm4 = $DB->get_record('course_modules', ['module' => $choice_module->id, 'instance' => $module4->id]);

        // The following should have no effect on the number of records in the results.

        // Add 1 course_completions for each user.
        $DB->insert_record('course_completions', ['userid' => $user1->id, 'course' => $course1->id]);
        $DB->insert_record('course_completions', ['userid' => $user2->id, 'course' => $course1->id]);

        // Add 3 course_completion_criteria.
        $DB->insert_record('course_completion_criteria', [
            'course' => 2,
            'criteriatype' => COMPLETION_CRITERIA_TYPE_ACTIVITY,
            'module' => 'choice',
            'moduleinstance' => $cm1->id,
        ]);
        $DB->insert_record('course_completion_criteria', [
            'course' => 2,
            'criteriatype' => COMPLETION_CRITERIA_TYPE_ACTIVITY,
            'module' => 'choice',
            'moduleinstance' => $cm2->id,
        ]);
        $DB->insert_record('course_completion_criteria', [
            'course' => 2,
            'criteriatype' => COMPLETION_CRITERIA_TYPE_ACTIVITY,
            'module' => 'choice',
            'moduleinstance' => $cm3->id,
        ]);

        // Add 3 grade_items (two for matching course_completion_criteria).
        $grade_item2_id = $DB->insert_record('grade_items', [
            'courseid' => $course1->id,
            'itemtype' => 'mod',
            'itemmodule' => 'choice',
            'iteminstance' => $module2->id,
        ]);
        $DB->insert_record('grade_items', [
            'courseid' => $course1->id,
            'itemtype' => 'mod',
            'itemmodule' => 'choice',
            'iteminstance' => $module3->id,
        ]);
        $grade_item4_id = $DB->insert_record('grade_items', [
            'courseid' => $course1->id,
            'itemtype' => 'mod',
            'itemmodule' => 'choice',
            'iteminstance' => $module4->id,
        ]);

        // Add 3 course_modules_completion for user1 (one with course_completion_criteria, one with grade_items, one with both), 1 for user2.
        $DB->insert_record('course_modules_completion', [
            'coursemoduleid' => $cm1->id,
            'userid' => $user1->id,
            'completionstate' => 0,
            'timemodified' => 0,
        ]);
        $DB->insert_record('course_modules_completion', [
            'coursemoduleid' => $cm2->id,
            'userid' => $user1->id,
            'completionstate' => 0,
            'timemodified' => 0,
        ]);
        $DB->insert_record('course_modules_completion', [
            'coursemoduleid' => $cm4->id,
            'userid' => $user1->id,
            'completionstate' => 0,
            'timemodified' => 0,
        ]);
        $DB->insert_record('course_modules_completion', [
            'coursemoduleid' => $cm4->id,
            'userid' => $user2->id,
            'completionstate' => 0,
            'timemodified' => 0,
        ]);

        // Add 2 grade_grades for user1, 1 for user2.
        $DB->insert_record('grade_grades', [
            'itemid' => $grade_item2_id,
            'userid' => $user1->id,
        ]);
        $DB->insert_record('grade_grades', [
            'itemid' => $grade_item4_id,
            'userid' => $user1->id,
        ]);
        $DB->insert_record('grade_grades', [
            'itemid' => $grade_item4_id,
            'userid' => $user2->id,
        ]);
    }

    /**
     * Returns expected result for column_test.
     * @param rb_column_option $columnoption
     * @return int
     */
    public function phpunit_column_test_expected_count($columnoption) {
        if (!PHPUNIT_TEST) {
            throw new coding_exception('phpunit_column_test_expected_count() cannot be used outside of unit tests');
        }
        // The core test creates some data, which totals 1 record. We added 8 records, so total 9.
        return 9;
    }
}
