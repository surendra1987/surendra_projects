<?php
/**
 * This file is part of Totara Perform
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
 * @author Oleg Demeshev <oleg.demeshev@totara.com>
 * @package mod_perform
 */

use core_phpunit\testcase;
use mod_perform\models\activity\settings\controls\control_manager;
use mod_perform\testing\activity_controls_trait;

/**
 * @group perform
 */
class mod_perform_activity_controls_default_track_test extends testcase {
    use activity_controls_trait;

    public function test_default_track_structure_controls(): void {
        $activity = $this->create_activity();
        $default_track = $activity->get_default_track();

        $controls = (new control_manager($activity->id))->get_controls(['default_track']);
        $data = $controls['default_track'];

        static::assertEquals(
            [
                'track_id' => $default_track->id
            ],
            $data
        );
        static::assertEquals($default_track->id, $data['track_id']);
    }
}
