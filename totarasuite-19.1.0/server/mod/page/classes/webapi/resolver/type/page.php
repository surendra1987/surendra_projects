<?php
/**
 * This file is part of Totara Core
 *
 * Copyright (C) 2025 onwards Totara Learning Solutions LTD
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
 * @author Qingyang Liu <qingyang.liu@totara.com>
 * @package mod_page
 */
namespace mod_page\webapi\resolver\type;

use coding_exception;
use core\format;
use core\webapi\execution_context;
use core\webapi\type_resolver;
use mod_page\formatter\page_formatter;
use mod_page\download\page as page_instance;

/**
 * Page type resolver
 */
class page extends type_resolver {

    /**
     * @param string $field
     * @param $source
     * @param array $args
     * @param execution_context $ec
     * @return array|mixed|null
     */
    public static function resolve(string $field, $source, array $args, execution_context $ec) {
        if (!$source instanceof page_instance) {
            throw new coding_exception('Only page object is accepted: ' . gettype($source));
        }

        return (new page_formatter($source, $source->get_mod_context()))
            ->format($field, $args['format'] ?? format::FORMAT_PLAIN);
    }
}