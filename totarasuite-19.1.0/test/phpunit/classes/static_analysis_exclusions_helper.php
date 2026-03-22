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
 * @author Fabian Derschatta <fabian.derschatta@totara.com>
 * @package phpunit
 */

/**
 * This class can be used to create a list of files/directories included for coverage.
 * It also generates a list of files and directories to exlcude.
 *
 * So the trick here is that we also want to build exclusions list to get it
 * supplied to SonarQube as sonarQube does not support having coverage inclusions,
 * making the thing a bit trickier.
 */
class static_analysis_exclusions_helper {
    protected static $exclusions = [];

    public static function get_exclusions(?string $totara_path = null) {

        if (!$totara_path !== null) {
            // Let's strip path if set
            if (!str_ends_with($totara_path, '/')) {
                $totara_path .= '/';
            }

            return array_map(function ($path) use ($totara_path) {
                return str_replace($totara_path, '', $path);
            }, static::$exclusions);
        }

        return static::$exclusions;
    }

    public static function reset_exclusions() {
        static::$exclusions = [];
    }

    protected static function add_exclusion(string $what) {
        static::$exclusions[] = $what;
    }

    public static function get_iterator($path) {
        $directory_iterator = new RecursiveDirectoryIterator($path);
        $filter_iterator = new RecursiveCallbackFilterIterator(
            $directory_iterator,
            function ($current, $key, $iterator) {
                if ($iterator->hasChildren()) {
                    return true;
                }
                return ($current->getFilename() === 'thirdpartylibs.xml');
            }
        );
        $iterator = new RecursiveIteratorIterator($filter_iterator);
        $thirdparty_paths = [];
        foreach ($iterator as $thirdpartyfile) {
            $dir = dirname($thirdpartyfile);
            $contents = file_get_contents($thirdpartyfile);
            preg_match_all('#<location>([^<]+)</location>#', $contents, $matches, PREG_PATTERN_ORDER);
            foreach ($matches[1] as $library_path) {
                if (str_contains($library_path, '*')) {
                    continue;
                }
                $pos = strrpos($library_path, '.');
                if ($pos !== false) {
                    $extension = substr($library_path, -$pos);
                    if ($extension !== 'php') {
                        continue;
                    }
                }
                $thirdparty_paths[] = $dir . DIRECTORY_SEPARATOR . $library_path;
            }
        }

        $directory_iterator = new RecursiveDirectoryIterator($path);
        $filter_iterator = new RecursiveCallbackFilterIterator(
            $directory_iterator,
            function ($current, $key, $iterator) use ($path, $thirdparty_paths) {
                /** @var SplFileInfo $current */
                if ($current->isDir()) {
                    if (in_array($current->getFilename(), ['.git', '.idea', 'node_modules', 'vendor', 'pix'])) {
                        // Not totara.
                        static::add_exclusion($current->getRealPath() . '/**/*');
                        return false;
                    }
                    if ($current->getFilename() === 'install') {
                        // Get rid of install dir.
                        static::add_exclusion($current->getRealPath() . '/**/*');
                        return false;
                    }
                    if ($current->getFilename() === 'en' && str_contains($current->getRealPath(), '/lang/en')) {
                        // Get rid of language files.
                        static::add_exclusion($current->getRealPath() . '/**/*');
                        return false;
                    }
                    if ($current->getFilename() === 'xmldb' && str_contains($current->getRealPath(), '/admin/tool')) {
                        // We don't cover xmldb editor
                        static::add_exclusion($current->getRealPath() . '/**/*');
                        return false;
                    }
                    if ($current->getFilename() === 'tests') {
                        // We skip the tests directories.
                        static::add_exclusion($current->getRealPath() . '/**/*');
                        return false;
                    }
                    if (str_contains($current->getRealPath(), '/classes/')) {
                        // We include the whole classes directory.
                        return false;
                    }
                    // In order to allow reiteration.
                    return true;
                }
                if (str_contains($current->getRealPath(), '/classes/')) {
                    // We include the whole classes directory.
                    return false;
                }
                if ($current->getExtension() !== 'php') {
                    static::add_exclusion($current->getRealPath());
                    return false;
                }
                if (in_array($current->getRealPath(), [
                    $path . '/config-dist.php',
                    $path . '/version.php',
                    $path . '/settings.php',
                    $path . '/index.php',
                    $path . '/config.php',
                    $path . '/install.php',
                    $path . '/brokenfile.php',
                    $path . '/admin/cli/install.php',
                ])) {
                    static::add_exclusion($current->getRealPath());
                    return false;
                }
                foreach ($thirdparty_paths as $library_path) {
                    if (str_contains($current->getPath(), $library_path)) {
                        static::add_exclusion($current->getRealPath());
                        return false;
                    }
                }
                if (preg_match(
                    '#/db/(access|services|tag|upgrade|log|subplugins|events|tasks|install|hooks|messages|renamedclasses|totara_postupgrade)\.php#', $current->getRealPath()
                )) {
                    static::add_exclusion($current->getRealPath());
                    return false;
                }
                $contents = file_get_contents($current->getRealPath());
                if (strpos($current->getFilename(), '_form.php') > 1
                    && preg_match('#class +[^\s]+ +extends +(moodleform|\\totara\\form)#', $contents)
                ) {
                    // We skip moodle forms and totara forms. This check needs to be before the moodle internal check!
                    static::add_exclusion($current->getRealPath());
                    return false;
                }
                if (str_contains($contents, 'MOODLE_INTERNAL')) {
                    // It declares MOODLE_INTERNAL, its a library file.
                    return true;
                }
                if (preg_match('#(require|include|require_once|include_once)[^\n]+?config\.php#', $contents) > 0) {
                    // It appears to include config.php, goodbye.
                    static::add_exclusion($current->getRealPath());
                    return false;
                }
                if (str_contains($contents, "define('CLI_SCRIPT', true);")) {
                    // It's a cli script.
                    static::add_exclusion($current->getRealPath());
                    return false;
                }
                if (preg_match('#(require|include|require_once|include_once)[^\n]+?lib/clilib\.php#', $contents) > 0) {
                    // It appears to be a cli script that does not require config.php
                    static::add_exclusion($current->getRealPath());
                    return false;
                }
                return true;
            }
        );
        $iterator_iterator = new RecursiveIteratorIterator($filter_iterator);

        $iterator = new CallbackFilterIterator(
            $iterator_iterator,
            function ($current, $key, $iterator) {
                /** @var SplFileInfo $current */
                if ($current->isDir()) {
                    if ($current->getFilename() === '.' && str_ends_with($current->getRealPath(), 'classes')) {
                        return true;
                    }
                    return false;
                }
                return true;
            }
        );
        return $iterator;
    }
}