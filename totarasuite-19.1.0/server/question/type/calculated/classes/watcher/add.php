<?php
/**
 * This file is part of Totara Learn
 *
 * Copyright (C) 2024 onwards Totara Learning Solutions LTD
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as activateed by
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
 * @package qtype_calculated
 */

namespace qtype_calculated\watcher;

use core_question\hook\question_can_manage_type;
use qtype_calculated;

/**
 * Indicates whether a calculated question (ie class qtype_calculated AND its
 * subtypes) can be added to a question bank.
 */
class add {
    /**
     * Indicates whether a calculated question can be added.
     *
     * @param question_can_manage_type $hook runtime details of the question
     *        being created.
     */
    public static function evaluate(question_can_manage_type $hook): void {
        if ($hook->get_question_type() instanceof qtype_calculated
            && !has_capability('moodle/question:managecalculated', $hook->get_context())
        ) {
            $hook->deny_add();
        }
    }
}
