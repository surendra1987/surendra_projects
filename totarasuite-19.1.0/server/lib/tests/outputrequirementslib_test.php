<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Unit tests for lib/outputrequirementslibphp.
 *
 * @package   core
 * @category  phpunit
 * @copyright 2012 Petr Škoda
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->libdir . '/outputrequirementslib.php');


class core_outputrequirementslib_test extends \core_phpunit\testcase {
    public function test_string_for_js() {

        $page = new moodle_page();
        $page->requires->string_for_js('course', 'moodle', 1);
        $page->requires->string_for_js('course', 'moodle', 1);
        $this->expectException('coding_exception');
        $page->requires->string_for_js('course', 'moodle', 2);

        // Note: we can not switch languages in phpunit yet,
        //       it would be nice to test that the strings are actually fetched in the footer.
    }

    public function test_one_time_output_normal_case() {
        $page = new moodle_page();
        $this->assertTrue($page->requires->should_create_one_time_item_now('test_item'));
        $this->assertFalse($page->requires->should_create_one_time_item_now('test_item'));
    }

    public function test_one_time_output_repeat_output_throws() {
        $page = new moodle_page();
        $page->requires->set_one_time_item_created('test_item');
        $this->expectException('coding_exception');
        $page->requires->set_one_time_item_created('test_item');
    }

    public function test_one_time_output_different_pages_independent() {
        $firstpage = new moodle_page();
        $secondpage = new moodle_page();
        $this->assertTrue($firstpage->requires->should_create_one_time_item_now('test_item'));
        $this->assertTrue($secondpage->requires->should_create_one_time_item_now('test_item'));
    }

    /**
     * Test for the jquery_plugin method.
     *
     * Test to make sure that backslashes are not generated with either slasharguments set to on or off.
     */
    public function test_jquery_plugin() {
        global $CFG, $PAGE;


        // With slasharguments on.
        $CFG->slasharguments = 1;

        $page = new moodle_page();
        $requirements = $page->requires;
        // Assert successful method call.
        $this->assertTrue($requirements->jquery_plugin('jquery'));
        $this->assertTrue($requirements->jquery_plugin('ui'));

        // Get the code containing the required jquery plugins.
        /* @var core_renderer $renderer */
        $renderer = $PAGE->get_renderer('core', null, RENDERER_TARGET_MAINTENANCE);
        $requirecode = $requirements->get_top_of_body_code($renderer);
        // Make sure that the generated code does not contain backslashes.
        $this->assertFalse(strpos($requirecode, '\\'), "Output contains backslashes: " . $requirecode);

        // With slasharguments off.
        $CFG->slasharguments = 0;

        $page = new moodle_page();
        $requirements = $page->requires;
        // Assert successful method call.
        $this->assertTrue($requirements->jquery_plugin('jquery'));
        $this->assertTrue($requirements->jquery_plugin('ui'));

        // Get the code containing the required jquery plugins.
        $requirecode = $requirements->get_top_of_body_code($renderer);
        // Make sure that the generated code does not contain backslashes.
        $this->assertFalse(strpos($requirecode, '\\'), "Output contains backslashes: " . $requirecode);
    }

    /**
     * Test AMD modules loading.
     */
    public function test_js_call_amd() {

        $page = new moodle_page();

        // Load an AMD module without a function call.
        $page->requires->js_call_amd('theme_foobar/lightbox');

        // Load an AMD module and call its function without parameters.
        $page->requires->js_call_amd('theme_foobar/demo_one', 'init');

        // Load an AMD module and call its function with some parameters.
        $page->requires->js_call_amd('theme_foobar/demo_two', 'init', [
            'foo',
            'keyWillIgnored' => 'baz',
            [42, 'xyz'],
        ]);

        $html = $page->requires->get_end_code();

        $this->assertStringContainsString('require(["theme_foobar/lightbox"]);', $html);
        $this->assertStringContainsString('require(["theme_foobar/demo_one"], function(amd) { amd.init(); });', $html);
        $this->assertStringContainsString('require(["theme_foobar/demo_two"], function(amd) { amd.init("foo", "baz", [42,"xyz"]); });', $html);
    }

    /**
     * Test that function will not produce syntax error if provided values cannot be json encoded
     */
    public function test_js_call_amd_json() {
        $page = new moodle_page();
        /**
         * @var page_requirements_manager $requirements
         */
        $requirements = $page->requires;

        // Valid case.
        $requirements->js_call_amd('core/add_block_popover', 'a', ['valid', 'text']);
        $code = implode(';', $requirements->get_raw_amd_js_code());
        $this->assertStringContainsString('a("valid", "text")', $code);
        $this->assertStringContainsString('"core/add_block_popover"', $code);

        // Valid: Empty array.
        $requirements->js_call_amd('core/test', 'd', [[], 'text']);
        $code = implode(';', $requirements->get_raw_amd_js_code());
        $this->assertStringContainsString('d([], "text")', $code);

        // Valid: Null value.
        $requirements->js_call_amd('core/test', 'e', [null, null]);
        $code = implode(';', $requirements->get_raw_amd_js_code());
        $this->assertStringContainsString('e(null, null)', $code);

        // Invalid UTF-8.
        $requirements->js_call_amd('core/test', 'b', ['invalid' . "\xB1\x31", 'text']);
        $code = implode(';', $requirements->get_raw_amd_js_code());
        $this->assertStringContainsString('b(null, "text")', $code);
        $this->assertStringNotContainsString('invalid', $code);

        // Invalid type.
        $requirements->js_call_amd('core/test', 'c', [NAN, 'text']);
        $code = implode(';', $requirements->get_raw_amd_js_code());
        $this->assertStringContainsString('c(null, "text")', $code);
    }

    /**
     * Test built-in requirements get added.
     */
    public function test_builtin_requirements(): void {
        $page = new moodle_page();
        $requires = $page->requires;
        $this->assertInstanceOf(page_requirements_manager::class, $requires);

        [$combined_end, $separate_end, $separate_content_end] = $this->get_end_code($requires);

        $shell_and_content_end_reqs = [
            'core/autoinitialise'
        ];

        foreach ($shell_and_content_end_reqs as $req) {
            $this->assert_string_contains_string_once($req, $combined_end);
            $this->assert_string_contains_string_once($req, $separate_end);
            $this->assert_string_contains_string_once($req, $separate_content_end);
        }

        $shell_end_reqs = [
            'totara/tui/javascript.php/1/en/p/tui',
            'requirejs/require.js',
            'core-jqueryajaxhandler',
        ];

        foreach ($shell_end_reqs as $req) {
            $this->assert_string_contains_string_once($req, $combined_end);
            $this->assert_string_contains_string_once($req, $separate_end);
            $this->assertStringNotContainsString($req, $separate_content_end);
        }
    }

    /**
     * Test that the requirements manager can track the requirements of a page.
     *
     * @dataProvider provide_requirements_data
     */
    public function test_requirements_tracking(array $data): void {
        $page = new moodle_page();
        $requires = $page->requires;
        $this->assertInstanceOf(page_requirements_manager::class, $requires);

        foreach ($data['calls'] as $call) {
            $method = $data['method'];
            $requires->$method(...$call);
        }

        if (isset($data['expected_top_of_body_code'])) {
            $html = $requires->get_top_of_body_code($page->get_renderer('core'));
            foreach ($data['expected_top_of_body_code'] as $expected) {
                $this->assert_string_contains_string_once($expected, $html);
            }
        }

        if (isset($data['expected_head_code'])) {
            $html = $requires->get_head_code($page, $page->get_renderer('core'));
            foreach ($data['expected_head_code'] as $expected) {
                $this->assert_string_contains_string_once($expected, $html);
            }
        }

        if (isset($data['expected_end_code'])) {
            [$combined_end, $separate_end, $separate_content_end] = $this->get_end_code($requires);

            foreach ($data['expected_end_code'] ?? [] as $expected) {
                // combined mode: should be in regular end code
                $this->assert_string_contains_string_once($expected, $combined_end);

                // separate mode: should not be in regular end code, should be in content end code
                $requires->output_content_js = false;
                $this->assertStringNotContainsString($expected, $separate_end);
                $this->assert_string_contains_string_once($expected, $separate_content_end);
                $requires->output_content_js = true;
            }
        }
    }

    /**
     * Get the different end code outputs.
     *
     * @param page_requirements_manager $requires
     * @return array
     */
    private function get_end_code(page_requirements_manager $requires): array {
        $combined_end = $requires->get_end_code();
        $requires->output_content_js = false;
        $separate_end = $requires->get_end_code();
        $separate_content_end = $requires->get_content_end_code();
        $requires->output_content_js = true;

        return [$combined_end, $separate_end, $separate_content_end];
    }

    /**
     * @return array
     */
    public static function provide_requirements_data() {
        return include(__DIR__ . '/fixtures/page_requirements_test_data.php');
    }

    /**
     * @return void
     */
    private function assert_string_contains_string_once($needle, $haystack): void {
        $this->assertEquals(1, substr_count($haystack, $needle), "Expected '$needle' to appear once in '$haystack'");
    }
}
