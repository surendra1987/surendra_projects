<?php
/**
 * This file is part of Totara Learn
 *
 * Copyright (C) 2020 onwards Totara Learning Solutions LTD
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
 * @author Cody Finegan <cody.finegan@totaralearning.com>
 * @package totara_engage
 */

use totara_engage\link\builder;
use totara_engage\link\empty_destination;
use totara_engage\link\library_destination;
use totara_engage\link\source_generator;

defined('MOODLE_INTERNAL') || die();

class totara_engage_link_generator_test extends \core_phpunit\testcase {
    /**
     * Check that all keys for links are unique
     */
    public function test_unique_keys() {
        // Just run the init method & make sure it doesn't crash
        $generator_cache = builder::get_generators_for_tests();

        // Actual unique check is done when the generators are built, so if it doesn't crash
        // then we're good
        $this->assertNotEmpty($generator_cache['source']);
        $this->assertNotEmpty($generator_cache['destination']);
    }

    /**
     * Validate the source generator can create links properly
     */
    public function test_source_generator(): void {
        $cases = [
            ['lb.0', '/totara/engage/your_resources.php'],
            ['lb.1', '/totara/engage/saved_resources.php'],
            ['lb.2', '/totara/engage/shared_with_you.php'],
            ['lb.4', '/totara/engage/search_results.php?search=test', ['search' => 'test']],
            ['lb.88', '/totara/engage/your_resources.php'],
            ['code1__code2__lb.88', '/totara/engage/your_resources.php?source=code1__code2'],
            ['ws.17.1', '/container/type/workspace/workspace.php?id=17&tab=library'],
            ['lb.0__ws.17.1', '/container/type/workspace/workspace.php?id=17&tab=library&source=lb.0'],

        ];

        foreach ($cases as $case) {
            $generator = builder::from_source($case[0]);

            if (isset($case[2])) {
                $attributes = array_merge($generator->get_attributes(), $case[2]);
                $generator->set_attributes($attributes);
            }

            if (null !== $case[1]) {
                $this->assertSame($case[1], $generator->url()->out_as_local_url(false));
            } else {
                $this->assertInstanceOf(empty_destination::class, $generator);
            }
        }
    }

    /**
     * @return array[]
     */
    public static function prepend_previous_sources_data_provider(): array {
        return [
            ['', 'lb.0', 'lb.0'], // Empty previous source
            ['ws.17.1', 'lb.0', 'ws.17.1__lb.0'],  // One previous source
            ['ws.17.1__lb.0__ws.17.1', 'lb.0', 'ws.17.1__lb.0__ws.17.1__lb.0'], // Multiple previous sources, repeats within are ok
            ['ws.17.1__lb.0__ws.17.1', 'ws.17.1', 'ws.17.1__lb.0__ws.17.1'], // Multiple previous sources, should not repeat at the end
            ['', 'invalid', ''], // Invalid source is removed
            ['ws.17.1', 'invalid', 'ws.17.1'], // Invalid source is removed
            ['lb.0__ws.17.1__invalid__ws.17.1', 'lb.0', 'lb.0__ws.17.1__lb.0'], // Invalid source and successive repeats are removed
            ['lb.0__invalid__bla__ws.17.1__bad', 'ws.17.1', 'lb.0__ws.17.1'], // Multiple invalid sources are removed as well
        ];
    }

    /**
     * @dataProvider prepend_previous_sources_data_provider
     * @param string $prev_sources_string
     * @param string $source
     * @param string $expected
     * @return void
     */
    public function test_prepend_previous_sources(string $prev_sources_string, string $source, string $expected): void {
        $_GET['source'] = $prev_sources_string;
        $this->assertSame($expected, source_generator::prepend_previous_sources($source));
    }

    public function test_prepend_previous_sources_with_referer(): void {
        $_GET['source'] = null;
        $_SERVER['HTTP_REFERER'] = '/test/index.php?test1=bla&source=ws.17.1__lb.0&test2=bla';
        $this->assertSame('ws.17.1__lb.0__lb.4', source_generator::prepend_previous_sources('lb.4'));
    }

    /**
     * Validate the destination tests correctly
     */
    public function test_destination_generator() {
        $generator = builder::to('page_library');
        $this->assertInstanceOf(library_destination::class, $generator);
        $generator = builder::to_library();
        $this->assertInstanceOf(library_destination::class, $generator);

        $cases = [
            ['page_your_resources', '/totara/engage/your_resources.php'],
            ['page_bookmarked', '/totara/engage/saved_resources.php'],
            ['page_shared', '/totara/engage/shared_with_you.php'],
            ['page_search', '/totara/engage/search_results.php?search=test', 'test'],
        ];

        foreach ($cases as $case) {
            $generator = call_user_func([builder::to_library(), $case[0]], $case[2] ?? null);
            $url = $generator->url()->out_as_local_url(false);
            $this->assertSame($case[1], $url);
        }
    }
}