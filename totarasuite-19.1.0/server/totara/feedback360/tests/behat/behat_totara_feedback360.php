<?php
/**
 * This file is part of Totara Learn
 *
 * Copyright (C) 2022 onwards Totara Learning Solutions LTD
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
 * @author Matthias Bonk <matthias.bonk@totara.com>
 * @package totara_feedback360
 */

require_once(__DIR__ . '/../../../../lib/behat/behat_base.php');

use core\orm\query\builder;

class behat_totara_feedback360 extends behat_base {

    /**
     * Activate a feedback360.
     *
     * @When /^I activate the "([^"]*)" feedback360$/
     *
     * @param string $feedback360_name the field name
     */
    public function i_activate_the_feedback360(string $feedback360_name): void {
        behat_hooks::set_step_readonly(false);

        $feedback360_record = builder::table('feedback360')
            ->where('name', $feedback360_name)
            ->one(true);

        $feedback360 = new feedback360($feedback360_record->id);
        $feedback360->activate();
    }
}
