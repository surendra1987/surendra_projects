<?php
/**
 * This file is part of Totara Core
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
 * @author Cody Finegan <cody.finegan@totara.com>
 * @package totara_cohort
 */

class totara_cohort_audience_clone_test extends \core_phpunit\testcase {
    /**
     * @return void
     */
    public function test_simple_clone(): void {
        global $CFG;
        require_once $CFG->dirroot . '/totara/cohort/lib.php';

        /** @var \totara_cohort\testing\generator $cohort_generator */
        $cohort_generator = $this->getDataGenerator()->get_plugin_generator('totara_cohort');

        $this->setAdminUser();

        $user1 = $this->getDataGenerator()->create_user();
        $user2 = $this->getDataGenerator()->create_user();

        $cohort = $cohort_generator->create_cohort();
        $cohort_generator->create_cohort_member([
            'cohortid' => $cohort->id,
            'userid' => $user1->id,
        ]);

        // Confirm user 1 is a member and 2 is not
        $this->assertTrue(cohort_is_member($cohort->id, $user1->id));
        $this->assertFalse(cohort_is_member($cohort->id, $user2->id));

        // Clone the cohort
        $cloned_id = totara_cohort_clone_cohort($cohort->id);

        // Confirm user 1 is a member and 2 is not of the cloned cohort
        $this->assertTrue(cohort_is_member($cloned_id, $user1->id));
        $this->assertFalse(cohort_is_member($cloned_id, $user2->id));
    }
}
