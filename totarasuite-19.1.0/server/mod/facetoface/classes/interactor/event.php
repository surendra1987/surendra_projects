<?php
/**
 * This file is part of Totara Core
 *
 * Copyright (C) 2025 onwards Totara Learning Solutions LTD
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
 * @author Aaron Machin <aaron.machin@totara.com>
 * @package mod_facetoface
 */

namespace mod_facetoface\interactor;

use coding_exception;
use context_system;
use core\entity\user;
use dml_exception;
use mod_facetoface\seminar_event;

/**
 * Event interactor to provide capability logic.
 */
class event {
    private seminar_event $event;
    private ?user $actor;

    /**
     * @param seminar_event $event The event being interacted with.
     * @param ?user $actor The actor attempting to interact with the event.
     */
    public function __construct(seminar_event $event, ?user $actor) {
        $this->event = $event;
        $this->actor = $actor;
    }

    /**
     * Returns whether the given actor can view the given seminar event.
     * @return bool
     * @throws coding_exception
     * @throws dml_exception
     */
    public function can_view(): bool {
        if (empty($this->event->get_facetoface())) {
            return has_capability('mod/facetoface:viewallsessions', context_system::instance(), $this->actor);
        }

        $seminar = $this->event->get_seminar();

        return has_any_capability(
            ['mod/facetoface:view', 'mod/facetoface:viewallsessions'],
            $seminar->get_context(),
            $this->actor,
        );
    }
}
