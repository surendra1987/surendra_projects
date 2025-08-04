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

define('CLI_SCRIPT', true);
define('CACHE_DISABLE_ALL', true);

require(__DIR__.'/../../server/config.php');
require(__DIR__.'/classes/static_analysis_exclusions_helper.php');
require_once($CFG->libdir.'/clilib.php');      // cli only functions

$totara_path = $CFG->srcroot;

// now get cli options
list($options, $unrecognized) = cli_get_params(
    [
        'help' => false,
        'sonar-qube' => false,
    ],
    [
        'h' => 'help',
        'sq' => 'sonar-qube',
    ]
);

if ($options['help']) {
    $help =
        "Generate a map for PHPUnit code coverage with all files/directories to include or exclude and 
        update the phpunit.xml file, adding or replacing the current <coverage> tag.
        If you specify a path it will only create the map for that folder otherwise for the whole project.

Options:
-sq, --sonar-qube Only prints a list of exclusions to be used by SonarQube coverage and does not update the phpunit.xml file
-h, --help        Print out this help

Example:
\$php test/phpunit/generate-coverage-map.php 
\$php test/phpunit/generate-coverage-map.php server/totara/api
";

    echo $help;
    die;
}

$for_sonar_qube = $options['sonar-qube'] ?? false;
$print_progress = !$for_sonar_qube;

$xml_path = "${totara_path}/test/phpunit/phpunit.xml";
$coverage_path = "{$totara_path}/server";

// Now let's check if someone added a path
if (!empty($unrecognized)) {
    if (count($unrecognized) > 1) {
        cli_error("Please specify either no path or only one, e.g. php test/phpunit/generate-coverage-map.php server/totara/api");
    }

    $coverage_path = $unrecognized[0];
    if (strpos($unrecognized[0], $totara_path) === false) {
        $coverage_path = $totara_path.'/'.$coverage_path;
    }

    if (!is_dir($coverage_path)) {
        cli_error("Invalid path specified. Please either specify a relative of absolute path");
    }

    $unrecognized = [];
}

if (!empty($unrecognized)) {
    $unrecognized = implode("\n  ", $unrecognized);
    cli_error(get_string('cliunknowoption', 'admin', $unrecognized));
}

if ($print_progress) {
    cli_writeln('Creating phpunit code coverage whitelist');
}

if ($print_progress) {
    cli_write('Identifying files and directories to whitelist');
}

$files = [];
foreach (static_analysis_exclusions_helper::get_iterator($coverage_path) as $path) {
    $files[$path->getRealPath()] = $path;
}

if ($print_progress) {
    cli_writeln('... found ' . count($files));
}

if ($for_sonar_qube) {
    // Now let's print a list of exclusions to be used by SonarQube coverage
    echo json_encode(array_unique(static_analysis_exclusions_helper::get_exclusions($totara_path)), JSON_PRETTY_PRINT);
    echo PHP_EOL;
    // Exiting without updating phpunit coverage whitelist
    exit(0);
}

if (!file_exists($xml_path)) {
    cli_error("phpunit.xml must be present in order to generate coverage whitelist".PHP_EOL."> {$xml_path}");
}

if ($print_progress) {
    cli_writeln('Parsing phpunit.xml file');
}
$xml = new DOMDocument();
$xml->preserveWhiteSpace = false;
$xml->formatOutput = true;
$loaded = $xml->load($xml_path);
if ($loaded === false) {
    cli_error("Failed to parse phpunit.xml");
}

if ($print_progress) {
    cli_write('Writing XML file with whitelist');
}
$root_node = $xml->getElementsByTagName('phpunit')->item(0);
$coverage = $xml->getElementsByTagName('coverage');
if ($coverage->count() > 0) {
    $coverage_node = $coverage->item(0);
    $coverage->item(0)->parentNode->removeChild($coverage_node);
}
$coverage_element = $xml->createElement('coverage');
$coverage_element->setAttribute('cacheDirectory', realpath(__DIR__.'/coverage/cache'));
$coverage_node = $root_node->appendChild($coverage_element);
$include_element = $xml->createElement('include');
$include_node = $coverage_node->appendChild($include_element);
$exclude_element = $xml->createElement('exclude');
$exclude_node = $coverage_node->appendChild($exclude_element);

foreach ($files as $file) {
    /** @var SplFileInfo $file */
    if ($file->isDir()) {
        $include_child_element = $xml->createElement('directory');
        $include_child_element->setAttribute('suffix', '.php');
        $include_child_element->textContent = $file->getRealPath();
        $include_node->appendChild($include_child_element);
        if ($file->getFilename() === '.' && str_ends_with($file->getRealPath(), 'classes')) {
            // Exclude autoloaded forms namespace. If you are putthing things other than forms in here then change your code!
            $exclude_child_element = $xml->createElement('directory');
            $include_child_element->setAttribute('suffix', '.php');
            $exclude_child_element->textContent = $file->getRealPath() . DIRECTORY_SEPARATOR . 'form';
            $exclude_node->appendChild($exclude_child_element);
        }
    } else {
        $include_child_element = $xml->createElement('file');
        $include_child_element->textContent = $file->getRealPath();
        $include_node->appendChild($include_child_element);
    }
}

$xml->save($xml_path);

if ($print_progress) {
    cli_writeln('... done' . PHP_EOL);
}
