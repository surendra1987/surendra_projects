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
 * @author Qingyang Liu <qingyang.liu@totara.com>
 * @package mod_facetoface
 */

namespace mod_facetoface\signup\restriction;

/**
 * Class attendee_can_overbook check the user who want to book or is added into the event has the permisson or not.
 */
class attendee_can_overbook extends actor_can_overbook {
    public function pass() : bool {

        if ($this->signup->get_actorid() == $this->signup->get_userid()) {
            return parent::pass();
        }

        $seminarevent = $this->signup->get_seminar_event();

        $module = $seminarevent->get_seminar()->get_coursemodule();
        $context = \context_module::instance($module->id);

        return has_capability('mod/facetoface:signupwaitlist', $context, $this->signup->get_userid());
    }


}