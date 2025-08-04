<?php
/*
 * This file is part of Totara Talent Experience Platform
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
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @author Kunle Odusan <kunle.odusan@totara.com>
 * @package core_mfa
 */

namespace core_mfa\testing;

use core\entity\user;
use core\testing\component_generator;
use core_mfa\model\instance;

/**
 * MFA generator
 */
class generator extends component_generator {

    /**
     * Create MFA instances
     *
     * @param array $data
     * @return instance
     */
    public function create_instances(array $data): instance {
        $user = user::repository()
            ->where('username', $data['user'])
            ->order_by('id')
            ->first();

        return instance::create(
            $user->id,
            $data['type'],
            $data['label'],
            json_decode($data['config'], true)
        );
    }
}
