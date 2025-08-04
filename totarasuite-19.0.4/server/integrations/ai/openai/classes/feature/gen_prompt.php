<?php
/*
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
 * @author Kunle Odusan <kunle.odusan@totara.com>
 */

namespace ai_openai\feature;

use ai_openai\plugininfo;
use core_ai\feature\generative_prompt;
use core_ai\feature\generative_prompt\prompt;
use core_ai\feature\generative_prompt\response;
use core_ai\feature\request;
use curl;
use Exception;

class gen_prompt extends generative_prompt {
    public const API_END_POINT = 'https://api.openai.com/v1/chat/completions';

    protected function call_api(request $request): response {
        $config_collection = $this->get_config();
        $api_key = $config_collection->get_value('api_key');
        $config_model = $config_collection->get_value('model');
        $model = !empty($config_model) ? $config_model : plugininfo::DEFAULT_MODEL;

        $curl = new curl();
        $curl->setHeader(['Content-type: application/json', 'Authorization: Bearer ' . $api_key]);

        $response_prompts = [];
        $error = null;

        try {
            $params = ['model' => $model, 'messages' => $this->get_messages($request)];
            $response = $curl->post(self::API_END_POINT, json_encode($params));
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

    private function get_messages(request $request): array {
        $messages = [];

        /** @var prompt $message*/
        foreach ($request->get_data() as $message) {
            $messages[] = [
                'role' => $message->get_role(),
                'content' => $message->get_message(),
            ];
        }

        return $messages;
    }
}
