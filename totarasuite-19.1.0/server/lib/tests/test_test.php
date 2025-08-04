<?php
/**
 * This file is part of Totara Learn
 *
 * Copyright (C) 2024 onwards Totara Learning Solutions LTD
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
 * @package core
 */

use core\collection;
use core_phpunit\testcase;
use totara_core\path;

/**
 * Test phpunit tests.
 */
class core_test_test extends testcase {
    /** @var collection */
    private $paths;

    protected function setUp(): void {
        parent::setUp();
        $this->paths = new collection();
    }

    protected function tearDown(): void {
        parent::tearDown();
        $this->paths = null;
    }

    protected function reset_paths(): void {
        $this->paths = new collection();
    }

    /**
     * @param string $dir
     */
    private function add_tests(string $dir): void {
        $items = new DirectoryIterator($dir);
        foreach ($items as $item) {
            if ($item->isDot() || !$item->isFile() || !preg_match('/_test\.php$/', $item->getFilename())) {
                continue;
            }
            $path = (new path($dir, $item->getFilename()))->out(true);
            @require_once($path);
            $this->paths->set($item->getFilename(), $path);
        }
    }

    private function get_method_body(ReflectionMethod $method): string {
        $start = $method->getStartLine() - 1;
        return implode(
            '',
            iterator_to_array(
                new LimitIterator(
                    new SplFileObject($method->getFileName()),
                    $start,
                    $method->getEndLine() - $start
                )
            )
        );
    }

    private function check_paths_for_assertions_in_non_test_methods(): array {
        $errors = [];
        $whitelist = ['setUp', 'tearDown'];
        foreach ($this->paths as $fullpath => $filename) {
            // We need to load the file to get the classname.
            $contents = @file_get_contents($fullpath);
            if (!preg_match("/class\s+([A-Za-z0-9_]*_test)\s+extends/", $contents, $matches)) {
                $errors[] = "{$filename}: Cannot find a testcase with a valid name. Does the classname end with \"_test\"?";
                continue;
            }
            $classname = $matches[1];
            $class = new ReflectionClass($classname);
            foreach($class->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
                // Skip inherited methods.
                if ($method->getDeclaringClass()->name != $class->name) {
                    continue;
                }
                // Skip methods starting with _test.
                if (substr($method->getName(), 0, 5) == 'test_') {
                    continue;
                }
                // Skip whitelisted methods.
                if (in_array($method->getName(), $whitelist)) {
                    continue;
                }
                // Skip data providers ending in _provider
                if (substr($method->getName(), -9) == '_provider') {
                    continue;
                }
                if (preg_match('/(\$this->|self::)assert/', $this->get_method_body($method))) {
                    $errors[] = "{$filename}: Public method {$method->getName()} has assertions but does not start with 'test_'.";
                    continue;
                }
                // Show that we've done something.
                $this->assertTrue(true);
            }
        }
        return $errors;
    }

    public function test_core_test_methods_will_all_run() {
        $this->add_tests(__DIR__);
        $errors = $this->check_paths_for_assertions_in_non_test_methods();

        if (!empty($errors)) {
            $this->fail(implode(PHP_EOL, $errors));
        }
    }

    public function test_plugin_test_methods_will_all_run() {
        foreach (core_plugin_manager::instance()->get_plugin_types() as $type => $location) {
            $plugins = core_plugin_manager::instance()->get_plugins_of_type($type);
            foreach ($plugins as $pluginname => $class) {
                $dir = $class->rootdir . '/tests';
                if ($dir && is_dir($dir)) {
                    $this->add_tests($dir);
                }
            }
            $errors = $this->check_paths_for_assertions_in_non_test_methods();
        }
        if (!empty($errors)) {
            $this->fail(implode(PHP_EOL, $errors));
        }
    }
}