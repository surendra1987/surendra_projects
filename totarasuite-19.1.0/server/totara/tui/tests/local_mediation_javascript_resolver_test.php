<?php
/**
 * This file is part of Totara Core
 *
 * Copyright (C) 2020 onwards Totara Learning Solutions LTD
 *
 * MIT License
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 *
 * @author Sam Hemelryk <sam.hemelryk@totaralearning.com>
 * @package totara_tui
 */

use totara_tui\local\locator\bundle;
use totara_tui\local\mediation\javascript\mediator;
use \totara_tui\local\mediation\javascript\resolver;

defined('MOODLE_INTERNAL') || die();

class totara_tui_local_mediation_javascript_resolver_test extends \core_phpunit\testcase {

    private function skip_if_build_not_present() {
        $vendors_file = bundle::get_vendors_file();
        if (empty($vendors_file) || !file_exists($vendors_file)) {
            $this->markTestSkipped('Tui build files must exist for this test to complete.');
        }
    }

    public function test_production_mode_cached() {
        $this->skip_if_build_not_present();

        $rev = time();

        [$js, $messages, $file] = $this->get_resolver($rev, 'p');

        self::assertSame('p', bundle::get_js_suffix_for_url());

        $expected = [
            'Header: Etag: "'.sha1('tui ' . $rev . ' tui p en').'"',
            'Header: Content-Disposition: inline; filename="javascript.php"',
            'Header: Date: ' . gmdate('D, d M Y', time()),
            'Header: Last-Modified: ' . gmdate('D, d M Y', filemtime($file)),
            'Header: Expires: ' . gmdate('D, d M Y', $this->get_lifetimestamp($rev)),
            'Header: Pragma: ',
            'Header: Cache-Control: public, max-age=604800, immutable',
            'Header: Accept-Ranges: none',
            'Header: Content-Type: application/javascript;charset=utf-8',
            'Header: X-Content-Type-Options: nosniff',
        ];
        if (!\min_enable_zlib_compression()) {
            $expected[] = 'Header: Content-Length: ' . filesize($file);
        }
        array_push($expected, ...[
            'Header: Vary: Accept-Encoding',
            'Exiting',
        ]);

        self::assertSame($expected, self::strip_debugging_messages($messages));

        self::assertStringStartsWith('(()=>{', $js);
    }

    public function test_development_mode_cached() {
        $this->skip_if_build_not_present();

        global $CFG;
        $CFG->forced_plugin_settings['totara_tui'] = ['development_mode' => true];

        $rev = time();

        [$js, $messages, $file] = $this->get_resolver($rev, 'd');

        self::assertSame('d', bundle::get_js_suffix_for_url());

        $expected = [
            'Header: Etag: "'.sha1('tui ' . $rev . ' tui d en').'"',
            'Header: Content-Disposition: inline; filename="javascript.php"',
            'Header: Date: ' . gmdate('D, d M Y', time()),
            'Header: Last-Modified: ' . gmdate('D, d M Y', filemtime($file)),
            'Header: Expires: ' . gmdate('D, d M Y', $this->get_lifetimestamp($rev)),
            'Header: Pragma: ',
            'Header: Cache-Control: public, max-age=604800, immutable',
            'Header: Accept-Ranges: none',
            'Header: Content-Type: application/javascript;charset=utf-8',
            'Header: X-Content-Type-Options: nosniff',
        ];
        if (!\min_enable_zlib_compression()) {
            $expected[] = 'Header: Content-Length: ' . filesize($file);
        }
        array_push($expected, ...[
            'Header: Vary: Accept-Encoding',
            'Exiting',
        ]);

        self::assertSame($expected, self::strip_debugging_messages($messages));

        self::assertStringStartsWith("/*\n * ATTENTION:", $js);
    }

    public function test_production_mode_uncached() {
        $this->skip_if_build_not_present();

        $rev = -1;

        [$js, $messages, $file] = $this->get_resolver($rev, 'p');

        self::assertSame('p', bundle::get_js_suffix_for_url());

        self::assertSame([
            'Header: Etag: "'.sha1('tui ' . $rev . ' tui p en').'"',
            'Header: Content-Disposition: inline; filename="javascript.php"',
            'Header: Date: ' . gmdate('D, d M Y', time()),
            'Header: Last-Modified: ' . gmdate('D, d M Y', time()),
            'Header: Expires: ' . gmdate('D, d M Y', time()),
            'Header: Cache-Control: no-cache',
            'Header: Pragma: no-cache',
            'Header: Accept-Ranges: none',
            'Header: Content-Type: application/javascript;charset=utf-8',
            'Header: X-Content-Type-Options: nosniff',
            'Exiting',
        ], self::strip_debugging_messages($messages));

        self::assertStringStartsWith('(()=>{', $js);
    }

    public function test_development_mode_uncached() {
        $this->skip_if_build_not_present();

        global $CFG;
        $CFG->forced_plugin_settings['totara_tui'] = ['development_mode' => true];

        $rev = -1;

        // Set the expected etag as an IF_NONE_MATCH header
        $resolver = new resolver(mediator::class, -1, 'd', 'tui', 'en');
        $method = new ReflectionMethod($resolver, 'calculate_etag');
        $method->setAccessible(true);
        $etag = $method->invoke($resolver);

        [$js, $messages, $file] = $this->get_resolver($rev, 'd');

        self::assertSame('d', bundle::get_js_suffix_for_url());

        self::assertSame([
            'Header: Etag: "' . $etag . '"',
            'Header: Content-Disposition: inline; filename="javascript.php"',
            'Header: Date: ' . gmdate('D, d M Y', time()),
            'Header: Last-Modified: ' . gmdate('D, d M Y', time()),
            'Header: Expires: ' . gmdate('D, d M Y', time()),
            'Header: Cache-Control: no-cache',
            'Header: Pragma: no-cache',
            'Header: Accept-Ranges: none',
            'Header: Content-Type: application/javascript;charset=utf-8',
            'Header: X-Content-Type-Options: nosniff',
            'Exiting',
        ], self::strip_debugging_messages($messages));

        self::assertStringStartsWith("/*\n * ATTENTION:", $js);
    }

    public function test_development_mode_uncached_stale_in_cache() {
        $this->skip_if_build_not_present();

        global $CFG;
        $CFG->forced_plugin_settings['totara_tui'] = ['development_mode' => true];
        $rev = -1;

        // Set the expected etag as an IF_NONE_MATCH header
        $resolver = new resolver(mediator::class, $rev, 'd', 'tui', 'en');
        $method = new ReflectionMethod($resolver, 'calculate_etag');
        $method->setAccessible(true);
        $_SERVER['HTTP_IF_NONE_MATCH'] = $etag = $method->invoke($resolver);

        // Once to prime the cache
        $this->get_resolver($rev, 'd');
        // And a second to check we've got the expected outcome aftewards.
        [$js, $messages, $file] = $this->get_resolver($rev, 'd');

        self::assertSame('d', bundle::get_js_suffix_for_url());

        self::assertSame([
            'Header: HTTP/1.1 304 Not Modified',
            'Header: Etag: "'.$etag.'"',
            'Header: Date: ' . gmdate('D, d M Y', time()),
            'Header: Last-Modified: ' . gmdate('D, d M Y', time()),
            'Header: Expires: ' . gmdate('D, d M Y', time()),
            'Header: Cache-Control: no-cache',
            'Header: Pragma: no-cache',
            'Exiting',
        ], self::strip_debugging_messages($messages));

        self::assertEmpty($js);
    }

    private function get_resolver(int $rev, $mode = 'p', $component = 'tui', $lang = 'en') {
        global $CFG;
        require_once($CFG->libdir . '/configonlylib.php');
        $resolver = new resolver(
            mediator::class,
            $rev,
            $mode,
            $component,
            $lang
        );
        ob_start();
        $resolver->resolve();
        $js = ob_get_contents();
        ob_end_clean();
        $messages = $this->getDebuggingMessages();
        $this->resetDebugging();


        $prop = new ReflectionProperty(\totara_tui\local\mediation\resolver::class, 'cachefile');
        $prop->setAccessible(true);

        return [$js, $messages, $prop->getValue($resolver)];
    }

    private static function strip_debugging_messages(array $messages): array {
        $return = [];
        foreach ($messages as $message) {
            $text = $message->message;
            // Get rid of the time from: Fri, 21 Aug 2020 03:26:20 => Fri, 21 Aug 2020
            $text = preg_replace('/(\w{3,4}, \d{1,2} \w{3,4} 20\d{2}) \d{1,2}:\d{1,2}:\d{1,2} GMT/', '$1', $text);
            $return[] = $text;
        }
        return $return;
    }

    private function get_lifetimestamp(?int $time = null) {
        return ($time ?? time()) + 7 * 24 * 60 * 60;
    }

    public function test_non_existent_component_production() {
        [$js, $messages] = $this->get_resolver(time(), 'p', 'monkeys');

        $expected = [
            'Header: HTTP/1.0 404 not found',
            'Exiting',
        ];

        self::assertSame($expected, self::strip_debugging_messages($messages));
    }

    public function test_non_existent_component_development() {
        [$js, $messages] = $this->get_resolver(-1, 'd', 'monkeys');

        $expected = [
            'Header: HTTP/1.0 404 not found',
            'Exiting',
        ];

        self::assertSame($expected, self::strip_debugging_messages($messages));
    }

    public function test_non_existent_language_production() {
        [$js, $messages] = $this->get_resolver(time(), 'p', 'tui', 'notalang');

        $expected = [
            'Header: HTTP/1.0 404 not found',
            'Exiting',
        ];

        self::assertSame($expected, self::strip_debugging_messages($messages));
    }

    public function test_non_existent_language_development() {
        [$js, $messages] = $this->get_resolver(time(), 'd', 'tui', 'notalang');

        $expected = [
            'Header: HTTP/1.0 404 not found',
            'Exiting',
        ];

        self::assertSame($expected, self::strip_debugging_messages($messages));
    }

    public function test_vendors_production() {
        $this->skip_if_build_not_present();

        $rev = time();
        [$js, $messages, $file] = $this->get_resolver(time(), 'p', 'vendors');
        self::assertStringStartsWith('/*! For license information please see vendors.js.LICENSE.txt */', $js);
        self::assertStringNotContainsString('/*!******************************************', $js);

        $expected = [
            'Header: Etag: "'.sha1('tui ' . $rev . ' vendors p en').'"',
            'Header: Content-Disposition: inline; filename="javascript.php"',
            'Header: Date: ' . gmdate('D, d M Y', time()),
            'Header: Last-Modified: ' . gmdate('D, d M Y', filemtime($file)),
            'Header: Expires: ' . gmdate('D, d M Y', $this->get_lifetimestamp($rev)),
            'Header: Pragma: ',
            'Header: Cache-Control: public, max-age=604800, immutable',
            'Header: Accept-Ranges: none',
            'Header: Content-Type: application/javascript;charset=utf-8',
            'Header: X-Content-Type-Options: nosniff',
        ];
        if (!\min_enable_zlib_compression()) {
            $expected[] = 'Header: Content-Length: ' . filesize($file);
        }
        array_push($expected, ...[
            'Header: Vary: Accept-Encoding',
            'Exiting',
        ]);

        self::assertSame($expected, self::strip_debugging_messages($messages));
    }

    public function test_vendors_development() {
        $this->skip_if_build_not_present();

        global $CFG;
        $CFG->forced_plugin_settings['totara_tui'] = ['development_mode' => true];

        $resolver = new resolver(mediator::class, -1, 'd', 'vendors', 'en');
        $method = new ReflectionMethod($resolver, 'calculate_etag');
        $method->setAccessible(true);
        $etag = $method->invoke($resolver);

        [$js, $messages] = $this->get_resolver(-1, 'd', 'vendors');
        self::assertStringStartsWith("\"use strict\";\n/*\n * ATTENTION:", $js);
        self::assertStringContainsString('/*!******************************************', $js);
        self::assertSame([
            'Header: Etag: "'.$etag.'"',
            'Header: Content-Disposition: inline; filename="javascript.php"',
            'Header: Date: ' . gmdate('D, d M Y', time()),
            'Header: Last-Modified: ' . gmdate('D, d M Y', time()),
            'Header: Expires: ' . gmdate('D, d M Y', time()),
            'Header: Cache-Control: no-cache',
            'Header: Pragma: no-cache',
            'Header: Accept-Ranges: none',
            'Header: Content-Type: application/javascript;charset=utf-8',
            'Header: X-Content-Type-Options: nosniff',
            'Exiting',
        ], self::strip_debugging_messages($messages));
    }

    public function test_theme_ventura_production() {
        $this->skip_if_build_not_present();

        $rev = time();
        [$js, $messages, $file] = $this->get_resolver(time(), 'p', 'theme_ventura');
        self::assertStringStartsWith("/* theme: ventura */\n(()=>{var e={Y7MP:e=>{function", $js);
        self::assertStringNotContainsString('/*!******************************************', $js);

        $expected = [
            'Header: Etag: "'.sha1('tui ' . $rev . ' theme_ventura p en').'"',
            'Header: Content-Disposition: inline; filename="javascript.php"',
            'Header: Date: ' . gmdate('D, d M Y', time()),
            'Header: Last-Modified: ' . gmdate('D, d M Y', filemtime($file)),
            'Header: Expires: ' . gmdate('D, d M Y', $this->get_lifetimestamp($rev)),
            'Header: Pragma: ',
            'Header: Cache-Control: public, max-age=604800, immutable',
            'Header: Accept-Ranges: none',
            'Header: Content-Type: application/javascript;charset=utf-8',
            'Header: X-Content-Type-Options: nosniff',
        ];
        if (!\min_enable_zlib_compression()) {
            $expected[] = 'Header: Content-Length: ' . filesize($file);
        }
        array_push($expected, ...[
            'Header: Vary: Accept-Encoding',
            'Exiting',
        ]);

        self::assertSame($expected, self::strip_debugging_messages($messages));
    }

    public function test_theme_ventura_development() {
        $this->skip_if_build_not_present();

        global $CFG;
        $CFG->forced_plugin_settings['totara_tui'] = ['development_mode' => true];

        $resolver = new resolver(mediator::class, -1, 'd', 'theme_ventura', 'en');
        $method = new ReflectionMethod($resolver, 'calculate_etag');
        $method->setAccessible(true);
        $etag = $method->invoke($resolver);

        [$js, $messages] = $this->get_resolver(-1, 'd', 'theme_ventura');
        self::assertStringStartsWith("/* theme: ventura */\n/*\n * ATTENTION:", $js);
        self::assertStringContainsString('/*!******************************************', $js);
        self::assertSame([
            'Header: Etag: "' . $etag . '"',
            'Header: Content-Disposition: inline; filename="javascript.php"',
            'Header: Date: ' . gmdate('D, d M Y', time()),
            'Header: Last-Modified: ' . gmdate('D, d M Y', time()),
            'Header: Expires: ' . gmdate('D, d M Y', time()),
            'Header: Cache-Control: no-cache',
            'Header: Pragma: no-cache',
            'Header: Accept-Ranges: none',
            'Header: Content-Type: application/javascript;charset=utf-8',
            'Header: X-Content-Type-Options: nosniff',
            'Exiting',
        ], self::strip_debugging_messages($messages));
    }

    public function test_theme_inspire_production() {
        $this->skip_if_build_not_present();

        $rev = time();
        [$js, $messages, $file] = $this->get_resolver(time(), 'p', 'theme_inspire');
        self::assertStringStartsWith("/* theme: inspire */\n(()=>{var e={DyMF:(e,t,a)=>{", $js);
        self::assertStringNotContainsString('/*!******************************************', $js);

        $expected = [
            'Header: Etag: "'.sha1('tui ' . $rev . ' theme_inspire p en').'"',
            'Header: Content-Disposition: inline; filename="javascript.php"',
            'Header: Date: ' . gmdate('D, d M Y', time()),
            'Header: Last-Modified: ' . gmdate('D, d M Y', filemtime($file)),
            'Header: Expires: ' . gmdate('D, d M Y', $this->get_lifetimestamp($rev)),
            'Header: Pragma: ',
            'Header: Cache-Control: public, max-age=604800, immutable',
            'Header: Accept-Ranges: none',
            'Header: Content-Type: application/javascript;charset=utf-8',
            'Header: X-Content-Type-Options: nosniff',
        ];
        if (!\min_enable_zlib_compression()) {
            $expected[] = 'Header: Content-Length: ' . filesize($file);
        }
        array_push($expected, ...[
            'Header: Vary: Accept-Encoding',
            'Exiting',
        ]);

        self::assertSame($expected, self::strip_debugging_messages($messages));
    }

    public function test_theme_inspire_development() {
        $this->skip_if_build_not_present();

        global $CFG;
        $CFG->forced_plugin_settings['totara_tui'] = ['development_mode' => true];

        $resolver = new resolver(mediator::class, -1, 'd', 'theme_inspire', 'en');
        $method = new ReflectionMethod($resolver, 'calculate_etag');
        $method->setAccessible(true);
        $etag = $method->invoke($resolver);

        [$js, $messages] = $this->get_resolver(-1, 'd', 'theme_inspire');
        self::assertStringStartsWith("/* theme: inspire */\n/*\n * ATTENTION:", $js);
        self::assertStringContainsString('/*!******************************************', $js);
        self::assertSame([
            'Header: Etag: "' . $etag . '"',
            'Header: Content-Disposition: inline; filename="javascript.php"',
            'Header: Date: ' . gmdate('D, d M Y', time()),
            'Header: Last-Modified: ' . gmdate('D, d M Y', time()),
            'Header: Expires: ' . gmdate('D, d M Y', time()),
            'Header: Cache-Control: no-cache',
            'Header: Pragma: no-cache',
            'Header: Accept-Ranges: none',
            'Header: Content-Type: application/javascript;charset=utf-8',
            'Header: X-Content-Type-Options: nosniff',
            'Exiting',
        ], self::strip_debugging_messages($messages));
    }
}
