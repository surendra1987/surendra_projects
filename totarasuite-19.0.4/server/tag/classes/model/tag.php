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

namespace core_tag\model;

use core\orm\entity\model;
use core_tag\entity\tag as entity;
use core_tag\entity\tag_instance;

/**
 * @property-read int    $id
 * @property-read int    $userid
 * @property-read string $name
 * @property-read string $rawname
 * @property-read string $description
 * @property-read int    $descriptionformat
 * @property-read int    $flag
 * @property-read int    $timemodified
 * @property-read int    $tagcollid
 * @property-read int    $isstandard
 *
 */
class tag extends model {

    /**
     * @var string[]
     */
    protected $entity_attribute_whitelist = [
        'id',
        'name',
        'description',
        'userid',
        'descriptionformat',
        'time_created',
        'flag',
        'tagcollid',
        'isstandard',
        'rawname'
    ];

    /**
     * @inheritdoc
     */
    protected static function get_entity_class(): string {
        return entity::class;
    }

    /**
     * @param int $item_id
     * @param string $item_type
     * @param string $component
     * @return array|tag[]
     */
    public static function get_tags_by_item(int $item_id, string $item_type, string $component): array {
        return tag_instance::repository()
            ->where('itemid', $item_id)
            ->where('itemtype', $item_type)
            ->where('component', $component)
            ->get()
            ->map(function (tag_instance $tag) {
                return self::load_by_id($tag->tagid);
            })
            ->all();
    }

    /**
     * @param int $collection_id
     * @return array
     */
    public static function get_tags_by_collection(int $collection_id): array {
        return entity::repository()
            ->where('tagcollid', $collection_id)
            ->get()
            ->map(function(entity $tag) {
                return static::load_by_entity($tag);
            })
            ->all(true);
    }
}
