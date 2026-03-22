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
 * @package hierarchy_organisation
 */

namespace hierarchy_organisation\webapi\resolver\mutation;

defined('MOODLE_INTERNAL') || die();

use core\webapi\execution_context;
use core\webapi\middleware\require_advanced_feature;
use core\webapi\middleware\require_login;
use core\webapi\middleware\require_user_capability;
use core\webapi\mutation_resolver;
use hierarchy_organisation\exception\organisation_exception;
use hierarchy_organisation\webapi\resolver\organisation_helper;
use totara_hierarchy\exception\hierarchy_deletion_prevented;

global $CFG;
/** @var \core_config $CFG */
require_once $CFG->dirroot.'/totara/hierarchy/lib.php';

/**
 * Mutation to create an organisation
 */
class delete_organisation extends mutation_resolver {
    use organisation_helper;

    /**
     * Deletes an organisation
     *
     * @param array $args
     * @param execution_context $ec
     * @return array
     * @throws \dml_exception
     * @throws organisation_exception
     * @throws hierarchy_deletion_prevented
     */
    public static function resolve(array $args, execution_context $ec) {

        $target_organisation = $args['target_organisation'] ?? [];

        $organisation = static::load_target_organisation($target_organisation);

        $hierarchy = \hierarchy::load_hierarchy('organisation');
        if (!$hierarchy->delete_hierarchy_item($organisation->id)) {
            throw new organisation_exception("Error deleting organisation");
        }

        return [
            'organisation_id' => $organisation->id
        ];
    }

    /**
     * @inheritDoc
     */
    public static function get_middleware(): array {
        return [
            new require_login(),
            new require_advanced_feature('organisations'),
            new require_user_capability('totara/hierarchy:deleteorganisation')
        ];
    }

}
