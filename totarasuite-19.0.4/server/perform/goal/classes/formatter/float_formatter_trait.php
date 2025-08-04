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
 * @author Matthias Bonk <matthias.bonk@totara.com>
 * @package perform_goal
 */

namespace perform_goal\formatter;

trait float_formatter_trait {

    /**
     * For the 'current value' and 'target value' fields: Make them look nice by reducing decimal points to 5 and stripping trailing zeros.
     * Don't use a localized decimal point because the front end uses these values for calculations.
     *
     * @param float $value
     * @return string
     */
    protected function format_float(float $value): string {
        return format_float($value, 5, false, true);
    }
}