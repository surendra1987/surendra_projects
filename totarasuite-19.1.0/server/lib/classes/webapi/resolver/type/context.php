<?php
/**
 * This file is part of Totara Learn
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
 * @author Chris Snyder <chris.snyder@totara.com>
 * @package core_context
 */

namespace core\webapi\resolver\type;

use coding_exception;
use context_system;
use core\entity\context as context_entity;
use core\formatter\context as context_formatter;
use core\webapi\execution_context;
use core\webapi\type_resolver;

defined('MOODLE_INTERNAL') || die();

/**
 * Maps a context entity into the GraphQL core_context type.
 */
class context extends type_resolver {
    public static function resolve(string $field, $instance, array $args, execution_context $ec) {
        /** @var context_entity $instance */
        if (!$instance instanceof context_entity) {
            throw new coding_exception('Invalid data handed to context type resolver');
        }

        if ($field == 'name') {
            $ctx = \context::instance_by_id($instance->id);
            return $ctx->get_context_name();
        }

        $execution_context = $ec->has_relevant_context() ? $ec->get_relevant_context() : context_system::instance();
        $formatter = new context_formatter($instance, $execution_context);
        return $formatter->format($field, $args['format'] ?? null);
    }
}