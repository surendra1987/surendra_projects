<?php
/**
 * This file is part of Totara Learn
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
 * @author Michael Ivanov <michael.ivanov@totaralearning.com>
 * @package totara_webapi
 */

defined('MOODLE_INTERNAL') || die();

use core_phpunit\testcase;
use totara_api\model\client;
use totara_core\advanced_feature;
use totara_webapi\controllers\external_pluginfile;
use totara_oauth2\testing\generator as oauth2_generator;

class totara_webapi_pluginfile_test extends testcase {
    /**
     * @return external_pluginfile
     */
    protected function get_pluginfile_controller_instance(): external_pluginfile {
        $class = new class extends external_pluginfile {
            protected function send_internal_server_error(string $message): void {
                throw new Exception($message);
            }
        };
        return new $class(false);
    }

    /**
     * @return void
     */
    public function test_external_pluginfile_require_token(): void {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_SERVER['SERVER_SOFTWARE'] = 'php';
        $_SERVER['HTTP_HOST'] = 'example.com';
        $_SERVER['HTTP_USER_AGENT'] = 'Mozilla/5.0 (X11; Linux x86_64; rv:100.0) Gecko/20100101 Firefox/100.0';
        $_SERVER['CONTENT_TYPE'] = 'application/json';
        $_SERVER['CONTENT_LENGTH'] = '101';

        $instance = $this->get_pluginfile_controller_instance();
        try {
            $instance->process('file_request');
            $this->fail('The exception should be thrown');
        } catch (Exception $exception) {
            $this->assertStringContainsString(
                'External pluginfile is denied.',
                $exception->getMessage()
            );
        }
    }

    /**
     * @return void
     */
    public function test_external_pluginfile_valid_token(): void {
        global $DB;
        $generator = oauth2_generator::instance();
        $user = self::getDataGenerator()->create_user();
        $role = $DB->get_record('role', ['archetype' => 'apiuser'], 'id');
        role_assign($role->id, $user->id, context_system::instance());
        $api_client = client::create(
            '123',
            $user->id,
            null,
            null,
            1,
            ['create_client_provider' => true]
        );
        /** @var \totara_oauth2\model\client_provider $client_provider */
        $client_provider = $api_client->oauth2_client_providers->first();

        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_SERVER['SERVER_SOFTWARE'] = 'php';
        $_SERVER['HTTP_HOST'] = 'example.com';
        $_SERVER['HTTP_USER_AGENT'] = 'Mozilla/5.0 (X11; Linux x86_64; rv:100.0) Gecko/20100101 Firefox/100.0';
        $_SERVER['CONTENT_TYPE'] = 'application/json';
        $_SERVER['CONTENT_LENGTH'] = '101';
        $access_token = $generator->create_access_token_from_client_provider(
            $client_provider->get_entity_copy(),
            time() + HOURSECS
        );
        $_SERVER['HTTP_AUTHORIZATION'] = "Bearer " . $access_token;

        $instance = $this->get_pluginfile_controller_instance();
        try {
            $instance->process('file_request');
            $this->fail('The exception should be thrown');
        } catch (Exception $exception) {
            $this->assertStringContainsString(
                'External pluginfile is denied.',
                $exception->getMessage()
            );
        }
    }

    /**
     * @return void
     */
    public function test_external_pluginfile_validate_user(): void {
        $generator = oauth2_generator::instance();
        $user = self::getDataGenerator()->create_user();
        global $DB;
        $role = $DB->get_record('role', ['archetype' => 'apiuser'], 'id');
        role_assign($role->id, $user->id, context_system::instance());
        $api_client = client::create(
            '123',
            $user->id,
            null,
            null,
            1,
            ['create_client_provider' => true]
        );
        /** @var \totara_oauth2\model\client_provider $client_provider */
        $client_provider = $api_client->oauth2_client_providers->first();

        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_SERVER['SERVER_SOFTWARE'] = 'php';
        $_SERVER['HTTP_HOST'] = 'example.com';
        $_SERVER['HTTP_USER_AGENT'] = 'Mozilla/5.0 (X11; Linux x86_64; rv:100.0) Gecko/20100101 Firefox/100.0';
        $_SERVER['CONTENT_TYPE'] = 'application/json';
        $_SERVER['CONTENT_LENGTH'] = '101';
        $access_token = $generator->create_access_token_from_client_provider(
            $client_provider->get_entity_copy(),
            time() + HOURSECS
        );
        $_SERVER['HTTP_AUTHORIZATION'] = "Bearer " . $access_token;

        $instance = $this->get_pluginfile_controller_instance();
        try {
            $instance->process('file_request');
        } catch (Exception $exception) {
            //
        }
        $this->assertNotEquals($user->id, $GLOBALS['USER']->id);
    }

    /**
     * @return void
     */
    public function test_external_invalid_token(): void {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_SERVER['SERVER_SOFTWARE'] = 'php';
        $_SERVER['HTTP_HOST'] = 'example.com';
        $_SERVER['HTTP_USER_AGENT'] = 'Mozilla/5.0 (X11; Linux x86_64; rv:100.0) Gecko/20100101 Firefox/100.0';
        $_SERVER['CONTENT_TYPE'] = 'application/json';
        $_SERVER['CONTENT_LENGTH'] = '101';
        $_SERVER['HTTP_AUTHORIZATION'] = "Bearer iNvAlIdTokEN";

        $instance = $this->get_pluginfile_controller_instance();
        try {
            $instance->process('file_request');
            $this->fail('The exception should be thrown');
        } catch (Exception $exception) {
            $this->assertStringContainsString(
                'External pluginfile is denied.',
                $exception->getMessage()
            );
        }
    }

    /**
     * @return void
     * @throws coding_exception
     */
    public function test_external_feature_disabled(): void {
        advanced_feature::disable('api');
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_SERVER['SERVER_SOFTWARE'] = 'php';

        ob_start();
        $this->get_pluginfile_controller_instance()->process('file_request');
        $response = ob_get_clean();
        $this->assertEquals(
            '{"errors":[{"message":"Feature api is not available."}],"error":"Feature api is not available."}',
            $response
        );
    }
}