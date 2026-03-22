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
 * @author Andrew Watkins <andrew.watkins@pixelfusion.co.nz>
 * @package core_ai
 */

namespace core_ai\remote_file;

use core_ai\remote_file\response\list_files;
use core_ai\remote_file\response\retrieve;
use core_ai\remote_file\response\create;
use core_ai\remote_file\response\delete;

/**
 * Implementation of remote_file for text files that are included directly in prompts
 * rather than being uploaded to the AI provider.
 */
class text_file extends remote_file_base {
    /** @var array List of text file extensions that should be handled as text */
    protected const TEXT_FILE_EXTENSIONS = ['txt', 'log', 'md', 'csv', 'html', 'htm'];

    /** @var string Path to the text file */
    protected string $file_path;

    /** @var string Content of the text file */
    protected string $content;

    /**
     * Get the human-readable name of this remote_file implementation
     *
     * @return string Human-readable name
     */
    public static function get_name(): string {
        return get_string('text_files', 'ai');
    }


    /**
     * Lists files from the text file implementation
     *
     * @param remote_file_request $request
     * @return list_files
     */
    protected function call_list(remote_file_request $request): list_files {
        $response = new list_files();
        $files = [];

        foreach ($this->file_responses as $response) {
            if (method_exists($response, 'get_id') && !empty($response->get_id())) {
                $files[] = [
                    'id' => $response->get_id(),
                    'filename' => $response->get_filename(),
                    'bytes' => $response->get_bytes(),
                    'created_at' => $response->get_created_at(),
                    'status' => $response->get_status(),
                ];
            }
        }

        $response->set_files($files);
        return $response;
    }

    /**
     * Retrieves file information from the text file implementation
     *
     * @param remote_file_request $request
     * @return retrieve
     */
    protected function call_retrieve(remote_file_request $request): retrieve {
        $file_id = $request->get_file_id();

        foreach ($this->file_responses as $response) {
            if (method_exists($response, 'get_id') && $response->get_id() === $file_id) {
                $retrieve_response = new retrieve();
                $retrieve_response->set_id($response->get_id())
                    ->set_filename($response->get_filename())
                    ->set_bytes($response->get_bytes())
                    ->set_created_at($response->get_created_at());
                return $retrieve_response;
            }
        }

        throw new \coding_exception('File not found');
    }

    /**
     * Downloads file content from the text file implementation
     *
     * @param remote_file_request $request
     * @return string File contents
     */
    protected function call_download(remote_file_request $request): string {
        $file_id = $request->get_file_id();
        return file_get_contents($file_id);
    }

    /**
     * Uploads file to the text file implementation
     *
     * @param remote_file_request $request
     * @return create
     */
    protected function call_upload(remote_file_request $request): create {
        $parameters = $request->get_parameters();

        $file_path = $parameters['file'];
        if (is_resource($file_path)) {
            $meta = stream_get_meta_data($file_path);
            $file_path = $meta['uri'];
        }
        // Note: file content read is deferred until the response_to_prompt method

        // Create a response for this text file
        $response = new create();
        $response->set_id($file_path)
            ->set_filename(basename($file_path))
            ->set_bytes(filesize($file_path))
            ->set_created_at(time())
            ->set_status('processed')
            ->set_prompt_callback([self::class, 'response_to_prompt']);

        $this->add_file_response($response);
        return $response;
    }

    /**
     * Deletes file from the text file implementation
     *
     * @param remote_file_request $request
     * @return delete
     */
    protected function call_delete(remote_file_request $request): delete {
        $file_id = $request->get_file_id();

        foreach ($this->file_responses as $key => $response) {
            if (method_exists($response, 'get_id') && $response->get_id() === $file_id) {
                unset($this->file_responses[$key]);
                $delete_response = new delete();
                $delete_response->set_id($file_id)
                    ->set_deleted(true);
                return $delete_response;
            }
        }

        throw new \coding_exception('File not found');
    }

    /**
     * Generate a prompt item for a file response (static version)
     * This is used as a callback by the response object
     *
     * @param create $response The file response to generate a prompt for
     * @return array|null The prompt item array or null if not handled
     */
    public static function response_to_prompt(create $response): ?array {
        $file_path = $response->get_id();
        $content = $response->get_file_content();

        // If we don't have content in the response, try to read it from the file
        if ($content === null && file_exists($file_path)) {
            $content = file_get_contents($file_path);
        }
        if ($content !== null) {
            return [
                'type' => 'input_text',
                'text' => "File: {$file_path}\n\n{$content}"
            ];
        }

        return null;
    }
}
