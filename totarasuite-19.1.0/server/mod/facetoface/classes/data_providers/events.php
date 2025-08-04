<?php
/**
 * This file is part of Totara Core
 *
 * Copyright (C) 2025 onwards Totara Learning Solutions LTD
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
 * @author Aaron Machin <aaron.machin@totara.com>
 * @package mod_facetoface
 */

namespace mod_facetoface\data_providers;

use coding_exception;
use core\orm\entity\entity;
use core\orm\entity\filter\filter_factory;
use core\orm\query\query_builder_to_repository_adapter;
use mod_facetoface\entity\filters\event_filter_factory;
use mod_facetoface\query\event\query as event_query;
use totara_core\data_provider\provider;

defined('MOODLE_INTERNAL') || die();

/**
 * Data provider for the events query
 */
class events extends provider {

    /**
     * Defines the internal database columns which can be ordered by
     */
    const ALLOWED_ORDER_BY_COLUMNS = [
        'id',
        'timecreated',
        'timemodified',
        'facetoface',
        'normalcost',
        'discountcost',
        'registrationtimestart',
        'registrationtimefinish',
    ];

    /**
     * @inheritDoc
     * @throws coding_exception
     */
    public static function create(?filter_factory $filter_factory = null): self {
        $builder = (new event_query())->get_builder();

        // Provide a stub entity to fulfill the repository adapter's parameter requirement; the entity itself is not used.
        $event_entity_stub = new class extends entity {
            public const TABLE = 'does_not_exist';
        };

        $repository_adapter = new query_builder_to_repository_adapter($event_entity_stub::class, $builder);
        $filter_factory = $filter_factory ?? new event_filter_factory($builder);

        return new self($repository_adapter, self::ALLOWED_ORDER_BY_COLUMNS, $filter_factory);
    }

    /**
     * @inheritDoc
     */
    public static function get_type(): string {
        return 'facetoface_event';
    }

    /**
     * @inheritDoc
     */
    public static function get_summary_format_select() {
        return 'description';
    }
}
