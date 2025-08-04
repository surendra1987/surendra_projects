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
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 *
 * @author Chris Snyder <chris.snyder@totara.com>
 * @package performelement_perform_goal_creation
 */

namespace performelement_perform_goal_creation\model;

use mod_perform\entity\activity\element_response_snapshot as element_response_snapshot_entity;
use mod_perform\models\response\element_response_snapshot;
use mod_perform\models\response\section_element_response;
use perform_goal\entity\goal as goal_entity;
use perform_goal\model\goal;
use stdClass;

/**
 * Holds point-in-time snapshot data for a personal goal creation response.
 *
 * Properties:
 * @property-read int $id ID
 * @property-read int $response_id section element response id this snapshot belongs to
 * @property-read string $item_type "perform_goal"
 * @property-read int $item_id database id of goal being snapshotted
 * @property-read stdClass $snapshot stdClass snapshot data
 * @property-read string|null $snapshot_raw JSON encoded snapshot data
 * @property-read int $created_at
 * @property-read int $updated_at
 *
 */
class goal_snapshot extends element_response_snapshot {

    public const ITEM_TYPE = 'perform_goal';

    /**
     * @inheritDoc
     */
    protected function get_original(): goal {
        return goal::load_by_id($this->item_id);
    }

    /**
     * @inheritDoc
     */
    public function update_snapshot(): goal_snapshot {
        // Swallow the goal entity whole.
        $goal_entity = new goal_entity($this->item_id);
        $this->entity->snapshot = self::json_encode($goal_entity);
        $this->entity->save();

        // TODO TL-37974: copy any files.

        return $this;
    }

    /**
     * Create a new snapshot entity with a goal snapshot in it.
     *
     * @param section_element_response $response
     * @param goal $item
     * @return static
     */
    public static function create(section_element_response $response, goal $item): self {
        // Check that we have the right type of response.
        if ($response->element->plugin_name != 'perform_goal_creation') {
            throw new \coding_exception("This goal_snapshot model should only be used with performelement_perform_goal_creation responses");
        }

        // Create the entity, but with an empty snapshot.
        $entity = new element_response_snapshot_entity();
        $entity->response_id = $response->id;
        $entity->item_type = static::ITEM_TYPE;
        $entity->item_id = $item->id;
        $entity->snapshot = '';
        $entity->save();
        $model = self::load_by_id($entity->id);

        // Grab the actual snapshot data.
        return $model->update_snapshot();
    }
}