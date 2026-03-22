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
 * @author Chris Snyder <chris.snyder@totara.com>
 * @package core_question
 */

namespace core_question\hook;

use context;
use question_bank;
use question_type;
use totara_core\hook\base as hook_base;

/**
 * Hook that allows question type plugins to deny capability to add a question.
 */
class question_can_manage_type extends hook_base {

    private $context;

    private $question_type;

    private $allow = true;

    /**
     * Default constructor.
     *
     * @param string|question_type $question type
     */
    public function __construct(
        string|question_type $question_type,
        context $context
    ) {
        $this->question_type = is_string($question_type)
            ? question_bank::get_qtype($question_type)
            : $question_type;

        $this->context = $context;
    }

    /**
     * Get the question type
     *
     * @return question_type
     */
    public function get_question_type(): question_type {
        return $this->question_type;
    }

    /**
     * Get the context
     *
     * @return context
     */
    public function get_context(): context {
        return $this->context;
    }

    /**
     * Tell the hook not to allow a question of that type to be added in context.
     *
     * @return void
     */
    public function deny_add(): void {
        $this->allow = false;
    }

    /**
     * Discover whether any hook watchers prevented adding this question type
     *
     * @return bool
     */
    public function is_allowed(): bool {
        return $this->allow;
    }
}
