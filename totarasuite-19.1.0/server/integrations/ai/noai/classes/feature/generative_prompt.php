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

use core_ai\feature\generative_prompt\generative_prompt_feature;
use core_ai\feature\generative_prompt\prompt;
use core_ai\feature\generative_prompt\request;
use core_ai\feature\generative_prompt\response;
use core_ai\feature\request_base;

/**
 * No AI implementation of generative_prompt feature.
 */
class generative_prompt extends generative_prompt_feature {
    /**
     * Handles a generative prompt request to No AI.
     *
     * @param request $request
     * @return response
     */
    protected function call_api(request_base|request $request): response {
        // Check if the interaction implements a noai_response() method.
        $interaction_class = $this->get_interaction_class_name();
        if (method_exists($interaction_class, 'noai_response')) {
            return $interaction_class::noai_response($request, $this);
        }

        // Generic noai response: echo the prompts back.
        $response_prompts = $request->get_prompts();
        $error = null;
        return new response($response_prompts, $error);
    }
}
