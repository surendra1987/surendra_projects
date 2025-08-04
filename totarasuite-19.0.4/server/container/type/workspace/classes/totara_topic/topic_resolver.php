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
 * @author Nathan Lewis <nathan.lewis@totara.com>
 * @package container_workspace
 */
namespace container_workspace\totara_topic;

use container_workspace\interactor\workspace\interactor as workspace_interactor;
use container_workspace\workspace;
use totara_topic\resolver\resolver;
use totara_topic\topic;

/**
 * Resolver for workspace's tags.
 */
final class topic_resolver extends resolver {
    /**
     * @param topic       $topic
     * @param int         $instanceid
     * @param string      $itemtype
     * @param int         $actorid
     *
     * @return bool
     */
    public function can_add_usage(topic $topic, int $instanceid, string $itemtype, int $actorid): bool {
        if ('course' != $itemtype) {
            debugging("Invalid itemtype '{$itemtype}'", DEBUG_DEVELOPER);
            return false;
        }

        $workspace = workspace::from_id($instanceid);
        $workspace_interactor = new workspace_interactor($workspace, $actorid);
        return $workspace_interactor->can_update();
    }

    /**
     * @param topic  $topic
     * @param int    $instanceid
     * @param string $itemtype
     * @param int    $actorid
     *
     * @return bool
     */
    public function can_delete_usage(topic $topic, int $instanceid, string $itemtype, int $actorid): bool {
        return $this->can_add_usage($topic, $instanceid, $itemtype, $actorid);
    }

    /**
     * @param int         $itemid
     * @param string|null $itemtype
     *
     * @return \context
     */
    public function get_context_of_item(int $itemid, ?string $itemtype = null): \context {
        $workspace = workspace::from_id($itemid);
        return $workspace->get_context();
    }
}