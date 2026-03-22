<?php
/*
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
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 *
 * @author Jaron Steenson <jaron.steenson@totaralearning.com>
 * @package mod_perform
 */

namespace mod_perform\data_providers\activity;

use core\collection;
use mod_perform\models\activity\subject_instance as subject_instance_model;

/**
 * Class subject_instance
 *
 * @package mod_perform\data_providers\activity
 */
class subject_instance_for_participant extends subject_instance_for_participant_provider {

    /**
     * @param int $participant_id The id of the user we would like to get activities that they are participating in.
     * @param int $participant_source see participant_source model for constants
     */
    public function __construct(int $participant_id, int $participant_source) {
        $this->participant_id = $participant_id;
        $this->participant_source = $participant_source;

        // Set default sorting.
        $this->sort_by('created_at');
    }

    /**
     * Map the subject instance entities to their respective model class.
     *
     * @return collection|subject_instance_model[]
     */
    protected function process_fetched_items(): collection {
        return $this->items->map_to(subject_instance_model::class);
    }
}