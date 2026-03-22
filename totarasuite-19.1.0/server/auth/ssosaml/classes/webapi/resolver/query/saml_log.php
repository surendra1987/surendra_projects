<?php
/**
 * This file is part of Totara Talent Experience Platform
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
 * @author Simon Chester <simon.chester@totara.com>
 * @package auth_ssosaml
 */

namespace auth_ssosaml\webapi\resolver\query;

use core\pagination\offset_cursor;
use core\webapi\execution_context;
use core\webapi\query_resolver;
use auth_ssosaml\data_provider\saml_log as data_provider;
use auth_ssosaml\entity\filter\saml_log_filter_factory;
use auth_ssosaml\entity\saml_log_entry as entity;
use auth_ssosaml\model\saml_log_entry as model;
use auth_ssosaml\webapi\middleware\require_capability;

/**
 * Query a list of scheduled rules
 */
class saml_log extends query_resolver {
    /**
     * Default page size if nothing was specified.
     */
    const DEFAULT_PAGE_SIZE = 50;

    /**
     * @inheritDoc
     */
    public static function get_middleware(): array {
        return [
            require_capability::class,
        ];
    }

    /**
     * @param array $args
     * @param execution_context $ec
     * @return array
     */
    public static function resolve(array $args, execution_context $ec): array {
        $input = $args['input'] ?? [];

        // Handle our pagination (we support both methods) with boilerplate
        if (!empty($input['pagination']['cursor'])) {
            // Cursor-based, use it instead
            $cursor = offset_cursor::decode($input['pagination']['cursor']);
        } else {
            $cursor = offset_cursor::create([
                'page' => $input['pagination']['page'] ?? 1,
                'limit' => $input['pagination']['limit'] ?? self::DEFAULT_PAGE_SIZE
            ]);
        }

        $filters = [
            'idp_id' => $input['idp_id'] ?? null,
        ];

        if (isset($input['active_session'])) {
            $filters['active_session'] = $input['active_session'];
        }
        $filters['test'] = $input['test'] ?? false;

        return data_provider::create(new saml_log_filter_factory())
            ->set_order('created_at, id', 'desc')
            ->set_filters($filters)
            ->fetch_offset_paginated($cursor, function (entity $rule): model {
                return model::load_by_entity($rule);
            });
    }
}
