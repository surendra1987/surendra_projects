<?php
/**
 * This file is part of Totara Core
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
 * @author Kunle Odusan <kunle.odusan@totara.com>
 * @package ai_openai
 */

namespace ai_openai\feature;

use ai_openai\plugininfo;
use core_ai\data_transfer_object_base as dto_base;
use core_ai\feature\generative_prompt\generative_prompt_feature;
use core_ai\feature\generative_prompt\prompt;
use core_ai\feature\generative_prompt\request;
use core_ai\feature\generative_prompt\response;
use core_ai\feature\request_base;
use Exception;

/**
 * Open AI implementation of generative_prompt feature.
 *
 * Uses the chat completions API.
 */
class generative_prompt extends generative_prompt_feature {
    /**
     * Handles a generative prompt request to OpenAI.
     *
     * @param request_base $request
     * @return response
     */
    protected function call_api(request_base|request $request): response {
        $config_collection = $this->get_config();
        $api_key = $config_collection->get_value('api_key');
        $model = $this->determine_model($request);

        $base_url = rtrim(plugininfo::get_base_url(), '/');
        $api_endpoint = $base_url . '/v1/chat/completions';

        $curl = static::new_https_client();
        $curl->setHeader(['Content-type: application/json', 'Authorization: Bearer ' . $api_key]);

        $response_prompts = [];
        $error = null;

        try {
            $params = ['model' => $model, 'messages' => $this->get_messages($request)];
            $response = $curl->post($api_endpoint, dto_base::json_encode($params));
            $response = json_decode($response);
            if (!empty($response->choices)) {
                foreach ($response->choices as $choice) {
                    $response_prompts[] = new prompt($choice->message->content, prompt::ASSISTANT_ROLE);
                }
            }
        } catch (Exception $exception) {
            $error = $exception->getMessage();
        }

        return new response($response_prompts, $error);
    }

    /**
     * Returns a collection of prompts (role + message pairs) to pass to OpenAI.
     *
     * @param request $request
     * @return array
     */
    private function get_messages(request $request): array {
        $messages = [];

        /** @var prompt $message*/
        foreach ($request->get_prompts() as $message) {
            $messages[] = [
                'role' => $message->get_role(),
                'content' => $message->get_message(),
            ];
        }

        return $messages;
    }

    /**
     * Uses the model passed via the request, or the open AI setting, or the default.
     *
     * @param request $request
     * @return string
     */
    private function determine_model(request $request) {
        $request_model = $request->get_model();
        if (!empty($request_model) && $request_model != 'global') {
            // Request model might be custom, so there's no way to validate. Just trust it.
            return $request_model;
        }
        $config_model = get_config('ai_openai', 'model_selection');
        if ($config_model == 'custom') {
            $config_model = get_config('ai_openai', 'model_custom');
        }
        if (empty($config_model)) {
            $config_model = plugininfo::DEFAULT_MODEL;
        }
        return $config_model;
    }
}
