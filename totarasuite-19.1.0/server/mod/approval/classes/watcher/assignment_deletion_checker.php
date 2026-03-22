<?php
/**
 * This file is part of Totara Learn
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
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 *
 * @author Angela Kuznetsova <angela.kuznetsova@totara.com>
 * @package mod_approval
 */

namespace mod_approval\watcher;

use coding_exception;
use hierarchy;
use mod_approval\entity\assignment\assignment;
use mod_approval\model\assignment\assignment_type\cohort as assignment_cohort;
use mod_approval\model\assignment\assignment_type\organisation;
use mod_approval\model\assignment\assignment_type\position;
use mod_approval\model\status;
use totara_cohort\hook\pre_delete_cohort_check;
use totara_hierarchy\hook\pre_delete_framework_check;
use totara_hierarchy\hook\pre_delete_item_check;

class assignment_deletion_checker {

    /**
     * Determine whether the hierarchy's items are used as approval workflow assignments
     *
     * @param pre_delete_framework_check $hook
     * @return void
     */
    public static function can_hierarchy_be_deleted(pre_delete_framework_check $hook): void {
        $prefix = $hook->get_prefix();
        if (!in_array($prefix, ['organisation', 'position'])) {
            return;
        }
        $hierarchy = hierarchy::load_hierarchy($prefix);

        $assignment_type = $prefix == 'organisation'
            ? organisation::get_code()
            : position::get_code();

        $assignments_count = assignment::repository()
            ->where('assignment_type', $assignment_type)
            ->where_raw(
                sprintf("assignment_identifier in (SELECT id from {%s} where frameworkid = :frameworkid)", $hierarchy->shortprefix),
                ['frameworkid' => $hook->get_id()]
            )
            ->where('status', '!=', status::ARCHIVED)
            ->count();

        if ($assignments_count > 0) {
            $identifier = "cannotdelete" . $prefix . "framework";
            $hook->add_reason(get_string($identifier, 'mod_approval'));
        }
    }

    /**
     * Determine whether the hierarchy item or it's children are used as approval workflow assignments.
     * @param pre_delete_item_check $hook
     * @return void
     * @throws coding_exception
     */
    public static function can_hierarchy_item_be_deleted(pre_delete_item_check $hook) {
        $prefix = $hook->get_prefix();
        if (!in_array($prefix, ['organisation', 'position'])) {
            return;
        }

        $hierarchy = hierarchy::load_hierarchy($prefix);
        $item = $hierarchy->get_item($hook->get_id());
        if (empty($item->path)) {
            // Edge case where the item's path gets messed up.
            return;
        }

        $ids = [$hook->get_id()];
        $children = array_keys($hierarchy->get_item_descendants($hook->get_id(), 'base.id'));
        if (!empty($children)) {
            $ids = $children;
        }

        $assignment_type = $prefix == 'organisation'
            ? organisation::get_code()
            : position::get_code();

        $assignments_count = assignment::repository()
            ->where('assignment_type', $assignment_type)
            ->where_in('assignment_identifier', $ids)
            ->where('status', '!=', status::ARCHIVED)
            ->count();

        if ($assignments_count > 0) {
            $hook->add_reason(get_string("cannotdelete$prefix", 'mod_approval'));
        }
    }

    /**
     * Determine whether the cohort is used as an approval workflow assignment
     *
     * @param pre_delete_cohort_check $hook
     * @return void
     */
    public static function can_cohort_be_deleted(pre_delete_cohort_check $hook): void {
        $cohort_id = $hook->get_id();

        $assignments_count = assignment::repository()
            ->where('assignment_type', assignment_cohort::get_code())
            ->where('assignment_identifier', $cohort_id)
            ->where('status', '!=', status::ARCHIVED)
            ->count();

        if ($assignments_count > 0) {
            $hook->add_reason(get_string('cannotdeletecohort', 'mod_approval'));
        }
    }
}