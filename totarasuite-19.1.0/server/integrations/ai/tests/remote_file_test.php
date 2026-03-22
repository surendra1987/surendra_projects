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

use core_ai\remote_file\response\create;
use core_ai\remote_file\response\delete;
use core_ai\remote_file\response\list_files;
use core_ai\remote_file\response\retrieve;
use core_phpunit\testcase;
use core\session\manager;
use core_ai\entity\interaction_log;
use core_ai\configuration\config_collection;
use fixtures\sample_remote_file;
use fixtures\sample_remote_file_provider;

require_once(__DIR__ . '/fixtures/sample_remote_file.php');
require_once(__DIR__ . '/fixtures/sample_remote_file_provider.php');

/**
 * @group ai_integrations
 */
class core_ai_remote_file_test extends testcase {

    /**
     * Test the get_name method
     */
    public function test_get_name(): void {
        $this->assertEquals('sample remote file', sample_remote_file::get_name());
    }

    /**
     * Test listing files
     */
    public function test_list_files(): void {
        $this->setAdminUser();
        $sink = $this->redirectEvents();

        $config = new config_collection([]);
        $remote_file = (new sample_remote_file_provider())->get_instance($config);

        // List files
        $response = $remote_file->list();

        // Check response
        $this->assertInstanceOf(list_files::class, $response);
        $files = $response->get_files();
        $this->assertCount(2, $files);
        $this->assertEquals('file1', $files[0]['id']);
        $this->assertEquals('sample.txt', $files[0]['filename']);
        $this->assertEquals('file2', $files[1]['id']);
        $this->assertEquals('example.json', $files[1]['filename']);

        // Verify logs
        $interaction_logs = interaction_log::repository()->get()->all();
        $this->assertCount(1, $interaction_logs);

        /** @var interaction_log $log */
        $log = $interaction_logs[0];

        $this->assertEquals(manager::get_realuser()->id, $log->user_id);
        $this->assertEquals(sample_remote_file::class, $log->feature);

        // Check request and response were logged
        $request_data = json_decode($log->request, true);
        $this->assertEquals('list', $request_data['action']);

        $response_data = json_decode($log->response, true);
        $this->assertArrayHasKey('files', $response_data);
        $this->assertCount(2, $response_data['files']);
    }

    /**
     * Test retrieving file information
     */
    public function test_retrieve_file(): void {
        $this->setAdminUser();

        $config = new config_collection([]);
        $remote_file = (new sample_remote_file_provider())->get_instance($config);

        // Retrieve file information
        $response = $remote_file->retrieve('file1');

        // Check response
        $this->assertInstanceOf(retrieve::class, $response);
        $this->assertEquals('file1', $response->get_id());
        $this->assertEquals('sample.txt', $response->get_filename());
        $this->assertEquals('assistants', $response->get_purpose());
        $this->assertEquals(1698765432, $response->get_created_at());
        $this->assertEquals(1024, $response->get_bytes());

        // Verify logs
        $interaction_logs = interaction_log::repository()->get()->all();
        $this->assertCount(1, $interaction_logs);

        /** @var interaction_log $log */
        $log = $interaction_logs[0];
        $this->assertEquals(manager::get_realuser()->id, $log->user_id);
        // Check request and response were logged
        $request_data = json_decode($log->request, true);
        $this->assertEquals('retrieve', $request_data['action']);
        $this->assertEquals('file1', $request_data['file_id']);

        $response_data = json_decode($log->response, true);
        $this->assertEquals('file1', $response_data['id']);
        $this->assertEquals('sample.txt', $response_data['filename']);
    }

    /**
     * Test downloading file content
     */
    public function test_download_file(): void {
        $this->setAdminUser();

        $config = new config_collection([]);
        $remote_file = (new sample_remote_file_provider())->get_instance($config);

        // Download file content
        $content = $remote_file->download('file1');

        // Check content
        $this->assertEquals('This is the content of sample.txt', $content);

        // Verify logs
        $interaction_logs = interaction_log::repository()->get()->all();
        $this->assertCount(1, $interaction_logs);

        /** @var interaction_log $log */
        $log = $interaction_logs[0];

        // Check request was logged
        $request_data = json_decode($log->request, true);
        $this->assertEquals('download', $request_data['action']);
        $this->assertEquals('file1', $request_data['file_id']);
    }

    /**
     * Test uploading a file
     */
    public function test_upload_file(): void {
        $this->setAdminUser();

        $config = new config_collection([]);
        $remote_file = (new sample_remote_file_provider())->get_instance($config);

        // Prepare upload parameters
        $parameters = [
            'file' => __DIR__ . '/fixtures/testfile.txt',
            'purpose' => 'assistants'
        ];

        // Upload file
        $response = $remote_file->upload($parameters);

        // Check response
        $this->assertInstanceOf(create::class, $response);
        $this->assertEquals('new_file', $response->get_id());
        $this->assertEquals('testfile.txt', $response->get_filename());
        $this->assertEquals('assistants', $response->get_purpose());
        $this->assertEquals(512, $response->get_bytes());
        $this->assertTrue($response->get_created_at() > 0);
        $this->assertEquals('uploaded', $response->get_status());

        // Verify logs
        $interaction_logs = interaction_log::repository()->get()->all();
        $this->assertCount(1, $interaction_logs);

        /** @var interaction_log $log */
        $log = $interaction_logs[0];

        // Check request and response were logged
        $request_data = json_decode($log->request, true);
        $this->assertEquals('upload', $request_data['action']);
        $this->assertEquals($parameters, $request_data['parameters']);

        $response_data = json_decode($log->response, true);
        $this->assertEquals('new_file', $response_data['id']);
        $this->assertEquals('testfile.txt', $response_data['filename']);
    }

    /**
     * Test deleting a file
     */
    public function test_delete_file(): void {
        $this->setAdminUser();

        $config = new config_collection([]);
        $remote_file = (new sample_remote_file_provider())->get_instance($config);

        // Delete file
        $response = $remote_file->delete('file1');

        // Check response
        $this->assertInstanceOf(delete::class, $response);
        $this->assertEquals('file1', $response->get_id());
        $this->assertTrue($response->is_deleted());

        // Verify logs
        $interaction_logs = interaction_log::repository()->get()->all();
        $this->assertCount(1, $interaction_logs);

        /** @var interaction_log $log */
        $log = $interaction_logs[0];

        // Check request and response were logged
        $request_data = json_decode($log->request, true);
        $this->assertEquals('delete', $request_data['action']);
        $this->assertEquals('file1', $request_data['file_id']);

        $response_data = json_decode($log->response, true);
        $this->assertEquals('file1', $response_data['id']);
        $this->assertTrue($response_data['deleted']);
    }
}
