<?php
/**
 * This file is part of Totara Learn
 *
 * Copyright (C) 2023 onwards Totara Learning Solutions LTDvs
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
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
 * @author Murali Nair <murali.nair@totaralearning.com>
 * @package totara_competency
 */

namespace totara_competency\webapi\resolver\query;

use core\pagination\offset_cursor;
use core\webapi\execution_context;
use invalid_parameter_exception;
use stdClass;
use totara_competency\data_providers\perform_overview;
use totara_competency\entity\competency_achievement;
use totara_competency\models\perform_overview\item;

/**
 * Handles the "totara_competency_perform_overview_by_state" GraphQL query.
 */
class perform_overview_by_state extends perform_overview_base {
    /**
     * {@inheritdoc}
     */
    public static function resolve(array $args, execution_context $ec) {
        $parms = self::parse($args);
        self::authorize($parms, $ec);

        [$cursor, $data_source] = self::create_paginated_data_source($args, $parms);
        return $data_source->fetch_offset_paginated(
            $cursor,
            fn(competency_achievement $achievement): item => new item($achievement)
        );
    }

    /**
     * Creates a totara_competency\data_providers\perform_overview data source.
     *
     * @param array<string,mixed> $args input arguments.
     * @param stdClass $parms the parsed values from self::parse().
     *
     * @return mixed[] [cursor cursor, perform_overview data source].
     */
    private static function create_paginated_data_source(
        array $args, stdClass $parms
    ): array {
        $state = $parms->state;
        if (!$state) {
            throw new invalid_parameter_exception('No state for overview by state');
        }

        $pagination = $args['input']['pagination'] ?? null;
        $enc_cursor = offset_cursor::create(
            [
                'page' => $pagination['page'] ?? 1,
                'limit' => $pagination['limit'] ?? perform_overview::DEFAULT_PAGE_SIZE
            ]
        );

        return [$enc_cursor, self::create_data_source($parms, $state)];
    }
}
