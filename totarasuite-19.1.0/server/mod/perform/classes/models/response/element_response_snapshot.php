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
 * @package mod_perform
 */

namespace mod_perform\models\response;

use coding_exception;
use core\orm\entity\model;
use core\orm\entity\traits\json_trait;
use mod_perform\entity\activity\element_response_snapshot as element_response_snapshot_entity;
use perform_goal\model\goal;
use stdClass;

/**
 * Holds point-in-time snapshot data for a section element response.
 * Abstract class to be implemented by elements which need snapshots.
 *
 * Properties:
 * @property-read int $id ID
 * @property-read int $response_id element response id this snapshot belongs to
 * @property-read string $item_type component name of object/record being snapshotted
 * @property-read int $item_id database id of object/record being snapshotted
 * @property-read stdClass $snapshot stdClass snapshot data
 * @property-read string|null $snapshot_raw JSON encoded snapshot data
 * @property-read int $created_at
 * @property-read int $updated_at
 *
 * @property-read section_element_response $section_element_response
 * @property-read goal $original
 *
 */
abstract class element_response_snapshot extends model {

    use json_trait;

    /** @var int Maximum string length for the item_type property */
    private const TYPE_MAX_LENGTH = 100;

    /** @var element_response_snapshot_entity */
    protected $entity;

    protected $entity_attribute_whitelist = [
        'id',
        'response_id',
        'item_type',
        'item_id',
        'created_at',
        'updated_at',
    ];

    protected $model_accessor_whitelist = [
        'section_element_response',
        'original',
        'snapshot',
        'snapshot_raw',
    ];

    /**
     * @inheritDoc
     */
    protected static function get_entity_class(): string {
        return element_response_snapshot_entity::class;
    }

    /**
     * Get the section element response model that owns this snapshot.
     *
     * @return section_element_response
     */
    public function get_section_element_response(): section_element_response {
        return section_element_response::load_by_id($this->entity->response_id);
    }

    /**
     * Returns the original object that this snapshot was taken from.
     *
     * @return mixed
     */
    abstract protected function get_original();

    /**
     * Returns a stdClass representation of the snapshotted object.
     *
     * @return stdClass
     */
    public function get_snapshot(): stdClass {
        return self::json_decode($this->entity->snapshot);
    }

    /**
     * Returns the raw JSON snapshot data.
     *
     * @return string
     */
    public function get_snapshot_raw(): string {
        return $this->entity->snapshot;
    }

    /**
     * Updates the snapshot from the original object and saves the entity.
     *
     * @return static
     */
    abstract public function update_snapshot();

    /**
     * Validate an item_type value.
     *
     * @param string $item_type
     * @return void
     * @throws coding_exception
     */
    protected static function validate_item_type(string $item_type): void {
        if ($item_type === '') {
            throw new coding_exception('Response snapshot must have an item_type.');
        }

        if (strlen($item_type) > self::TYPE_MAX_LENGTH) {
            throw new coding_exception('Response snapshot item_type must not be longer than ' . self::TYPE_MAX_LENGTH . ' characters.');
        }
    }

    /**
     * Delete a snapshot instance.
     */
    public function delete(): void {
        $this->entity->delete();
    }

    /**
     * Reload the properties on the model's entity.
     *
     * @return self
     */
    public function refresh(): self {
        $this->entity->refresh();

        return $this;
    }
}
