<?php
/**
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
 * @author Sam Hemelryk <sam.hemelryk@totara.com>
 * @package core
 */

class core_phpunit_naming_conventions_test extends \core_phpunit\testcase {

    private static array $known_classes = [];

    /**
     * @return array|Generator
     */
    public static function phpunit_file_locator(): iterable {
        global $CFG;

        $directories = [
            ['core', $CFG->libdir . '/tests'],
            // The following are defined in test/phpunit/phpunit.xml.dist as they do not match normal component locations.
            ['core_ddl', $CFG->libdir . '/ddl/tests'],
            ['core_dml', $CFG->libdir . '/dml/tests'],
            ['core_phpunit', $CFG->libdir . '/phpunit/tests'],
            ['core_test', $CFG->libdir . '/testing/tests'],
            ['core_files', $CFG->libdir . '/filestorage/tests'],
            ['core_grade', $CFG->libdir . '/grade/tests'],
            ['core_backup', $CFG->dirroot . '/backup/controller/tests'],
            ['core_backup', $CFG->dirroot . '/backup/converter/moodle1/tests'],
            ['core_backup', $CFG->dirroot . '/backup/moodle2/tests'],
            ['core_iplookup', $CFG->dirroot . '/iplookup/tests'],
            ['core_question', $CFG->dirroot . '/question/engine/tests'],
            ['core_question', $CFG->dirroot . '/question/type/tests'],
            ['core_external', $CFG->dirroot . '/lib/external/tests'],

        ];
        foreach (\core_component::get_core_subsystems() as $subsystem => $directory) {
            $directory_tests = $directory . DIRECTORY_SEPARATOR . 'tests';
            if (file_exists($directory_tests) && is_dir($directory_tests)) {
                $component = 'core_' . $subsystem;
                $directories[] = [$component, $directory_tests];
            }
        }

        foreach (\core_component::get_plugin_types() as $plugin_type => $plugin_type_directory) {
            $standardplugins = \core_plugin_manager::standard_plugins_list($plugin_type);
            foreach (\core_component::get_plugin_list($plugin_type) as $plugin_name => $plugin_directory) {
                if (!in_array($plugin_name, $standardplugins)) {
                    continue;
                }
                $directory_tests = $plugin_directory . DIRECTORY_SEPARATOR . 'tests';
                if (file_exists($directory_tests) && is_dir($directory_tests)) {
                    $component = $plugin_type . '_' . $plugin_name;
                    $directories[] = [$component, $directory_tests];
                }
            }
        }

        foreach ($directories as [$component, $path]) {
            $path = str_replace('/', DIRECTORY_SEPARATOR, $path);
            $directory = new \RecursiveDirectoryIterator($path);
            $iterator = new \RecursiveIteratorIterator($directory);
            foreach ($iterator as $info) {
                /** @var SplFileInfo $info */
                if (!$info->isFile()) {
                    continue;
                }
                if ($info->getExtension() !== 'php') {
                    continue;
                }
                yield [$info->getFilename(), $info->getPathname(), $info->getBasename('.php'), $component];
            }
        }
    }

    /**
     * @dataProvider phpunit_file_locator
     * @return void
     */
    public function test_file(string $filename, string $pathname, string $basename, string $component) {
        global $CFG;

        $expected_scenario = (substr($filename, -9) === '_test.php');
        $content = file_get_contents($pathname);

        /**
         * Actual class name matching occurs at \core_phpunit\testcase_autoloader::load()
         * This regex is slightly more rigid as it is aligned to best practice, not just what works.
         * It is also attempts to align to the future state including namespacing.
         * @see \core_phpunit\testcase_autoloader::load()
         */
        $regex_classname = '#(?<abstract>abstract\s+)?class\s+(?<classname>[a-zA-Z0-9]+(_[a-zA-Z0-9]+)*_test(case)?)\s+extends#';
        $relative_path = substr($pathname, strlen($CFG->srcroot) + 1);

        if (!$expected_scenario) {
            $count = preg_match($regex_classname, $content, $matches);
            if ($count) {
                $is_abstract = $matches[1];
                if (!$is_abstract) {
                    self::fail('PHPUnit file in tests directory appears to contain tests but is not correctly named => ' . "[{$component}] $relative_path");
                }
            }
            return;
        }

        $count = preg_match_all($regex_classname, $content, $matches);
        if ($count === 0) {
            self::fail('Class in PHPUnit test file is not named correctly; ' . "[{$component}] $relative_path");
        }
        if ($count > 1) {
            self::fail('PHPUnit test file contains multiple classes;  ' . "[{$component}] $relative_path");
        }
        $is_abstract = $matches['abstract'][0] ?? false;
        if ($is_abstract) {
            // Test files should be named `_test.php`
            // Abstract classes/fixtures should not be in a file ending with `_test.php`
            self::fail('PHPUnit test file contains an abstract class; ' . "[{$component}] $relative_path");
        }

        $classname = isset($matches['classname'][0]) ? trim($matches['classname'][0]) : false;
        if (!$classname) {
            self::fail('PHPUnit test file class name was not correctly identified.');
        }

        $namespace_regex = '#^namespace ([^;]+);$#m';
        if (preg_match($namespace_regex, $content, $matches)) {
            $namespace = $matches[1];
            self::fail('Namespaces are not currently permitted for PHPUnit testcases `' . $namespace . '` ' . $relative_path);
        }

        $expected_classname = $component . '_' . $basename;
        self::assertSame($expected_classname, $classname, 'PHPUnit test case class name does not conform to naming standards');

        $trimmed_content = ltrim($content, "\ \t\n\r\0\x0B");
        if ($trimmed_content !== $content) {
            self::fail('There is whitespace before the opening PHP tag in the PHPUnit testfile ' . $relative_path);
        }

        if (isset(self::$known_classes[$classname])) {
            // And you have successfully copied a class and forgotten to rename it.
            // PHPUnit loads all classes into runtime so you will encounter problems doing this.
            self::fail('Duplicate PHPUnit testcase class class name found ' . $classname);
        }
        self::$known_classes[$classname] = 1;

        require_once($pathname);
        $reflection_class = new ReflectionClass($classname);

        self::assertTrue($reflection_class->isSubclassOf('\core_phpunit\testcase'), 'PHPUnit class `'.$classname.'` does not extend the \core_phpunit\testcase class');

        $methods = $reflection_class->getMethods();
        $count_tests = 0;
        foreach ($methods as $method) {
            if (substr($method->getName(), 0, 5) === 'test_') {
                $count_tests ++;
            }
        }
        if ($count_tests === 0) {
            self::fail('The PHPUnit class `' . $classname . '` contains no tests.');
        }

        // Confirm the methods call their parents correctly
        $methods = ['tearDown', 'tearDownAfterClass'];
        foreach ($methods as $method_name) {
            // No method
            if (!$reflection_class->hasMethod($method_name)) {
                continue;
            }

            // Isn't defined in the active class (probably inherited)
            $method = $reflection_class->getMethod($method_name);
            if ($method->getDeclaringClass()->getName() !== $reflection_class->getName()) {
                continue;
            }

            $class_content = explode("\n", $content);
            $start = $method->getStartLine();
            $end = $method->getEndLine();
            $length = $end - $start;

            if (isset($class_content[$start])) {
                $class_content = array_slice($class_content, $start, $length);
                $calls_parent = false;
                foreach ($class_content as $line) {
                    if (str_contains($line, 'parent::' . $method_name)) {
                        $calls_parent = true;
                        break;
                    }
                }

                if (!$calls_parent) {
                    self::fail('The PHPUnit method `' . $classname . '::' . $method_name . '` does not contain a call to parent::' . $method_name . '()');
                }
            }
            unset($class_content);
        }
    }

    public static function tearDownAfterClass(): void {
        // Reset known classes back to an empty array to free up memory.
        self::$known_classes = [];

        parent::tearDownAfterClass();
    }
}
