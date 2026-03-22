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
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @author David Curry <david.curry@totara.com>
 * @package container_workspace
 */
namespace container_workspace\totara_catalog\workspace\observer;

use totara_catalog\observer\object_update_observer;

final class user_changed extends object_update_observer {
    /**
     * @return string[]
     */
    public function get_observer_events(): array {
        return [
            '\core\event\user_deleted',
        ];
    }

    /**
     * @return void
     */
    protected function init_change_objects(): void {
        global $DB;

        $sql = 'SELECT course_id FROM "ttr_workspace" WHERE user_id = :user_id';

        $workspace_ids = $DB->get_fieldset_sql($sql, ['user_id' => $this->event->objectid]);

        foreach ($workspace_ids as $workspace_id) {
            $this->register_for_delete($workspace_id);
        }
    }
}
