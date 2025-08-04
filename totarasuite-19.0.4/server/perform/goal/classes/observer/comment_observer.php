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
 * @author Oleg Demeshev <oleg.demeshev@totara.com>
 * @author Murali Nair <murali.nair@totara.com>
 * @package perform_goal
 */

namespace perform_goal\observer;

use core\event\base;
use totara_comment\event\comment_created as comment_created_event;
use totara_comment\event\comment_updated as comment_updated_event;
use totara_comment\event\reply_created as reply_created_event;
use totara_notification\external_helper;
use totara_notification\resolver\resolver_helper;
use perform_goal\entity\goal;
use perform_goal\settings_helper;
use perform_goal\totara_comment\comment_resolver;
use perform_goal\totara_notification\constants;
use perform_goal\totara_notification\resolver\comment_created;
use perform_goal\totara_notification\resolver\comment_updated;

final class comment_observer {
    /**
     * Handler when a comment is created.
     *
     * @param comment_created_event $event the triggering event.
     */
    public static function comment_created(comment_created_event $event): void {
        self::on_event($event, 'instanceid', comment_created::class);
    }

    /**
     * Handler when a comment is updated.
     *
     * @param comment_updated_event $event the triggering event
     */
    public static function comment_updated(comment_updated_event $event): void {
        self::on_event($event, 'instanceid', comment_updated::class);
    }

    /**
     * Handler when a comment is created as reply.
     *
     * @param reply_created_event $event the triggering event
     */
    public static function reply_created(reply_created_event $event): void {
        // Totara code has this very bad habit of defining associative arrays
        // with raw values as keys in a module's public interface/API. Makes for
        // very brittle code; see the subtle difference in the second parameter
        // compared to the other handler methods?
        self::on_event($event, 'instance_id', comment_created::class);
    }

    /**
     * Common comment event handler.
     *
     * @param base $event comment event.
     * @param string $goal_id_key key to look up the goal id.
     * @param string $resolver_class CN resolver associated with the comment
     *        event. This is assumed to be a comment_base subclass.
     */
    private static function on_event(
        base $event,
        string $goal_id_key,
        string $resolver_class
    ): void {
        $data = $event->other;
        if ($data['component'] !== settings_helper::get_component()
            || $data['area'] !== comment_resolver::AREA) {
            // Just in case this observer gets comment events related to other
            // components. Highly improbable but possible.
            return;
        }

        $goal_id = $data[$goal_id_key];
        $goal = goal::repository()->where('id', $goal_id)->one(false);
        if (!$goal) {
            // Goal was deleted in the interim.
            return;
        }

        $event_data = [
            constants::DATA_COMMENT_ID => $event->objectid,
            constants::DATA_COMMENTER_UID => $event->userid,
            constants::DATA_GOAL_ID => $goal_id,
            constants::DATA_GOAL_SUBJECT_UID => $goal->user_id,
            constants::DATA_TIME_CREATED => $event->timecreated
        ];

        $resolver = resolver_helper::instantiate_resolver_from_class(
            $resolver_class, $event_data
        );
        external_helper::create_notifiable_event_queue($resolver);
    }

    /**
     * Default constructor.
     *
     * Private because this class is not meant to be instantiated.
     */
    private function __construct() {
        // EMPTY BLOCK
    }
}