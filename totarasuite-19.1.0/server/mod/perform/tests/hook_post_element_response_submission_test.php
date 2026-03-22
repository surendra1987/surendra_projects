<?php
/**
 * This file is part of Totara Perform
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
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 *
 * @author Chris Snyder <chris.snyder@totara.com>
 * @package mod_perform
 */

use core_phpunit\testcase;
use mod_perform\hook\post_element_response_submission;
use mod_perform\models\activity\participant_instance;
use mod_perform\testing\generator as perform_generator;

/**
 * @coversDefaultClass mod_perform\hook\post_element_response_submission
 */
class mod_perform_hook_post_element_response_submission_test extends testcase {

    private $hook;

    public function setUp(): void {
        $this->setAdminUser();
        $activity = perform_generator::instance()->create_activity_in_container();
        $element = perform_generator::instance()->create_element();
        $subject_instance = perform_generator::instance()->create_subject_instance([
            'activity_id' => $activity->id,
            'subject_is_participating' => true,
            'subject_user_id' => $this->getDataGenerator()->create_user()->id
        ]);
        $participant_instance = participant_instance::load_by_id($subject_instance->participant_instances->first()->id);

        $this->hook = new post_element_response_submission(
            42,
            $element,
            $participant_instance,
            'Some data'
        );
        parent::setUp();
    }

    protected function tearDown(): void {
        $this->hook = null;
        parent::tearDown();
    }

    /**
     * @covers ::get_response_id
     */
    public function test_get_response_id() {
        $this->assertEquals(42, $this->hook->get_response_id());
    }

    /**
     * @covers ::get_response_data
     */
    public function test_get_response_data() {
        $this->assertEquals('Some data', $this->hook->get_response_data());
    }

    /**
     * @covers ::set_response_data
     */
    public function test_set_response_data() {
        $this->hook->set_response_data('New data');
        $this->assertEquals('New data', $this->hook->get_response_data());
    }

    /**
     * @covers ::get_response_data_to_save
     */
    public function test_get_response_data_to_save() {
        $this->assertEquals('Some data', $this->hook->get_response_data_to_save());
    }

    /**
     * @covers ::set_response_data_to_save
     */
    public function test_set_response_data_to_save() {
        $this->hook->set_response_data_to_save('Save this instead');
        $this->assertEquals('Save this instead', $this->hook->get_response_data_to_save());
    }

    /**
     * @covers ::set_response_data
     * @covers ::set_response_data_to_save
     */
    public function test_response_data_vs_response_data_to_save() {
        $this->hook->set_response_data_to_save('Save this instead');
        $this->assertEquals('Some data', $this->hook->get_response_data());
        $this->assertEquals('Save this instead', $this->hook->get_response_data_to_save());

        $this->hook->set_response_data('Somebody save me');
        $this->assertEquals('Somebody save me', $this->hook->get_response_data());
        $this->assertEquals('Somebody save me', $this->hook->get_response_data_to_save());
    }

    /**
     * @covers ::get_element
     */
    public function test_get_element() {
        $this->assertInstanceOf(\mod_perform\models\activity\element::class, $this->hook->get_element());
    }

    /**
     * @covers ::matches_element_plugin
     */
    public function test_matches_element_plugin() {
        $this->assertTrue($this->hook->matches_element_plugin(\performelement_short_text\short_text::class));
        $this->assertFalse($this->hook->matches_element_plugin(\performelement_long_text\long_text::class));
    }

    /**
     * @covers ::get_participant_instance
     */
    public function test_get_participant_instance() {
        $this->assertInstanceOf(\mod_perform\models\activity\participant_instance::class, $this->hook->get_participant_instance());
    }
}