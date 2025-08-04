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

namespace ai_openai\remote_file;

use core_ai\configuration\config_collection;
use core_ai\remote_file\remote_file_request;
use core_ai\remote_file\response\create;
use core_ai\remote_file\response\delete;
use core_ai\remote_file\response\list_files;
use core_ai\remote_file\response\retrieve;
use core_ai\remote_file\text_file;
use curl;

class remote_file extends text_file {
    /** @var string Base URL for OpenAI API */
    private const API_BASE_URL = 'https://api.openai.com/v1/files';

    /** @var string API key for OpenAI */
    protected string $api_key;

    /** @var curl Curl instance */
    protected curl $curl;

    /** @var array List of supported file extensions for upload */
    protected const SUPPORTED_EXTENSIONS = ['pdf'];

    /** @var int Maximum uploaded file size */
    public const MAX_UPLOAD_FILE_SIZE = 1024 * 1024 * 512;

    /**
     * Get the human-readable name of this remote_file implementation
     *
     * @return string Human-readable name
     */
    public static function get_name(): string {
        return get_string('file_provider', 'ai_openai');
    }

    /**
     * Gets a new https client with the authorization: bearer header set.
     *
     * @param string $auth_key authorization key.
     * @param string $content_type curl request content type.
     *
     * @return curl the updated curl request.
     */
    private static function new_authenticated_https_client(string $auth_key, string $content_type): curl {
        $request = static::new_https_client();
        $request->setHeader(
            [
                "Content-type: $content_type",
                "Authorization: Bearer $auth_key"
            ]
        );

        return $request;
    }

    /**
     * Constructor for the openai_file class
     *
     * @param config_collection $config The configuration collection
     * @param string $interaction_class_name The interaction class name
     */
    public function __construct(config_collection $config, string $interaction_class_name) {
        parent::__construct($config, $interaction_class_name);

        // Initialize configuration
        $config_collection = $this->get_config();
        $this->api_key = $config_collection->get_value('api_key');

        // Initialize curl instance
        $this->curl = self::new_authenticated_https_client($this->api_key, 'application/json');
    }

    /**
     * Check if the file type is supported for upload
     *
     * @param array<string, mixed> $parameters Upload parameters including file path
     * @return bool True if the file type is supported for upload
     */
    protected function is_supported_upload_type(array $parameters): bool {
        if (!isset($parameters['file'])) {
            return false;
        }
        $file_path = $parameters['file'];
        $filename = $parameters['filename'] ?? basename($file_path);
        $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

        return in_array($extension, self::SUPPORTED_EXTENSIONS)
            && filesize($file_path) <= static::MAX_UPLOAD_FILE_SIZE;
    }

    /**
     * Lists files from the OpenAI API
     *
     * @param remote_file_request $request
     * @return list_files
     */
    protected function call_list(remote_file_request $request): list_files {
        $response_data = json_decode($this->curl->get(self::API_BASE_URL));

        // Convert OpenAI response to our list_files response format
        $response = new list_files();
        $files = [];

        if (isset($response_data->data)) {
            foreach ($response_data->data as $file) {
                $files[] = [
                    'id' => $file->id,
                    'filename' => $file->filename,
                    'purpose' => $file->purpose,
                    'created_at' => $file->created_at,
                    'bytes' => $file->bytes,
                    'status' => $file->status,
                ];
            }
        }

        $response->set_files($files);
        $response->set_raw_data((array)$response_data);

        return $response;
    }

    /**
     * Retrieves file information from the OpenAI API
     *
     * @param remote_file_request $request
     * @return retrieve
     */
    protected function call_retrieve(remote_file_request $request): retrieve {
        $file_id = $request->get_file_id();
        $url = self::API_BASE_URL . '/' . $file_id;

        $response_data = json_decode($this->curl->get($url));

        // Convert OpenAI response to our retrieve response format
        $response = new retrieve();
        $response->set_id($response_data->id)
            ->set_filename($response_data->filename)
            ->set_purpose($response_data->purpose)
            ->set_created_at($response_data->created_at)
            ->set_bytes($response_data->bytes);

        $response->set_raw_data((array)$response_data);

        return $response;
    }

    /**
     * Downloads file content from the OpenAI API
     *
     * @param remote_file_request $request
     * @return string File contents
     */
    protected function call_download(remote_file_request $request): string {
        $file_id = $request->get_file_id();
        $url = self::API_BASE_URL . '/' . $file_id . '/content';

        return $this->curl->get($url);
    }

    /**
     * Uploads file to the OpenAI API
     *
     * @param remote_file_request $request
     * @return create
     */
    protected function call_upload(remote_file_request $request): create {
        $parameters = $request->get_parameters();
        // Check if this is a supported file type
        if (!$this->is_supported_upload_type($parameters)) {
            // Fall back to parent class for text files
            // This will set the appropriate callback for text files
            return parent::call_upload($request);
        }

        // Create a special upload curl client that isn't JSON.
        $upload_curl_client = self::new_authenticated_https_client($this->api_key, 'multipart/form-data');

        // Format parameters for multipart/form-data
        $post_data = [
            'purpose' => $parameters['purpose'] ?? 'user_data',
        ];
        $file_path = $parameters['file'];
        $file_name = $parameters['filename'] ?? basename($file_path);

        // Create CURLFile with proper mime type and postname
        $post_data['file'] = new \CURLFile($file_path, null, $file_name);

        // Perform the upload
        $response_data = json_decode(
            $upload_curl_client->post(self::API_BASE_URL, $post_data)
        );

        // Convert OpenAI response to our create response format
        $response = new create();

        // Set ID from response data if available
        if (isset($response_data->id) && $response_data->id !== null) {
            $response->set_id($response_data->id);
        }
        if (isset($response_data->filename) && $response_data->filename !== null) {
            $response->set_filename($response_data->filename);
        }
        if (isset($response_data->purpose) && $response_data->purpose !== null) {
            $response->set_purpose($response_data->purpose);
        }
        if (isset($response_data->created_at) && $response_data->created_at !== null) {
            $response->set_created_at($response_data->created_at);
        }
        if (isset($response_data->bytes) && $response_data->bytes !== null) {
            $response->set_bytes($response_data->bytes);
        }

        // Set status if available
        if (isset($response_data->status) && $response_data->status !== null) {
            $response->set_status($response_data->status);
        } else {
            $response->set_status('uploaded');
        }

        // Set the prompt callback
        $response->set_prompt_callback([self::class, 'response_to_prompt']);
        $response->set_raw_data((array)$response_data);

        // Append the response to the file_responses list
        $this->add_file_response($response);
        return $response;
    }

    /**
     * Deletes file from the OpenAI API
     *
     * @param remote_file_request $request
     * @return delete
     */
    protected function call_delete(remote_file_request $request): delete {
        $file_id = $request->get_file_id();
        if (!str_starts_with($file_id, 'file-')) {
            return parent::call_delete($request);
        }
        $url = self::API_BASE_URL . '/' . $file_id;

        $response_data = json_decode($this->curl->delete($url));

        // Convert OpenAI response to our delete response format
        $response = new delete();
        $response->set_id($response_data->id)
            ->set_deleted($response_data->deleted);

        $response->set_raw_data((array)$response_data);

        return $response;
    }



    /**
     * Generate a prompt item for a file response (static version)
     * This is used as a callback by the response object
     *
     * @param create $response The file response to generate a prompt for
     * @return array|null The prompt item array or null if not handled
     */
    public static function response_to_prompt(create $response): ?array {
        if (empty($response->get_id())) {
            return null;
        }
        return [
            'type' => 'input_file',
            'file_id' => $response->get_id()
        ];
    }
}
