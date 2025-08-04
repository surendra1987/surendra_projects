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

namespace totara_competency\webapi\resolver\type;

use coding_exception;
use context_system;
use core\webapi\execution_context;
use core\webapi\type_resolver;
use totara_competency\models\perform_overview\overview;
use totara_competency\formatter\perform_overview\overview as formatter;

/**
 * Maps the totara_competency\models\perform_overview\overview class into a
 * GraphQL totara_competency_perform_overview_result type.
 */
class perform_overview_result extends type_resolver {
    /**
     * {@inheritdoc}
     */
    public static function resolve(
        string $field,
        $source,
        array $args,
        execution_context $ec
    ) {
        if (!$source instanceof overview) {
            throw new coding_exception(
                __METHOD__ . ' requires an input ' . overview::class
            );
        }

        $format = $args['format'] ?? null;
        $context = $ec->has_relevant_context()
            ? $ec->get_relevant_context()
            : context_system::instance();

        return (new formatter($source, $context))->format($field, $format);
    }
}
