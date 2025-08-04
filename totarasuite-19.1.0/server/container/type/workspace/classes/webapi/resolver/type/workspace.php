<?php
/**
 * This file is part of Totara Learn
 *
 * Copyright (C) 2020 onwards Totara Learning Solutions LTD
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
 * @author Kian Nguyen <kian.nguyen@totaralearning.com>
 * @package container_workspace
 */
namespace container_workspace\webapi\resolver\type;

use coding_exception;
use container_workspace\entity\workspace_discussion;
use container_workspace\formatter\workspace\formatter;
use container_workspace\interactor\workspace\interactor;
use container_workspace\loader\audience\loader as audiences_loader;
use container_workspace\loader\member\loader;
use container_workspace\loader\member\member_request_loader;
use container_workspace\member\status;
use container_workspace\query\audience\query as audience_query;
use container_workspace\query\member\member_request_query;
use container_workspace\query\member\query;
use container_workspace\query\workspace\access;
use core\webapi\execution_context;
use core\webapi\type_resolver;
use container_workspace\workspace as model;
use core\theme\helper as theme_helper;
use totara_core\advanced_feature;
use totara_topic\provider\topic_provider;

/**
 * Type resolver for workspace.
 */
class workspace extends type_resolver {
    /**
     * @param string    $field
     * @param model     $workspace
     * @param array     $args
     * @param execution_context $ec
     *
     * @return mixed|null
     */
    public static function resolve(string $field, $workspace, array $args, execution_context $ec) {
        advanced_feature::require('container_workspace');

        if (!($workspace instanceof model)) {
            throw new coding_exception("Invalid parameter that is not type of " . model::class);
        }

        switch ($field) {
            case 'owner':
                return $workspace->owners()->first();

            case 'owners':
                return $workspace->owners();

            case 'interactor':
                $actor_id = $args['actor_id'] ?? null;

                return new interactor($workspace, $actor_id);

            case 'total_members':
                $workspace_id = $workspace->get_id();
                $query = new query($workspace_id);
                $query->set_member_status(status::get_active());

                $paginator = loader::get_members($query);
                return $paginator->get_total();

            case 'total_audiences':
                if (!(new interactor($workspace))->can_add_audiences()) {
                    return 0;
                }
                $query = new audience_query($workspace);
                $paginator = audiences_loader::get_audiences($query);

                return $paginator->get_total();

            case 'image':
                $theme_config = theme_helper::load_theme_config($args['theme'] ?? null);
                return $workspace->get_image($theme_config)->out();

            case 'access':
                if ($workspace->is_public()) {
                    return access::get_code(access::PUBLIC);
                }

                if ($workspace->is_private()) {
                    return access::get_code(access::PRIVATE);
                }

                return access::get_code(access::HIDDEN);

            case 'total_member_requests':
                if ($workspace->is_public()) {
                    // Save it from redundant fetching.
                    return 0;
                }

                $workspace_id = $workspace->get_id();
                $query = new member_request_query($workspace_id);
                $paginator = member_request_loader::get_member_requests($query);

                return $paginator->get_total();

            case 'total_discussions':
                $workspace_id = $workspace->get_id();
                return workspace_discussion::repository()->count_for_workspace($workspace_id);

            case 'tags':
                return topic_provider::get_for_item($workspace->get_id(), 'container_workspace', 'course');

            default:
                $format = $args['format'] ?? null;

                return (new formatter($workspace))->format($field, $format);
        }
    }
}
