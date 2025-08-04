<?php
/**
 * This file is part of Totara LMS
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
 * @author Chris Snyder <chris.snyder@totara.com>
 * @package mod_approval
 */

namespace mod_approval\data_provider\application\role_map;

use core\orm\query\builder;

trait approval_role_map_trait {

    /**
     * @inheritDoc
     */
    protected function get_instance_join(): ?string {
        return "JOIN {course_modules} instance ON instance.id = ctx.instanceid";
    }

    /**
     * @inheritDoc
     */
    protected function get_instance_where(): ?string {
        return "AND instance.module = :moduleid";
    }

    /**
     * @inheritDoc
     */
    protected function get_instance_params(): array {
        $module = builder::table('modules')
            ->select('id')
            ->where('name', '=', 'approval')
            ->one(true);
        return ['moduleid' => $module->id];
    }
}
