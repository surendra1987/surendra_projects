<?php
/**
 * This file is part of Totara Core
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
 * @author Qingyang Liu <qingyang.liu@totara.com>
 * @package core
 */

namespace core\webapi\resolver\query;

use context;
use context_system;
use core\webapi\execution_context;
use core\webapi\middleware\reopen_session_for_writing;
use core\webapi\middleware\require_login;
use core\webapi\query_resolver;
use moodle_exception;
use moodle_url;
use totara_core\tui\tree\tree_node;
use totara_core\util\settings_navigation_tree_util as util;

/**
 * Gets a tree structure with navigation links to course settings pages.
 * What settings options are returned will be based upon what context is provided to the query.
 *
 */
class course_settings_navigation_tree extends query_resolver {

    /**
     * Resolve the tree.
     * Note that there are no capability checks done other than the standard require login check.
     *
     * @param array $args
     * @param execution_context $ec
     * @return tree_node[]
     */
    public static function resolve(array $args, execution_context $ec): array {
        $context = context::instance_by_id($args['context_id'], IGNORE_MISSING);
        if (!$context) {
            throw new moodle_exception('nopermissions');
        }
        if (!$context instanceof context_system) {
            $ec->set_relevant_context($context);
        }

        $page_url = new moodle_url($args['page_url']);
        $settings_nav = util::initialise_settings_navigation($context, $page_url);

        return util::load_trees($settings_nav, $context, util::COURSE_SETTINGS);
    }

    /**
     * @inheritDoc
     */
    public static function get_middleware(): array {
        return [
            new reopen_session_for_writing(),
            new require_login(),
        ];
    }

}