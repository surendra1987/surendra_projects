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
 * @package container_workspace
 * @category totara_catalog
 */

namespace container_workspace\totara_catalog\workspace\observer;

defined('MOODLE_INTERNAL') || die();

use totara_catalog\observer\object_update_observer;

/**
 * Update catalog data based on deleted workspace.
 */
class workspace_deleted extends object_update_observer {

    public function get_observer_events(): array {
        return [
            '\container_workspace\event\workspace_deleted',
        ];
    }

    /**
     * Init workspace remove object for deleted workspace
     */
    protected function init_change_objects(): void {
        $this->register_for_delete($this->event->objectid);
    }
}
