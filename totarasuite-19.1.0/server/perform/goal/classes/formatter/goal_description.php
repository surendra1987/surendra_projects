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
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @author Murali Nair <murali.nair@totara.com>
 * @package perform_goal
 */

namespace perform_goal\formatter;

use core\json_editor\helper\document_helper;

/**
 * Provides formatting for a perform_goal description.
 */
class goal_description {

    /**
     * Extra goal description formatting before passing it on to the goal formatter class.
     *
     * @param ?string $description the description if any.
     *
     * @return string|null the formatted description.
     */
    public static function format(?string $description): ?string {
        $trimmed = $description ? trim($description) : null;

        if (!$trimmed) {
            return null;
        }

        if (!document_helper::looks_like_json($trimmed, true)) {
            // We only support Weka format out of the box here. So ignore if it doesn't look like JSON.
            return null;
        }

        if (document_helper::is_document_empty($trimmed)) {
            return null;
        }

        // JSON document must be sanitised before passing it on to the goal formatter.
        return document_helper::clean_json_document($trimmed);
    }
}