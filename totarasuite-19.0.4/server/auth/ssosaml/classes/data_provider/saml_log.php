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

namespace auth_ssosaml\data_provider;

use core\orm\entity\filter\filter_factory;
use totara_core\data_provider\provider;
use auth_ssosaml\entity\saml_log_entry;

/**
 * Data provider for SAML log.
 */
class saml_log extends provider {
    /**
     * Valid sort fields
     */
    public const SORT_FIELDS = [
        'id' => 'id',
        'created_at' => 'created_at',
    ];

    /**
     * Create a new instance of the SAML log data provider.
     *
     * @param filter_factory|null $filter_factory
     * @return provider
     */
    public static function create(?filter_factory $filter_factory = null): provider {
        return new static(
            saml_log_entry::repository(),
            self::SORT_FIELDS,
            $filter_factory
        );
    }

    /**
     * @inheritDoc
     */
    public static function get_summary_format_select(): string {
        return 'id';
    }

    /**
     * @inheritDoc
     */
    public static function get_type(): string {
        return 'saml_log_entry';
    }

    /**
     * @param string $request_id
     * @return saml_log_entry|null
     */
    public static function find_by_request_id(string $request_id): ?saml_log_entry {
        return saml_log_entry::repository()->where('request_id', $request_id)
            ->where_null('content_response')
            ->order_by('id', 'DESC')
            ->get()
            ->first();
    }

    /**
     * @param int $id
     * @return saml_log_entry|null
     */
    public static function find_by_id(int $id): ?saml_log_entry {
        return saml_log_entry::repository()->where('id', $id)
            ->order_by('id', 'DESC')
            ->get()
            ->first();
    }
}
