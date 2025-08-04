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
 * @author Simon Chester <simon.chester@totara.com>
 * @package goaltype_basic
 */

namespace goaltype_basic\webapi\resolver\query;

use core\webapi\execution_context;
use core\webapi\middleware\require_advanced_feature;
use core\webapi\middleware\require_authenticated_user;
use core\webapi\middleware\require_login;
use core\webapi\query_resolver;
use goaltype_basic\hook\creation_options;
use goaltype_basic\model\creation_option;

class creation_config extends query_resolver {
    /**
     * {@inheritdoc}
     */
    public static function resolve(array $args, execution_context $ec) {
        $options = [
            // Standard manual option
            new creation_option(
                id: 'goaltype_basic/manual',
                name: get_string('create_manually', 'goaltype_basic'),
                description: get_string('create_manually_desc', 'goaltype_basic'),
                icon_component: 'tui/components/icons/Cube',
                ui_component: 'performgoal_type_basic/components/manage/create/CreateManual',
            )
        ];

        // Allow plugins to add additional options
        $hook = new creation_options($options);
        $hook->execute();

        return [
            'options' => $hook->get_options(),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public static function get_middleware(): array {
        return [
            new require_advanced_feature('perform_goals'),
            new require_login(),
            new require_authenticated_user(),
        ];
    }
}
