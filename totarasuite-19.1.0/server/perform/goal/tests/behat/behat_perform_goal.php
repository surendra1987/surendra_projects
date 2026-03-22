<?php
/**
 * This file is part of Totara Perform
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
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 *
 * @author Rodney Cruden-Powell <rodney.cruden-powell@totara.com>
 * @package perform_goal
 */

use Behat\Mink\Exception\ExpectationException;
use perform_goal\entity\goal;

/**
 * Perform Goal steps definitions.
 */
class behat_perform_goal extends behat_base {

    /**
     * Go to the goals landing page.
     *
     * @Then /^I navigate to the goals landing page$/
     */
    public function i_navigate_to_the_goals_landing_page() {
        \behat_hooks::set_step_readonly(false);

        $url = new moodle_url('/perform/goal/index.php');
        $this->getSession()->visit($this->locate_path($url->out_as_local_url(false)));
        $this->wait_for_pending_js();
    }
}