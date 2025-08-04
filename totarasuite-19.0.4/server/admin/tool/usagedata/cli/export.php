<?php
/**
 * This file is part of Totara Talent Experience Platform
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
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @author Aaron Machin <aaron.machin@totara.com>
 * @package tool_usagedata
 */

define('CLI_SCRIPT', true);

global $CFG;

require_once __DIR__ . '/../../../../config.php';
require_once $CFG->libdir . '/clilib.php';

use tool_usagedata\config;
use tool_usagedata\export;
use tool_usagedata\exporter;

[$options, $unrecognized] = cli_get_params(
    // Long options
    [
        'help' => false,
        'class' => false,
        'send' => false,
        'force' => false,
        'pretty' => false,
    ],
    // Short options
    [
        'h' => 'help',
        'c' => 'class',
        's' => 'send',
        'f' => 'force',
        'p' => 'pretty',
    ]
);

if ($unrecognized) {
    $unrecognized = implode("\n  ", $unrecognized);
    cli_error(get_string('cliunknowoption', 'admin', $unrecognized), 2);
}

if ($options['help']) {
    echo "
Exporter script to collate usagedata.
Default is to output collated JSON data to stdout.

Usage:
  Send to data collection service:
    php server/admin/tool/usagedata/cli/export.php --send
    
  Export to file:
    php server/admin/tool/usagedata/cli/export.php > file.json
    
  Inspect a single export class output (example given is course_count_of_configuration export class)
    php server/admin/tool/usagedata/cli/export.php --class=core_course\\\\usagedata\\\\course_count_of_configuration
    
Options:
    -h, --help              Print out this help message :)
    -c, --class             Exports a single export class namespace.
                            When exporting a single class, `--send` is ignored.
    -s, --send              Whether we want to send the exported data to the data collection service.
                            Explicit opt-in to avoid accidental exports
                            Ignored when `--class` is given.
    -f, --force             Ignore opt-out and force an export.
                            If `opt_out` is enabled for this site, this option will be required to override opt-out and run an export.
    -p, --pretty            Formatting option only affects output to stdout (does not affect format when --send[ing] to data collector)
";

    return;
}

if (config::is_opt_out_enabled() && !$options['force']) {
    echo "
This site has opt_out enabled.
You can either --force an export, or disable opt_out in configuration
It might be worth considering why opt_out was enabled before taking further actions!

";

    return;
}

if ($options['force']) {
    echo 'WARNING: You are using the --force option';
    echo "\n";
}

if ($options['class'] !== false) {
    $class = $options['class'];

    if (empty($class) || !class_exists($class)) {
        echo "The specified class was not found.\n";
        exit;
    }

    $export_class = new $class();

    if (!$export_class instanceof export) {
        echo "The class specified is not an export class (instance of tool_usagedata\\export)'.\n";
        exit;
    }

    echo json_encode($export_class->export(), $options['pretty'] ? JSON_PRETTY_PRINT : 0);
    exit;
}

$exporter = new exporter();

if ($options['send']) {
    try {
        $exporter->export_to_data_collector();
    } catch (Exception $e) {
        echo 'Ran into issue exporting: ' . $e->getMessage() . "\n";
        return;
    }

    echo "Exported!\n";
    return;
}

echo json_encode($exporter->export(), $options['pretty'] ? JSON_PRETTY_PRINT : 0);
