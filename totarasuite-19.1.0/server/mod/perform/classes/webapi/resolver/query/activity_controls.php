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
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @author Matthias Bonk <matthias.bonk@totara.com>
 * @package mod_perform
 */

namespace mod_perform\webapi\resolver\query;

use coding_exception;
use core\webapi\middleware\reopen_session_for_writing;
use core\webapi\execution_context;
use core\webapi\middleware\require_advanced_feature;
use core\webapi\query_resolver;
use mod_perform\models\activity\settings\controls\control_manager;
use mod_perform\webapi\middleware\require_activity;
use mod_perform\webapi\middleware\require_manage_capability;

class activity_controls extends query_resolver {
    /**
     * {@inheritdoc}
     */
    public static function resolve(array $args, execution_context $ec) {
        $args = $args['input'];

        try {
            $controls = (new control_manager($args['activity_id']))->get_controls($args['control_keys']);
        } catch (coding_exception $exc) {
            return [
                'controls' => '',
                'success' => false,
                'errors' => [
                    'code' => '',
                    'message' => $exc->debuginfo
                ],
            ];
        }

        return [
            'controls' => json_encode(
                $controls,
                JSON_THROW_ON_ERROR
            ),
            'success' => true,
            'errors' => [],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public static function get_middleware(): array {
        return [
            reopen_session_for_writing::class,
            new require_advanced_feature('performance_activities'),
            require_activity::by_activity_id('input.activity_id', true),
            require_manage_capability::class
        ];
    }
}