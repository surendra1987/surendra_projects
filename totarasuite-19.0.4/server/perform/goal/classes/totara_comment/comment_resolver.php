<?php
/**
 * This file is part of Totara Perform
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
 * @author Oleg Demeshev <oleg.demeshev@totara.com>
 * @author Matthias Bonk <matthias.bonk@totara.com>
 * @package perform_goal
 */

namespace perform_goal\totara_comment;

use coding_exception;
use perform_goal\entity\goal as goal_entity;
use totara_comment\comment;
use totara_comment\resolver;
use perform_goal\model\goal;
use perform_goal\interactor\goal_interactor;

final class comment_resolver extends resolver {

    public const AREA = 'goal_comment';

    /**
     * @param string $area
     * @return bool
     */
    private function is_valid_area(string $area): bool {
        return $area === self::AREA;
    }

    /**
     * @param int $goal_id
     * @return bool
     */
    private function is_valid_goal_id(int $goal_id): bool {
        return goal_entity::repository()->where('id', $goal_id)->exists();
    }

    /**
     * {@inheritDoc}
     */
    public function is_allow_to_create(int $instanceid, string $area, int $actorid): bool {
        if (!$this->is_valid_area($area) || !$this->is_valid_goal_id($instanceid)) {
            return false;
        }

        return (goal_interactor::from_goal(
            goal::load_by_id($instanceid), $actorid
        ))->can_view();
    }

    /**
     * {@inheritDoc}
     */
    public function is_allow_to_delete(comment $comment, int $actorid): bool {
        if (is_siteadmin($actorid)) {
            return true;
        }
        if ($comment->get_userid() != $actorid) {
            return false;
        }
        return (goal_interactor::from_goal(
            goal::load_by_id($comment->get_instanceid())
        ))->can_view();
    }

    /**
     * {@inheritDoc}
     */
    public function is_allow_to_update(comment $comment, int $actorid): bool {
        if (is_siteadmin($actorid)) {
            return true;
        }
        if ($comment->get_userid() != $actorid) {
            return false;
        }
        return (goal_interactor::from_goal(
            goal::load_by_id($comment->get_instanceid())
        ))->can_view();
    }

    /**
     * {@inheritDoc}
     */
    public function get_context_id(int $instance_id, string $area): int {
        if ($this->is_valid_area($area)) {
            return goal::load_by_id($instance_id)->get_context()->id;
        }
        throw new coding_exception('Area is invalid');
    }

    /**
     * {@inheritDoc}
     */
    public function get_owner_id_from_instance(string $area, int $instance_id): ?int {
        if ($this->is_valid_area($area)) {
            return goal::load_by_id($instance_id)->user_id;
        }
        throw new coding_exception('Area is invalid');
    }

    /**
     * {@inheritDoc}
     */
    public function can_see_comments(int $instance_id, string $area, int $actor_id): bool {
        if (!$this->is_valid_area($area) || !$this->is_valid_goal_id($instance_id)) {
            return false;
        }

        return (goal_interactor::from_goal(
            goal::load_by_id($instance_id), $actor_id
        ))->can_view();
    }

    /**
     * {@inheritDoc}
     */
    public function can_create_reaction_on_comment(comment $comment, int $actor_id): bool {
        if ($actor_id === $comment->get_userid()
            || $comment->get_component() !== $this->get_component()
            || !$this->is_valid_area($comment->get_area())
            || !$this->is_valid_goal_id($comment->get_instanceid())
        ) {
            return false;
        }

        return (goal_interactor::from_goal(
            goal::load_by_id($comment->get_instanceid()), $actor_id
        ))->can_view();
    }

    /**
     * @inheritDoc
     * Override this so comment 'Report' action functionality is disabled on Totara Goals comments.
     */
    public function can_report_comment(comment $comment, int $actor_id): bool {
        return false;
    }

    /**
     * @inheritDoc
     * Override this so comment 'Report' action functionality is disabled on Totara Goals replies on comments.
     */
    public function can_report_reply(comment $comment, int $actor_id): bool {
        return false;
    }
}
