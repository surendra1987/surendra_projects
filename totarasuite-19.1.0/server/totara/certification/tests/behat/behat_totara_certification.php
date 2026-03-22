<?php
/*
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
 * @author David Curry <david.curry@totara.com>
 * @package totara_certification
 */

require_once(__DIR__ . '/../../../../lib/behat/behat_base.php');

use \Behat\Gherkin\Node\TableNode as TableNode;

class behat_totara_certification extends behat_base {

    /**
     * Goes directly to the create certification form
     *
     * TOTARA: No longer rely on admin navigation, instead to directly to URL
     * When we are testing functionality of page, not how to get there.
     *
     * @Given /^I go to the certification creation form$/
     * @return void
     */
    public function i_go_to_the_certification_creation_form(): void {
        \behat_hooks::set_step_readonly(false);

        $args = [];

        // The category the course is going into, we could make this an arg.
        $args['category'] = 1;
        $args['iscertif'] = 1;

        $url = new moodle_url('/totara/program/add.php', $args);

        // Redirect.
        $this->getSession()->visit($this->locate_path($url->out_as_local_url(false)));
        $this->wait_for_pending_js();
    }
}
