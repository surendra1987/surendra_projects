<?php
/*
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
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 *
 * @author Murali Nair <murali.nair@totara.com>
 * @author Chris Snyder <chris.snyder@totara.com>
 * @package perform_goal
 */

namespace perform_goal\controllers;

use context_system;
use core\date_format;
use core\entity\user;
use core\format;
use perform_goal\formatter\goal as goal_formatter;
use perform_goal\formatter\goal_raw_data as goal_raw_data_formatter;
use perform_goal\formatter\status as status_formatter;
use perform_goal\interactor\goal_interactor;
use perform_goal\model\goal;
use perform_goal\model\goal_raw_data;
use perform_goal\model\status\status;
use totara_core\advanced_feature;
use totara_core\dates\date_time_setting;
use totara_core\formatter\date_time_setting_formatter;
use totara_mvc\controller;

abstract class controller_base extends controller {
    // Page parameter.
    public const PARAM_TARGET_USER_ID = 'userid';

    /**
     * Logged on user
     */
    protected user $user;

    /**
     * User whose goal page this is.
     */
    protected user $target_user;

    /**
     * Interactor used by the controller
     */
    protected goal_interactor $interactor;

    /**
     * Constructor.
     *
     * @param goal_interactor $interactor associated goal interactor.
     */
    public function __construct(goal_interactor $interactor) {
        $logged_in_user = user::logged_in();
        $this->user = $logged_in_user;

        $target_user_id = $this->get_optional_param(
            self::PARAM_TARGET_USER_ID, null, PARAM_INT
        );

        $this->target_user = is_null($target_user_id)
            ? $logged_in_user
            : new user($target_user_id, true, true);

        $this->interactor = $interactor;

        parent::__construct();
    }

    /**
     * @inheritDoc
     */
    protected function authorize(): void {
        advanced_feature::require('perform_goals');

        if (!$this->interactor->can_view_personal_goals()) {
            print_error('accessdenied', 'admin');
        }
    }

    /**
     * Returns the goal properties for this controller. Note this is a subset of
     * the graphql goal type.
     *
     * @return array<string,mixed> these properties:
     *         int id: goal id
     *         string assignment_type: goal assignment type.
     *         string plugin_name: the goaltype plugin that implements the goal.
     *         float current_value: current progress value.
     *         string description: the goal description as an HTML string.
     *         string name: the goal name.
     *         string start_date: start day, month and year formatted according
     *         to the current user's locale.
     *         string status: goal status as ['id' => code, 'label' => label].
     *         string target_date: target day, month and year formatted according
     *         to the current user's locale.
     *         float target_value: target progress value.
     *         string updated_at:  day, month and year when the goal was last
     *         updated formatted according to the current user's locale.
     */
    protected function goal_properties(goal $goal): array {
        $formatter = new goal_formatter($goal, $goal->context);
        $date_format = date_format::FORMAT_DATELONG;

        return [
            'assignment_type' => $formatter->format('assignment_type'),
            'plugin_name' => $formatter->format('plugin_name'),
            'current_value' => $formatter->format('current_value'),
            'description' => $formatter->format('description', format::FORMAT_HTML),
            'id' => $formatter->format('id'),
            'name' => $formatter->format('name', format::FORMAT_PLAIN),
            'start_date' => $formatter->format('start_date', $date_format),
            'status' => $this->formatted_status($goal->status),
            'target_date' => $formatter->format('target_date', $date_format),
            'target_value' => $formatter->format('target_value'),
            'updated_at' => $formatter->format('updated_at', $date_format)
        ];
    }

    /**
     * Returns the raw data properties for this controller. Note this has the
     * same structure as the graphql goal_raw_data type.
     *
     * @return array<string,mixed> these properties:
     *         - mixed[array<string,mixed>] available_statuses: the statuses as
     *           a set of (id, label) key value pairs.
     *         - string description: the raw goal description eg if a description
     *           was stored as json string, then the json string is returned; if
     *           a description was stored as HTML, then the HTML is returned.
     *         - array<string,mixed> start_date: goal start date as formatted by
     *           self::convert_date_for_raw_data()
     *         - array<string,mixed> target_date: goal target date as formatted
     *           by self::convert_date_for_raw_data()
     */
    protected function raw_data_properties(goal $goal): array {
        $raw_data = new goal_raw_data($goal);
        $formatter = new goal_raw_data_formatter($raw_data, $raw_data->context);

        $available_statuses = array_map(
            fn(status $status): array => $this->formatted_status($status),
            $raw_data->available_statuses
        );

        return [
            'available_statuses' => $available_statuses,
            'description' => $formatter->format('description', format::FORMAT_RAW),
            'start_date' => $this->convert_date_for_raw_data(
                $raw_data->start_date
            ),
            'target_date' =>  $this->convert_date_for_raw_data(
                $raw_data->target_date
            )
        ];
    }

    /**
     * Returns the properties for the given timestamp.
     *
     * @param int $timestamp the timestamp to format
     *
     * @return array<string,mixed> these properties:
     *         string iso: the timestamp in iso date format.
     */
    protected function convert_date_for_raw_data(int $timestamp): array {
        $formatter = new date_time_setting_formatter(
            new date_time_setting($timestamp), context_system::instance()
        );

        return [
            'iso' => $formatter->format('iso')
            // timezone is not required currently.
        ];
    }

    /**
     * Returns the properties for the given timestamp.
     *
     * @param status $status
     * @return array<string,mixed> these properties:
     *         string iso: the timestamp in iso date format.
     */
    protected function formatted_status(status $status): array {
        $formatter = new status_formatter($status, context_system::instance());

        return [
            'id' => $formatter->format('id'),
            'label' => $formatter->format('label'),
        ];
    }
}