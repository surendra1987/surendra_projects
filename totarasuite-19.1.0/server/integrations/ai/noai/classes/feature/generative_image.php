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
 * @author Chris Snyder <chris.snyder@totara.com>
 * @package ai_noai
 */

namespace ai_noai\feature;

use coding_exception;
use core_ai\feature\generative_image\generative_image_feature;
use core_ai\feature\generative_image\request;
use core_ai\feature\generative_image\response;
use core_ai\feature\request_base;
use wikimedia;

/**
 * No AI implementation of generative_image feature.
 */
class generative_image extends generative_image_feature {

    public function validate_request(request_base|request $request): void {
        // Validate prompt
        $prompt = $request->get_prompt();
        if (empty($prompt)) {
            throw new coding_exception('Prompt is required');
        }
    }

    /**
     * Handles a generative prompt request to No AI.
     *
     * @param request $request
     * @return response
     */
    protected function call_api(request_base|request $request): response {
        global $CFG;
        require_once $CFG->dirroot . '/repository/wikimedia/wikimedia.php';

        // Check if the interaction implements a noai_response() method.
        $interaction_class = $this->get_interaction_class_name();
        if (method_exists($interaction_class, 'noai_response')) {
            return $interaction_class::noai_response($request, $this);
        }

        // Fetch an image from wikimedia.
        $client = new wikimedia;
        $caption = $request->get_prompt();
        $images = $client->search_images($caption, 0, ['iiurlwidth' => 1024, 'iiurlheight' => 1024]);
        $image = $images[array_rand($images)];
        $url = $image['source'];
        return new response($url, null, $caption, null);
    }
}
