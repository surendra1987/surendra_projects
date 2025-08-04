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
 * @author Navjeet Singh <navjeet.singh@totara.com>
 * @author Ning Zhou <ning.zhou@totara.com>
 * @package mod_approval
 */

use container_approval\approval;
use core\orm\query\order;
use mod_approval\entity\assignment\assignment as assignment_entity;
use mod_approval\entity\workflow\workflow as workflow_entity;
use mod_approval\hook\column_options;
use mod_approval\model\workflow\workflow as workflow_model;
use mod_approval\model\assignment\assignment_type\provider as assignment_type_provider;
use totara_core\advanced_feature;

/**
 * Override role assignments
 */
class rb_source_approval_override_role_assignment extends rb_base_source {
    /**
     * @var array $context_query
     */
    private array $context_query;

    /**
     * @inheritDoc
     */
    public function __construct(rb_global_restriction_set $globalrestrictionset = null) {
        // Remember the active global restriction set.
        $this->globalrestrictionset = $globalrestrictionset;
        $this->base = '{role_assignments}';
        $this->sourcetitle   = get_string('sourcetitle', 'rb_source_approval_override_role_assignment');
        $this->sourcesummary = get_string('sourcesummary', 'rb_source_approval_override_role_assignment');
        $this->sourcelabel   = get_string('sourcelabel', 'rb_source_approval_override_role_assignment');

        $this->joinlist        = $this->define_joinlist();
        $this->columnoptions   = $this->define_columnoptions();
        $this->defaultcolumns  = $this->define_defaultcolumns();
        $this->filteroptions   = $this->define_filteroptions();
        $this->defaultfilters  = $this->define_defaultfilters();
        $this->paramoptions    = $this->define_paramoptions();
        $this->requiredjoins   = [$this->get_join('approval_workflow')];
        $this->usedcomponents[] = 'mod_approval';

        $this->sourcewhere = "base.component = '' AND base.itemid = 0";

        $this->cacheable = false;

        parent::__construct();
    }

    /**
     * Define role assignments join list
     * @return array
     */
    protected function define_joinlist() {
        $joinlist = [
            new rb_join(
                'role',
                'INNER',
                '{role}',
                'role.id = base.roleid',
                REPORT_BUILDER_RELATION_ONE_TO_MANY,
                'base'
            ),
            new rb_join(
                'context',
                'INNER',
                '{context}',
                'context.id = base.contextid',
                REPORT_BUILDER_RELATION_ONE_TO_MANY,
                'base'
            ),
            new rb_join(
                'course_module',
                'INNER',
                '{course_modules}',
                'course_module.id = context.instanceid AND context.contextlevel = ' . CONTEXT_MODULE,
                REPORT_BUILDER_RELATION_ONE_TO_ONE,
                'context'
            ),
            new rb_join(
                'approval',
                'INNER',
                '{approval}',
                'approval.id = course_module.instance',
                REPORT_BUILDER_RELATION_ONE_TO_ONE,
                'course_module'
            ),
            new rb_join(
                'approval_workflow',
                'INNER',
                '{approval_workflow}',
                'approval_workflow.course_id = course_module.course',
                REPORT_BUILDER_RELATION_ONE_TO_ONE,
                'course_module'
            ),
        ];

        $this->add_core_user_tables($joinlist, 'base', 'userid');
        return $joinlist;
    }

    /**
     * define column options
     * @return array
     * @throws coding_exception
     */
    protected function define_columnoptions() {
        $columnoptions = [
            new rb_column_option(
                'role',
                'shortname',
                get_string('role_shortname', 'rb_source_approval_override_role_assignment'),
                'role.shortname',
                [
                    'joins' => 'role',
                    'dbdatatype' => 'text',
                    'displayfunc' => 'format_string',
                ]
            ),
            new rb_column_option(
                'role',
                'id',
                get_string('role_id', 'rb_source_approval_override_role_assignment'),
                'role.id',
                [
                    'joins' => 'role',
                    'dbdatatype' => 'integer',
                    'displayfunc' => 'format_string',
                ]
            ),
            new rb_column_option(
                'role',
                'name',
                get_string('role_name', 'rb_source_approval_override_role_assignment'),
                'role.name',
                [
                    'joins' => 'role',
                    'dbdatatype' => 'text',
                    'displayfunc' => 'role_name',
                    'extrafields' => [
                        'base_roleid' => 'base.roleid',
                    ],
                ]
            ),
            new rb_column_option(
                'approval_workflow',
                'name',
                get_string('workflow_name', 'rb_source_approval_override_role_assignment'),
                'approval_workflow.name',
                [
                    'joins' => 'approval_workflow',
                    'dbdatatype' => 'text',
                    'displayfunc' => 'format_string',
                ]
            ),
            new rb_column_option(
                'approval',
                'assignment_type',
                get_string('assignment_type', 'rb_source_approval_override_role_assignment'),
                'approval.assignment_type',
                [
                    'joins' => 'approval',
                    'dbdatatype' => 'text',
                    'displayfunc' => 'assignment_type',
                ]
            ),
            new rb_column_option(
                'approval',
                'name',
                get_string('assignment_name', 'rb_source_approval_override_role_assignment'),
                'approval.name',
                [
                    'joins' => 'approval',
                    'dbdatatype' => 'text',
                    'displayfunc' => 'format_string',
                ]
            ),
            new rb_column_option(
                'approval',
                'assignment_identifier',
                get_string('assignment_identifier', 'rb_source_approval_override_role_assignment'),
                'approval.assignment_identifier',
                [
                    'joins' => 'approval',
                    'dbdatatype' => 'integer',
                    'displayfunc' => 'format_string',
                ]
            ),
            new rb_column_option(
                'approval',
                'status',
                get_string('status', 'rb_source_approval_override_role_assignment'),
                'approval.status',
                [
                    'joins' => 'approval',
                    'dbdatatype' => 'integer',
                    'displayfunc' => 'form_version_status',
                ]
            ),
            new rb_column_option(
                'approval',
                'is_default',
                get_string('is_default', 'rb_source_approval_override_role_assignment'),
                'approval.is_default',
                [
                    'joins' => 'approval',
                    'dbdatatype' => 'integer',
                    'displayfunc' => 'assignment_is_default',
                ]
            ),
            new rb_column_option(
                'approval',
                'id_number',
                get_string('assignment_id_number', 'rb_source_approval_override_role_assignment'),
                'approval.id_number',
                [
                    'joins' => 'approval',
                    'dbdatatype' => 'text',
                    'displayfunc' => 'format_string',
                ]
            ),
            new rb_column_option(
                'approval',
                'id',
                get_string('assignment_id', 'rb_source_approval_override_role_assignment'),
                'approval.id',
                [
                    'joins' => 'approval',
                    'dbdatatype' => 'integer',
                    'displayfunc' => 'format_string',
                ]
            ),
            new rb_column_option(
                'approval_workflow',
                'course_id',
                get_string('course_id', 'rb_source_approval_override_role_assignment'),
                'approval_workflow.course_id',
                [
                    'joins' => 'approval_workflow',
                    'dbdatatype' => 'integer',
                    'displayfunc' => 'format_string',
                ]
            ),
            new rb_column_option(
                'approval_workflow',
                'id',
                get_string('workflow_id', 'rb_source_approval_override_role_assignment'),
                'approval_workflow.id',
                [
                    'joins' => 'approval_workflow',
                    'dbdatatype' => 'integer',
                    'displayfunc' => 'format_string',
                ]
            ),
            new rb_column_option(
                'base',
                'context_id',
                get_string('context_id', 'rb_source_approval_override_role_assignment'),
                'base.contextid',
                [
                    'joins' => 'base',
                    'dbdatatype' => 'integer',
                    'displayfunc' => 'format_string',
                ]
            ),
            new rb_column_option(
                'approval',
                'assignment_type_id',
                get_string('assignment_type_id', 'rb_source_approval_override_role_assignment'),
                'approval.assignment_type',
                [
                    'joins' => 'approval',
                    'dbdatatype' => 'integer',
                    'displayfunc' => 'format_string',
                ]
            ),
        ];
        $column_hook = new column_options('approval_override_role_assignment', $columnoptions);
        $column_hook->execute();
        $columnoptions = $column_hook->get_columns();
        $this->add_core_user_columns($columnoptions);
        return $columnoptions;
    }

    /**
     * Define default column options
     *
     * @return array
     */
    protected function define_defaultcolumns() {
        return [
            [
                'type' => 'user',
                'value' => 'fullname',
                'rowheader' => true,
            ],
            [
                'type' => 'approval_workflow',
                'value' => 'name',
            ],
            [
                'type' => 'role',
                'value' => 'name',
            ],
            [
                'type' => 'approval',
                'value' => 'name',
            ],
            [
                'type' => 'approval',
                'value' => 'assignment_type',
            ],
            [
                'type' => 'approval',
                'value' => 'id_number',
            ],
            [
                'type' => 'approval',
                'value' => 'assignment_type_id',
                'hidden' => true,
            ],
            [
                'type' => 'user',
                'value' => 'username',
                'hidden' => true,
            ],
            [
                'type' => 'role',
                'value' => 'shortname',
                'hidden' => true,
            ],
        ];
    }

    /**
     * Define filter options
     *
     * @return array
     * @throws coding_exception
     * @throws dml_exception
     */
    protected function define_filteroptions() {
        $filteroptions = [];
        $filteroptions[] = $this->get_workflow_filter();
        $filteroptions[] = $this->get_assignment_name();
        $filteroptions[] = $this->get_assignment_type();
        $filteroptions[] = $this->get_role_name();
        $filteroptions[] = $this->get_approval_id_number();
        $this->add_core_user_filters($filteroptions);

        return $filteroptions;
    }

    /**
     * Filter by workflow
     *
     * @return rb_filter_option
     * @throws coding_exception
     */
    private function get_workflow_filter(): rb_filter_option {
        $workflows = workflow_entity::repository()
            ->order_by('name', order::DIRECTION_ASC)
            ->get()
            ->map(function (workflow_entity $workflow) {
                return workflow_model::load_by_entity($workflow)->name;
            })->all(true);

        return new rb_filter_option(
            'approval_workflow',
            'name',
            get_string('workflow_name', 'rb_source_approval_override_role_assignment'),
            'correlated_subquery_select',
            [
                'simplemode' => true,
                'cachingcompatible' => false,
                'selectchoices' => $workflows,
                'searchfield' => 'approval_workflow.id',
                'subquery' => "%2\$s",
            ],
            'approval_workflow.course_id',
            'approval_workflow'
        );
    }

    /**
     * Filter by assignment name
     *
     * @return rb_filter_option
     */
    private function get_assignment_name(): rb_filter_option {
        return new rb_filter_option(
            'approval',
            'name',
            get_string('assignment_name', 'rb_source_approval_override_role_assignment'),
            'text',
            [],
            'approval.name',
            'approval'
        );
    }

    /**
     * Filter by approval Id Number
     *
     * @return rb_filter_option
     */
    private function get_approval_id_number(): rb_filter_option {
        return new rb_filter_option(
            'approval',
            'id_number',
            get_string('assignment_id_number', 'rb_source_approval_override_role_assignment'),
            'text',
            [],
            'approval.id_number',
            'approval'
        );
    }

    /**
     * Filter by assignment type
     *
     * @return rb_filter_option
     * @throws \mod_approval\exception\model_exception
     * @throws coding_exception
     */
    private function get_assignment_type(): rb_filter_option {
        $assignment_types = [];
        $assignments = assignment_entity::repository()
            ->select('assignment_type')
            ->group_by('assignment_type')
            ->get();

        foreach ($assignments as $assignment) {
            $assignment_type_provider = assignment_type_provider::get_by_code($assignment->assignment_type);
            $assignment_types[$assignment->assignment_type] = $assignment_type_provider::get_label();
        }

        asort($assignment_types);

        return new rb_filter_option(
            'approval',
            'assignment_type',
            get_string('assignment_type', 'rb_source_approval_override_role_assignment'),
            'correlated_subquery_select',
            [
                'simplemode' => true,
                'cachingcompatible' => false,
                'selectchoices' => $assignment_types,
                'searchfield' => 'approval.assignment_type',
                'subquery' => "%2\$s",
            ],
            'approval.assignment_type',
            'approval'
        );
    }

    /**
     * Filter by role name
     *
     * @return rb_filter_option
     * @throws coding_exception
     * @throws dml_exception
     */
    private function get_role_name(): rb_filter_option {
        global $DB;
        $role_names = [];
        $roles = [];
        $context_query = $this->get_context_query();
        if ($context_query) {
            $sql_query = "SELECT r.id, r.shortname, r.name
                        FROM {role} r
                        LEFT JOIN {role_assignments} base ON base.roleid = r.id AND base.component = '' AND base.itemid = 0
                        WHERE EXISTS (" . $context_query['query'] . ")
                        GROUP BY r.id, r.shortname, r.name";
            $roles = $DB->get_records_sql($sql_query, $context_query['params']);
        }

        if (!empty($roles)) {
            $options = [];
            foreach (role_get_names() as $role) {
                $options[$role->id] = $role->localname;
            }

            foreach ($roles as $role_assignment) {
                $role_names[$role_assignment->id] = !empty($role_assignment->name) ? $role_assignment->name : $options[$role_assignment->id] ?? $role_assignment->shortname;
            }

            asort($role_names);
        }

        return new rb_filter_option(
            'role',
            'role_name',
            get_string('role_name', 'rb_source_approval_override_role_assignment'),
            'correlated_subquery_select',
            [
                'simplemode' => true,
                'cachingcompatible' => false,
                'selectchoices' => $role_names,
                'searchfield' => 'base.roleid',
                'subquery' => "%2\$s",
            ],
            'base.roleid',
            'base'
        );
    }

    protected function define_defaultfilters() {
        return [
            [
                'type' => 'approval_workflow',
                'value' => 'name',
            ],
            [
                'type' => 'role',
                'value' => 'role_name',
            ],
            [
                'type' => 'approval',
                'value' => 'assignment_type',
            ],
            [
                'type' => 'approval',
                'value' => 'name',
            ],
            [
                'type' => 'approval',
                'value' => 'id_number',
            ],
            [
                'type' => 'user',
                'value' => 'fullname',
            ],
        ];
    }

    /**
     * @inheritDoc
     */
    protected function define_paramoptions() {
        return [
            new rb_param_option(
                'workflow_id',
                'approval_workflow.id',
                'approval_workflow'
            )
        ];
    }

    /**
     * Global restrictions
     *
     * @return bool
     */
    public function global_restrictions_supported() {
        return true;
    }

    /**
     * Returns expected result for column_test.
     *
     * @param rb_column_option $columnoption
     * @return int
     */
    public function phpunit_column_test_expected_count($columnoption) {
        if (!PHPUNIT_TEST) {
            throw new coding_exception('phpunit_column_test_expected_count() cannot be used outside of unit tests');
        }
        // Unit tests create a few test reports, so this source will find them.
        return 0;
    }

    /**
     * Hide this source if feature disabled or hidden.
     * @return bool
     */
    public static function is_source_ignored() {
        return advanced_feature::is_disabled('approval_workflows');
    }

    /**
     * Get query string and query parameter for retrieving all workflows context ids
     *
     * @return array
     */
    private function get_context_query() {
        if (empty($this->context_query)) {
            $sql = 'SELECT 1 FROM {context} c
                    INNER JOIN {course_modules} cm ON cm.id = c.instanceid AND c.contextlevel = :contextlevel
                        INNER JOIN {approval_workflow} aw ON aw.course_id = cm.course
                    WHERE aw.id IS NOT NULL AND c.id=base.contextid';
            $query_params = [
                'contextlevel' => CONTEXT_MODULE,
                'category_name' => approval::DEFAULT_CATEGORY_NAME
            ];

            $this->context_query = [
                'query' => $sql,
                'params' => $query_params,
            ];
        }
        return $this->context_query;
    }
}