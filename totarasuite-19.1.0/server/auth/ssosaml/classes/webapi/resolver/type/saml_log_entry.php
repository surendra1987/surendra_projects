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

namespace auth_ssosaml\webapi\resolver\type;

use auth_ssosaml\model\saml_log_entry as model;
use auth_ssosaml\webapi\formatter\saml_log_entry_formatter;
use coding_exception;
use core\format;
use core\webapi\execution_context;
use core\webapi\type_resolver;

class saml_log_entry extends type_resolver {
    /**
     * @param string $field
     * @param $source
     * @param array $args
     * @param execution_context $ec
     * @return mixed|void
     */
    public static function resolve(string $field, $source, array $args, execution_context $ec) {
        if (!($source instanceof model)) {
            throw new coding_exception('Expected a saml_log_entry model');
        }

        $formatter = new saml_log_entry_formatter($source, $source->get_context());

        return $formatter->format($field, $args['format'] ?? format::FORMAT_PLAIN);
    }
}
