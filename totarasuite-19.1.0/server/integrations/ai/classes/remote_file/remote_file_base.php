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

namespace core_ai\remote_file;

use core_ai\configuration\config_collection;
use core_ai\feature\generative_prompt_with_file\prompt;
use core_ai\model\interaction_log;
use core_ai\remote_file\response\create;
use core_ai\remote_file\response\delete;
use core_ai\remote_file\response\list_files;
use core_ai\remote_file\response\retrieve;
use curl;

/**
 * Abstract base class for remote file operations with AI providers
 *
 * This class provides a standardized interface for interacting with file operations
 * across various AI providers (OpenAI, Azure, etc.). It implements a template pattern
 * where public methods handle common operations such as logging, while provider-specific
 * implementations are delegated to abstract protected methods.
 *
 * This class is designed for file operations that interact with remote file storage services.
 * It publishes multiple methods for listing, retrieving, uploading, downloading, and deleting files.
 *
 * ## Implementation Guidelines
 *
 * To implement a concrete remote_file for a specific AI provider:
 *
 * 1. Create a subclass that extends this abstract class
 * 2. Implement the abstract methods to connect with your specific AI provider's API
 * 3. Ensure proper conversion between the provider's response format and our standardized response objects
 * 4. Register your implementation via a provider class that implements provider_interface
 *
 * ## Architecture Pattern
 *
 * This class follows a template method pattern where:
 * - Public methods define the overall operation flow and handle cross-cutting concerns (logging)
 * - Protected abstract methods are implemented by concrete subclasses to handle provider-specific API calls
 * - Each operation follows a consistent pattern of:
 *   1. Create a request object with operation details
 *   2. Log the request
 *   3. Call the provider-specific implementation
 *   4. Log the response
 *   5. Return the standardized response
 *
 * ## Example Implementation
 *
 * ```php
 * class my_provider_file extends remote_file {
 *     public static function get_name(): string {
 *         return 'My Provider Files';
 *     }
 *
 *     protected function call_list(request $request): list_response {
 *         // Call provider-specific API and convert response to our format
 *         $provider_response = $this->api_client->listFiles();
 *         $response = new list_response();
 *         // ... convert and return
 *         return $response;
 *     }
 *
 *     // Implement other abstract methods...
 * }
 * ```
 */
abstract class remote_file_base {

    /** @var config_collection Configuration parameters for the remote file implementation */
    protected config_collection $config;

    /** @var string Class name to use for interaction logging */
    protected string $interaction_class_name;

    /** @var array List of file responses */
    protected array $file_responses = [];

    /**
     * Constructor for remote_file implementations
     *
     * @param config_collection $config Configuration parameters
     * @param string $interaction_class_name Class name to use for interaction logging
     */
    public function __construct(config_collection $config, string $interaction_class_name) {
        $this->config = $config;
        $this->interaction_class_name = $interaction_class_name;
    }

    /**
     * Destructor to ensure any uploaded files are cleaned up
     */
    public function __destruct() {
        $this->cleanup_files();
    }

    /**
     * Get the human-readable name of this remote_file implementation
     *
     * This name is used in the UI to identify this implementation and should
     * match the name returned by the corresponding provider's get_name() method.
     *
     * @return string Human-readable name of the implementation
     */
    abstract public static function get_name(): string;

    /**
     * Returns a list of files that belong to the user's organization.
     *
     * This method lists all files available from the AI provider. It handles
     * logging the request and response while delegating the actual API call
     * to the concrete implementation's call_list method.
     *
     * @return list_files Response containing the list of files
     */
    public function list(): list_files {
        $request = new remote_file_request();
        $request->set_action('list');

        $interaction_log = interaction_log::create_from_remote_file($request, $this);
        $response = $this->call_list($request);
        $interaction_log->log_response($response);

        return $response;
    }

    /**
     * Returns information about a specific file.
     *
     * This method retrieves metadata about a specific file from the AI provider.
     * It handles logging the request and response while delegating the actual API
     * call to the concrete implementation's call_retrieve method.
     *
     * @param string $file File ID to retrieve
     * @return retrieve Response containing the file information
     */
    public function retrieve(string $file): retrieve {
        $request = new remote_file_request();
        $request->set_action('retrieve');
        $request->set_file_id($file);

        $interaction_log = interaction_log::create_from_remote_file($request, $this);
        $response = $this->call_retrieve($request);
        $interaction_log->log_response($response);

        return $response;
    }

    /**
     * Returns the contents of the specified file.
     *
     * This method downloads the actual content of a file from the AI provider.
     * It handles logging the request and response while delegating the actual
     * API call to the concrete implementation's call_download method.
     *
     * @param string $file File ID to download
     * @return string File contents
     */
    public function download(string $file): string {
        $request = new remote_file_request();
        $request->set_action('download');
        $request->set_file_id($file);

        interaction_log::create_from_remote_file($request, $this);
        return $this->call_download($request);
    }

    /**
     * Upload a file that contains document(s) to be used across various endpoints/features.
     *
     * This method handles file uploads, delegating to the concrete implementation's
     * call_upload method for supported file types, or falling back to text file
     * handling for unsupported types.
     *
     * @param array<string, mixed> $parameters Upload parameters including file path and purpose
     * @return create Response containing information about the uploaded file
     */
    public function upload(array $parameters): create {
        if (!isset($parameters['file'])) {
            throw new \coding_exception('File parameter is required for upload');
        }

        // Handle as an uploadable file
        $request = new remote_file_request();
        $request->set_action('upload');
        $request->set_parameters($parameters);

        $interaction_log = interaction_log::create_from_remote_file($request, $this);
        $response = $this->call_upload($request);
        $interaction_log->log_response($response);

        return $response;
    }

    /**
     * Check if the file type is supported for upload
     *
     * @param array<string, mixed> $parameters Upload parameters including file path
     * @return bool True if the file type is supported for upload
     */
    protected function is_supported_upload_type(array $parameters): bool {
        return false;
    }

    /**
     * Delete a file.
     *
     * This method deletes a file from the AI provider. It handles logging the
     * request and response while delegating the actual API call to the concrete
     * implementation's call_delete method.
     *
     * @param string $file File ID to delete
     * @return delete Response confirming deletion
     */
    public function delete(string $file): delete {
        $request = new remote_file_request();
        $request->set_action('delete');
        $request->set_file_id($file);

        $interaction_log = interaction_log::create_from_remote_file($request, $this);
        $response = $this->call_delete($request);
        $interaction_log->log_response($response);

        return $response;
    }

    /**
     * Get the class name used for interaction logging
     *
     * @return string Interaction class name
     */
    public function get_interaction_class_name(): string {
        return $this->interaction_class_name;
    }

    /**
     * Get the configuration collection
     *
     * @return config_collection Configuration parameters
     */
    public function get_config(): config_collection {
        return $this->config;
    }

    /**
     * Lists files from the AI provider.
     *
     * Concrete implementations should implement this method to call their
     * specific AI provider's API to list available files and convert the
     * response to our standardized list_response format.
     *
     * @param remote_file_request $request Request object containing action and parameters
     * @return list_files Standardized response containing file list
     */
    abstract protected function call_list(remote_file_request $request): list_files;

    /**
     * Retrieves file information from the AI provider.
     *
     * Concrete implementations should implement this method to call their
     * specific AI provider's API to retrieve file information and convert
     * the response to our standardized retrieve_response format.
     *
     * @param remote_file_request $request Request object containing file ID and other parameters
     * @return retrieve Standardized response containing file information
     */
    abstract protected function call_retrieve(remote_file_request $request): retrieve;

    /**
     * Downloads file content from the AI provider.
     *
     * Concrete implementations should implement this method to call their
     * specific AI provider's API to download a file's content and return
     * it as a string.
     *
     * @param remote_file_request $request Request object containing file ID and other parameters
     * @return string File contents
     */
    abstract protected function call_download(remote_file_request $request): string;

    /**
     * Uploads file to the AI provider.
     *
     * Concrete implementations should implement this method to call their
     * specific AI provider's API to upload a file and convert the response
     * to our standardized create_response format.
     *
     * @param remote_file_request $request Request object containing file path, purpose, and other parameters
     * @return create Standardized response containing uploaded file information
     */
    abstract protected function call_upload(remote_file_request $request): create;

    /**
     * Deletes file from the AI provider.
     *
     * Concrete implementations should implement this method to call their
     * specific AI provider's API to delete a file and convert the response
     * to our standardized delete_response format.
     *
     * @param remote_file_request $request Request object containing file ID and other parameters
     * @return delete Standardized response confirming deletion
     */
    abstract protected function call_delete(remote_file_request $request): delete;

    /**
     * Create a prompt object for the file
     *
     * @param string $description Optional description of the file
     * @return prompt Prompt object with the appropriate structure for the file
     */
    public function get_prompt(string $description = ''): prompt {
        $structured_content = [];
        foreach ($this->file_responses as $response) {
            // Use the response's get_prompt method directly if it exists
            if (method_exists($response, 'get_prompt')) {
                $prompt_item = $response->get_prompt();
                if ($prompt_item !== null) {
                    $structured_content[] = $prompt_item;
                }
            }
        }
        if (empty($structured_content)) {
            return new prompt('', prompt::USER_ROLE);
        }

        return new prompt('', prompt::USER_ROLE, $structured_content);
    }

    /**
     * Get the list of file responses
     *
     * @return array List of file responses
     */
    public function get_file_responses(): array {
        return $this->file_responses;
    }

    /**
     * Add a file response to the list
     *
     * @param mixed $response The file response to add
     */
    protected function add_file_response($response): void {
        $this->file_responses[] = $response;
    }

    /**
     * Clear all file responses
     */
    protected function clear_file_responses(): void {
        $this->file_responses = [];
    }

    /**
     * Clean up any uploaded files from the remote service
     *
     * This method should be called when files are no longer needed to ensure
     * they are properly removed from the remote service.
     *
     * @return bool True if cleanup was successful, false otherwise
     */
    public function cleanup_files(): bool {
        $success = true;
        // If no files were uploaded, return true
        if (empty($this->file_responses)) {
            return true;
        }
        // Iterate through all uploaded files and delete them
        foreach ($this->file_responses as $response) {
            if (method_exists($response, 'get_id') && !empty($response->get_id())) {
                try {
                    $delete_response = $this->delete($response->get_id());
                    // Check if deletion was successful
                    if (!$delete_response->is_deleted()) {
                        $success = false;
                    }
                } catch (\Exception $e) {
                    // Log error and continue with other files
                    $success = false;
                }
            }
        }

        // Clear the upload responses array after cleanup
        if ($success) {
            $this->file_responses = [];
        }

        return $success;
    }

    /**
     * Creates a curl HTTPS client to use for API requests.
     *
     * @return curl
     */
    protected static function new_https_client(): curl {
        $curl = new curl();
        // Don't time out during HTTP transfer, but do time out if no connection.
        $curl->setopt([
            'CURLOPT_TIMEOUT' => 0,
            'CURLOPT_CONNECTTIMEOUT' => 5,
            'CURLOPT_PROTOCOLS' => CURLPROTO_HTTPS
        ]);
        return $curl;
    }
}
