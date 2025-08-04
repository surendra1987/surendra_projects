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

use core\session\manager;
use core_ai\configuration\config_collection;
use core_ai\entity\interaction_log;
use core_ai\event\feature_interaction;
use core_ai\feature;
use core_ai\feature\generative_prompt\prompt;
use core_ai\feature\generative_prompt\request;
use core_ai\feature\generative_prompt\response;
use core_ai\model\interaction_log as interaction_log_model;
use core_phpunit\testcase;

require_once(__DIR__.'/fixtures/sample_feature.php');

/**
 * @group ai_integrations
 */
class core_ai_model_interaction_log_test extends testcase {

    public function test_log_response(): void {
        $user = $this->getDataGenerator()->create_user();
        $log_entity = (new interaction_log([
            'user_id' => $user->id,
            'interaction' => 'test_interaction',
            'request' => '{}',
            'plugin' => 'test_ai',
            'feature' => 'test_feature',
            'configuration' => '',
        ]))->save();

        $interaction_log = interaction_log_model::load_by_entity($log_entity);
        $response = ['my response'];
        $interaction_log->log_response(new response($response));
        $this->assertEquals(json_encode($response), $interaction_log->response);
    }

    public function test_create_and_log_response(): void {
        $this->setAdminUser();

        $sink = $this->redirectEvents();
        $feature = $this->createStub(feature::class);
        $feature->method('get_interaction_class_name')->willReturn('test_interaction');
        $feature->method('get_config')->willReturn(new config_collection([]));

        $log = interaction_log_model::create(
            new request([
                new prompt('Hello'),
                new prompt('world'),
            ]),
            $feature
        );

        // assert event got triggered
        $events = $sink->get_events();
        $this->assertCount(1, $events);
        $this->assertInstanceOf(feature_interaction::class, $events[0]);

        $this->assertEquals(manager::get_realuser()->id, $log->user_id);
        $this->assertEquals('test_interaction', $log->interaction);
        $this->assertEqualsCanonicalizing(
            [
                [
                    'role' => 'user',
                    'message' => 'Hello',
                ],
                [
                    'role' => 'user',
                    'message' => 'world',
                ],
            ],
            json_decode($log->request, true)
        );

        // This based on interpreting of Mock feature class
        $this->assertEquals('feature', $log->plugin);
        $this->assertEquals(json_encode($feature->get_config()), $log->configuration);
    }
}
