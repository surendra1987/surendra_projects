<?php
/**
 * This file is part of Totara Core
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
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @author David Curry <david.curry@totara.com>
 * @package mobile_completedlearning
 */

namespace mobile_completedlearning\webapi\resolver\type;

use core\webapi\execution_context;
use core\webapi\type_resolver;
use mobile_completedlearning\formatter\page_formatter;

class page extends type_resolver {

    /**
     * Resolve program fields
     *
     * @param string $field
     * @param \stdClass $page
     * @param array $args
     * @param execution_context $ec
     * @return mixed
     * @throws \coding_exception
     * @throws \dml_exception
     */
    public static function resolve(string $field, $page, array $args, execution_context $ec) {
        $context = $ec->has_relevant_context() ? $ec->get_relevant_context() : \context_system::instance();

        $format = $args['format'] ?? null;
        $formatter = new page_formatter($page, $context);

        // Run through the fields, overriding for mobile where necessary, most logic is done in the query already.
        switch ($field) {
            case 'total':
                return $page->total ?? null;
            case 'pointer':
                return $page->pointer ?? null;
            case 'completedLearning':
                return $page->items ?? [];
            default:
                return $formatter->format($field, $format);
        }
    }
}
