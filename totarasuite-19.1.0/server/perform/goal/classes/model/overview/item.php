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
 * @author Murali Nair <murali.nair@totara.com>
 * @package perform_goal
 */

namespace perform_goal\model\overview;

use coding_exception;
use core\entity\user;
use DateTimeImmutable;
use perform_goal\controllers\goals_overview;
use perform_goal\interactor\goal_interactor;
use perform_goal\model\goal;
use perform_goal\model\status\status_helper;

/**
 * Perform goal overview item data.
 *
 * @property-read int $achievement_date target date
 * @property-read int $assignment_date start date
 * @property-read null|string $description goal description
 * @property-read array<string,mixed> $due target date details
 * @property-read goal $goal the underlying goal. This is for testing purposes.
 * @property-read int $id goal id
 * @property-read array<string,mixed> $last_update last update details
 * @property-read string $name goal name
 * @property-read null|string $url link to perform goal
 * @property-read int $user_id goal subject.
 */
class item {
    /**
     * @var goal perform goal.
     */
    private goal $goal;

    /**
     * @var string goal url.
     */
    private string $url;

    /**
     * Default constructor.
     *
     * @param goal $goal perform goal.
     */
    public function __construct(goal $goal) {
        $this->goal = $goal;

        $user = user::logged_in();

        $this->url = goals_overview::create_url(
            $this->goal->id,
            $user && (int)$user->id !== (int)$this->goal->user_id
                ? $this->goal->user_id
                : null
        )->out(false);
    }

    /**
     * Magic attribute getter
     *
     * @param string $field field whose value is to returned.
     *
     * @return mixed the field value.
     *
     * @throws coding_exception if the field name is unknown.
     */
    public function __get(string $field) {
        switch ($field) {
            case 'id':
            case 'name':
            case 'description':
            case 'user_id':
                return $this->goal->$field;

            case 'achievement_date':
                return $this->goal->closed_at;

            case 'assignment_date':
                return $this->goal->start_date;

            case 'due':
                return $this->due_details();

            case 'goal':
                return $this->goal;

            case 'last_update':
                return $this->last_update_details();

            case 'url':
                return goal_interactor::from_goal($this->goal)->can_view()
                    ? $this->url
                    : null;

            default:
                throw new coding_exception(
                    'Unknown ' . self::class . " attribute: $field"
                );
        }
    }

    /**
     * Formulates details of when the goal is due.
     *
     * @return array<string,mixed> the due details with these fields:
     *         - [int] 'date': the goal target date
     *         - [bool] 'due_soon': if the goal is due soon
     *         - [bool] 'overdue': if the goal is overdue
     */
    private function due_details(): array {
        $now = time();
        $week_from_today = $now + WEEKSECS;

        $date = (new DateTimeImmutable())
            ->setTimestamp($this->goal->target_date)
            ->setTime(23, 59, 59)
            ->getTimestamp();

        $is_completed = in_array(
            $this->goal->status::get_code(),
            status_helper::achieved_status_codes()
        );

        $overdue = $is_completed ? false : ($now > $date);
        $due_soon = $is_completed
            ? false
            : ($date >= $now && $date <= $week_from_today);

        return [
            'date' => $date, 'due_soon' => $due_soon, 'overdue' => $overdue
        ];
    }

    /**
     * Formulates details of when the goal was last updated.
     *
     * @return array<string,mixed> the last update details with these fields:
     *         - [int] 'date': the goal was last updated
     *         - [string] 'description': what changed in the last update
     */
    private function last_update_details(): array {
        $status = $this->goal->status::get_label();
        $status_ts = $this->goal->status_updated_at;

        $value = number_format($this->goal->current_value, 1);
        $value_ts = $this->goal->current_value_updated_at;

        $goal_updated_at_ts = $this->goal->updated_at;

        $args = null;
        $lang_string = null;
        $date = null;

        switch (true) {
            case $goal_updated_at_ts > $status_ts && $goal_updated_at_ts > $value_ts:
                // This is a special case when a goal has had its 'updated_at' field set to the latest value, i.e. it's
                // higher than the other date fields. An example for this would when be a goal_task got added to the goal.
                $date = $goal_updated_at_ts;
                $lang_string = 'overview_last_update_descr_goal_updated_at';
                break;

            case abs($status_ts - $value_ts) < 10:
                $date = $status_ts;
                $lang_string = 'overview_last_update_descr_status_and_value';
                $args = (object) [
                    'status' => $status,
                    'value' => $value,
                    'target' => number_format($this->goal->target_value, 1)
                ];
                break;

            case $status_ts > $value_ts:
                $date = $status_ts;
                $lang_string = 'overview_last_update_descr_status';
                $args = $status;
                break;

            default:
                $date = $value_ts;
                $lang_string = 'overview_last_update_descr_value';
                $args = $value;
        }

        return [
            'date' => $date,
            'description' => get_string($lang_string, 'perform_goal', $args)
        ];
    }
}