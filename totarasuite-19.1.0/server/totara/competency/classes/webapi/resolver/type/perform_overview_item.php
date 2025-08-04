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
use totara_competency\models\perform_overview\item;
use totara_competency\formatter\perform_overview\item as formatter;

/**
 * Maps the totara_competency\models\perform_overview\item class into a GraphQL
 * totara_competency_perform_overview_item type.
 */
class perform_overview_item extends type_resolver {
    /**
     * Default formats
     */
    private const FORMATS = [
        formatter::ACHIEVEMENT_DATE => date_format::FORMAT_DATELONG,
        formatter::ACHIEVEMENT_LEVEL => format::FORMAT_PLAIN,
        formatter::ASSIGNMENT_DATE => date_format::FORMAT_DATELONG,
        formatter::ASSIGNMENT_TYPE => format::FORMAT_PLAIN,
        formatter::COMPETENCY_DESC => format::FORMAT_HTML,
        formatter::COMPETENCY_NAME => format::FORMAT_PLAIN
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
        if (!$source instanceof item) {
            throw new coding_exception(
                __METHOD__ . ' requires an input ' . item::class
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
