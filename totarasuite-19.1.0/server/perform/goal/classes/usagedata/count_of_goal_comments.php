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

namespace perform_goal\usagedata;

use totara_comment\entity\comment as totara_comment;
use tool_usagedata\export;
use perform_goal\settings_helper;
use perform_goal\totara_comment\comment_resolver;

class count_of_goal_comments implements export {

    private const RESULT_KEY_TOTAL_GOAL_COMMENTS_COUNT = 'total_goal_comments_count';

    /**
     * @inheritDoc
     */
    public function get_summary(): string {
        return get_string('count_of_goal_comments_summary', 'perform_goal');
    }

    /**
     * @inheritDoc
     */
    public function get_type(): int {
        return export::TYPE_OBJECT;
    }

    /**
     * @inheritDoc
     */
    public function export(): array {
        $result_comments_count = totara_comment::repository()
            ->select(['id'])
            // 'perform_goal'
            ->where('component', settings_helper::get_component())
            // 'goal_comment'
            ->where('area', comment_resolver::AREA)
            ->where_null('timedeleted')
            ->count();

        return [
            self::RESULT_KEY_TOTAL_GOAL_COMMENTS_COUNT => $result_comments_count,
        ];
    }
}
