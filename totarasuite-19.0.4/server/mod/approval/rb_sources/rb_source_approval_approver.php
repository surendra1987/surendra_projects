<?php
/**
 * This file is part of Totara Talent Experience Platform
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
 * @author Angela Kuznetsova <angela.kuznetsova@totara.com>
 * @package mod_approval
 */

use mod_approval\hook\column_options;
use mod_approval\model\assignment\approver_type\relationship;
use mod_approval\model\assignment\approver_type\user as user_approver;
use totara_core\advanced_feature;
use mod_approval\model\assignment\assignment_type\cohort;
use mod_approval\model\assignment\assignment_type\organisation;
use mod_approval\model\assignment\assignment_type\position;

defined('MOODLE_INTERNAL') || die();

class rb_source_approval_approver extends rb_base_source {

    public function __construct(rb_global_restriction_set $globalrestrictionset = null) {
        // Remember the active global restriction set.
        $this->globalrestrictionset = $globalrestrictionset;
        $this->base = '{approval_approver}';
        $this->sourcetitle = get_string('sourcetitle', 'rb_source_approval_approver');
        $this->sourcesummary = get_string('sourcesummary', 'rb_source_approval_approver');
        $this->sourcelabel = get_string('sourcelabel', 'rb_source_approval_approver');
        $this->joinlist = $this->define_joinlist();
        $this->columnoptions = $this->define_columnoptions();
        $this->filteroptions = $this->define_filteroptions();
        $this->defaultcolumns = $this->define_defaultcolumns();
        $this->requiredcolumns = $this->define_requiredcolumns();
        $this->defaultfilters = $this->define_defaultfilters();
        $this->paramoptions = $this->define_paramoptions();
        $this->usedcomponents[] = 'mod_approval';
        $this->sourcewhere = 'base.active = 1';
        parent::__construct();
    }

    /**
     * Define approval approver type join list
     *
     * @return array
     */
    protected function define_joinlist(): array {
        $joinlist = [
            new rb_join(
                'approval_level',
                'INNER',
                '{approval_workflow_stage_approval_level}',
                'approval_level.id = base.workflow_stage_approval_level_id',
                null,
                'base'
            ),
            new rb_join(
                'assignment',
                'INNER',
                '{approval}',
                'assignment.id = base.approval_id',
                null,
                'base'
            ),
            new rb_join(
                'workflow_stage',
                'INNER',
                '{approval_workflow_stage}',
                'workflow_stage.id = approval_level.workflow_stage_id',
                null,
                'approval_level'
            ),
            new rb_join(
                'workflow_version',
                'INNER',
                '{approval_workflow_version}',
                'workflow_version.id = workflow_stage.workflow_version_id',
                null,
                'workflow_stage'
            ),
            new rb_join(
                'workflow',
                'INNER',
                '{approval_workflow}',
                'workflow.id = workflow_version.workflow_id',
                null,
                'workflow_version'
            ),
        ];
        $user_type = user_approver::TYPE_IDENTIFIER;
        $this->add_core_user_tables($joinlist, 'base', 'identifier', 'iuser', "base.identifier = iuser.id AND base.type = $user_type");
        return $joinlist;
    }

    /**
     * Define approval approver type column options
     *
     * @return array
     */
    protected function define_columnoptions(): array {
        global $DB;
        $usednamefields = totara_get_all_user_name_fields_join('iuser', null, true);
        $allnamefields = totara_get_all_user_name_fields_join('iuser');
        $user_type = user_approver::TYPE_IDENTIFIER;
        $columnoptions = [
            new rb_column_option(
                'workflow',
                'id',
                get_string('workflow_id', 'rb_source_approval_approver'),
                'workflow.id',
                [
                    'dbdatatype' => 'integer',
                    'displayfunc' => 'integer',
                    'joins' => 'workflow',
                ]
            ),
            new rb_column_option(
                'workflow',
                'name',
                get_string('workflow_name', 'rb_source_approval_approver'),
                'workflow.name',
                [
                    'dbdatatype' => 'text',
                    'displayfunc' => 'format_string',
                    'joins' => 'workflow',
                ]
            ),
            new rb_column_option(
                'workflow_version',
                'id',
                get_string('workflow_version_id', 'rb_source_approval_approver'),
                'workflow_version.id',
                [
                    'dbdatatype' => 'integer',
                    'displayfunc' => 'integer',
                    'joins' => 'workflow_version',
                ]
            ),
            new rb_column_option(
                'workflow_stage',
                'name',
                get_string('workflow_stage_name', 'rb_source_approval_approver'),
                'workflow_stage.name',
                [
                    'dbdatatype' => 'text',
                    'displayfunc' => 'format_string',
                    'joins' => 'workflow_stage',
                ]
            ),
            new rb_column_option(
                'approval_level',
                'name',
                get_string('approval_level_name', 'rb_source_approval_approver'),
                'approval_level.name',
                [
                    'dbdatatype' => 'text',
                    'displayfunc' => 'format_string',
                    'joins' => 'approval_level',
                ]
            ),
            new rb_column_option(
                'approval_level',
                'id',
                get_string('approval_level_id', 'rb_source_approval_approver'),
                'approval_level.id',
                [
                    'dbdatatype' => 'integer',
                    'displayfunc' => 'integer',
                    'joins' => 'approval_level',
                ]
            ),
            new rb_column_option(
                'assignment',
                'assignment_type',
                get_string('assignment_type', 'rb_source_approval_approver'),
                'assignment.assignment_type',
                [
                    'displayfunc' => 'assignment_type',
                    'joins' => 'assignment',
                ]
            ),
            new rb_column_option(
                'assignment',
                'id_number',
                get_string('assignment_id_number', 'rb_source_approval_approver'),
                'assignment.id_number',
                [
                    'dbdatatype' => 'text',
                    'displayfunc' => 'format_string',
                    'joins' => 'assignment',
                ]
            ),
            new rb_column_option(
                'assignment',
                'name',
                get_string('assignment_name', 'rb_source_approval_approver'),
                'assignment.name',
                [
                    'dbdatatype' => 'text',
                    'displayfunc' => 'format_string',
                    'joins' => 'assignment',
                ]
            ),
            new rb_column_option(
                'approval_approver',
                'type',
                get_string('approver_type', 'rb_source_approval_approver'),
                'base.type',
                [
                    'displayfunc' => 'approver_type',
                ]
            ),
            new rb_column_option(
                'approval_approver',
                'name',
                get_string('approver_name', 'rb_source_approval_approver'),
                "CASE WHEN iuser.id IS NULL OR base.type <> $user_type THEN " . $DB->sql_cast_2char('base.identifier') . " ELSE " . $DB->sql_concat_join("' '", $usednamefields) .
                " END",
                [
                    'displayfunc' => 'approver_name',
                    'extrafields' => array_merge([
                        'approver_type_code' => 'base.type'
                    ], $allnamefields),
                    'joins' => 'iuser',
                ]
            ),
            new rb_column_option(
                'approval_approver',
                'username',
                get_string('approver_username', 'rb_source_approval_approver'),
                "CASE WHEN base.type != $user_type THEN " . $DB->sql_cast_2char('base.identifier') . " ELSE " . $DB->sql_cast_2char('iuser.username') . ' END',
                [
                    'displayfunc' => 'approver_username',
                    'extrafields' => [
                        'approver_type_code' => 'base.type',
                    ],
                    'joins' => 'iuser',
                ]
            ),
            new rb_column_option(
                'approval_approver',
                'identifier',
                get_string('approver_id', 'rb_source_approval_approver'),
                'base.identifier',
                [
                    'dbdatatype' => 'integer',
                    'displayfunc' => 'integer',
                ]
            ),
            new rb_column_option(
                'approval_approver',
                'ancestor_id',
                get_string('inherited_approver', 'rb_source_approval_approver'),
                'CASE WHEN base.ancestor_id IS NULL THEN 0 ELSE 1 END',
                [
                    'dbdatatype' => 'integer',
                    'displayfunc' => 'yes_or_no',
                ]
            ),
            new rb_column_option(
                'approval_approver',
                'created',
                get_string('approval_created', 'rb_source_approval_approver'),
                'base.created',
                [
                    'dbdatatype' => 'timestamp',
                    'displayfunc' => 'nice_datetime',
                ]
            ),
            new rb_column_option(
                'approval_approver',
                'updated',
                get_string('approval_updated', 'rb_source_approval_approver'),
                'base.updated',
                [
                    'dbdatatype' => 'timestamp',
                    'displayfunc' => 'nice_datetime',
                ]
            ),
        ];
        $column_hook = new column_options('approval_approver', $columnoptions);
        $column_hook->execute();
        $columnoptions = $column_hook->get_columns();
        $this->add_core_user_columns($columnoptions, 'iuser');
        return $columnoptions;
    }

    /**
     * Define approval approver type filter options
     *
     * @return array
     */
    protected function define_filteroptions(): array {
        $filteroptions = [
            new rb_filter_option(
                'workflow',
                'id',
                get_string('workflow_id', 'rb_source_approval_approver'),
                'number',
            ),
            new rb_filter_option(
                'workflow',
                'name',
                get_string('workflow_name', 'rb_source_approval_approver'),
                'text',
                [
                    'hiddenoperator' => [rb_filter_type::RB_FILTER_ISEMPTY, rb_filter_type::RB_FILTER_ISNOTEMPTY]
                ]
            ),
            new rb_filter_option(
                'workflow_stage',
                'name',
                get_string('workflow_stage_name', 'rb_source_approval_approver'),
                'text',
                [
                    'hiddenoperator' => [rb_filter_type::RB_FILTER_ISEMPTY, rb_filter_type::RB_FILTER_ISNOTEMPTY]
                ]
            ),
            new rb_filter_option(
                'assignment',
                'name',
                get_string('assignment_name', 'rb_source_approval_approver'),
                'text',
                [
                    'hiddenoperator' => [rb_filter_type::RB_FILTER_ISEMPTY, rb_filter_type::RB_FILTER_ISNOTEMPTY]
                ]
            ),
            new rb_filter_option(
                'assignment',
                'assignment_type',
                get_string('assignment_type', 'rb_source_approval_approver'),
                'select',
                [
                    'selectchoices' => [
                        cohort::get_code() => cohort::get_label(),
                        organisation::get_code() => organisation::get_label(),
                        position::get_code() => position::get_label(),
                    ],
                    'simplemode' => true
                ]
            ),
            new rb_filter_option(
                'assignment',
                'id_number',
                get_string('assignment_id_number', 'rb_source_approval_approver'),
                'text',
                [
                    'hiddenoperator' => [rb_filter_type::RB_FILTER_ISEMPTY, rb_filter_type::RB_FILTER_ISNOTEMPTY]
                ]
            ),
            new rb_filter_option(
                'approval_approver',
                'name',
                get_string('approver_name', 'rb_source_approval_approver'),
                'text',
                [
                    'hiddenoperator' => [rb_filter_type::RB_FILTER_ISEMPTY, rb_filter_type::RB_FILTER_ISNOTEMPTY]
                ]
            ),
            new rb_filter_option(
                'approval_approver',
                'type',
                get_string('approver_type', 'rb_source_approval_approver'),
                'select',
                [
                    'selectchoices' => [
                        relationship::get_code() => (new relationship())->label(),
                        user_approver::get_code() => (new user_approver())->label(),
                    ],
                    'simplemode' => true
                ]
            ),
            new rb_filter_option(
                'approval_approver',
                'ancestor_id',
                get_string('inherited_approver', 'rb_source_approval_approver'),
                'select',
                array(
                    'selectchoices' => array(0 => get_string('no'), 1 => get_string('yes')),
                    'simplemode' => true
                )
            ),
        ];
        $this->add_core_user_filters($filteroptions);
        return $filteroptions;
    }

    /**
     * Define default column options
     *
     * @return array
     */
    protected function define_defaultcolumns(): array {
        return static::get_default_columns();
    }

    /**
     * Define approval approver type default columns
     *
     * @return string[][]
     */
    public static function get_default_columns(): array {
        return [
            [
                'type' => 'workflow_stage',
                'value' => 'name',
                'heading' => get_string('workflow_stage_name', 'rb_source_approval_approver'),
                'rowheader' => true,
            ],
            [
                'type' => 'approval_level',
                'value' => 'name',
                'heading' => get_string('approval_level_name', 'rb_source_approval_approver'),
            ],
            [
                'type' => 'assignment',
                'value' => 'assignment_type',
                'heading' => get_string('assignment_type_name', 'rb_source_approval_approver'),
            ],
            [
                'type' => 'assignment',
                'value' => 'id_number',
                'heading' => get_string('assignment_id_number', 'rb_source_approval_approver'),
            ],
            [
                'type' => 'assignment',
                'value' => 'name',
                'heading' => get_string('assignment_name', 'rb_source_approval_approver'),
            ],
            [
                'type' => 'approval_approver',
                'value' => 'type',
                'heading' => get_string('approver_type', 'rb_source_approval_approver'),
            ],
            [
                'type' => 'approval_approver',
                'value' => 'name',
                'heading' => get_string('approver_name', 'rb_source_approval_approver'),
            ],
            [
                'type' => 'approval_approver',
                'value' => 'created',
                'heading' => get_string('approval_created', 'rb_source_approval_approver'),
            ],
            [
                'type' => 'approval_approver',
                'value' => 'updated',
                'heading' => get_string('approval_updated', 'rb_source_approval_approver'),
            ],
        ];
    }

    /**
     * Define default filter options
     *
     * @return array
     */
    protected function define_defaultfilters(): array {
        return static::get_default_filters();
    }

    /**
     * Define approval approver type default filters
     *
     * @return string[][]
     */
    public static function get_default_filters(): array {
        return [
            [
                'type' => 'workflow_stage',
                'value' => 'name'
            ],
            [
                'type' => 'assignment',
                'value' => 'assignment_type'
            ],
            [
                'type' => 'assignment',
                'value' => 'id_number',
            ],
            [
                'type' => 'assignment',
                'value' => 'name'
            ],
            [
                'type' => 'approval_approver',
                'value' => 'type'
            ],
            [
                'type' => 'approval_approver',
                'value' => 'name'
            ]
        ];
    }

    protected function define_paramoptions() {
        return [
            new rb_param_option(
                'workflow_id',
                'workflow.id',
                'workflow'
            ),
        ];
    }

    /**
     * Global restrictions
     *
     * @return bool
     */
    public function global_restrictions_supported(): bool {
        return true;
    }

    /**
     * Returns expected result for column_test.
     *
     * @param rb_column_option $columnoption
     * @return int
     */
    public function phpunit_column_test_expected_count($columnoption): int {
        if (!PHPUNIT_TEST) {
            throw new coding_exception('phpunit_column_test_expected_count() cannot be used outside of unit tests');
        }
        // Unit tests create a few test reports, so this source will find them.
        return 0;
    }

    /**
     * Hide this source if feature disabled or hidden.
     *
     * @return bool
     */
    public static function is_source_ignored(): bool {
        return advanced_feature::is_disabled('approval_workflows');
    }
}