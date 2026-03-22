<?php
/*
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
 * @author Kunle Odusan <kunle.odusan@totara.com>
 * @package totara_cohort
 */

namespace totara_cohort\exception;

use coding_exception;
use moodle_exception;

class cohort_deletion_prevented extends moodle_exception {

    protected array $reasons = [];

    public function __construct(array $reasons) {
        if (empty($reasons)) {
            throw new coding_exception("Reasons for preventing cohort deletion can not be empty");
        }
        $message = $reasons[0];
        $this->reasons = $reasons;
        parent::__construct($message);
    }

    public function get_reasons(): array {
        return $this->reasons;
    }
}
