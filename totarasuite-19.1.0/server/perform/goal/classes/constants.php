<?php
/*
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
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 *
 * @author Matthias Bonk <matthias.bonk@totara.com>
 * @package perform_goal
 */

namespace perform_goal;

class constants {

    /**
     * A list of possible sort options.
     * This is used for:
     *  - processing the input from GraphQL (via resolver)
     *  - telling the front end what sorting options are available (via controller)
     */
    public const EXPOSED_SORT_OPTIONS = [
        // When updating this, please also update the corresponding comment in the GraphQL schema.
        'created_at' => ['created_at', 'DESC'],
        'target_date' => ['target_date', 'ASC'],
        'most_complete' => ['completion_percent', 'DESC'],
        'least_complete' => ['completion_percent', 'ASC'],
    ];
}