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

namespace fixtures;

use core_ai\remote_file\remote_file_base;
use core_ai\remote_file\remote_file_request;
use core_ai\remote_file\response\create;
use core_ai\remote_file\response\delete;
use core_ai\remote_file\response\list_files;
use core_ai\remote_file\response\retrieve;

/**
 * Sample remote file implementation for testing
 */
class sample_remote_file extends remote_file_base {

    /**
     * Get the name of this remote file implementation
     *
     * @return string
     */
    public static function get_name(): string {
        return 'sample remote file';
    }

    /**
     * Lists sample files
     *
     * @param remote_file_request $request
     * @return list_files
     */
    protected function call_list(remote_file_request $request): list_files {
        $response = new list_files();

        $files = [
            [
                'id' => 'file1',
                'filename' => 'sample.txt',
                'purpose' => 'assistants',
                'created_at' => 1698765432,
                'bytes' => 1024,
                'status' => 'processed',
            ],
            [
                'id' => 'file2',
                'filename' => 'example.json',
                'purpose' => 'fine-tune',
                'created_at' => 1698754321,
                'bytes' => 2048,
                'status' => 'processed',
            ],
        ];

        $response->set_files($files);
        $response->set_raw_data($files);

        return $response;
    }

    /**
     * Retrieves information about a sample file
     *
     * @param remote_file_request $request
     * @return retrieve
     */
    protected function call_retrieve(remote_file_request $request): retrieve {
        $file_id = $request->get_file_id();
        $response = new retrieve();

        if ($file_id === 'file1') {
            $response->set_id('file1')
                ->set_filename('sample.txt')
                ->set_purpose('assistants')
                ->set_created_at(1698765432)
                ->set_bytes(1024);
        } else {
            $response->set_id('file2')
                ->set_filename('example.json')
                ->set_purpose('fine-tune')
                ->set_created_at(1698754321)
                ->set_bytes(2048);
        }

        $response->set_raw_data($response->to_array());

        return $response;
    }

    /**
     * Downloads content of a sample file
     *
     * @param remote_file_request $request
     * @return string File contents
     */
    protected function call_download(remote_file_request $request): string {
        $file_id = $request->get_file_id();

        if ($file_id === 'file1') {
            return 'This is the content of sample.txt';
        } else {
            return '{"name":"example","value":42}';
        }
    }

    /**
     * Uploads a file to the sample implementation
     *
     * @param remote_file_request $request
     * @return create
     */
    protected function call_upload(remote_file_request $request): create {
        $parameters = $request->get_parameters();
        $response = new create();

        $filename = basename($parameters['file']);

        $response->set_id('new_file')
            ->set_filename($filename)
            ->set_purpose($parameters['purpose'])
            ->set_created_at(time())
            ->set_bytes(512)
            ->set_status('uploaded');

        $response->set_prompt_callback([self::class, 'response_to_prompt']);
        $response->set_raw_data($response->to_array());
        // Append the response to the file_responses list
        $this->add_file_response($response);
        return $response;
    }

    /**
     * Deletes a sample file
     *
     * @param remote_file_request $request
     * @return delete
     */
    protected function call_delete(remote_file_request $request): delete {
        $file_id = $request->get_file_id();
        $response = new delete();

        $response->set_id($file_id)
            ->set_deleted(true);

        $response->set_raw_data($response->to_array());

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
