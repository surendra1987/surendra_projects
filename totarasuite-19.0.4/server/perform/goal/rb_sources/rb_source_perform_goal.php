<?php
/**
 * This file is part of Totara Perform
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
 * @author Oleg Demeshev <oleg.demeshev@totara.com>
 * @package perform_goal
 */

use perform_goal\settings_helper;
use perform_goal\model\goal as goal_model;
use perform_goal\model\status\completed;
use perform_goal\testing\generator;

defined('MOODLE_INTERNAL') || die();

/**
 * Defines reportbuilder information for the perform_goal report source.
 */
class rb_source_perform_goal extends rb_base_source {

    use totara_job\rb\source\report_trait;

    /** @var string $lang_component */
    protected $lang_component = 'rb_source_perform_goal';

    public function __construct(rb_global_restriction_set $globalrestrictionset = null) {
        // Remember the active global restriction set.
        $this->globalrestrictionset = $globalrestrictionset;
        // Apply global user restrictions.
        $this->add_global_report_restriction_join('base', 'user_id');

        $this->base = '{perform_goal}';
        $this->joinlist = $this->define_joinlist();

        $this->columnoptions = $this->define_columnoptions();
        $this->filteroptions = $this->define_filteroptions();
        $this->contentoptions = $this->define_contentoptions();

        $this->defaultcolumns = $this->define_defaultcolumns();
        $this->defaultfilters = $this->define_defaultfilters();

        $this->sourcetitle = get_string('sourcetitle', $this->lang_component);
        $this->sourcesummary = get_string('sourcesummary', $this->lang_component);
        $this->sourcelabel = get_string('sourcelabel', $this->lang_component);

        $this->usedcomponents[] = 'perform_goal';
        parent::__construct();
    }

    /**
     * @inheritDoc
     */
    public static function is_source_ignored() {
        return settings_helper::is_perform_goals_disabled();
    }

    /**
     * @inheritDoc
     */
    public function global_restrictions_supported() {
        return true;
    }

    /**
     * @inheritDoc
     */
    protected function define_contentoptions() {
        $contentoptions = [];
        // Add the manager/position/organisation content options.
        $this->add_basic_user_content_options($contentoptions);
        return $contentoptions;
    }

    /**
     * @inheritDoc
     */
    protected function define_joinlist() {
        $joinlist = [];
        $this->add_core_user_tables($joinlist, 'base', 'user_id');
        $this->add_totara_job_tables($joinlist, 'base', 'user_id');

        return $joinlist;
    }

    /**
     * @inheritDoc
     */
    protected function define_defaultcolumns() {
        $default_columns = [
            [
                'type' => 'base',
                'value' => 'name'
            ],
            [
                'type' => 'user',
                'value' => 'namelink'
            ],
            [
                'type' => 'job_assignment',
                'value' => 'allpositionnames'
            ],
            [
                'type' => 'job_assignment',
                'value' => 'allorganisationnames'
            ],
            [
                'type' => 'job_assignment',
                'value' => 'allmanagernames'
            ],
            [
                'type' => 'base',
                'value' => 'status'
            ],
            [
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
        $default_filters = [
            [
                'type' => 'base',
                'value' => 'name'
            ]
        ];

        return $default_filters;
    }

    /**
     * @inheritDoc
     */
    protected function define_columnoptions() {
        $completed_status_code = completed::get_code();

        $column_options = [
            new rb_column_option(
                'base',
                'goalid',
                '',
                'base.id',
                ['selectable' => false]
            ),
            new rb_column_option(
                'base',
                'name',
                get_string('name', $this->lang_component),
                'base.name',
                [
                    'displayfunc' => 'format_string',
                    'dbdatatype' => 'char',
                    'outputformat' => 'text',
                ]
            ),
            new rb_column_option(
                'base',
                'id_number',
                get_string('id_number', $this->lang_component),
                'base.id_number',
                [
                    'displayfunc' => 'format_string',
                    'dbdatatype' => 'char',
                    'outputformat' => 'text',
                ]
            ),
            new rb_column_option(
                'base',
                'start_date',
                get_string('start_date', $this->lang_component),
                'base.start_date',
                [
                    'displayfunc' => 'nice_date',
                    'dbdatatype' => 'timestamp',
                ]
            ),
            new rb_column_option(
                'base',
                'target_date',
                get_string('target_date', $this->lang_component),
                'base.target_date',
                [
                    'displayfunc' => 'nice_date',
                    'dbdatatype' => 'timestamp',
                ]
            ),
            new rb_column_option(
                'base',
                'status',
                get_string('status', $this->lang_component),
                'base.status',
                [
                    'displayfunc' => 'goal_status',
                    'dbdatatype' => 'char',
                    'outputformat' => 'text',
                ]
            ),
            new rb_column_option(
                'base',
                'current_value',
                get_string('current_value', $this->lang_component),
                "CASE
                    WHEN base.target_value > 0 THEN (base.current_value / base.target_value * 100)
                    ELSE 0
                END",
                [
                    'displayfunc' => 'goal_current_value',
                    'dbdatatype' => 'float'
                ]
            ),
            new rb_column_option(
                'base',
                'closed_at',
                get_string('closed_at', $this->lang_component),
                'base.closed_at',
                [
                    'displayfunc' => 'nice_date',
                    'dbdatatype' => 'timestamp',
                ]
            ),

            new rb_column_option(
                'base',
                'status_updated_at',
                get_string('status_updated_at', $this->lang_component),
                'base.status_updated_at',
                [
                    'displayfunc' => 'nice_datetime',
                    'dbdatatype' => 'timestamp',
                ]
            ),
            new rb_column_option(
                'base',
                'completed_at',
                get_string('completed_at', $this->lang_component),
                "CASE
                    WHEN base.status = '$completed_status_code' THEN base.status_updated_at
                    ELSE NULL
                END",
                [
                    'displayfunc' => 'nice_date',
                    'dbdatatype' => 'timestamp',
                ]
            ),
        ];

        $this->add_core_user_columns($column_options);
        $this->add_totara_job_columns($column_options);

        return $column_options;
    }

    /**
     * @inheritDoc
     */
    protected function define_filteroptions() {
        $filter_options = [
            new rb_filter_option(
                'base',
                'name',
                get_string('name', $this->lang_component),
                'text'
            ),
            new rb_filter_option(
                'base',
                'id_number',
                get_string('id_number', $this->lang_component),
                'text'
            ),
            new rb_filter_option(
                'base',
                'start_date',
                get_string('start_date', $this->lang_component),
                'date'
            ),
            new rb_filter_option(
                'base',
                'target_date',
                get_string('target_date', $this->lang_component),
                'date'
            ),
            new rb_filter_option(
                'base',
                'closed_at',
                get_string('closed_at', $this->lang_component),
                'date'
            ),
            new rb_filter_option(
                'base',
                'status',
                get_string('status', $this->lang_component),
                'select',
                [
                    'selectchoices' => goal_model::get_status_choices(),
                    'simplemode' => true,
                ]
            )

        ];

        $this->add_core_user_filters($filter_options);
        $this->add_totara_job_filters($filter_options, 'base', 'user_id');

        return $filter_options;
    }

    /**
     * Create goal data for unit tests.
     * @param totara_reportbuilder_column_test $testcase
     * @return void
     */
    public function phpunit_column_test_add_data(totara_reportbuilder_column_test $testcase): void {
        if (!PHPUNIT_TEST) {
            throw new coding_exception('phpunit_column_test_add_data() cannot be used outside of unit tests');
        }

        $user = \core_phpunit\testcase::getDataGenerator()->create_user();
        $goal = generator::instance()->create_goal(
            \perform_goal\testing\goal_generator_config::new([
                'user_id' => $user->id
            ])
        );
    }
}