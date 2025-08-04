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
 * @package ai_openai
 */

namespace ai_openai\feature;

use ai_openai\plugininfo;
use ai_openai\remote_file\remote_file_provider;
use core_ai\ai_exception;
use core_ai\data_transfer_object_base as dto_base;
use core_ai\feature\generative_prompt_with_file\generative_prompt_with_file_feature;
use core_ai\feature\generative_prompt_with_file\prompt;
use core_ai\feature\generative_prompt_with_file\request;
use core_ai\feature\generative_prompt_with_file\response;
use core_ai\feature\request_base;
use core_ai\feature\response_base;
use core_ai\remote_file\remote_file_provider as remote_file_provider_interface;
use Exception;

/**
 * Open AI implementation of generative_prompt_with_file feature.
 *
 * Uses the responses API.
 */
class generative_prompt_with_file extends generative_prompt_with_file_feature {
    /**
     * Get the remote file provider for this feature
     *
     * @return remote_file_provider The OpenAI file provider implementation
     */
    public function get_remote_file_provider(): remote_file_provider_interface {
        return new remote_file_provider();
    }

    /**
     * Process the API request
     *
     * @param request_base|request $request The request containing prompts
     * @return response_base The response containing generated prompts or structured data
     * @throws \coding_exception
     */
    protected function call_api(request_base|request $request): response_base {
        if (!$request instanceof request) {
            throw new \coding_exception('Invalid request type');
        }

        $config = $this->get_config();
        $api_key = $config->get_value('api_key');

        if (empty($api_key)) {
            return new response([], 'API key not configured');
        }

        $curl = static::new_https_client();
        $curl->setHeader([
            'Content-type: application/json',
            'Authorization: Bearer ' . $api_key
        ]);

        $response_data = [];
        $error = null;

        try {
            $params = [
                'model' => $this->determine_model($request),
                'input' => $this->get_messages($request)
            ];

            // Add schema if provided
            $schema = $request->get_schema();
            if ($schema !== null) {
                $params['text'] = $schema;
            }

            // Add tools if provided
            $tools = $request->get_tools();
            if ($tools !== null) {
                $params['tools'] = $tools;
            }

            // Use the configured base URL for the endpoint
            $base_url = rtrim(plugininfo::get_base_url(), '/');
            $endpoint = $base_url . '/v1/responses';
            $response = $curl->post($endpoint, dto_base::json_encode($params));
            if ($curl->get_errno()) {
                throw new ai_exception('Curl error: ' . $curl->error);
            }

            $response_obj = json_decode($response);

            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new ai_exception('Invalid JSON response: ' . json_last_error_msg());
            }

            try {
                // Convert schema array to object if it exists
                $schema_obj = $schema !== null ? (object)$schema : null;
                $response_data = $this->format_response($response_obj, $schema_obj);
            } catch (Exception $e) {
                $error = $e->getMessage();
                error_log('OpenAI Responses API Exception: ' . $error);
            }

            // Handle error cases
            if (!empty($response_obj->error)) {
                $error = "API Error: " . $response_obj->error->message;
            }
        } catch (Exception $exception) {
            $error = $exception->getMessage();
            error_log('OpenAI API Exception: ' . $error);
        }

        return new response($response_data, $error);
    }

    /**
     * Format the request data for the OpenAI API
     *
     * @param request $request The request containing prompts
     * @return array Array of messages in OpenAI format
     */
    private function get_messages(request $request): array {
        return array_map(function (prompt $message) {
            return [
                'role' => $message->get_role(),
                'content' => $message->get_content(),
            ];
        }, $request->get_prompts());
    }

    /**
     * Formats the OpenAI API response into either structured data or an array of prompts.
     *
     * @param object $response The decoded JSON response from OpenAI
     * @param object|null $schema The schema for structured output, or null for regular prompts
     * @return array Array of prompt objects or structured data
     * @throws Exception If structured response parsing fails
     */
    private function format_response(object $response, ?object $schema = null): array {
        // Try to extract content from the structured response format
        if (!empty($response->output) && is_array($response->output)) {
            $formatted_data = $this->extract_structured_content($response->output, $schema);
            if (!empty($formatted_data)) {
                return $formatted_data;
            }
        }

        // Fallback to raw response if no structured content found
        return $this->format_raw_response($response, $schema);
    }

    /**
     * Extracts content from the structured response format.
     *
     * @param array $output_items Array of output items from the response
     * @param object|null $schema The schema for structured output
     * @return array Array of prompt objects or structured data
     */
    private function extract_structured_content(array $output_items, ?object $schema): array {
        $formatted_data = [];

        foreach ($output_items as $output_item) {
            if ($output_item->status !== 'completed' || $output_item->type !== 'message') {
                continue;
            }

            if (!isset($output_item->content) || !is_array($output_item->content)) {
                continue;
            }

            foreach ($output_item->content as $content_item) {
                if (!isset($content_item->text)) {
                    continue;
                }

                if ($schema !== null) {
                    $parsed_data = json_decode($content_item->text, true);
                    if (json_last_error() === JSON_ERROR_NONE) {
                        return $parsed_data;
                    }
                } else {
                    $formatted_data[] = new prompt($content_item->text, prompt::ASSISTANT_ROLE);
                }
            }
        }

        return $formatted_data;
    }

    /**
     * Formats the raw response as a fallback.
     *
     * @param object $response The decoded JSON response
     * @param object|null $schema The schema for structured output
     * @return array Array of prompt objects or structured data
     * @throws Exception If structured response parsing fails
     */
    private function format_raw_response(object $response, ?object $schema): array {
        if ($schema !== null) {
            $response_json = json_encode($response);
            $parsed_data = json_decode($response_json, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                return $parsed_data;
            }
            throw new Exception('Failed to parse structured response: ' . json_last_error_msg());
        }

        return [new prompt(dto_base::json_encode($response), prompt::ASSISTANT_ROLE)];
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
            $config_model = plugininfo::DEFAULT_STRUCTURED_MODEL;
        }
        return $config_model;
    }
}
