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

namespace mod_approval\userdata;

use context;
use core\collection;
use mod_approval\entity\assignment\assignment_approver as assignment_approver_entity;
use mod_approval\model\assignment\assignment_approver as assignment_approver_model;
use totara_userdata\userdata\item;
use totara_userdata\userdata\export;
use totara_userdata\userdata\target_user;
use mod_approval\model\assignment\approver_type\user as user_approver;

class approver_user extends item {

    /**
     * @inheritDoc
     */
    public static function is_purgeable(int $user_status) {
        return true;
    }

    /**
     * @inheritDoc
     */
    public static function is_exportable() {
        return true;
    }

    /**
     * @inheritDoc
     */
    public static function is_countable() {
        return true;
    }

    /**
     * Is the given context level compatible with this item?
     * @return array
     */
    public static function get_compatible_context_levels(): array {
        return [
            CONTEXT_SYSTEM,
            CONTEXT_COURSECAT,
            CONTEXT_COURSE,
            CONTEXT_MODULE
        ];
    }

    /**
     * Execute user data purging for this item.
     * @param target_user $user
     * @param context $context restriction for purging e.g., system context for everything, course context for purging one course
     * @return int result self::RESULT_STATUS_SUCCESS, self::RESULT_STATUS_ERROR or self::RESULT_STATUS_SKIPPED
     */
    protected static function purge(target_user $user, context $context): int {

        $approver_models = static::get_approvers_records($user)->map_to(assignment_approver_model::class);
        foreach ($approver_models as $approver) {
            $approver->delete();
        }
        return self::RESULT_STATUS_SUCCESS;
    }

    /**
     * Count user data for this item.
     * @param target_user $user
     * @param context $context restriction for counting i.e., system context for everything and course context for course data
     * @return int amount of data or negative integer status code (self::RESULT_STATUS_ERROR or self::RESULT_STATUS_SKIPPED)
     */
    protected static function count(target_user $user, context $context): int {
        return static::get_approvers_records($user)->count();
    }

    /**
     * Export user data from this item.
     *
     * @param target_user $user* @param context $context restriction for exporting i.e., system context for everything and course context for course export
     * @return export|int result object or integer error code self::RESULT_STATUS_ERROR or self::RESULT_STATUS_SKIPPED
     */
    protected static function export(target_user $user, \context $context) {
        $export = new export();
        $export->data = static::get_approvers_records($user)->all();
        return $export;
    }

    protected static function get_approvers_records(target_user $approver): collection {
        return assignment_approver_entity::repository()
            ->where('identifier', $approver->id)
            ->where('type', user_approver::TYPE_IDENTIFIER)
            ->get();
    }

    /**
     * @return array
     */
    public static function get_fullname_string(): array {
        return ['user_data_item_approver_user', 'mod_approval'];
    }
}
