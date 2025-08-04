<?php
/**
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
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 *
 * @author Ning Zhou <ning.zhou@totara.com>
 * @package usagedata
 */

use core_phpunit\testcase;
use totara_job\job_assignment;
use totara_job\usagedata\assignments_per_user;

class totara_job_usagedata_assignments_per_user_test extends testcase {
    public function test_export() {
        $generator = $this->getDataGenerator();

        // 3 with one assignment
        $user = $generator->create_user();
        job_assignment::create_default($user->id);
        $user = $generator->create_user();
        job_assignment::create_default($user->id);
        $user = $generator->create_user();
        job_assignment::create_default($user->id);

        // 2 with three assignments
        $user = $generator->create_user();
        job_assignment::create_default($user->id);
        job_assignment::create_default($user->id);
        job_assignment::create_default($user->id);
        $user = $generator->create_user();
        job_assignment::create_default($user->id);
        job_assignment::create_default($user->id);
        job_assignment::create_default($user->id);

        // 2 with 10+
        // 1 with 10, and a 1 with 11
        $user = $generator->create_user();
        job_assignment::create_default($user->id);
        job_assignment::create_default($user->id);
        job_assignment::create_default($user->id);
        job_assignment::create_default($user->id);
        job_assignment::create_default($user->id);
        job_assignment::create_default($user->id);
        job_assignment::create_default($user->id);
        job_assignment::create_default($user->id);
        job_assignment::create_default($user->id);
        job_assignment::create_default($user->id);
        $user = $generator->create_user();
        job_assignment::create_default($user->id);
        job_assignment::create_default($user->id);
        job_assignment::create_default($user->id);
        job_assignment::create_default($user->id);
        job_assignment::create_default($user->id);
        job_assignment::create_default($user->id);
        job_assignment::create_default($user->id);
        job_assignment::create_default($user->id);
        job_assignment::create_default($user->id);
        job_assignment::create_default($user->id);
        job_assignment::create_default($user->id);

        $results = (new assignments_per_user())->export();

        $this->assertEquals(3, $results['1']);
        $this->assertEquals(2, $results['3']);
        $this->assertEquals(2, $results['10+']);
    }
}