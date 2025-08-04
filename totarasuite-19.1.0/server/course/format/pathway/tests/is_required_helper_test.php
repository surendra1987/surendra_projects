<?php
/*
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
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 *
 * @author Nathan Lewis <nathan.lewis@totara.com>
 * @package format_pathway
 */

defined('MOODLE_INTERNAL') || die();

use core_phpunit\testcase;
use format_pathway\is_required_helper;

/**
 * @group format_pathway
 */
class format_pathway_is_required_helper_test extends testcase {

    protected function get_mocked_helper(array $override_activity_page_results = []): is_required_helper {
        $activity_page_results = [
            'is_page_only_for_web' => true,
            'is_current_request_ajax_script' => false,
            'is_replaceable_layout' => true,
            'is_course_id_set' => true,
            'is_course_format_pathway' => true,
        ];
        $results = array_merge($activity_page_results, $override_activity_page_results);
        $mocked_helper = $this->getMockBuilder(is_required_helper::class)
            ->disableOriginalConstructor()
            ->onlyMethods([
                'is_page_only_for_web',
                'is_current_request_ajax_script',
                'is_replaceable_layout',
                'is_course_id_set',
                'is_course_format_pathway',
            ])
            ->getMock();
        $mocked_helper
            ->expects(self::atMost(1))
            ->method('is_page_only_for_web')
            ->willReturn($results['is_page_only_for_web']);
        $mocked_helper
            ->expects(self::atMost(1))
            ->method('is_current_request_ajax_script')
            ->willReturn($results['is_current_request_ajax_script']);
        $mocked_helper
            ->expects(self::atMost(1))
            ->method('is_replaceable_layout')
            ->willReturn($results['is_replaceable_layout']);
        $mocked_helper
            ->expects(self::atMost(1))
            ->method('is_course_id_set')
            ->willReturn($results['is_course_id_set']);
        $mocked_helper
            ->expects(self::atMost(1))
            ->method('is_course_format_pathway')
            ->willReturn($results['is_course_format_pathway']);
        return $mocked_helper;
    }

    /**
     * Gets the result of calling calcualte_result.
     *
     * Arbitrary data is passed in as params. It is assumed that the params are not used in the function because
     * the sub-functions are all mocked and ignore their inputs.
     *
     * @param is_required_helper $helper
     * @return bool
     * @throws ReflectionException
     */
    private function get_calculate_result(is_required_helper $helper): bool {
        $class = new ReflectionClass($helper);
        $method = $class->getMethod('calculate_result');
        $method->setAccessible(true);
        return $method->invokeArgs($helper, array(new moodle_page(), 'xyz')); // Params aren't actually used, just needed for function call.
    }

    public function test_instantiate_instance(): void {
        // Simply call the function and make sure that no error occurred.
        $helper = is_required_helper::initialise_instance(new moodle_page(), 'xyz');

        // We don't care about the result, we just want to know that a result is available.
        self::assertNotNull($helper->should_replace_layout());
    }

    public function test_instantiate_instance_prevents_loops(): void {
        $page = new moodle_page();

        // Setup up the instance. Mock instance does not call calculate_result.
        $mock_instance = $this->get_mocked_helper();
        self::assertNull($mock_instance->should_replace_layout());
        is_required_helper::set_instance($mock_instance);

        // Call the function - because result has not been calculated, the function thinks that it is being called recursively.
        $instance = is_required_helper::initialise_instance($page, '');

        // Make sure that the loop avoidance occurred.
        self::assertDebuggingCalled(['Loop detected - helper instance is not initialised']);

        self::assertEquals($mock_instance, $instance);
    }

    public function test_instantiate_instance_can_be_rerun_with_same_page(): void {
        $page = new moodle_page();

        $first_instance = is_required_helper::initialise_instance($page, '');
        $second_instance = is_required_helper::initialise_instance($page, '');

        self::assertDebuggingCalledCount(0);
        self::assertEquals($first_instance, $second_instance);
    }

    public function test_instantiate_instance_can_be_rerun_with_different_page(): void {
        $page = new moodle_page();

        $first_instance = is_required_helper::initialise_instance($page, '');
        $page->fragment_requires = 'some random stuff';
        $second_instance = is_required_helper::initialise_instance($page, '');

        self::assertDebuggingCalled(['Helper instance should only be instantiated once: ' . sha1(json_encode($page))]);
        self::assertNotEquals($first_instance, $second_instance);
    }

    public function test_get_instance_before_instantiation(): void {
        self::expectException(coding_exception::class);
        self::expectExceptionMessage('Helper instance has not been initialised');

        is_required_helper::get_instance();
    }

    public function test_calculate_result_with_activity_page(): void {
        $helper = $this->get_mocked_helper();
        self::assertTrue($this->get_calculate_result($helper));
    }

    public function test_calculate_result_with_non_web_page(): void {
        $helper = $this->get_mocked_helper(['is_page_only_for_web' => false]);
        self::assertFalse($this->get_calculate_result($helper));
    }

    public function test_calculate_result_with_ajax_script(): void {
        $helper = $this->get_mocked_helper(['is_current_request_ajax_script' => true]);
        self::assertFalse($this->get_calculate_result($helper));
    }

    public function test_calculate_result_without_replaceable_layout(): void {
        $helper = $this->get_mocked_helper(['is_replaceable_layout' => false]);
        self::assertFalse($this->get_calculate_result($helper));
    }

    public function test_calculate_result_with_no_course_id(): void {
        $helper = $this->get_mocked_helper(['is_course_id_set' => false]);
        self::assertFalse($this->get_calculate_result($helper));
    }

    public function test_calculate_result_with_other_course_format(): void {
        $helper = $this->get_mocked_helper(['is_course_format_pathway' => false]);
        self::assertFalse($this->get_calculate_result($helper));
    }
}
