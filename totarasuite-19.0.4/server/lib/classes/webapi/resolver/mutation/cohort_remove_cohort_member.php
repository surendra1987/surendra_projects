<?php
/**
 * This file is part of Totara Core
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
 * @author Qingyang Liu <qingyang.liu@totara.com>
 * @package core
 */

namespace core\webapi\resolver\mutation;

use core\entity\cohort;
use core\entity\cohort_member;
use core\entity\user;
use core\reference\user_record_reference;
use core\webapi\execution_context;
use core\webapi\middleware\require_authenticated_user;
use core\webapi\mutation_resolver;
use core\webapi\resolver\query\cohort_static_cohorts;
use totara_cohort\cohort_interactor;
use totara_cohort\cohort_record_reference;
use totara_cohort\exception\cohort_remove_cohort_member_exception;

/**
 * GraphQL mutation to remove the member from the cohort.
 */
class cohort_remove_cohort_member extends mutation_resolver {

    /**
     * @inheritdoc
     */
    public static function resolve(array $args, execution_context $ec): array {
        global $CFG;
        require_once("$CFG->dirroot/cohort/lib.php");

        $user_reference = $args['user'];
        $cohort_reference = $args['cohort'];

        if (empty($user_reference)) {
            throw new cohort_remove_cohort_member_exception('User reference is required.');
        }

        if (empty($cohort_reference)) {
            throw new cohort_remove_cohort_member_exception('Cohort reference is required.');
        }

        $current_user = user::logged_in();
        $cohort_interactor = cohort_interactor::for_user($current_user);
        if (!$cohort_interactor->can_assign_or_manage_cohort()) {
            throw new cohort_remove_cohort_member_exception('Sorry, but you do not have manage or assign permissions to do that.');
        }

        $target_user = user_record_reference::load_for_viewer($user_reference, $current_user);
        $target_cohort = cohort_record_reference::load_for_viewer($cohort_reference, $current_user);

        if ($target_cohort->cohorttype != cohort_static_cohorts::TYPE_STATIC) {
            throw new cohort_remove_cohort_member_exception('You can only remove member from static audience.');
        }

        if (!cohort_member::repository()->is_user_member($target_user->id, $target_cohort->id)) {
            return [
                'user' => $target_user,
                'cohort' => new cohort($target_cohort->id),
                'success' => true,
                'user_was_member' => false
            ];
        }

        cohort_remove_member($target_cohort->id, $target_user->id);
        return [
            'user' => $target_user,
            'cohort' => new cohort($target_cohort->id),
            'success' => true,
            'user_was_member' => true
        ];
    }

    /**
     * @inheritDoc
     */
    public static function get_middleware(): array {
        return [
            new require_authenticated_user(),
        ];
    }
}