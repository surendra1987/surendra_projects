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

namespace core\webapi\resolver\type;

use coding_exception;
use core\webapi\execution_context;
use core\webapi\type_resolver;

/**
 * The type resolver is to get each type for course interactor.
 */
class course_interactor extends type_resolver {

    /**
     * @param string            $field
     * @param \container_course\interactor\course_interactor  $course_interactor
     * @param array             $args
     * @param execution_context $ec
     * @return mixed
     */
    public static function resolve(string $field, $course_interactor, array $args, execution_context $ec) {
        switch ($field) {
            case 'can_enrol':
                return $course_interactor->can_enrol();
            case 'can_view':
                return $course_interactor->can_view();
            case 'supports_non_interactive_enrol':
                return $course_interactor->supports_non_interactive_enrol();
            case 'is_site_guest':
                return $course_interactor->is_site_guest();
            case 'non_interactive_enrol_instance_enabled':
                return $course_interactor->non_interactive_enrol_instance_enabled();
            case 'is_siteadmin':
                return $course_interactor->is_siteadmin();
            case 'is_enrolled':
                return $course_interactor->is_enrolled();
            case 'can_mark_self_complete':
                return $course_interactor->can_mark_self_complete();
            case 'guest_enrol_enabled':
                return $course_interactor->guest_enrol_enabled();
            case 'is_guest':
                return $course_interactor->is_guest();
            case 'can_view_hidden_activities':
                return $course_interactor->can_view_hidden_activities();
            case 'can_update_course':
                return $course_interactor->can_update_course();
            case 'non_interactive_enrol_requires_approval':
                return $course_interactor->non_interactive_enrol_requires_approval();
            case 'is_enrolled_not_pending_approval':
                return $course_interactor->is_enrolled_not_pending_approval();
            default:
                throw new coding_exception("The field '{$field}' is not yet supported");
        }
    }
}