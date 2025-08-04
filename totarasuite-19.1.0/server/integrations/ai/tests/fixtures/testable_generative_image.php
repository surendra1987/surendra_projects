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
 */


use ai_openai\feature\generative_image as openai_generative_image;
use core_ai\feature\generative_image\response;
use core_ai\feature\request_base;
use core_ai\configuration\config_collection;

class testable_generative_image extends openai_generative_image {
    public function __construct() {
        parent::__construct(new config_collection([]), 'dummy_interaction');
    }

    public function call_api(request_base $request): response {
        // Return a dummy response for testing
        return new response('http://example.com/image.png', null, 'A test image', null);
    }
}
