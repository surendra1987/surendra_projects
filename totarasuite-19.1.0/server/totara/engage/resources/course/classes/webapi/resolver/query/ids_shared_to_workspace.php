<?php
/**
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
 * @author Simon Chester <simon.chester@totara.com>
 * @package engage_course
 */

namespace engage_course\webapi\resolver\query;

use container_workspace\workspace;
use core_container\factory;
use core\webapi\execution_context;
use core\webapi\query_resolver;
use totara_engage\card\card_loader;
use totara_engage\query\query;
use container_workspace\interactor\workspace\interactor as workspace_interactor;
use core\webapi\middleware\require_advanced_feature;
use core\webapi\middleware\require_login;
use container_workspace\totara_engage\share\recipient\library;

/**
 * Get IDs of courses shared to workspace.
 */
class ids_shared_to_workspace extends query_resolver {
    /** @inheritDoc */
    public static function resolve(array $args, execution_context $ec): array {
        /** @var workspace $workspace */
        $workspace = factory::from_id($args['workspace_id']);

        if (!$workspace->is_typeof(workspace::get_type())) {
            throw new \coding_exception("Container is not a workspace");
        }

        if (!$ec->has_relevant_context()) {
            $ec->set_relevant_context($workspace->get_context());
        }

        $interactor = new workspace_interactor($workspace);
        if (!$interactor->can_view_library()) {
            throw new \moodle_exception('access_denied', 'container_workspace');
        }

        $query = new query();
        $query->set_component('container_workspace');
        $recipient = new library($args['workspace_id']);
        $loader = new card_loader($query);

        return $loader->fetch_shared_instance_ids_by_component($recipient, 'engage_course');
    }

    /** @inheritDoc */
    public static function get_middleware(): array {
        return [
            new require_login(),
            new require_advanced_feature('container_workspace'),
        ];
    }
}
