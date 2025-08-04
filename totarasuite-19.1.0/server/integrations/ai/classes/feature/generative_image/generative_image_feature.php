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
 * @author Andrew Watkins <andrew.watkins@pixelfusion.co.nz>
 * @package core_ai
 */

namespace core_ai\feature\generative_image;

use coding_exception;
use core_ai\feature\feature_base;

/**
 * Generative image abstract class; create a concrete implementation in any ai connector
 * plugin that supports generating an image.
 */
abstract class generative_image_feature extends feature_base {

    /**
     * Get the display name for this feature.
     *
     * @return string
     * @throws coding_exception
     */
    public static function get_name(): string {
        return get_string('feature_generative_image', 'ai');
    }

    /**
     * Generative image requests can include many different parameters. Interactions will want to
     * validate requests before run() is called.
     *
     * @param request $request
     * @return void
     */
    abstract public function validate_request(request $request): void;

}
