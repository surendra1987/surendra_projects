<?php
/**
 * This file is part of Totara Learn
 *
 * Copyright (C) 2022 onwards Totara Learning Solutions LTD
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
 * @author  Johannes Cilliers <johannes.cilliers@totaralearning.com>
 * @package tool_diagnostic
 */

use tool_diagnostic\config_json_provider;
use tool_diagnostic\manager;
use tool_diagnostic\provider\provider;

const CLI_SCRIPT = 1;

require_once __DIR__.'/../../../../config.php';

/** @var core_config $CFG */
require_once $CFG->libdir.'/clilib.php';

error_reporting(E_ALL | E_STRICT);
ini_set('display_errors', 1);

[$options, $unrecognized] = cli_get_params(
    [
        'help' => false,
        'list' => false,
    ],
    [
        'h' => 'help',
        'l' => 'list',
    ]
);

if ($unrecognized) {
    $unrecognized = implode("\n  ", $unrecognized);
    cli_error(get_string('cliunknowoption', 'admin', $unrecognized), 2);
}

if (!empty($options['help'])) {
    echo get_string('cli_help_text', 'tool_diagnostic');
    return 0;
}

$config_provider = new config_json_provider();

if (!empty($options['list'])) {
    $manager = new manager($config_provider, true);
    $providers = $manager->get_providers();
    /** @var provider $provider */
    foreach ($providers as $provider) {
        $provider_status_string = $provider->is_enabled()
            ? get_string('provider_enabled', 'tool_diagnostic')
            : get_string('provider_disabled', 'tool_diagnostic');

        echo str_pad($provider::get_id(), 30)
            . str_pad($provider_status_string, 20)
            . $provider::get_description() . PHP_EOL;
    }
    return 0;
}

$manager = new manager($config_provider);
$file_info = $manager->run_diagnostics();

echo get_string(
    'file_created',
    'tool_diagnostic',
    $file_info['download_url']
);
echo PHP_EOL;

echo get_string(
    'file_created_cli',
    'tool_diagnostic',
    $file_info['download_url']
);
echo PHP_EOL;
