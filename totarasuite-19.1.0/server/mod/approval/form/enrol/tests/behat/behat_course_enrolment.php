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
 * @author Nathaniel Walmsley <nathaniel.walmsley@totara.com>
 * @package mod_approval
 */

require_once(__DIR__ . '/../../../../../../lib/behat/behat_base.php');

use approvalform_enrol\installer;

class behat_course_enrolment extends behat_base {

    /**
     * @Given /^there is a course enrolment workflow with the name "(?P<title_string>(?:[^"]|\\")*)"$/
     * @param string $title
     */
    public function install_enrolment_workflow(string $title): void {
        $installer = new installer();
        $installer->install_demo_workflow($title, true);
    }
}