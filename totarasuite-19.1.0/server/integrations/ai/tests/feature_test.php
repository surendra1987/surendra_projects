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
 * @author Kunle Odusan <kunle.odusan@totara.com>
 */

use core\session\manager;
use core_ai\entity\interaction_log;
use core_ai\event\feature_interaction;
use core_ai\feature\generative_prompt\response;
use core_ai\feature\interaction_input_interface;
use core_phpunit\testcase;
use fixtures\sample_connector_feature;
use fixtures\sample_interaction;

require_once(__DIR__ . '/fixtures/sample_feature.php');
require_once(__DIR__ . '/fixtures/sample_connector_feature.php');
require_once(__DIR__ . '/fixtures/sample_interaction.php');

/**
 * @group ai_integrations
 */
class core_ai_feature_test extends testcase {

    public function test_get_name(): void {
        $this->assertEquals('sample feature', sample_connector_feature::get_name());
    }

    public function test_generate_via_interaction(): void {
        $this->setAdminUser();
        $sink = $this->redirectEvents();

        // invoke the feature
        $mock = $this->getMockBuilder(interaction_input_interface::class)->getMock();
        $result = (new sample_interaction())->run($mock);

        $events = $sink->get_events();

        // assert event got triggered
        $this->assertCount(1, $events);
        $this->assertInstanceOf(feature_interaction::class, $events[0]);

        $response = $result->response;
        $this->assertInstanceOf(response::class, $response);
        $this->assertEqualsCanonicalizing(['this is the result'], $response->to_array());

        // assert request got logged
        $interaction_logs = interaction_log::repository()->get()->all();
        $this->assertCount(1, $interaction_logs);

        /** @var interaction_log $log */
        $log = $interaction_logs[0];
        $this->assertEquals(manager::get_realuser()->id, $log->user_id);
        $this->assertEquals(sample_interaction::class, $log->interaction);

        // assert the request and responses were logged.
        $this->assertEqualsCanonicalizing(
            [
                'prompts' => [
                    [
                        'role' => 'user',
                        'message' => 'Hello',
                    ],
                    [
                        'role' => 'user',
                        'message' => 'world',
                    ],
                ],
                'model' => 'sample',
            ],
            json_decode($log->request, true)
        );
        $this->assertEquals(json_encode(['this is the result']), $log->response);
        $expected_configs = [
            'feature' => [],
            'interaction' => [
                'enabled' => '1',
                'model' => 'sample',
            ]
        ];
        $this->assertEquals(json_encode($expected_configs), $log->configuration);
    }
}
