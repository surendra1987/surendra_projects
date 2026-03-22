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
 * @author Wajdi Bshara <wajdi.bshara@aleido.com>
 * @package hierarchy_position
 */

namespace hierarchy_position\webapi\resolver\mutation;

defined('MOODLE_INTERNAL') || die();

use core\webapi\execution_context;
use core\webapi\middleware\require_advanced_feature;
use core\webapi\middleware\require_login;
use core\webapi\middleware\require_user_capability;
use core\webapi\mutation_resolver;
use hierarchy_position\exception\position_exception;
use hierarchy_position\webapi\resolver\position_helper;

global $CFG;
/** @var \core_config $CFG */
require_once $CFG->dirroot.'/totara/hierarchy/lib.php';

/**
 * Mutation to create a position
 */
class delete_position extends mutation_resolver {
    use position_helper;

    /**
     * Deletes a position
     *
     * @return array
     * @throws position_exception
     * @throws \dml_exception
     */
    public static function resolve(array $args, execution_context $ec) {

        $target_position = $args['target_position'] ?? [];

        if (empty($target_position)) {
            throw new position_exception('The required "target_position" parameter is empty.');
        }

        $position = static::load_target_position($target_position);

        $hierarchy = \hierarchy::load_hierarchy('position');

        if (!$hierarchy->delete_hierarchy_item($position->id)) {
            throw new position_exception("Error deleting position");
        }

        return [
            'position_id' => $position->id
        ];
    }

    /**
     * @inheritDoc
     */
    public static function get_middleware(): array {
        return [
            new require_login(),
            new require_advanced_feature('positions'),
            new require_user_capability('totara/hierarchy:deleteposition')
        ];
    }

}
