<?php
/**
 *  This file is part of Totara Core
 *
 *  Copyright (C) 2024 onwards Totara Learning Solutions LTD
 *
 *  This program is free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @package core
 * @author Simon Coggins <simon.coggins@totara.com>
 */

namespace core\webapi\formatter\field;

use coding_exception;
use core\format;
use core_text;

/**
 * Formats a given backed enum into a string
 */
class backed_enum_field_formatter extends base {

    /**
     * Returns true if the format passed in the constructor is valid
     *
     * @throws coding_exception
     * @return bool
     */
    protected function validate_format(): bool {
        // Default is used if no format provided.
        if (is_null($this->format)) {
            return true;
        }

        $valid_formats = [
            format::FORMAT_RAW,
            format::FORMAT_PLAIN,
            format::FORMAT_HTML
        ];

        // Do a custom check for valid but (currently) unsupported formats for this formatter,
        // anything else should go through to base for the generic exception.
        $unsupported_formats = array_diff(format::get_available(), $valid_formats);
        if (in_array($this->format, $unsupported_formats)) {
            throw new coding_exception($this->format . ' format is currently not supported by the backed enum formatter.');
        }

        return in_array($this->format, $valid_formats);
    }

    /**
     * Formats the enum value as a plain (uppercase) string
     *
     * @param \BackedEnum $value
     * @return string
     */
    protected function format_plain(\BackedEnum $value): string {
        return core_text::strtoupper($value->value);
    }

    /**
     * Formats the enum value as an HTML string. In practice this is the same as PLAIN
     *
     * @param \BackedEnum $value
     * @return string
     */
    protected function format_html(\BackedEnum $value): string {
        return $this->format_plain($value);
    }

    /**
     * Leave it as it is
     *
     * @param \BackedEnum $value
     * @return \BackedEnum
     */
    protected function format_raw(\BackedEnum $value): \BackedEnum {
        return $value;
    }

    /**
     * The default format to apply.
     *
     * @param mixed $value
     * @return mixed
     */
    protected function get_default_format($value) {
        return $this->format_plain($value);
    }
}
