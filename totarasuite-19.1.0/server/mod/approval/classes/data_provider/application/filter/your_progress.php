<?php
/**
 * This file is part of Totara Learn
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
 * @author Chris Snyder <chris.snyder@totaralearning.com>
 * @package mod_approval
 */

namespace mod_approval\data_provider\application\filter;

use coding_exception;
use core\orm\entity\filter\filter;
use core\orm\query\builder;
use invalid_parameter_exception;
use mod_approval\entity\application\application;
use mod_approval\entity\application\application_action;
use mod_approval\entity\assignment\assignment_approver;
use mod_approval\model\application\action\approve;
use mod_approval\model\application\action\reject;
use mod_approval\model\assignment\approver_type\user;
use mod_approval\model\assignment\approver_type\relationship;
use totara_job\entity\job_assignment;

/**
 * Filter by your progress - PENDING, APPROVED, or REJECTED
 */
class your_progress extends filter {

    /**
     * @var string
     */
    protected $application_table_alias;

    /**
     * @var int
     */
    protected $user_id;

    /**
     * @param int $user_id
     * @param string $application_table_alias
     */
    public function __construct(int $user_id, string $application_table_alias = 'application') {
        parent::__construct([]);
        $this->user_id = $user_id;
        $this->application_table_alias = $application_table_alias;
    }

    /**
     * @inheritDoc
     */
    public function apply(): void {
        if (is_array($this->value)) {
            throw new coding_exception('overall progress filter requires a single value');
        }

        $filter_method_name = $this->get_filter_function_name($this->value);

        if (!method_exists($this, $filter_method_name)) {
            throw new invalid_parameter_exception('Could not find the filter function according to the given value');
        }

        $this->$filter_method_name();
    }

    /**
     * Get filter function name by given value
     *
     * @param string $value
     * @return string
     */
    private function get_filter_function_name(string $value): string {
        $value =  strtolower($value);
        if ($value === 'n/a') {
            $value = 'na';
        }
        return 'filter_' . $value;
    }

    /**
     * Apply approved filter to main query
     *
     * @return void
     */
    private function filter_approved():void {
        $this->builder->join(
            [application_action::TABLE, 'a_action'],
            $this->application_table_alias . '.id',
            '=',
            'a_action.application_id'
        );
        $this->builder->where('a_action.user_id', '=', $this->user_id);
        $this->builder->where('a_action.code', '=', approve::get_code());
    }

    /**
     * Apply rejected filter to main query
     *
     * @return void
     */
    private function filter_rejected():void {
        $this->builder->join(
            [application_action::TABLE, 'a_action'],
            $this->application_table_alias . '.id',
            '=',
            'a_action.application_id'
        );
        $this->builder->where('a_action.user_id', '=', $this->user_id);
        $this->builder->where('a_action.code', '=', reject::get_code());
    }

    /**
     * Apply pending filter to main query
     *
     * @return void
     */
    private function filter_pending(): void {
        $this->builder->join([assignment_approver::TABLE, 'approver'], function (builder $joining) {
            $joining->where_field(
                $this->application_table_alias . '.current_approval_level_id',
                '=',
                'approver.workflow_stage_approval_level_id'
            )
                ->where_field('approver.approval_id', '=', $this->application_table_alias . '.approval_id')
                ->where('approver.active', '=', true);
        });
        $this->builder->left_join(
            [job_assignment::TABLE, 'ja'],
            $this->application_table_alias . '.job_assignment_id',
            '=',
            'ja.id'
        );
        $this->builder->left_join([job_assignment::TABLE, 'managerja'], 'ja.managerjaid', '=', 'managerja.id');
        $this->builder->left_join([job_assignment::TABLE, 'tempmanagerja'], function (builder $joining) {
            $joining->where_field('ja.tempmanagerjaid', '=', 'tempmanagerja.id')
                ->where('ja.tempmanagerexpirydate', '>', time());
        });
        // Application current_level_id is equal to a level where you are an approver.
        $this->builder->where(function (builder $where){
            $where->where('approver.type', '=', user::TYPE_IDENTIFIER)
                ->where('approver.identifier', '=', $this->user_id);
        });
        // OR application current level is equal to a manager approver level, and you are the applicant's
        // manager or temporary manager.
        $this->builder->or_where(function (builder $where){
            $where->where('approver.type', '=', relationship::TYPE_IDENTIFIER)
                ->where(function (builder $subwhere){
                    $subwhere->where('managerja.userid', '=', $this->user_id)
                        ->or_where('tempmanagerja.userid', '=', $this->user_id);
                });
        });
    }

    /**
     * Apply N/A filter to main query
     * the query will look like following
     *
     * AND NOT EXISTS (
     * SELECT application1.id FROM mdl_approval_application AS application1
     * LEFT JOIN mdl_approval_approver AS approver1 ON  application1.current_approval_level_id=approver1.workflow_stage_approval_level_id
     * LEFT JOIN mdl_job_assignment AS ja ON application1.job_assignment_id=ja.id
     * LEFT JOIN mdl_job_assignment AS managerja ON ja.managerjaid=managerja.id AND ja.managerjaid IS NOT NULL
     * LEFT JOIN mdl_job_assignment AS tempmanagerja ON ja.tempmanagerjaid=tempmanagerja.id AND ja.tempmanagerexpirydate>1726042164 AND ja.tempmanagerjaid IS NOT NULL
     * WHERE application1.user_id<>99992 AND
     * approver1.active=1 AND
     * (approver1.type=1 AND (managerja.userid=99992 OR tempmanagerja.userid=99992)) AND
     * application1.id=application.id
     * )
     * AND NOT EXISTS (
     * SELECT application2.id FROM mdl_approval_application AS application2
     * LEFT JOIN mdl_approval_approver AS approver2 ON application2.current_approval_level_id=approver2.workflow_stage_approval_level_id
     * WHERE application2.user_id<>99992 AND
     * approver2.active=1 AND
     * (approver2.type=2 AND approver2.identifier=99992) AND
     * application2.id=application.id
     * )
     * AND action.id IS NULL
     * OR (action.code<>1 AND action.code<>0)
     * 
     * @return void
     */
    private function filter_na():void {
        $this->builder->left_join([application_action::TABLE, 'a_action'], function (builder $joining) {
            $joining->where_field($this->application_table_alias . '.id', '=', 'a_action.application_id')
                ->where('a_action.user_id', '=', $this->user_id);
        });

        $this->builder->where(function (builder $where) {
            $application_table_alias = 'sub_application';
            $sub_builder = builder::table(application::TABLE)
                ->select('id')
                ->as($application_table_alias)
                ->left_join([assignment_approver::TABLE, 'approver'], $application_table_alias.'.current_approval_level_id', '=', 'approver.workflow_stage_approval_level_id')
                ->left_join([job_assignment::TABLE, 'ja'], $application_table_alias.'.job_assignment_id', '=', 'ja.id')
                ->left_join([job_assignment::TABLE, 'managerja'], 'ja.managerjaid', '=', 'managerja.id')
                ->left_join([job_assignment::TABLE, 'tempmanagerja'], function (builder $joining) {
                    $joining->where_field('ja.tempmanagerjaid', '=', 'tempmanagerja.id')
                        ->where('ja.tempmanagerexpirydate', '>', time());
                })
                ->where($application_table_alias.'.user_id', '<>', $this->user_id)
                ->where('approver.active', '=', true)
                ->where(function (builder $where) {
                    $where->where(function (builder $subwhere) {
                        $subwhere->where('approver.type', '=', relationship::TYPE_IDENTIFIER)
                            ->where(function (builder $subsubwhere) {
                                $subsubwhere->where('managerja.userid', '=', $this->user_id)
                                    ->or_where('tempmanagerja.userid', '=', $this->user_id);
                            });
                    });
                })
                ->where_field('application.id', '=', $application_table_alias.'.id');

            $where->where('', '!exists', $sub_builder);
        });

        $this->builder->where(function (builder $where) {
            $application_table_alias = 'sub_application1';
            $approver_table_alias = 'approver1';
            $sub_builder = builder::table(application::TABLE)
                ->select('id')
                ->as($application_table_alias)
                ->left_join([assignment_approver::TABLE, $approver_table_alias], $application_table_alias.'.current_approval_level_id', '=', $approver_table_alias.'.workflow_stage_approval_level_id')
                ->where($application_table_alias.'.user_id', '<>', $this->user_id)
                ->where($approver_table_alias.'.active', '=', true)
                ->where(function (builder $where) use ($approver_table_alias) {
                    $where->where(function (builder $subwhere) use ($approver_table_alias) {
                        $subwhere->where($approver_table_alias.'.type', '=', user::TYPE_IDENTIFIER)
                            ->where($approver_table_alias.'.identifier', '=', $this->user_id);
                    });
                })
                ->where_field('application.id', '=', $application_table_alias.'.id');

            $where->where('', '!exists', $sub_builder);
        });

        $this->builder->where_null('a_action.id');
        $this->builder->or_where(function (builder $orwhere) {
            $orwhere->where('a_action.code', '!=', approve::get_code())
                ->where('a_action.code', '!=', reject::get_code());
        });
    }
}