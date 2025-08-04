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
 * @author Kunle Odusan <kunle.odusan@totara.com>
 * @package core_ai
 */

namespace core_ai\event;

use context_system;
use core\event\base;
use core_ai\entity\interaction_log;

/**
 * Generic event class for AI feature interactions.
 */
class feature_interaction extends base {

    protected function init() {
        $this->data['crud'] = 'c';
        $this->data['edulevel'] = self::LEVEL_OTHER;
        $this->data['objecttable'] = 'ai_interaction_log';
    }

    /**
     * Returns localised event name.
     *
     * @return string
     */
    public static function get_name() {
        return get_string('event_ai_feature_interaction', 'ai');
    }

    /**
     * Returns English event description.
     *
     * @return string
     */
    public function get_description() {
        $plugin = $this->other['plugin'];
        $feature = $this->other['feature'];
        $interaction = $this->other['interaction'];

        return "The user with id '$this->relateduserid' has made a request to the '$plugin' '$feature' feature using the '$interaction' interaction";
    }

    /**
     * Uses the information in the interaction log to generate an event.
     *
     * Note that only metadata is stored in the system event, never user- or service-generated data.
     *
     * @param interaction_log $log
     * @return base
     */
    public static function create_from_log(interaction_log $log): base {
        return static::create([
            'objectid' => $log->id,
            'relateduserid' => $log->user_id,
            'other' => [
                'plugin' => $log->plugin,
                'feature' => $log->feature,
                'interaction' => $log->interaction,
            ],
            'context' => context_system::instance(),
        ]);
    }
}
