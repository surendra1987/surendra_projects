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

use coding_exception;
use ai_openai\plugininfo;
use core_ai\ai_exception;
use core_ai\feature\request_base;
use core_ai\feature\generative_image\generative_image_feature;
use core_ai\feature\generative_image\request;
use core_ai\feature\generative_image\response;
use Exception;

/**
 * Open AI implementation of generative_prompt feature.
 */
class generative_image extends generative_image_feature {
    // Known OpenAI image models, with supported sizes.
    private const MODELS = [
        'dall-e-2' => ['256x256', '512x512', '1024x1024'],
        'dall-e-3' => ['1024x1024', '1024x1792', '1792x1024'],
        'gpt-image-1' => ['auto', '1024x1024', '1536x1024', '1024x1536']
    ];

    /**
     * Validate a request against the feature's current configuration.
     *
     * @param request $request
     * @return void
     * @throws \coding_exception
     */
    public function validate_request(request $request): void {
        // Validate model selection if applicable
        $model = $request->get_model();
        if (!array_key_exists($model, self::MODELS)) {
            throw new coding_exception('Invalid model selection');
        }

        // Validate image size based on model
        if (!empty($request->size)) {
            $allowed_sizes = $model ? self::MODELS[$model] : [];

            if (!in_array($request->size, $allowed_sizes)) {
                throw new coding_exception(sprintf('Invalid image size for %s model', $model ?? 'selected'));
            }
        }

        // Validate quality
        if (!empty($request->quality)) {
            $allowed_qualities = ['auto', 'standard', 'hd', 'high', 'medium', 'low'];
            if (!in_array($request->quality, $allowed_qualities)) {
                throw new coding_exception('Invalid quality setting');
            }
        }

        // Validate response format
        if (!empty($request->response_format)) {
            $allowed_formats = ['url', 'b64_json'];
            if (!in_array($request->response_format, $allowed_formats)) {
                throw new coding_exception('Invalid response format');
            }
        }

        // Validate prompt
        $prompt = $request->get_prompt();
        if (empty($prompt)) {
            throw new coding_exception('Prompt is required');
        }
    }

    /**
     * Call the OpenAI API to generate an image.
     *
     * @param request_base|request $request The image generation request
     * @return response The image generation response
     */
    protected function call_api(request_base|request $request): response {
        $api_key = $this->get_api_key();
        if ($api_key instanceof response) {
            return $api_key;
        }

        try {
            // Validate the request which will also set the master prompt if configured
            $this->validate_request($request);

            $curl = static::new_https_client();
            $curl->setHeader([
                'Content-Type: application/json',
                'Authorization: Bearer ' . $api_key
            ]);

            // Use the configured base URL for the endpoint
            $base_url = rtrim(plugininfo::get_base_url(), '/');
            $endpoint = $base_url . '/v1/images/generations';
            $response_data = $curl->post($endpoint, $request->to_json());
            $response_json = json_decode($response_data);

            return match (true) {
                $response_json === null => throw new ai_exception(
                    'Invalid response from OpenAI API'
                ),

                // Return error response?
                isset($response_json->error) => new response(
                    null,
                    null,
                    null,
                    $response_json->error->message ?? 'Unknown error'
                ),

                // In this version we only return the first image - we only generate one image at a time.
                $response_json->data && count($response_json->data) > 0 => new response(
                    $response_json->data[0]->url ?? null,
                    $response_json->data[0]->b64_json ?? null,
                    $response_json->data[0]->revised_prompt
                        ?? $response_json->data[0]->revised_prompt
                        ?? null
                ),
                default => new response(null, null, 'No image data received')
            };
        } catch (coding_exception $e) {
            return new response(null, null, 'Configuration error: ' . $e->getMessage());
        } catch (Exception $e) {
            return new response(null, null, $e->getMessage());
        }
    }

    /**
     * Returns the api key from the current configuration.
     *
     * @return string|response the api key or the error response if the key
     *         could not be determined.
     */
    private function get_api_key(): string|response {
        $config = $this->get_config();
        if (!$config) {
            return new response(null, null, 'Configuration not initialized');
        }

        $api_key = $config->get_value('api_key');
        return !empty($api_key)
            ? $api_key
            : new response(null, null, 'OpenAI API key not configured');
    }
}
