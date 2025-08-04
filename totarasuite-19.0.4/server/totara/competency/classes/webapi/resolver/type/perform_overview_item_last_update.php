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
use core\format;
use core\date_format;
use core\webapi\execution_context;
use core\webapi\type_resolver;
use totara_competency\models\perform_overview\item_last_update;
use totara_competency\formatter\perform_overview\item_last_update as formatter;

/**
 * Maps the totara_competency\models\perform_overview\item_last_update class
 * into a GraphQL totara_competency_perform_overview_item_last_update type.
 */
class perform_overview_item_last_update extends type_resolver {
    /**
     * Default formats
     */
    private const FORMATS = [
        formatter::DATE => date_format::FORMAT_DATELONG,
        formatter::DESC => format::FORMAT_PLAIN
    ];

    /**
     * {@inheritdoc}
     */
    public static function resolve(
        string $field,
        $source,
        array $args,
        execution_context $ec
    ) {
        if (!$source instanceof item_last_update) {
            throw new coding_exception(
                __METHOD__ . ' requires an input ' . item_last_update::class
            );
        }

        $format = $args['format'] ?? null;
        if (!$format) {
            $format = self::FORMATS[$field] ?? null;
        }

        $context = $ec->has_relevant_context()
            ? $ec->get_relevant_context()
            : context_system::instance();

        return (new formatter($source, $context))->format($field, $format);
    }
}
