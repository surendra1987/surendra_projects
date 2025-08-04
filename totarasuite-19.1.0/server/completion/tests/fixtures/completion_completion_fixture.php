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
 * @package core_completion
 */

use completion_completion as base_completion_completion;

/**
 * Fixture class to test when updates are executed in the completion_completion class
 */
class completion_completion_fixture extends base_completion_completion {

    private int $update_count = 0;

    public function update() {
        $this->update_count++;
        return parent::update();
    }

    public function get_update_count(): int {
        return $this->update_count;
    }

}